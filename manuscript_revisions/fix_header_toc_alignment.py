import re
from copy import deepcopy
from pathlib import Path
from tempfile import NamedTemporaryFile
from zipfile import ZIP_DEFLATED, ZipFile

from lxml import etree


SOURCE = Path(
    r"C:\xampp\htdocs\capstone-laravel-web\manuscript_revisions"
    r"\Harviana Maam nats - Headers TOC Figures Fixed.docx"
)
OUTPUT = Path(
    r"C:\xampp\htdocs\capstone-laravel-web\manuscript_revisions"
    r"\Harviana Maam nats - Headers TOC Alignment Corrected.docx"
)

W = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
NS = {"w": W}
XML_SPACE = "{http://www.w3.org/XML/1998/namespace}space"


def qn(tag: str) -> str:
    return f"{{{W}}}{tag}"


def paragraph_text(paragraph) -> str:
    return " ".join(
        " ".join(paragraph.xpath(".//w:t/text()", namespaces=NS)).split()
    )


def direct_paragraphs(body):
    return [child for child in body if child.tag == qn("p")]


def find_paragraph(paragraphs, text: str, *, first: bool = True):
    matches = [
        paragraph for paragraph in paragraphs if paragraph_text(paragraph) == text
    ]
    if not matches:
        raise ValueError(f"Paragraph not found: {text!r}")
    return matches[0] if first else matches[-1]


def ensure_ppr(paragraph):
    ppr = paragraph.find(qn("pPr"))
    if ppr is None:
        ppr = etree.Element(qn("pPr"))
        paragraph.insert(0, ppr)
    return ppr


def set_right_leader_tab(paragraph, position: int = 7200) -> None:
    ppr = ensure_ppr(paragraph)
    tabs = ppr.find(qn("tabs"))
    if tabs is None:
        tabs = etree.Element(qn("tabs"))
        insertion_index = 0
        pstyle = ppr.find(qn("pStyle"))
        if pstyle is not None:
            insertion_index = ppr.index(pstyle) + 1
        ppr.insert(insertion_index, tabs)
    else:
        tabs.clear()

    tab = etree.SubElement(tabs, qn("tab"))
    tab.set(qn("val"), "right")
    tab.set(qn("leader"), "dot")
    tab.set(qn("pos"), str(position))


def set_appendix_tabs(paragraph) -> None:
    ppr = ensure_ppr(paragraph)
    tabs = ppr.find(qn("tabs"))
    if tabs is None:
        tabs = etree.Element(qn("tabs"))
        insertion_index = 0
        pstyle = ppr.find(qn("pStyle"))
        if pstyle is not None:
            insertion_index = ppr.index(pstyle) + 1
        ppr.insert(insertion_index, tabs)
    else:
        tabs.clear()

    title_tab = etree.SubElement(tabs, qn("tab"))
    title_tab.set(qn("val"), "left")
    title_tab.set(qn("pos"), "1440")

    page_tab = etree.SubElement(tabs, qn("tab"))
    page_tab.set(qn("val"), "right")
    page_tab.set(qn("leader"), "dot")
    page_tab.set(qn("pos"), "8208")

    ind = ppr.find(qn("ind"))
    if ind is None:
        ind = etree.SubElement(ppr, qn("ind"))
    ind.set(qn("left"), "720")
    for attribute in ("firstLine", "hanging"):
        ind.attrib.pop(qn(attribute), None)


def set_list_entry_tabs(paragraph, include_page_tab: bool) -> None:
    ppr = ensure_ppr(paragraph)
    tabs = ppr.find(qn("tabs"))
    if tabs is None:
        tabs = etree.Element(qn("tabs"))
        insertion_index = 0
        pstyle = ppr.find(qn("pStyle"))
        if pstyle is not None:
            insertion_index = ppr.index(pstyle) + 1
        ppr.insert(insertion_index, tabs)
    else:
        tabs.clear()

    title_tab = etree.SubElement(tabs, qn("tab"))
    title_tab.set(qn("val"), "left")
    title_tab.set(qn("pos"), "1440")

    if include_page_tab:
        page_tab = etree.SubElement(tabs, qn("tab"))
        page_tab.set(qn("val"), "right")
        page_tab.set(qn("leader"), "dot")
        page_tab.set(qn("pos"), "8496")

    ind = ppr.find(qn("ind"))
    if ind is None:
        ind = etree.SubElement(ppr, qn("ind"))
    ind.set(qn("left"), "1440")
    ind.set(qn("hanging"), "720")
    ind.attrib.pop(qn("firstLine"), None)


def set_list_continuation_format(paragraph) -> None:
    set_right_leader_tab(paragraph, 8496)
    ppr = ensure_ppr(paragraph)
    ind = ppr.find(qn("ind"))
    if ind is None:
        ind = etree.SubElement(ppr, qn("ind"))
    ind.set(qn("left"), "1440")
    for attribute in ("firstLine", "hanging"):
        ind.attrib.pop(qn(attribute), None)


def first_text_rpr(paragraph):
    for run in paragraph.findall(qn("r")):
        if run.find(qn("t")) is not None:
            rpr = run.find(qn("rPr"))
            return deepcopy(rpr) if rpr is not None else None
    return None


def make_run(*, text=None, tab=False, rpr=None):
    run = etree.Element(qn("r"))
    if rpr is not None:
        run.append(deepcopy(rpr))
    if tab:
        etree.SubElement(run, qn("tab"))
    elif text is not None:
        node = etree.SubElement(run, qn("t"))
        node.set(XML_SPACE, "preserve")
        node.text = text
    return run


def make_field_run(kind: str, value: str | None = None, rpr=None):
    run = etree.Element(qn("r"))
    if rpr is not None:
        run.append(deepcopy(rpr))
    if kind == "fldChar":
        node = etree.SubElement(run, qn("fldChar"))
        node.set(qn("fldCharType"), value)
    elif kind == "instrText":
        node = etree.SubElement(run, qn("instrText"))
        node.set(XML_SPACE, "preserve")
        node.text = value
    elif kind == "text":
        node = etree.SubElement(run, qn("t"))
        node.text = value
    else:
        raise ValueError(kind)
    return run


def field_bookmark(paragraph):
    for value in paragraph.xpath(".//w:instrText/text()", namespaces=NS):
        match = re.search(r"\bPAGEREF\s+([A-Za-z0-9_]+)", value)
        if match:
            return match.group(1)
    return None


def field_cached_value(paragraph) -> str:
    fld_separate_seen = False
    values = []
    for run in paragraph.findall(qn("r")):
        field_char = run.find(qn("fldChar"))
        if field_char is not None:
            field_type = field_char.get(qn("fldCharType"))
            if field_type == "separate":
                fld_separate_seen = True
                continue
            if field_type == "end" and fld_separate_seen:
                break
        if fld_separate_seen:
            values.extend(run.xpath("./w:t/text()", namespaces=NS))
    cached = "".join(values).strip()
    return cached if cached.isdigit() else "1"


def clean_entry_title(text: str) -> str:
    text = " ".join(text.split())
    # Remove the old manually typed dot leader and every stale page-number
    # fragment that followed it (for example, "14 6").
    text = re.sub(r"(?:\s*\.\s*){2,}\s*(?:\d+\s*)*$", "", text)
    text = re.sub(r"\s+\.\s*(?:\d+\s*)*$", "", text)
    return text.rstrip()


def rebuild_pageref_entry(paragraph, tab_position: int) -> None:
    bookmark = field_bookmark(paragraph)
    if not bookmark:
        return

    title = clean_entry_title(paragraph_text(paragraph))
    # The visible cached field result is included in paragraph_text. If no dot
    # leader existed, remove the trailing field value as a fallback.
    cached = field_cached_value(paragraph)
    title = re.sub(rf"\s+{re.escape(cached)}$", "", title).rstrip()

    rpr = first_text_rpr(paragraph)
    ppr = paragraph.find(qn("pPr"))
    for child in list(paragraph):
        if child is not ppr and child.tag not in (qn("bookmarkStart"), qn("bookmarkEnd")):
            paragraph.remove(child)

    paragraph.append(make_run(text=title, rpr=rpr))
    paragraph.append(make_run(tab=True, rpr=rpr))
    paragraph.append(make_field_run("fldChar", "begin", rpr))
    paragraph.append(
        make_field_run("instrText", f" PAGEREF {bookmark} \\h ", rpr)
    )
    paragraph.append(make_field_run("fldChar", "separate", rpr))
    paragraph.append(make_field_run("text", cached, rpr))
    paragraph.append(make_field_run("fldChar", "end", rpr))
    set_right_leader_tab(paragraph, tab_position)


def rebuild_static_entry(
    paragraph, title: str, page: str, tab_position: int
) -> None:
    rpr = first_text_rpr(paragraph)
    ppr = paragraph.find(qn("pPr"))
    for child in list(paragraph):
        if child is not ppr:
            paragraph.remove(child)
    paragraph.append(make_run(text=title, rpr=rpr))
    paragraph.append(make_run(tab=True, rpr=rpr))
    paragraph.append(make_run(text=page, rpr=rpr))
    set_right_leader_tab(paragraph, tab_position)


def rebuild_appendix_entry(paragraph) -> None:
    bookmark = field_bookmark(paragraph)
    if not bookmark or not bookmark.startswith("appendix_"):
        return

    cached = field_cached_value(paragraph)
    visible = re.sub(rf"\s+{re.escape(cached)}$", "", paragraph_text(paragraph))
    match = re.match(r"^([A-K])\s+(.+)$", visible.strip())
    if not match:
        raise ValueError(f"Appendix TOC entry could not be parsed: {visible!r}")
    letter, title = match.groups()

    rpr = first_text_rpr(paragraph)
    ppr = paragraph.find(qn("pPr"))
    for child in list(paragraph):
        if child is not ppr and child.tag not in (qn("bookmarkStart"), qn("bookmarkEnd")):
            paragraph.remove(child)

    paragraph.append(make_run(text=letter, rpr=rpr))
    paragraph.append(make_run(tab=True, rpr=rpr))
    paragraph.append(make_run(text=title, rpr=rpr))
    paragraph.append(make_run(tab=True, rpr=rpr))
    paragraph.append(make_field_run("fldChar", "begin", rpr))
    paragraph.append(
        make_field_run("instrText", f" PAGEREF {bookmark} \\h ", rpr)
    )
    paragraph.append(make_field_run("fldChar", "separate", rpr))
    paragraph.append(make_field_run("text", cached, rpr))
    paragraph.append(make_field_run("fldChar", "end", rpr))
    set_appendix_tabs(paragraph)


def rebuild_numbered_list_entry(paragraph) -> bool:
    bookmark = field_bookmark(paragraph)
    cached = field_cached_value(paragraph) if bookmark else None
    visible = paragraph_text(paragraph)
    if cached:
        visible = re.sub(rf"\s+{re.escape(cached)}$", "", visible).rstrip()
    match = re.match(r"^(\d+)\s+(.+)$", visible)
    if not match:
        return False
    number, title = match.groups()

    rpr = first_text_rpr(paragraph)
    ppr = paragraph.find(qn("pPr"))
    for child in list(paragraph):
        if child is not ppr and child.tag not in (qn("bookmarkStart"), qn("bookmarkEnd")):
            paragraph.remove(child)

    paragraph.append(make_run(text=number, rpr=rpr))
    paragraph.append(make_run(tab=True, rpr=rpr))
    paragraph.append(make_run(text=title, rpr=rpr))
    if bookmark:
        paragraph.append(make_run(tab=True, rpr=rpr))
        paragraph.append(make_field_run("fldChar", "begin", rpr))
        paragraph.append(
            make_field_run("instrText", f" PAGEREF {bookmark} \\h ", rpr)
        )
        paragraph.append(make_field_run("fldChar", "separate", rpr))
        paragraph.append(make_field_run("text", cached, rpr))
        paragraph.append(make_field_run("fldChar", "end", rpr))
    set_list_entry_tabs(paragraph, include_page_tab=bool(bookmark))
    return True


def remove_explicit_page_breaks(paragraph) -> None:
    for br in list(paragraph.xpath(".//w:br[@w:type='page']", namespaces=NS)):
        parent = br.getparent()
        parent.remove(br)
        if parent.tag == qn("r") and len(parent) == 0:
            grandparent = parent.getparent()
            grandparent.remove(parent)


def element_is_empty_paragraph(element) -> bool:
    if element.tag != qn("p"):
        return False
    if paragraph_text(element):
        return False
    if element.xpath(".//w:drawing|.//w:pict|.//w:object", namespaces=NS):
        return False
    if element.xpath("./w:pPr/w:sectPr", namespaces=NS):
        return False
    return True


def trim_blank_paragraphs_before_heading(body, heading) -> None:
    section_break = heading.getprevious()
    if section_break is None or not section_break.xpath(
        "./w:pPr/w:sectPr", namespaces=NS
    ):
        return
    previous = section_break.getprevious()
    while previous is not None and element_is_empty_paragraph(previous):
        candidate = previous.getprevious()
        body.remove(previous)
        previous = candidate


def clone_header(package: dict[str, bytes], source_number: int, label: str) -> bytes:
    root = etree.fromstring(package[f"word/header{source_number}.xml"])
    text_nodes = root.xpath(".//w:t", namespaces=NS)
    for node in text_nodes:
        if (node.text or "").strip().lower().startswith("references"):
            node.text = f"{label} "
            break
    else:
        if not text_nodes:
            raise ValueError(f"Header {source_number} contains no text node")
        text_nodes[0].text = f"{label} "
    return etree.tostring(
        root, xml_declaration=True, encoding="UTF-8", standalone=True
    )


def header_relationship_name(number: int) -> str:
    return f"word/_rels/header{number}.xml.rels"


def direct_tables(body):
    return [child for child in body if child.tag == qn("tbl")]


def insert_cv_section_break(body, table, template_sectpr):
    paragraph = etree.Element(qn("p"))
    ppr = etree.SubElement(paragraph, qn("pPr"))
    ppr.append(deepcopy(template_sectpr))
    table.addprevious(paragraph)


def reposition_page_target_bookmarks(document) -> None:
    for bookmark in list(document.xpath("//w:bookmarkStart", namespaces=NS)):
        name = bookmark.get(qn("name")) or ""
        if name.startswith("_") or not name:
            continue
        paragraph = bookmark.getparent()
        if paragraph is None or paragraph.tag != qn("p"):
            continue
        first_visible = None
        for child in paragraph:
            if child.tag == qn("r") and child.xpath(
                ".//w:t[normalize-space(.) != '']", namespaces=NS
            ):
                first_visible = child
                break
        if first_visible is None:
            continue
        paragraph.remove(bookmark)
        paragraph.insert(paragraph.index(first_visible), bookmark)


def update_abstract_total_pages(document, total_pages: int) -> None:
    """Keep the abstract metadata consistent with the verified final pagination."""
    matches = document.xpath(
        "//w:t[starts-with(normalize-space(.), 'Total No. of Pages:')]",
        namespaces=NS,
    )
    if len(matches) != 1:
        raise ValueError(
            f"Expected one abstract total-page field; found {len(matches)}"
        )
    matches[0].text = f"Total No. of Pages: {total_pages}"


def main() -> None:
    with ZipFile(SOURCE, "r") as archive:
        package = {name: archive.read(name) for name in archive.namelist()}

    document = etree.fromstring(package["word/document.xml"])
    body = document.find(qn("body"))
    paragraphs = direct_paragraphs(body)

    toc_heading = find_paragraph(paragraphs, "TABLE OF CONTENTS")
    lof_heading = find_paragraph(paragraphs, "LIST OF FIGURES")
    lot_heading = find_paragraph(paragraphs, "LIST OF TABLES")
    chapter1_heading = find_paragraph(paragraphs, "Ch apter 1", first=False)

    toc_index = paragraphs.index(toc_heading)
    lof_index = paragraphs.index(lof_heading)
    lot_index = paragraphs.index(lot_heading)
    chapter1_index = paragraphs.index(chapter1_heading)

    # Rebuild every dynamic front-matter page entry with one consistent
    # right-aligned tab stop and an automatic dot leader.
    for paragraph in paragraphs[toc_index + 1 : lof_index]:
        if field_bookmark(paragraph):
            rebuild_pageref_entry(paragraph, 8208)
    for paragraph in paragraphs[lof_index + 1 : chapter1_index]:
        if field_bookmark(paragraph):
            rebuild_pageref_entry(paragraph, 8496)
    for paragraph in paragraphs[toc_index + 1 : lof_index]:
        if (field_bookmark(paragraph) or "").startswith("appendix_"):
            rebuild_appendix_entry(paragraph)
    for paragraph in paragraphs[lof_index + 2 : chapter1_index]:
        if rebuild_numbered_list_entry(paragraph):
            continue
        if field_bookmark(paragraph):
            set_list_continuation_format(paragraph)

    # The title page is the only static page-number entry.
    title_entry = next(
        paragraph
        for paragraph in paragraphs[toc_index + 1 : lof_index]
        if paragraph_text(paragraph).startswith("TITLE PAGE")
    )
    rebuild_static_entry(title_entry, "TITLE PAGE", "1", 8208)

    # The former LOF heading carried a manual page break in addition to its
    # section break, producing a blank page and a header on the first LOF page.
    remove_explicit_page_breaks(lof_heading)

    # Standardize the continuation-header placement to the geometry used by
    # the reference-compliant rebuilt headers.
    labels = {
        6: "Abstract",
        8: "Acknowledgement",
        10: "Dedication",
        14: "List of Tables",
        16: "Introduction",
        18: "Methodology",
    }
    for number, label in labels.items():
        package[f"word/header{number}.xml"] = clone_header(package, 22, label)
        package[header_relationship_name(number)] = package[
            header_relationship_name(22)
        ]

    # Each student's CV must start without a page header. Continuation pages
    # receive the Curriculum Vitae running header.
    tables = direct_tables(body)
    if len(tables) < 17:
        raise ValueError(f"Expected at least 17 body tables; found {len(tables)}")
    final_sectpr = body.find(qn("sectPr"))
    for table in (tables[15], tables[16]):
        insert_cv_section_break(body, table, final_sectpr)

    # Remove only the redundant empty paragraphs that produced header-only
    # pages. Other appendix spacing is preserved because several appendix
    # covers use anchored objects.
    paragraphs = direct_paragraphs(body)
    trim_targets = [
        find_paragraph(paragraphs, "LIST OF TABLES"),
        find_paragraph(paragraphs, "Ch apter 1", first=False),
        next(
            paragraph
            for paragraph in paragraphs
            if paragraph_text(paragraph).startswith("Appendix J")
        ),
    ]
    for heading in trim_targets:
        trim_blank_paragraphs_before_heading(body, heading)

    # Bookmarks originally placed before a leading page-break run resolve to
    # the previous page. Move each target bookmark directly before its visible
    # title text so PAGEREF returns the actual page.
    reposition_page_target_bookmarks(document)
    update_abstract_total_pages(document, 167)

    settings = etree.fromstring(package["word/settings.xml"])
    update_fields = settings.find(qn("updateFields"))
    if update_fields is None:
        update_fields = etree.SubElement(settings, qn("updateFields"))
    update_fields.set(qn("val"), "true")

    package["word/document.xml"] = etree.tostring(
        document, xml_declaration=True, encoding="UTF-8", standalone=True
    )
    package["word/settings.xml"] = etree.tostring(
        settings, xml_declaration=True, encoding="UTF-8", standalone=True
    )

    with NamedTemporaryFile(delete=False, suffix=".docx") as temporary:
        temp_path = Path(temporary.name)
    with ZipFile(temp_path, "w", ZIP_DEFLATED) as archive:
        for name, data in package.items():
            archive.writestr(name, data)

    OUTPUT.write_bytes(temp_path.read_bytes())
    temp_path.unlink()
    print(OUTPUT)


if __name__ == "__main__":
    main()
