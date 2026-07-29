from __future__ import annotations

import copy
import os
import tempfile
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

from lxml import etree


TARGET = Path(
    r"C:\Users\JDnStefs\Downloads\Capstone 2\Harviana chap 1, 2, 3, & 4 - Copy Final Final Backup 4.docx"
)
REFERENCE = Path(
    r"C:\xampp\htdocs\capstone-laravel-web\.codex_qa\backup4_header_toc\pre_edit_backup.docx"
)
DOCUMENT_PART = "word/document.xml"
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


def repair_document(current_xml: bytes, reference_xml: bytes) -> bytes:
    parser = etree.XMLParser(remove_blank_text=False)
    current = etree.fromstring(current_xml, parser)
    reference = etree.fromstring(reference_xml, parser)
    current_paras = current.xpath(".//w:body/w:p", namespaces=NS)
    reference_paras = reference.xpath(".//w:body/w:p", namespaces=NS)

    # Word's manual TOC entries are paragraphs 190-204. Replacing the entire
    # paragraph text shifted the following paragraph formatting backward.
    # Restore only the paragraph properties from the intact reference copy;
    # preserve the newly corrected page-number text and all other content.
    for number in range(190, 205):
        current_p = current_paras[number - 1]
        reference_p = reference_paras[number - 1]
        current_ppr = current_p.find(f"{{{W_NS}}}pPr")
        reference_ppr = reference_p.find(f"{{{W_NS}}}pPr")

        if current_ppr is not None:
            current_p.remove(current_ppr)
        if reference_ppr is not None:
            current_p.insert(0, copy.deepcopy(reference_ppr))

    return etree.tostring(
        current, xml_declaration=True, encoding="UTF-8", standalone="yes"
    )


def main() -> None:
    with ZipFile(REFERENCE, "r") as source:
        reference_xml = source.read(DOCUMENT_PART)

    fd, temp_name = tempfile.mkstemp(suffix=".docx", dir=TARGET.parent)
    os.close(fd)
    temp_path = Path(temp_name)
    try:
        with ZipFile(TARGET, "r") as source, ZipFile(temp_path, "w") as destination:
            repaired = repair_document(source.read(DOCUMENT_PART), reference_xml)
            for info in source.infolist():
                payload = repaired if info.filename == DOCUMENT_PART else source.read(info.filename)
                destination.writestr(clone_info(info), payload)
        os.replace(temp_path, TARGET)
        print("Restored TOC paragraph formatting for entries 190-204.")
    finally:
        if temp_path.exists():
            temp_path.unlink()


if __name__ == "__main__":
    main()
