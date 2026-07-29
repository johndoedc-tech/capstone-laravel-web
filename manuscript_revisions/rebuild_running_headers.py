from copy import deepcopy
from pathlib import Path
from tempfile import NamedTemporaryFile
from zipfile import ZIP_DEFLATED, ZipFile

from lxml import etree


SOURCE = Path(
    r"C:\xampp\htdocs\capstone-laravel-web\manuscript_revisions"
    r"\Harviana_Maam_Nats_Content_Revised.docx"
)
OUTPUT = Path(
    r"C:\xampp\htdocs\capstone-laravel-web\manuscript_revisions"
    r"\Harviana_Maam_Nats_Structurally_Revised.docx"
)

W = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
R = "http://schemas.openxmlformats.org/officeDocument/2006/relationships"
PR = "http://schemas.openxmlformats.org/package/2006/relationships"
CT = "http://schemas.openxmlformats.org/package/2006/content-types"
NS = {"w": W, "r": R}


def qn(namespace: str, tag: str) -> str:
    return f"{{{namespace}}}{tag}"


def element_text(element) -> str:
    return " ".join(
        " ".join(element.xpath(".//w:t/text()", namespaces=NS)).split()
    )


def find_body_paragraph(body, prefix: str, *, exact: bool = False):
    for child in body:
        if child.tag == qn(W, "p"):
            text = element_text(child)
            if (exact and text == prefix) or (not exact and text.startswith(prefix)):
                return child
    raise ValueError(f"Body paragraph not found: {prefix!r}")


def find_body_table(body, table_number: int):
    count = 0
    for child in body:
        if child.tag == qn(W, "tbl"):
            count += 1
            if count == table_number:
                return child
    raise ValueError(f"Body table not found: {table_number}")


def nearest_previous_sectpr(target):
    previous = target.getprevious()
    while previous is not None:
        if previous.tag == qn(W, "p"):
            sect = previous.find(f"./{qn(W, 'pPr')}/{qn(W, 'sectPr')}")
            if sect is not None:
                return sect
        previous = previous.getprevious()
    raise ValueError("No preceding section properties found")


def set_header_references(sectpr, default_rid: str, first_rid: str) -> None:
    for ref in list(sectpr.findall(qn(W, "headerReference"))):
        sectpr.remove(ref)

    default = etree.Element(qn(W, "headerReference"))
    default.set(qn(W, "type"), "default")
    default.set(qn(R, "id"), default_rid)

    first = etree.Element(qn(W, "headerReference"))
    first.set(qn(W, "type"), "first")
    first.set(qn(R, "id"), first_rid)

    insert_at = 0
    sectpr.insert(insert_at, default)
    sectpr.insert(insert_at + 1, first)

    if sectpr.find(qn(W, "titlePg")) is None:
        sectpr.append(etree.Element(qn(W, "titlePg")))


def make_section_break(sectpr_template):
    paragraph = etree.Element(qn(W, "p"))
    ppr = etree.SubElement(paragraph, qn(W, "pPr"))
    ppr.append(deepcopy(sectpr_template))
    return paragraph


def clear_page_break_before(paragraph) -> None:
    if paragraph.tag != qn(W, "p"):
        return
    ppr = paragraph.find(qn(W, "pPr"))
    if ppr is None:
        return
    for node in list(ppr.findall(qn(W, "pageBreakBefore"))):
        ppr.remove(node)


def clone_header(package: dict[str, bytes], source_number: int, label: str) -> bytes:
    root = etree.fromstring(package[f"word/header{source_number}.xml"])
    text_nodes = root.xpath(".//w:t", namespaces=NS)
    if not text_nodes:
        raise ValueError(f"Header {source_number} has no text nodes")
    text_nodes[0].text = f"{label} "
    return etree.tostring(
        root, xml_declaration=True, encoding="UTF-8", standalone=True
    )


def next_relationship_id(rels_root) -> str:
    numbers = []
    for relationship in rels_root:
        rid = relationship.get("Id", "")
        if rid.startswith("rId") and rid[3:].isdigit():
            numbers.append(int(rid[3:]))
    return f"rId{max(numbers, default=0) + 1}"


def add_header_relationship(rels_root, target: str) -> str:
    rid = next_relationship_id(rels_root)
    relationship = etree.SubElement(rels_root, qn(PR, "Relationship"))
    relationship.set("Id", rid)
    relationship.set(
        "Type",
        "http://schemas.openxmlformats.org/officeDocument/2006/relationships/header",
    )
    relationship.set("Target", target)
    return rid


def ensure_header_content_type(content_types_root, part_name: str) -> None:
    for override in content_types_root.findall(qn(CT, "Override")):
        if override.get("PartName") == part_name:
            return
    override = etree.SubElement(content_types_root, qn(CT, "Override"))
    override.set("PartName", part_name)
    override.set(
        "ContentType",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml",
    )


def main() -> None:
    with ZipFile(SOURCE, "r") as archive:
        package = {name: archive.read(name) for name in archive.namelist()}

    document_root = etree.fromstring(package["word/document.xml"])
    body = document_root.find(qn(W, "body"))
    document_rels = etree.fromstring(package["word/_rels/document.xml.rels"])
    content_types = etree.fromstring(package["[Content_Types].xml"])

    # Replace the page-specific IF-field headers with ordinary PAGE fields.
    package["word/header20.xml"] = clone_header(
        package, 22, "Findings of the Study"
    )
    package["word/_rels/header20.xml.rels"] = package[
        "word/_rels/header22.xml.rels"
    ]
    package["word/header32.xml"] = clone_header(
        package, 24, "Curriculum Vitae"
    )
    package["word/_rels/header32.xml.rels"] = package[
        "word/_rels/header24.xml.rels"
    ]
    package["word/header12.xml"] = clone_header(
        package, 22, "Table of Contents"
    )
    package["word/_rels/header12.xml.rels"] = package[
        "word/_rels/header22.xml.rels"
    ]

    # Create a standard Chapter 4 continuation header.
    chapter4_header_number = 34
    chapter4_header_name = f"header{chapter4_header_number}.xml"
    package[f"word/{chapter4_header_name}"] = clone_header(
        package, 22, "Conclusions and Recommendations"
    )
    package[f"word/_rels/{chapter4_header_name}.rels"] = package[
        "word/_rels/header22.xml.rels"
    ]
    chapter4_header_rid = add_header_relationship(
        document_rels, chapter4_header_name
    )
    ensure_header_content_type(
        content_types, f"/word/{chapter4_header_name}"
    )

    # Create a standard List of Figures continuation header.
    list_figures_header_number = 35
    list_figures_header_name = f"header{list_figures_header_number}.xml"
    package[f"word/{list_figures_header_name}"] = clone_header(
        package, 22, "List of Figures"
    )
    package[f"word/_rels/{list_figures_header_name}.rels"] = package[
        "word/_rels/header22.xml.rels"
    ]
    list_figures_header_rid = add_header_relationship(
        document_rels, list_figures_header_name
    )
    ensure_header_content_type(
        content_types, f"/word/{list_figures_header_name}"
    )

    # Split the Table of Contents and List of Figures into separate sections,
    # removing their former page-specific conditional header.
    list_figures = find_body_paragraph(body, "LIST OF FIGURES", exact=True)
    list_tables = find_body_paragraph(body, "LIST OF TABLES", exact=True)
    front_matter_end_sectpr = nearest_previous_sectpr(list_tables)
    toc_break = make_section_break(front_matter_end_sectpr)
    list_figures.addprevious(toc_break)
    inserted_toc_sectpr = toc_break.find(
        f"./{qn(W, 'pPr')}/{qn(W, 'sectPr')}"
    )
    set_header_references(inserted_toc_sectpr, "rId26", "rId27")
    set_header_references(
        front_matter_end_sectpr, list_figures_header_rid, "rId27"
    )

    # Split Chapter 3 and Chapter 4 into separate sections.
    chapter4 = find_body_paragraph(body, "Chapter 4")
    references = find_body_paragraph(body, "REFERENCES", exact=True)
    chapter3_end_sectpr = nearest_previous_sectpr(references)
    chapter3_break = make_section_break(chapter3_end_sectpr)
    chapter4.addprevious(chapter3_break)
    clear_page_break_before(chapter4)

    # The inserted break keeps the Chapter 3 header; the existing end break now
    # governs Chapter 4 and receives the Chapter 4 header.
    inserted_chapter3_sectpr = chapter3_break.find(
        f"./{qn(W, 'pPr')}/{qn(W, 'sectPr')}"
    )
    set_header_references(inserted_chapter3_sectpr, "rId75", "rId76")
    set_header_references(
        chapter3_end_sectpr, chapter4_header_rid, "rId76"
    )

    # Split Appendices E–K into independent sections. Appendix E already begins
    # a section; each inserted break closes the preceding appendix.
    final_sectpr = body.find(qn(W, "sectPr"))
    appendix_sectpr = deepcopy(final_sectpr)
    set_header_references(appendix_sectpr, "rId81", "rId83")

    appendix_targets = [
        find_body_paragraph(body, "Appendix F"),
        find_body_paragraph(body, "Appendix G"),
        find_body_paragraph(body, "Appendix H"),
        find_body_paragraph(body, "Appendix I"),
        find_body_paragraph(body, "Appendix J"),
        find_body_paragraph(body, "A ppendix K"),
        find_body_table(body, 15),  # First Curriculum Vitae table
    ]

    for target in appendix_targets:
        break_paragraph = make_section_break(appendix_sectpr)
        target.addprevious(break_paragraph)
        clear_page_break_before(target)

    # The final section is Curriculum Vitae.
    set_header_references(final_sectpr, "rId126", "rId127")

    package["word/document.xml"] = etree.tostring(
        document_root,
        xml_declaration=True,
        encoding="UTF-8",
        standalone=True,
    )
    package["word/_rels/document.xml.rels"] = etree.tostring(
        document_rels,
        xml_declaration=True,
        encoding="UTF-8",
        standalone=True,
    )
    package["[Content_Types].xml"] = etree.tostring(
        content_types,
        xml_declaration=True,
        encoding="UTF-8",
        standalone=True,
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
