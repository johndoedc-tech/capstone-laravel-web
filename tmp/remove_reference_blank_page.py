from __future__ import annotations

import os
import re
import tempfile
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

from lxml import etree


TARGET = Path(
    r"C:\Users\JDnStefs\Downloads\Capstone 2\Harviana chap 1, 2, 3, & 4 - Copy Final Final Backup 4.docx"
)
DOCUMENT_PART = "word/document.xml"
HEADER_PART = "word/header32.xml"
W_NS = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
NS = {"w": W_NS}


def clone_info(info: ZipInfo) -> ZipInfo:
    clone = ZipInfo(info.filename, info.date_time)
    clone.compress_type = ZIP_DEFLATED
    clone.comment = info.comment
    clone.extra = info.extra
    clone.create_system = info.create_system
    clone.create_version = info.create_version
    clone.extract_version = info.extract_version
    clone.flag_bits = info.flag_bits
    clone.volume = info.volume
    clone.internal_attr = info.internal_attr
    clone.external_attr = info.external_attr
    return clone


def paragraph_text(paragraph: etree._Element) -> str:
    return "".join(paragraph.xpath(".//w:t/text()", namespaces=NS)).strip()


def patch_document(xml: bytes) -> tuple[bytes, int]:
    parser = etree.XMLParser(remove_blank_text=False)
    root = etree.fromstring(xml, parser)
    body = root.find(f"{{{W_NS}}}body")
    paragraphs = body.xpath("./w:p", namespaces=NS)

    appendix_indexes = [
        i for i, paragraph in enumerate(paragraphs)
        if paragraph_text(paragraph) == "APPENDICES"
    ]
    if len(appendix_indexes) < 2:
        raise RuntimeError("Could not locate the manuscript APPENDICES heading")

    appendix_index = appendix_indexes[-1]
    section_paragraph = paragraphs[appendix_index - 1]
    if section_paragraph.find("w:pPr/w:sectPr", namespaces=NS) is None:
        raise RuntimeError("Expected section break immediately before APPENDICES")

    removable: list[etree._Element] = []
    cursor = appendix_index - 2
    while cursor >= 0:
        paragraph = paragraphs[cursor]
        if paragraph_text(paragraph):
            break
        if paragraph.find("w:pPr/w:sectPr", namespaces=NS) is not None:
            break
        removable.append(paragraph)
        cursor -= 1

    if not removable:
        raise RuntimeError("No unnecessary blank paragraphs found before APPENDICES")
    for paragraph in removable:
        body.remove(paragraph)

    # The removed page shifts all appendix and curriculum-vitae starts back by one.
    toc_pages = {
        190: 139, 191: 140, 192: 141, 193: 143, 194: 146,
        195: 147, 196: 148, 197: 149, 198: 151, 199: 152,
        200: 153, 201: 154, 202: 155, 203: 156, 204: 159,
    }
    paragraphs = body.xpath("./w:p", namespaces=NS)
    for number, page in toc_pages.items():
        paragraph = paragraphs[number - 1]
        text_nodes = paragraph.xpath(".//w:t", namespaces=NS)
        for node in reversed(text_nodes):
            value = node.text or ""
            match = re.search(r"\d+\s*$", value)
            if match:
                node.text = value[: match.start()] + str(page) + value[match.end():]
                break
        else:
            raise RuntimeError(f"No page number found in TOC paragraph {number}")

    return (
        etree.tostring(root, xml_declaration=True, encoding="UTF-8", standalone="yes"),
        len(removable),
    )


def patch_header(xml: bytes) -> bytes:
    parser = etree.XMLParser(remove_blank_text=False)
    root = etree.fromstring(xml, parser)
    instructions = root.xpath(".//w:instrText", namespaces=NS)
    expected = [151, 158, 159, 161, 163]
    updated = [150, 157, 158, 160, 162]
    if len(instructions) != 35:
        raise RuntimeError(f"Expected 35 header instruction fragments, found {len(instructions)}")

    for group_index, (old, new) in enumerate(zip(expected, updated)):
        group = instructions[group_index * 7 : (group_index + 1) * 7]
        comparison = group[3]
        cached_result = group[5]
        if comparison.text is None or f'= {old} "' not in comparison.text:
            raise RuntimeError(f"Unexpected header comparison: {comparison.text!r}")
        if (cached_result.text or "").strip() != str(old):
            raise RuntimeError(f"Unexpected cached header page: {cached_result.text!r}")
        comparison.text = comparison.text.replace(f"= {old}", f"= {new}", 1)
        cached_result.text = str(new)

    return etree.tostring(
        root, xml_declaration=True, encoding="UTF-8", standalone="yes"
    )


def main() -> None:
    fd, temp_name = tempfile.mkstemp(suffix=".docx", dir=TARGET.parent)
    os.close(fd)
    temp_path = Path(temp_name)
    try:
        with ZipFile(TARGET, "r") as source:
            document_xml, removed = patch_document(source.read(DOCUMENT_PART))
            header_xml = patch_header(source.read(HEADER_PART))
            with ZipFile(temp_path, "w") as destination:
                for info in source.infolist():
                    if info.filename == DOCUMENT_PART:
                        payload = document_xml
                    elif info.filename == HEADER_PART:
                        payload = header_xml
                    else:
                        payload = source.read(info.filename)
                    destination.writestr(clone_info(info), payload)
        os.replace(temp_path, TARGET)
        print(f"Removed {removed} blank paragraphs and updated shifted pagination.")
    finally:
        if temp_path.exists():
            temp_path.unlink()


if __name__ == "__main__":
    main()
