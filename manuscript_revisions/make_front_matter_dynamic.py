import re
from copy import deepcopy
from pathlib import Path
from tempfile import NamedTemporaryFile
from zipfile import ZIP_DEFLATED, ZipFile

from lxml import etree


SOURCE = Path(
    r"C:\xampp\htdocs\capstone-laravel-web\manuscript_revisions"
    r"\Harviana_Maam_Nats_Structurally_Revised.docx"
)
OUTPUT = Path(
    r"C:\xampp\htdocs\capstone-laravel-web\manuscript_revisions"
    r"\Harviana_Maam_Nats_Final_Revised.docx"
)

W = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
NS = {"w": W}
XML_SPACE = "{http://www.w3.org/XML/1998/namespace}space"


def qn(tag: str) -> str:
    return f"{{{W}}}{tag}"


def paragraph_text(paragraph) -> str:
    return " ".join(
        " ".join(paragraph.xpath(".//w:t/text()", namespaces=NS)).split()
    )


def direct_body_paragraphs(body):
    return [child for child in body if child.tag == qn("p")]


def find_paragraph(paragraphs, text: str, *, exact: bool = True, last: bool = True):
    matches = []
    for paragraph in paragraphs:
        value = paragraph_text(paragraph)
        if (exact and value == text) or (not exact and value.startswith(text)):
            matches.append(paragraph)
    if not matches:
        raise ValueError(f"Target paragraph not found: {text!r}")
    return matches[-1] if last else matches[0]


def find_numbered_label(paragraphs, label: str, number: int):
    matches = []
    pattern = re.compile(rf"^{re.escape(label)}\s*((?:\d\s*)+)$", re.IGNORECASE)
    for paragraph in paragraphs:
        value = paragraph_text(paragraph)
        match = pattern.match(value)
        if match and int(re.sub(r"\s+", "", match.group(1))) == number:
            matches.append(paragraph)
    if not matches:
        raise ValueError(f"Numbered label not found: {label} {number}")
    return matches[-1]


def add_bookmark(paragraph, bookmark_id: int, bookmark_name: str) -> None:
    start = etree.Element(qn("bookmarkStart"))
    start.set(qn("id"), str(bookmark_id))
    start.set(qn("name"), bookmark_name)
    end = etree.Element(qn("bookmarkEnd"))
    end.set(qn("id"), str(bookmark_id))

    ppr = paragraph.find(qn("pPr"))
    insert_at = 1 if ppr is not None else 0
    paragraph.insert(insert_at, start)
    paragraph.append(end)


def field_run(kind: str, value: str | None = None, rpr=None):
    run = etree.Element(qn("r"))
    if rpr is not None:
        run.append(deepcopy(rpr))
    if kind == "fldChar":
        element = etree.SubElement(run, qn("fldChar"))
        element.set(qn("fldCharType"), value)
    elif kind == "instrText":
        element = etree.SubElement(run, qn("instrText"))
        element.set(XML_SPACE, "preserve")
        element.text = value
    elif kind == "text":
        element = etree.SubElement(run, qn("t"))
        element.text = value
    else:
        raise ValueError(kind)
    return run


def replace_page_number(paragraph, bookmark_name: str) -> str:
    text_nodes = paragraph.xpath(".//w:t", namespaces=NS)
    matched_node = None
    cached_value = "1"
    for node in reversed(text_nodes):
        match = re.search(r"(\d+)\s*$", node.text or "")
        if match:
            matched_node = node
            cached_value = match.group(1)
            node.text = (node.text or "")[: match.start(1)]
            break
    if matched_node is None:
        raise ValueError(
            f"No trailing page number in entry: {paragraph_text(paragraph)!r}"
        )

    source_run = matched_node.getparent()
    rpr = source_run.find(qn("rPr"))
    insertion_index = paragraph.index(source_run) + 1
    field_runs = [
        field_run("fldChar", "begin", rpr),
        field_run("instrText", f" PAGEREF {bookmark_name} \\h ", rpr),
        field_run("fldChar", "separate", rpr),
        field_run("text", cached_value, rpr),
        field_run("fldChar", "end", rpr),
    ]
    for offset, run in enumerate(field_runs):
        paragraph.insert(insertion_index + offset, run)
    return cached_value


def find_first_paragraph_in_table(table, text: str):
    for paragraph in table.xpath(".//w:p", namespaces=NS):
        if paragraph_text(paragraph) == text:
            return paragraph
    raise ValueError(f"Table paragraph not found: {text!r}")


def main() -> None:
    with ZipFile(SOURCE, "r") as archive:
        package = {name: archive.read(name) for name in archive.namelist()}

    document = etree.fromstring(package["word/document.xml"])
    body = document.find(qn("body"))
    paragraphs = direct_body_paragraphs(body)

    # Reserve bookmark IDs beyond any existing bookmark.
    existing_ids = [
        int(node.get(qn("id")))
        for node in document.xpath("//w:bookmarkStart", namespaces=NS)
        if (node.get(qn("id")) or "").isdigit()
    ]
    next_id = max(existing_ids, default=0) + 1
    bookmarks: dict[str, str] = {}

    def bookmark_target(name: str, target_paragraph) -> None:
        nonlocal next_id
        add_bookmark(target_paragraph, next_id, name)
        bookmarks[name] = name
        next_id += 1

    # Major section and subsection targets.
    target_specs = {
        "approval": ("APPROVAL SHEET", True),
        "abstract": ("ABSTRACT", True),
        "acknowledgement": ("A CKNOWLEDGEMENT", True),
        "dedication": ("DEDICATION", True),
        "toc": ("TABLE OF CONTENTS", True),
        "lof": ("LIST OF FIGURES", True),
        "lot": ("LIST OF TABLES", True),
        "background": ("Background of the Study", True),
        "importance": ("Importance of the Study", True),
        "objectives": ("Objectives of the Study", True),
        "definitions": ("Definition of Terms", True),
        "software_methodology": ("Software Development Methodology", True),
        "design_thinking": ("Design Thinking", True),
        "scope": ("Scope and Delimitation of the Study", True),
        "data_gathering": ("Data Gathering Techniques", True),
        "sources": ("Sources of Data", True),
        "usability_instruments": ("Usability Evaluation Instruments", True),
        "software_tools": ("Software Development Tools", True),
        "information_requirements": ("Information Requirements for Harviana", True),
        "architecture": ("Architecture Framework of Harviana", True),
        "features": ("Features of Harviana", True),
        "extent_usability": ("Extent of the Usability of Harviana", True),
        "conclusions": ("Chapter 4", True),
        "recommendations": ("Recommendations", True),
        "references": ("REFERENCES", True),
        "appendix_a": ("Appendix A", True),
        "appendix_b": ("Appendix B", True),
        "appendix_c": ("Appendix C", True),
        "appendix_d": ("Appendix D", True),
        "appendix_e": ("Appendix E", True),
        "appendix_f": ("Appendix F", True),
        "appendix_g": ("Appendix G", False),
        "appendix_h": ("Appendix H", True),
        "appendix_i": ("Appendix I", False),
        "appendix_j": ("Appendix J", False),
        "appendix_k": ("A ppendix K", False),
    }
    for bookmark_name, (target_text, exact) in target_specs.items():
        bookmark_target(
            bookmark_name,
            find_paragraph(paragraphs, target_text, exact=exact, last=True),
        )

    # Curriculum Vitae begins in the first CV table.
    body_tables = [child for child in body if child.tag == qn("tbl")]
    curriculum_target = find_first_paragraph_in_table(
        body_tables[14], "CURRICULUM VITAE"
    )
    bookmark_target("curriculum_vitae", curriculum_target)

    # Figure and table caption targets.
    for number in range(1, 39):
        target = find_numbered_label(paragraphs, "Figure", number)
        bookmark_target(f"figure_{number}", target)
    for number in range(1, 15):
        target = find_numbered_label(paragraphs, "Table", number)
        bookmark_target(f"table_{number}", target)

    # Front-matter boundaries.
    toc_heading = find_paragraph(paragraphs, "TABLE OF CONTENTS", last=False)
    lof_heading = find_paragraph(paragraphs, "LIST OF FIGURES", last=False)
    lot_heading = find_paragraph(paragraphs, "LIST OF TABLES", last=False)
    chapter1_heading = find_paragraph(paragraphs, "Ch apter 1", last=True)

    toc_start = paragraphs.index(toc_heading)
    lof_start = paragraphs.index(lof_heading)
    lot_start = paragraphs.index(lot_heading)
    chapter1_start = paragraphs.index(chapter1_heading)

    toc_entry_map = {
        "APPROVAL SHEET": "approval",
        "ABSTRACT": "abstract",
        "ACKNOWLEDGEMENT": "acknowledgement",
        "DEDICATION": "dedication",
        "TABLE OF CONTENTS": "toc",
        "LIST OF FIGURES": "lof",
        "LIST OF TABLES": "lot",
        "Background of the Study": "background",
        "Importance of the Study": "importance",
        "Objectives of the Study": "objectives",
        "Definition of Terms": "definitions",
        "Software Development Methodology": "software_methodology",
        "Design Thinking": "design_thinking",
        "Scope and Delimitation of the Study": "scope",
        "Data Gathering Techniques": "data_gathering",
        "Sources of Data": "sources",
        "Usability Evaluation Instruments": "usability_instruments",
        "Software Development Tools": "software_tools",
        "Information Requirements for Harviana": "information_requirements",
        "Architecture Framework of Harviana": "architecture",
        "Features of Harviana": "features",
        "Extent of the Usability of Harviana": "extent_usability",
        "Conclusions": "conclusions",
        "Recommendations": "recommendations",
        "REFERENCES": "references",
        "Communication Letter": "appendix_a",
        "Endorsement Letter": "appendix_b",
        "Business Model Canvas": "appendix_c",
        "Invitation Letter": "appendix_d",
        "TBI Assessment Certificate": "appendix_e",
        "Sample Usability Test Survey": "appendix_f",
        "Technology Brief": "appendix_g",
        "Documentation": "appendix_h",
        "Supporting Interfaces of Harviana": "appendix_i",
        "Relevant Code Snippets": "appendix_j",
        "User Manual": "appendix_k",
        "CURRICULUM VITAE": "curriculum_vitae",
    }

    for paragraph in paragraphs[toc_start + 1 : lof_start]:
        value = paragraph_text(paragraph)
        for marker, bookmark_name in toc_entry_map.items():
            if marker in value and re.search(r"\d+\s*$", value):
                replace_page_number(paragraph, bookmark_name)
                break

    # List of Figures: continuation lines inherit the most recent figure number.
    active_figure = None
    for paragraph in paragraphs[lof_start + 1 : lot_start]:
        value = paragraph_text(paragraph)
        match = re.match(r"^(\d{1,2})\b", value)
        if match:
            active_figure = int(match.group(1))
        if active_figure and re.search(r"\d+\s*$", value):
            replace_page_number(paragraph, f"figure_{active_figure}")
            active_figure = None

    # List of Tables follows the same continuation-line rule.
    active_table = None
    for paragraph in paragraphs[lot_start + 1 : chapter1_start]:
        value = paragraph_text(paragraph)
        match = re.match(r"^(\d{1,2})\b", value)
        if match:
            active_table = int(match.group(1))
        if active_table and re.search(r"\d+\s*$", value):
            replace_page_number(paragraph, f"table_{active_table}")
            active_table = None

    # Ask Word to refresh PAGEREF and PAGE fields when the file is opened.
    settings = etree.fromstring(package["word/settings.xml"])
    update_fields = settings.find(qn("updateFields"))
    if update_fields is None:
        update_fields = etree.SubElement(settings, qn("updateFields"))
    update_fields.set(qn("val"), "true")

    package["word/document.xml"] = etree.tostring(
        document, xml_declaration=True, encoding="UTF-8", standalone=True
    )
    package["word/settings.xml"] = etree.tostring(
        settings, xml_declaration=True, encoding="UTF-8", standalone=True
    )

    with NamedTemporaryFile(delete=False, suffix=".docx") as temporary:
        temp_path = Path(temporary.name)
    with ZipFile(temp_path, "w", ZIP_DEFLATED) as archive:
        for name, data in package.items():
            archive.writestr(name, data)

    OUTPUT.write_bytes(temp_path.read_bytes())
    temp_path.unlink()
    print(OUTPUT)


if __name__ == "__main__":
    main()
