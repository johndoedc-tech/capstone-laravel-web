from pathlib import Path
from zipfile import ZipFile

from lxml import etree


DOCX = Path(
    r"C:\xampp\htdocs\capstone-laravel-web\manuscript_revisions"
    r"\Harviana Maam nats - Headers TOC Alignment Corrected.docx"
)

W = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
R = "http://schemas.openxmlformats.org/officeDocument/2006/relationships"
NS = {"w": W, "r": R}


def qn(tag: str) -> str:
    return f"{{{W}}}{tag}"


def paragraph_text(paragraph) -> str:
    return " ".join(" ".join(paragraph.xpath(".//w:t/text()", namespaces=NS)).split())


def attr(element, name: str):
    return None if element is None else element.get(qn(name))


with ZipFile(DOCX) as archive:
    package = {name: archive.read(name) for name in archive.namelist()}

document = etree.fromstring(package["word/document.xml"])
body = document.find(qn("body"))
paragraphs = [child for child in body if child.tag == qn("p")]

headings = ["TABLE OF CONTENTS", "LIST OF FIGURES", "LIST OF TABLES", "Ch apter 1"]
indices = {}
for heading in headings:
    for index, paragraph in enumerate(paragraphs):
        if paragraph_text(paragraph) == heading:
            indices[heading] = index
            break

print("FRONT MATTER")
for start_name, end_name in zip(headings, headings[1:]):
    print(f"\n[{start_name}]")
    for index in range(indices[start_name], indices[end_name]):
        paragraph = paragraphs[index]
        text = paragraph_text(paragraph)
        if not text:
            continue
        ppr = paragraph.find(qn("pPr"))
        tabs = []
        if ppr is not None:
            for tab in ppr.xpath("./w:tabs/w:tab", namespaces=NS):
                tabs.append(
                    (
                        attr(tab, "val"),
                        attr(tab, "pos"),
                        attr(tab, "leader"),
                    )
                )
        ind = None if ppr is None else ppr.find(qn("ind"))
        jc = None if ppr is None else ppr.find(qn("jc"))
        tab_chars = len(paragraph.xpath(".//w:tab", namespaces=NS))
        fields = [
            " ".join(value.split())
            for value in paragraph.xpath(".//w:instrText/text()", namespaces=NS)
        ]
        print(
            f"{index:04d} tabs={tabs} tabchars={tab_chars} "
            f"left={attr(ind, 'left')} hanging={attr(ind, 'hanging')} "
            f"first={attr(ind, 'firstLine')} jc={attr(jc, 'val')} "
            f"fields={fields} text={text!r}"
        )

print("\nSECTIONS")
section_count = 0
for index, child in enumerate(body):
    sectpr = None
    if child.tag == qn("p"):
        sectpr = child.find(f"./{qn('pPr')}/{qn('sectPr')}")
    elif child.tag == qn("sectPr"):
        sectpr = child
    if sectpr is None:
        continue
    section_count += 1
    refs = []
    for ref in sectpr.findall(qn("headerReference")):
        refs.append((attr(ref, "type"), ref.get(f"{{{R}}}id")))
    title_pg = sectpr.find(qn("titlePg")) is not None
    start_text = ""
    for following in body[index + 1 :]:
        if following.tag in (qn("p"), qn("tbl")):
            start_text = paragraph_text(following)
            if start_text:
                break
    print(
        f"section={section_count:02d} body_index={index:04d} "
        f"titlePg={title_pg} refs={refs} next={start_text[:100]!r}"
    )

rels = etree.fromstring(package["word/_rels/document.xml.rels"])
relationships = {
    relationship.get("Id"): relationship.get("Target") for relationship in rels
}

print("\nHEADERS")
for name in sorted(
    (name for name in package if name.startswith("word/header") and name.endswith(".xml")),
    key=lambda value: int(value.split("header", 1)[1].split(".xml", 1)[0]),
):
    root = etree.fromstring(package[name])
    text = paragraph_text(root)
    fields = [
        " ".join(value.split())
        for value in root.xpath(".//w:instrText/text()", namespaces=NS)
    ]
    paragraphs_header = root.xpath(".//w:p", namespaces=NS)
    props = []
    for paragraph in paragraphs_header:
        ppr = paragraph.find(qn("pPr"))
        jc = None if ppr is None else ppr.find(qn("jc"))
        tabs = []
        if ppr is not None:
            for tab in ppr.xpath("./w:tabs/w:tab", namespaces=NS):
                tabs.append((attr(tab, "val"), attr(tab, "pos"), attr(tab, "leader")))
        props.append({"jc": attr(jc, "val"), "tabs": tabs})
    print(f"{name}: text={text!r} fields={fields} props={props}")

print("\nSECTION HEADER TARGETS")
for section_index, sectpr in enumerate(
    document.xpath("//w:sectPr", namespaces=NS), start=1
):
    entries = []
    for ref in sectpr.findall(qn("headerReference")):
        rid = ref.get(f"{{{R}}}id")
        entries.append((attr(ref, "type"), rid, relationships.get(rid)))
    print(section_index, entries)
