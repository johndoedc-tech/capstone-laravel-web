from __future__ import annotations

import copy
import pathlib
import re
import sys
import zipfile

from lxml import etree


W = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
R = "http://schemas.openxmlformats.org/officeDocument/2006/relationships"
PR = "http://schemas.openxmlformats.org/package/2006/relationships"
XML = "http://www.w3.org/XML/1998/namespace"
NS = {"w": W, "r": R}


def qn(namespace: str, local: str) -> str:
    return f"{{{namespace}}}{local}"


def make_run(rpr: etree._Element | None, child: etree._Element) -> etree._Element:
    run = etree.Element(qn(W, "r"))
    if rpr is not None:
        run.append(copy.deepcopy(rpr))
    run.append(child)
    return run


def fld_char(kind: str) -> etree._Element:
    node = etree.Element(qn(W, "fldChar"))
    node.set(qn(W, "fldCharType"), kind)
    return node


def instr(text: str) -> etree._Element:
    node = etree.Element(qn(W, "instrText"))
    node.set(qn(XML, "space"), "preserve")
    node.text = text
    return node


def text_node(text: str) -> etree._Element:
    node = etree.Element(qn(W, "t"))
    if text.startswith(" ") or text.endswith(" "):
        node.set(qn(XML, "space"), "preserve")
    node.text = text
    return node


def append_page_field(paragraph: etree._Element, rpr: etree._Element | None, cached: str) -> None:
    paragraph.append(make_run(rpr, fld_char("begin")))
    paragraph.append(make_run(rpr, instr(" PAGE ")))
    paragraph.append(make_run(rpr, fld_char("separate")))
    paragraph.append(make_run(rpr, text_node(cached)))
    paragraph.append(make_run(rpr, fld_char("end")))


def append_conditional_header(
    paragraph: etree._Element,
    rpr: etree._Element | None,
    page: int,
    label: str,
) -> None:
    # { IF { PAGE } = n "Label { PAGE }" "" }
    paragraph.append(make_run(rpr, fld_char("begin")))
    paragraph.append(make_run(rpr, instr(" IF ")))
    append_page_field(paragraph, rpr, str(page))
    paragraph.append(make_run(rpr, instr(f' = {page} "{label} ')))
    append_page_field(paragraph, rpr, str(page))
    paragraph.append(make_run(rpr, instr('" "" ')))
    paragraph.append(make_run(rpr, fld_char("separate")))
    # Keep the stored result empty; Word evaluates the field for every page.
    paragraph.append(make_run(rpr, text_node("")))
    paragraph.append(make_run(rpr, fld_char("end")))


def build_conditional_header(template_xml: bytes, conditions: list[tuple[int, str]]) -> bytes:
    root = etree.fromstring(template_xml)
    paragraphs = root.xpath("./w:p", namespaces=NS)
    if not paragraphs:
        raise RuntimeError("Template header has no paragraph.")
    paragraph = paragraphs[0]
    ppr = paragraph.find(qn(W, "pPr"))
    first_run = paragraph.find(qn(W, "r"))
    rpr = first_run.find(qn(W, "rPr")) if first_run is not None else None
    for child in list(paragraph):
        if child is not ppr:
            paragraph.remove(child)
    for page, label in conditions:
        append_conditional_header(paragraph, rpr, page, label)
    return etree.tostring(root, xml_declaration=True, encoding="UTF-8", standalone="yes")


def paragraph_text(paragraph: etree._Element) -> str:
    pieces: list[str] = []
    for node in paragraph.iter():
        if node.tag == qn(W, "t") and node.text:
            pieces.append(node.text)
        elif node.tag == qn(W, "tab"):
            pieces.append("\t")
        elif node.tag == qn(W, "br"):
            pieces.append("\n")
    return "".join(pieces)


def replace_trailing_number(paragraph: etree._Element, number: int) -> None:
    text_nodes = paragraph.xpath(".//w:t", namespaces=NS)
    combined = "".join(node.text or "" for node in text_nodes)
    match = re.search(r"\d+\s*$", combined)
    if match:
        replacement_inserted = False
        cursor = 0
        for node in text_nodes:
            value = node.text or ""
            node_start = cursor
            node_end = cursor + len(value)
            cursor = node_end
            if node_end <= match.start() or node_start >= match.end():
                continue
            local_start = max(0, match.start() - node_start)
            local_end = min(len(value), match.end() - node_start)
            prefix = value[:local_start]
            suffix = value[local_end:]
            inserted = ""
            if not replacement_inserted:
                inserted = str(number)
                replacement_inserted = True
            node.text = prefix + inserted + suffix
        return

    # The Appendix M TOC entry had no page number. Add one using the last
    # run's formatting so the inserted value matches the surrounding entry.
    runs = paragraph.xpath("./w:r", namespaces=NS)
    run = etree.Element(qn(W, "r"))
    if runs:
        previous_rpr = runs[-1].find(qn(W, "rPr"))
        if previous_rpr is not None:
            run.append(copy.deepcopy(previous_rpr))
    run.append(text_node(f"  {number}"))
    paragraph.append(run)


def update_toc(document_xml: bytes) -> bytes:
    root = etree.fromstring(document_xml)
    paragraphs = root.xpath(".//w:body/w:p", namespaces=NS)
    start = next(i for i, p in enumerate(paragraphs) if paragraph_text(p).strip() == "TABLE OF CONTENTS")
    end = next(
        i
        for i in range(start + 1, len(paragraphs))
        if paragraph_text(paragraphs[i]).strip() == "LIST OF FIGURES"
    )

    updates = [
        ("LIST OF FIGURES .", 12),
        ("LIST OF TABLES .", 14),
        ("Background of the Study", 15),
        ("Importance of the Study", 28),
        ("Objectives of the Study", 31),
        ("Definition of Terms", 32),
        ("Software Development Methodology", 37),
        ("Design Thinking", 44),
        ("Scope and Delimitation", 63),
        ("Data Gathering Techniques", 65),
        ("Sources of Data", 67),
        ("Software Development Tools", 69),
        ("Information Requirements for Harviana", 74),
        ("Architecture Framework of Harviana", 78),
        ("Features of Harviana", 91),
        ("Extent of the Usability of Harviana", 102),
        ("Conclusions .", 118),
        ("Recommendations .", 120),
        ("REFERENCES", 122),
        ("Communication Letter", 129),
        ("Endorsement Letter", 130),
        ("Business Model Canvas", 131),
        ("Invitation Letter", 133),
        ("Sample Usability Test Survey", 136),
        ("Technology Brief", 137),
        ("Documentation", 138),
        ("Supporting Interfaces of Harviana", 139),
        ("Code Snippet for Shared Recommendation", 141),
        ("Code Snippet for ML-Based Production", 142),
        ("Code Snippet for Interactive Agricultural", 143),
        ("Code Snippet for  Crop Data Management", 144),
        ("Code Snippet for Actual-Harvest Record", 145),
        ("User Manual", 146),
        ("CURRICULUM VITAE", 149),
    ]

    matched: set[str] = set()
    for paragraph in paragraphs[start:end]:
        text = paragraph_text(paragraph)
        for needle, number in updates:
            if needle in text:
                replace_trailing_number(paragraph, number)
                matched.add(needle)
                break

    missing = [needle for needle, _ in updates if needle not in matched]
    if missing:
        raise RuntimeError(f"TOC entries not found: {missing}")
    return etree.tostring(root, xml_declaration=True, encoding="UTF-8", standalone="yes")


def header_target(sect: etree._Element, rel_targets: dict[str, str], kind: str = "default") -> str:
    refs = sect.xpath(f'./w:headerReference[@w:type="{kind}"]', namespaces=NS)
    if not refs:
        raise RuntimeError(f"Section is missing its {kind} header.")
    rid = refs[0].get(qn(R, "id"))
    return "word/" + rel_targets[rid].lstrip("/")


def main(source: str, destination: str) -> None:
    with zipfile.ZipFile(source) as src:
        entries = {item.filename: src.read(item.filename) for item in src.infolist()}

    document = etree.fromstring(entries["word/document.xml"])
    rels = etree.fromstring(entries["word/_rels/document.xml.rels"])
    rel_targets = {
        rel.get("Id"): rel.get("Target")
        for rel in rels.findall(qn(PR, "Relationship"))
    }
    sections = document.xpath(".//w:sectPr", namespaces=NS)
    if len(sections) != 17:
        raise RuntimeError(f"Expected 17 original sections, found {len(sections)}.")

    template_header = header_target(sections[12], rel_targets)
    toc_header = header_target(sections[5], rel_targets)
    appendix_header = header_target(sections[16], rel_targets)

    template_rels = "word/_rels/" + pathlib.PurePosixPath(template_header).name + ".rels"
    toc_rels = "word/_rels/" + pathlib.PurePosixPath(toc_header).name + ".rels"
    appendix_rels = "word/_rels/" + pathlib.PurePosixPath(appendix_header).name + ".rels"

    entries[toc_header] = build_conditional_header(
        entries[template_header],
        [(10, "Table of Contents"), (11, "Table of Contents"), (13, "List of Figures")],
    )
    entries[appendix_header] = build_conditional_header(
        entries[template_header],
        [
            (140, "Appendices"),
            (147, "Appendices"),
            (148, "Appendices"),
            (150, "Curriculum Vitae"),
            (152, "Curriculum Vitae"),
        ],
    )
    if template_rels in entries:
        entries[toc_rels] = entries[template_rels]
        entries[appendix_rels] = entries[template_rels]

    entries["word/document.xml"] = update_toc(entries["word/document.xml"])

    with zipfile.ZipFile(destination, "w", zipfile.ZIP_DEFLATED) as dst:
        for name, data in entries.items():
            dst.writestr(name, data)
    print(destination)


if __name__ == "__main__":
    main(sys.argv[1], sys.argv[2])
