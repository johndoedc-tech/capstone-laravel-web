from __future__ import annotations

import copy
import os
import tempfile
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

from lxml import etree


TARGET = Path(r"C:\Users\JDnStefs\Downloads\Harviana Final 1.docx")
CHAPTER_HEADER = "word/header20.xml"
CONDITIONAL_HEADER = "word/header32.xml"
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


def instruction(run: etree._Element) -> etree._Element:
    node = run.find(f"{{{W_NS}}}instrText")
    if node is None:
        raise RuntimeError("Expected an instruction-text run")
    return node


def make_if_group(
    template: list[etree._Element], page: int, label: str
) -> list[etree._Element]:
    group = [copy.deepcopy(run) for run in template]
    instruction(group[1]).text = " IF "
    instruction(group[3]).text = " PAGE "
    instruction(group[5]).text = str(page)
    instruction(group[7]).text = f'= {page} "{label} '
    instruction(group[9]).text = " PAGE "
    instruction(group[11]).text = str(page)
    instruction(group[13]).text = '" "" '
    return group


def replace_header_fields(
    header_root: etree._Element,
    template: list[etree._Element],
    specifications: list[tuple[int, str]],
) -> None:
    paragraph = header_root.xpath("./w:p", namespaces=NS)[0]
    for run in paragraph.xpath("./w:r", namespaces=NS):
        paragraph.remove(run)
    for page, label in specifications:
        for run in make_if_group(template, page, label):
            paragraph.append(run)


def main() -> None:
    fd, temp_name = tempfile.mkstemp(suffix=".docx", dir=TARGET.parent)
    os.close(fd)
    temp_path = Path(temp_name)

    try:
        with ZipFile(TARGET, "r") as source:
            parser = etree.XMLParser(remove_blank_text=False)
            chapter = etree.fromstring(source.read(CHAPTER_HEADER), parser)
            conditional = etree.fromstring(source.read(CONDITIONAL_HEADER), parser)

            conditional_paragraph = conditional.xpath("./w:p", namespaces=NS)[0]
            template = conditional_paragraph.xpath("./w:r", namespaces=NS)[:15]
            if len(template) != 15:
                raise RuntimeError("Could not obtain the conditional-field template")

            chapter_specs = [
                *[(page, "Findings of the Study") for page in range(83, 126)],
                *[
                    (page, "Conclusions and Recommendations")
                    for page in range(127, 130)
                ],
            ]
            replace_header_fields(chapter, template, chapter_specs)

            appendix_specs = [
                (150, "Appendices"),
                *[(page, "Appendices") for page in range(152, 156)],
                (157, "Appendices"),
                (158, "Appendices"),
                (160, "Curriculum Vitae"),
                (162, "Curriculum Vitae"),
                (164, "Curriculum Vitae"),
            ]
            replace_header_fields(conditional, template, appendix_specs)

            patched = {
                CHAPTER_HEADER: etree.tostring(
                    chapter,
                    xml_declaration=True,
                    encoding="UTF-8",
                    standalone="yes",
                ),
                CONDITIONAL_HEADER: etree.tostring(
                    conditional,
                    xml_declaration=True,
                    encoding="UTF-8",
                    standalone="yes",
                ),
            }

            with ZipFile(temp_path, "w") as destination:
                for info in source.infolist():
                    payload = patched.get(info.filename, source.read(info.filename))
                    destination.writestr(clone_info(info), payload)

        os.replace(temp_path, TARGET)
        print(
            "Updated Chapter 3/4, appendix, and curriculum-vitae header conditions."
        )
    finally:
        if temp_path.exists():
            temp_path.unlink()


if __name__ == "__main__":
    main()
