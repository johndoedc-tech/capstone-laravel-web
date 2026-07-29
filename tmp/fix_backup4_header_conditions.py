from __future__ import annotations

import os
import tempfile
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

from lxml import etree


TARGET = Path(
    r"C:\Users\JDnStefs\Downloads\Capstone 2\Harviana chap 1, 2, 3, & 4 - Copy Final Final Backup 4.docx"
)
HEADER_PART = "word/header32.xml"
W_NS = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
NS = {"w": W_NS}


def copy_info(info: ZipInfo) -> ZipInfo:
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


def patch_header(xml: bytes) -> tuple[bytes, list[str]]:
    parser = etree.XMLParser(remove_blank_text=False)
    root = etree.fromstring(xml, parser)
    instructions = root.xpath(".//w:instrText", namespaces=NS)

    # The header contains five nested IF/PAGE field groups.  Each group has
    # seven instruction fragments.  Change only the comparison constant and
    # the cached inner PAGE result; leave all field characters/nesting intact.
    expected = [150, 157, 158, 160, 162]
    updated = [151, 158, 159, 161, 163]
    changes: list[str] = []

    if len(instructions) != 35:
        raise RuntimeError(f"Expected 35 instruction fragments, found {len(instructions)}")

    for group_index, (old, new) in enumerate(zip(expected, updated)):
        group = instructions[group_index * 7 : (group_index + 1) * 7]
        comparison = group[3]
        cached_result = group[5]

        expected_fragment = f'= {old} "'
        if comparison.text is None or expected_fragment not in comparison.text:
            raise RuntimeError(
                f"Unexpected IF fragment for group {group_index + 1}: {comparison.text!r}"
            )
        if (cached_result.text or "").strip() != str(old):
            raise RuntimeError(
                f"Unexpected cached PAGE result for group {group_index + 1}: "
                f"{cached_result.text!r}"
            )

        comparison.text = comparison.text.replace(f"= {old}", f"= {new}", 1)
        cached_result.text = str(new)
        changes.append(f"{old}->{new}")

    return (
        etree.tostring(root, xml_declaration=True, encoding="UTF-8", standalone="yes"),
        changes,
    )


def main() -> None:
    if not TARGET.exists():
        raise FileNotFoundError(TARGET)

    fd, temp_name = tempfile.mkstemp(suffix=".docx", dir=TARGET.parent)
    os.close(fd)
    temp_path = Path(temp_name)

    try:
        with ZipFile(TARGET, "r") as source, ZipFile(temp_path, "w") as destination:
            if HEADER_PART not in source.namelist():
                raise RuntimeError(f"Missing package part: {HEADER_PART}")

            patched, changes = patch_header(source.read(HEADER_PART))
            for info in source.infolist():
                payload = patched if info.filename == HEADER_PART else source.read(info.filename)
                destination.writestr(copy_info(info), payload)

        os.replace(temp_path, TARGET)
        print("Header conditions updated: " + ", ".join(changes))
    finally:
        if temp_path.exists():
            temp_path.unlink()


if __name__ == "__main__":
    main()
