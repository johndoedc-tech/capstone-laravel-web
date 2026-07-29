from __future__ import annotations

import argparse
import json
import re
import zipfile
from pathlib import Path

from docx import Document
from docx.document import Document as _Document
from docx.table import Table, _Cell
from docx.text.paragraph import Paragraph
from docx.oxml.table import CT_Tbl
from docx.oxml.text.paragraph import CT_P


def iter_block_items(parent):
    if isinstance(parent, _Document):
        parent_elm = parent.element.body
    elif isinstance(parent, _Cell):
        parent_elm = parent._tc
    else:
        raise ValueError("unsupported parent")
    for child in parent_elm.iterchildren():
        if isinstance(child, CT_P):
            yield Paragraph(child, parent)
        elif isinstance(child, CT_Tbl):
            yield Table(child, parent)


def paragraph_flags(paragraph: Paragraph) -> dict:
    text = paragraph.text.strip()
    ppr = paragraph._p.pPr
    page_break_before = bool(ppr is not None and ppr.pageBreakBefore is not None)
    keep_with_next = bool(ppr is not None and ppr.keepNext is not None)
    return {
        "text": text,
        "style": paragraph.style.name if paragraph.style else "",
        "alignment": str(paragraph.alignment),
        "page_break_before": page_break_before,
        "keep_with_next": keep_with_next,
        "run_count": len(paragraph.runs),
        "bold_all": bool(paragraph.runs) and all(r.bold is True for r in paragraph.runs if r.text.strip()),
    }


def extract_docx(path: Path) -> dict:
    document = Document(path)
    blocks = []
    para_idx = 0
    table_idx = 0
    for block in iter_block_items(document):
        if isinstance(block, Paragraph):
            info = paragraph_flags(block)
            info.update({"kind": "paragraph", "paragraph_index": para_idx})
            blocks.append(info)
            para_idx += 1
        else:
            rows = [[cell.text.strip() for cell in row.cells] for row in block.rows]
            blocks.append({
                "kind": "table",
                "table_index": table_idx,
                "rows": rows,
                "row_count": len(rows),
                "column_count": max((len(r) for r in rows), default=0),
            })
            table_idx += 1

    with zipfile.ZipFile(path) as zf:
        names = set(zf.namelist())
        document_xml = zf.read("word/document.xml").decode("utf-8", errors="replace")
        comments = "word/comments.xml" in names
        comment_count = 0
        if comments:
            comments_xml = zf.read("word/comments.xml").decode("utf-8", errors="replace")
            comment_count = len(re.findall(r"<w:comment\b", comments_xml))
        tracked_insertions = len(re.findall(r"<w:ins\b", document_xml))
        tracked_deletions = len(re.findall(r"<w:del\b", document_xml))

    sections = []
    for i, section in enumerate(document.sections):
        sections.append({
            "index": i,
            "page_width_inches": round(section.page_width.inches, 3),
            "page_height_inches": round(section.page_height.inches, 3),
            "top_margin_inches": round(section.top_margin.inches, 3),
            "bottom_margin_inches": round(section.bottom_margin.inches, 3),
            "left_margin_inches": round(section.left_margin.inches, 3),
            "right_margin_inches": round(section.right_margin.inches, 3),
        })

    return {
        "source": str(path),
        "paragraph_count": para_idx,
        "table_count": table_idx,
        "inline_shape_count": len(document.inline_shapes),
        "comments_present": comments,
        "comment_count": comment_count,
        "tracked_insertions": tracked_insertions,
        "tracked_deletions": tracked_deletions,
        "sections": sections,
        "blocks": blocks,
    }


def write_readable(data: dict, out: Path) -> None:
    lines = []
    for block in data["blocks"]:
        if block["kind"] == "paragraph":
            if block["text"]:
                lines.append(
                    f"P{block['paragraph_index']:04d}\t[{block['style']}]\t{block['text']}"
                )
        else:
            lines.append(f"TABLE {block['table_index']} ({block['row_count']}x{block['column_count']})")
            for row in block["rows"]:
                lines.append("\t".join(row))
            lines.append("END TABLE")
    out.write_text("\n".join(lines), encoding="utf-8")


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("input", type=Path)
    parser.add_argument("output_prefix", type=Path)
    args = parser.parse_args()
    args.output_prefix.parent.mkdir(parents=True, exist_ok=True)
    data = extract_docx(args.input)
    args.output_prefix.with_suffix(".json").write_text(
        json.dumps(data, indent=2, ensure_ascii=False), encoding="utf-8"
    )
    write_readable(data, args.output_prefix.with_suffix(".txt"))
    print(json.dumps({k: v for k, v in data.items() if k != "blocks"}, indent=2))


if __name__ == "__main__":
    main()
