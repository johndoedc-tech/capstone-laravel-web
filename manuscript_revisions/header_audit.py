from __future__ import annotations

import json
import re
import zipfile
import argparse
from pathlib import Path
from lxml import etree
from pypdf import PdfReader

NS = {
    "w": "http://schemas.openxmlformats.org/wordprocessingml/2006/main",
    "r": "http://schemas.openxmlformats.org/officeDocument/2006/relationships",
    "a": "http://schemas.openxmlformats.org/drawingml/2006/main",
    "wp": "http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing",
}
PKG_REL_NS = "http://schemas.openxmlformats.org/package/2006/relationships"

DOCX = Path(
    r"C:\xampp\htdocs\capstone-laravel-web\manuscript_revisions"
    r"\Harviana Maam nats - Headers TOC Figures Fixed.docx"
)
PDF = Path(
    r"C:\Users\JDnStefs\Downloads\Capstone 2\[FINAL PAPER] - TulongLegal.pdf"
)


def qname(ns: str, local: str) -> str:
    return f"{{{ns}}}{local}"


def element_text(el: etree._Element) -> str:
    return "".join(el.xpath(".//w:t/text()", namespaces=NS)).strip()


def paragraph_alignment(p: etree._Element) -> str:
    val = p.xpath("./w:pPr/w:jc/@w:val", namespaces=NS)
    return val[0] if val else "(inherited)"


def twips_attr(el: etree._Element | None, local: str) -> str | None:
    if el is None:
        return None
    return el.get(qname(NS["w"], local))


def audit_docx(docx_path: Path = DOCX) -> dict:
    with zipfile.ZipFile(docx_path) as zf:
        doc = etree.fromstring(zf.read("word/document.xml"))
        rels = etree.fromstring(zf.read("word/_rels/document.xml.rels"))
        relmap = {
            r.get("Id"): r.get("Target")
            for r in rels.findall(qname(PKG_REL_NS, "Relationship"))
        }

        body = doc.find("w:body", NS)
        blocks = list(body)
        sections = []
        section_start_block = 0
        for index, block in enumerate(blocks):
            sect_pr = block.find("./w:pPr/w:sectPr", NS)
            endpoint_type = "paragraph"
            endpoint_text = element_text(block)
            if sect_pr is None and block.tag == qname(NS["w"], "sectPr"):
                sect_pr = block
                endpoint_type = "body-final"
                endpoint_text = "(end of document)"
            if sect_pr is None:
                continue

            refs = []
            for ref in sect_pr.findall("w:headerReference", NS):
                rid = ref.get(qname(NS["r"], "id"))
                target = relmap.get(rid)
                refs.append(
                    {
                        "type": ref.get(qname(NS["w"], "type")),
                        "rid": rid,
                        "target": target,
                    }
                )
            pg_num = sect_pr.find("w:pgNumType", NS)
            pg_mar = sect_pr.find("w:pgMar", NS)
            sec_type = sect_pr.find("w:type", NS)
            section = {
                "index": len(sections) + 1,
                "start_block_index": section_start_block,
                "block_index": index,
                "endpoint_type": endpoint_type,
                "endpoint_text": endpoint_text,
                "title_page": sect_pr.find("w:titlePg", NS) is not None,
                "section_break_type": (
                    sec_type.get(qname(NS["w"], "val"))
                    if sec_type is not None
                    else "(default nextPage)"
                ),
                "page_number_start": (
                    pg_num.get(qname(NS["w"], "start"))
                    if pg_num is not None
                    else None
                ),
                "page_number_format": (
                    pg_num.get(qname(NS["w"], "fmt"))
                    if pg_num is not None
                    else None
                ),
                "header_distance_twips": twips_attr(pg_mar, "header"),
                "top_margin_twips": twips_attr(pg_mar, "top"),
                "headers": refs,
            }
            start_texts = []
            for candidate in blocks[section_start_block : index + 1]:
                text = element_text(candidate)
                if text:
                    start_texts.append(text)
                if len(start_texts) >= 8:
                    break
            section["start_texts"] = start_texts
            sections.append(section)
            section_start_block = index + 1

        headers = {}
        for section in sections:
            for ref in section["headers"]:
                target = ref["target"]
                if not target or target in headers:
                    continue
                name = "word/" + target.lstrip("/")
                root = etree.fromstring(zf.read(name))
                paras = []
                for i, p in enumerate(root.xpath(".//w:p", namespaces=NS), 1):
                    fields = [
                        x.strip()
                        for x in p.xpath(".//w:instrText/text()", namespaces=NS)
                        if x.strip()
                    ]
                    tabs = [
                        {
                            "val": tab.get(qname(NS["w"], "val")),
                            "pos": tab.get(qname(NS["w"], "pos")),
                        }
                        for tab in p.xpath("./w:pPr/w:tabs/w:tab", namespaces=NS)
                    ]
                    ind = p.find("./w:pPr/w:ind", NS)
                    spacing = p.find("./w:pPr/w:spacing", NS)
                    paras.append(
                        {
                            "index": i,
                            "text": element_text(p),
                            "alignment": paragraph_alignment(p),
                            "fields": fields,
                            "tabs": tabs,
                            "indent_left": twips_attr(ind, "left"),
                            "indent_right": twips_attr(ind, "right"),
                            "first_line": twips_attr(ind, "firstLine"),
                            "hanging": twips_attr(ind, "hanging"),
                            "space_before": twips_attr(spacing, "before"),
                            "space_after": twips_attr(spacing, "after"),
                            "line": twips_attr(spacing, "line"),
                        }
                    )
                drawings = []
                for i, anchor in enumerate(
                    root.xpath(".//wp:anchor | .//wp:inline", namespaces=NS), 1
                ):
                    ext = anchor.find("wp:extent", NS)
                    pos_h = anchor.find("wp:positionH", NS)
                    pos_v = anchor.find("wp:positionV", NS)
                    drawings.append(
                        {
                            "index": i,
                            "kind": etree.QName(anchor).localname,
                            "cx": ext.get("cx") if ext is not None else None,
                            "cy": ext.get("cy") if ext is not None else None,
                            "pos_h_relative": (
                                pos_h.get("relativeFrom") if pos_h is not None else None
                            ),
                            "pos_h_offset": (
                                pos_h.findtext("wp:posOffset", namespaces=NS)
                                if pos_h is not None
                                else None
                            ),
                            "pos_v_relative": (
                                pos_v.get("relativeFrom") if pos_v is not None else None
                            ),
                            "pos_v_offset": (
                                pos_v.findtext("wp:posOffset", namespaces=NS)
                                if pos_v is not None
                                else None
                            ),
                        }
                    )
                headers[target] = {
                    "paragraphs": paras,
                    "drawings": drawings,
                    "table_count": len(root.xpath(".//w:tbl", namespaces=NS)),
                }
        return {
            "docx": str(docx_path),
            "section_count": len(sections),
            "sections": sections,
            "headers": headers,
        }


def audit_pdf() -> dict:
    reader = PdfReader(PDF)
    pages = []
    header_pattern = re.compile(
        r"^(?P<label>[A-Za-z][A-Za-z &/-]+?)\s+(?P<number>\d+)\s*$"
    )
    for index, page in enumerate(reader.pages, 1):
        text = page.extract_text() or ""
        lines = [re.sub(r"\s+", " ", line).strip() for line in text.splitlines()]
        lines = [line for line in lines if line]
        candidates = []
        for line in lines[:8] + lines[-8:]:
            m = header_pattern.match(line)
            if m:
                candidates.append(line)
        pages.append(
            {
                "page_index": index,
                "top_lines": lines[:6],
                "bottom_lines": lines[-5:],
                "header_candidates": candidates,
            }
        )
    return {"pdf": str(PDF), "page_count": len(reader.pages), "pages": pages}


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--docx", type=Path, default=DOCX)
    parser.add_argument("--out", type=Path, default=None)
    parser.add_argument("--skip-pdf", action="store_true")
    args = parser.parse_args()
    report = {"docx": audit_docx(args.docx)}
    if not args.skip_pdf:
        report["reference_pdf"] = audit_pdf()
    out = args.out or Path(__file__).with_name("header_audit_report.json")
    out.write_text(json.dumps(report, indent=2), encoding="utf-8")
    print(out)


if __name__ == "__main__":
    main()
