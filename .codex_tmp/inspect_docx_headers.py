from __future__ import annotations

import collections
import pathlib
import sys
import zipfile

from lxml import etree


W = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
R = "http://schemas.openxmlformats.org/officeDocument/2006/relationships"
PR = "http://schemas.openxmlformats.org/package/2006/relationships"
NS = {"w": W, "r": R, "a": "http://schemas.openxmlformats.org/drawingml/2006/main"}


def text_of(root: etree._Element) -> str:
    values = root.xpath(".//w:t/text() | .//w:instrText/text() | .//a:t/text()", namespaces=NS)
    return " | ".join(" ".join(v.split()) for v in values if v.strip())


def main(path: str) -> None:
    with zipfile.ZipFile(path) as zf:
        doc = etree.fromstring(zf.read("word/document.xml"))
        rels = etree.fromstring(zf.read("word/_rels/document.xml.rels"))
        targets = {
            rel.get("Id"): rel.get("Target")
            for rel in rels.findall(f"{{{PR}}}Relationship")
        }
        sects = doc.xpath(".//w:sectPr", namespaces=NS)
        print(f"sections={len(sects)}")
        used = collections.defaultdict(list)
        for index, sect in enumerate(sects, 1):
            title = bool(sect.xpath("./w:titlePg", namespaces=NS))
            entries = []
            for ref in sect.xpath("./w:headerReference", namespaces=NS):
                kind = ref.get(f"{{{W}}}type")
                rid = ref.get(f"{{{R}}}id")
                target = targets.get(rid, "?")
                entries.append(f"{kind}:{target}")
                used[target].append((index, kind))
            print(f"section {index}: titlePg={title} headers={entries}")

        print("\nHEADER PARTS")
        for target, owners in sorted(used.items()):
            name = "word/" + target.lstrip("/")
            root = etree.fromstring(zf.read(name))
            drawings = len(root.xpath(".//w:drawing", namespaces=NS))
            picts = len(root.xpath(".//w:pict", namespaces=NS))
            fields = root.xpath(".//w:instrText/text()", namespaces=NS)
            print(
                f"{target}: owners={owners} drawings={drawings} picts={picts} "
                f"fields={fields} text={text_of(root)!r}"
            )


if __name__ == "__main__":
    main(sys.argv[1])
