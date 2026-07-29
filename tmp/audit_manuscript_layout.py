from __future__ import annotations

import re
from pathlib import Path

from docx import Document
from docx.oxml.ns import qn
from docx.table import Table
from docx.text.paragraph import Paragraph


DOCX = Path(r"C:\xampp\htdocs\capstone-laravel-web\tmp\Harviana-layout-working.docx")


def norm(text: str) -> str:
    return " ".join(text.split())


def iter_blocks(doc: Document):
    for child in doc.element.body.iterchildren():
        if child.tag == qn("w:p"):
            yield "p", Paragraph(child, doc)
        elif child.tag == qn("w:tbl"):
            yield "t", Table(child, doc)


def drawing_count(p: Paragraph) -> int:
    return len(p._p.xpath(".//w:drawing")) + len(p._p.xpath(".//w:pict"))


def main():
    doc = Document(DOCX)
    blocks = list(iter_blocks(doc))

    print("FIGURE CAPTION AUDIT")
    for i, (kind, block) in enumerate(blocks):
        if kind != "p":
            continue
        text = norm(block.text)
        m = re.fullmatch(r"Figure\s+(\d+)", text, flags=re.I)
        if not m:
            continue
        nearby = []
        total_drawings = 0
        for j in range(i, min(i + 7, len(blocks))):
            k2, b2 = blocks[j]
            if j > i and k2 == "p" and re.fullmatch(r"Figure\s+\d+", norm(b2.text), flags=re.I):
                break
            if k2 == "p":
                dc = drawing_count(b2)
                total_drawings += dc
                nearby.append((j, "P", norm(b2.text)[:90], dc))
            else:
                nearby.append((j, "T", f"rows={len(b2.rows)} cols={len(b2.columns)}", 0))
        print(f"Figure {m.group(1)} block={i} drawings={total_drawings} nearby={nearby}")

    print("\nTABLE CAPTION AUDIT")
    for i, (kind, block) in enumerate(blocks):
        if kind != "p":
            continue
        text = norm(block.text)
        m = re.fullmatch(r"Table\s+(\d+)", text, flags=re.I)
        if not m:
            continue
        nearby = []
        table_found = False
        for j in range(i, min(i + 6, len(blocks))):
            k2, b2 = blocks[j]
            if j > i and k2 == "p" and re.fullmatch(r"Table\s+\d+", norm(b2.text), flags=re.I):
                break
            if k2 == "p":
                nearby.append((j, "P", norm(b2.text)[:90], drawing_count(b2)))
            else:
                table_found = True
                nearby.append((j, "T", f"rows={len(b2.rows)} cols={len(b2.columns)}", 0))
                break
        print(f"Table {m.group(1)} block={i} table_found={table_found} nearby={nearby}")


if __name__ == "__main__":
    main()
