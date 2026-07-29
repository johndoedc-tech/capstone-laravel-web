from __future__ import annotations

from copy import deepcopy
from pathlib import Path
import re

from docx import Document
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.table import Table
from docx.text.paragraph import Paragraph


DOCX = Path(r"C:\xampp\htdocs\capstone-laravel-web\tmp\Harviana-layout-working.docx")
EMU_PER_INCH = 914400


def norm(text: str) -> str:
    return " ".join(text.split())


def replace_text_preserve_first_run(paragraph: Paragraph, text: str) -> None:
    rpr = deepcopy(paragraph.runs[0]._r.rPr) if paragraph.runs and paragraph.runs[0]._r.rPr is not None else None
    for child in list(paragraph._p):
        if child.tag.endswith("}r") or child.tag.endswith("}hyperlink"):
            paragraph._p.remove(child)
    run = paragraph.add_run(text)
    if rpr is not None:
        run._r.insert(0, rpr)


def replace_labeled_body(paragraph: Paragraph, label: str, body: str) -> None:
    if len(paragraph.runs) < 4 or norm(paragraph.runs[1].text) != label:
        raise RuntimeError(f"Unexpected run structure for {label!r}: {paragraph.text!r}")
    paragraph.runs[3].text = body


def compact_paragraph(paragraph: Paragraph, keep: bool) -> None:
    pf = paragraph.paragraph_format
    pf.keep_with_next = keep
    pf.line_spacing = 1.0
    pf.space_before = 0
    pf.space_after = 0


def drawing_count(paragraph: Paragraph) -> int:
    return len(paragraph._p.xpath(".//w:drawing")) + len(paragraph._p.xpath(".//w:pict"))


def set_drawing_width(paragraph: Paragraph, width_inches: float) -> None:
    target_cx = int(round(width_inches * EMU_PER_INCH))
    for drawing in paragraph._p.xpath(".//w:drawing"):
        wp_extents = drawing.xpath(".//wp:extent")
        xfrm_extents = drawing.xpath(".//a:xfrm/a:ext")
        if not wp_extents:
            continue
        old_cx = int(wp_extents[0].get("cx"))
        old_cy = int(wp_extents[0].get("cy"))
        target_cy = int(round(old_cy * target_cx / old_cx))
        for extent in wp_extents:
            extent.set("cx", str(target_cx))
            extent.set("cy", str(target_cy))
        for extent in xfrm_extents:
            extent.set("cx", str(target_cx))
            extent.set("cy", str(target_cy))
    paragraph.alignment = WD_ALIGN_PARAGRAPH.LEFT


def iter_body_blocks(doc: Document):
    for child in doc.element.body.iterchildren():
        if child.tag == qn("w:p"):
            yield "p", Paragraph(child, doc)
        elif child.tag == qn("w:tbl"):
            yield "t", Table(child, doc)


def set_row_cant_split(row) -> None:
    tr_pr = row._tr.get_or_add_trPr()
    if tr_pr.find(qn("w:cantSplit")) is None:
        tr_pr.append(OxmlElement("w:cantSplit"))


def main() -> None:
    doc = Document(DOCX)
    paragraphs = doc.paragraphs

    replacements = {
        "Farmer Dashboard and Crop Recommendations": (
            "The dashboard is the Farmer's main workspace after login. It summarizes active crop plans, reminders, "
            "harvest progress, nearby crop information, and recommendations based on available agricultural records. "
            "Quick links also provide access to the calendar and other commonly used Farmer functions."
        ),
        "LGU Validator Features": (
            "The LGU Validator side provides tools for reviewing Farmer-submitted damage and harvest records, "
            "examining supporting evidence, adding validation notes, and approving or returning reports."
        ),
        "LGU Report Validation": (
            "LGU Validators can filter assigned reports, examine damage or harvest details and supporting evidence, "
            "and approve submissions or return them for correction. Harviana records the validator, decision, "
            "validation date and time, and notes as part of the report history."
        ),
        "DA Administrator Features": (
            "The DA Administrator side provides tools for monitoring validated records and municipal supply, managing "
            "crop data and user accounts, viewing map-based information, and preparing administrative reports."
        ),
        "Administrative Dashboard and Municipal Supply Forecast": (
            "The administrative dashboard summarizes crop records, municipalities, production, actual harvests, damage, "
            "and recent system activity. Its municipal supply values help DA Administrators review the estimated "
            "availability of selected vegetables by location and production period."
        ),
        "Crop Data Management": (
            "DA Administrators can import, add, update, archive, search, and filter agricultural production records by "
            "municipality, crop, year, and other stored categories. These records support Harviana's maps, production "
            "estimates, municipal supply forecasts, and administrative reports."
        ),
        "User and Role Management": (
            "DA Administrators can create and update accounts, assign Farmer, LGU Validator, or DA Administrator roles, "
            "reset passwords, remove accounts, and filter users by role or status. These controls maintain role-based "
            "access to Harviana's functions and records."
        ),
        "Reports and Export Generation": (
            "The Reports Dashboard organizes harvest accuracy, crop production, damage records, planting records, planted "
            "areas, production values, and actual harvest information into report-focused views. DA Administrators can "
            "filter the available records according to crop, municipality, production period, and report category. The "
            "Planting Report and Export page provides the corresponding detailed records and allows the information to be "
            "prepared for documentation, further analysis, and administrative reporting."
        ),
    }
    for p in paragraphs:
        n = norm(p.text)
        for label, body in replacements.items():
            if n.startswith(label + "."):
                replace_labeled_body(p, label, body)
                break

    figure_intros = {
        26: "\tFigure 26 presents the Farmer Dashboard and Crop Recommendations feature, which summarizes crop records, reminders, and recommended actions for Farmers.",
        27: "\tFigure 27 presents the Crop Planning and Farmer Calendar feature, where Farmers create crop plans and review scheduled agricultural activities.",
        28: "\tFigure 28 presents the ML-Based Production Estimate displayed after the Farmer provides the required crop-plan details.",
        29: "\tFigure 29 presents the Damage Report Submission feature, where Farmers record crop damage and attach supporting evidence.",
        30: "\tFigure 30 presents the Actual Harvest Recording feature, where Farmers submit the production outcome of a completed crop plan.",
        31: "\tFigure 31 presents the LGU Report Validation feature, including its report queue, evidence review, and approval or return actions.",
        32: "\tFigure 32 presents the Administrative Dashboard and Municipal Supply Forecast, which consolidate crop and supply information for DA Administrators.",
        33: "\tFigure 33 presents the Crop Data Management page used by DA Administrators to maintain Harviana's agricultural records.",
        34: "\tFigure 34 presents the User and Role Management page used to maintain authorized accounts and assigned roles.",
        35: "\tFigure 35 presents the Interactive Agricultural Map and Weather feature, which provides a location-based view of agricultural and weather information.",
        36: "\tFigure 36 presents the Reports and Export Generation feature, which combines agricultural summaries with planting and harvest records for export.",
    }
    for p in paragraphs:
        n = norm(p.text)
        for number, revised in figure_intros.items():
            if n.startswith(f"Figure {number} presents"):
                replace_text_preserve_first_run(p, revised)
                # In the Features section, the "Figure X presents" sentence,
                # figure number, title, and image are kept as one visual unit.
                p.paragraph_format.keep_with_next = True
                break

    # Long, double-spaced table introductions were leaving most of a page
    # empty before the caption and table. Keep these introductions concise and
    # attach them to the complete caption-title-table unit.
    table_intros = {
        10: "\tTable 10 presents the DA Administrators' responses for the Ease of Use dimension of the USE questionnaire.",
        12: "\tTable 12 presents the DA Administrators' responses for the Satisfaction dimension of the USE questionnaire.",
        13: "\tTable 13 presents the overall USE questionnaire result for the DA Administrators.",
        14: "\tTable 14 presents the combined usability summary for Farmers and DA Administrators.",
    }
    for p in paragraphs:
        n = norm(p.text)
        for number, revised in table_intros.items():
            if n.startswith(f"Table {number} presents"):
                replace_text_preserve_first_run(p, revised)
                compact_paragraph(p, True)
                break

    # Consolidate a repetitive interpretation so the following Satisfaction
    # table can remain complete on the same page as its introduction and title.
    for p in list(paragraphs):
        n = norm(p.text)
        if n.startswith("As presented in Table 11"):
            replace_text_preserve_first_run(
                p,
                "\tAs presented in Table 11, the Ease of Learning dimension obtained a mean of 6.58, "
                "interpreted as Strongly Agree and the highest among the four USE dimensions. The highest-rated "
                "item was \u201cIt is easy to learn to use it,\u201d with a weighted mean of 6.72. These results indicate "
                "that DA Administrators were able to understand and learn Harviana's administrative interface "
                "quickly. The organized dashboard, map filters, forecast display, and report functions helped "
                "respondents access and interpret agricultural information with minimal difficulty."
            )
        elif n.startswith("This finding suggests that the system's administrative interface") or n.startswith(
            "This finding suggests that the system\u2019s administrative interface"
        ):
            p._element.getparent().remove(p._element)

    # Keep every figure number, figure title, and embedded image on the same page.
    # Introductory "Figure X presents" prose is intentionally not chained so it can
    # fill the preceding page without creating a large blank area.
    for i, p in enumerate(paragraphs):
        n = norm(p.text)
        exact_caption = re.fullmatch(r"Figure\s+\d+", n, flags=re.I)
        combined_caption = bool(re.match(r"^Figure\s+\d+\s+", n, flags=re.I) and "\n" in p.text)
        if not exact_caption and not combined_caption:
            continue
        compact_paragraph(p, True)
        image_paragraphs = []
        for j in range(i + 1, min(i + 6, len(paragraphs))):
            q = paragraphs[j]
            if drawing_count(q):
                image_paragraphs.append(q)
                # Capture a consecutive second image such as Figure 36.
                if j + 1 < len(paragraphs) and drawing_count(paragraphs[j + 1]):
                    image_paragraphs.append(paragraphs[j + 1])
                break
            compact_paragraph(q, True)
        for k, image_p in enumerate(image_paragraphs):
            compact_paragraph(image_p, k < len(image_paragraphs) - 1)
            image_p.alignment = WD_ALIGN_PARAGRAPH.LEFT

    # Explicit image widths keep the screenshots readable while allowing each
    # description-caption-image block to fit naturally on one page.
    image_widths = {
        26: 4.30, 27: 4.30, 28: 4.30, 29: 4.30, 30: 4.30,
        31: 4.00, 32: 4.30, 33: 4.00, 34: 4.00, 35: 4.30, 36: 4.00,
    }
    for i, p in enumerate(paragraphs):
        m = re.fullmatch(r"Figure\s+(\d+)", norm(p.text), flags=re.I)
        if not m:
            continue
        number = int(m.group(1))
        if number not in image_widths:
            continue
        for j in range(i + 1, min(i + 6, len(paragraphs))):
            q = paragraphs[j]
            if drawing_count(q):
                set_drawing_width(q, image_widths[number])
                if j + 1 < len(paragraphs) and drawing_count(paragraphs[j + 1]):
                    set_drawing_width(paragraphs[j + 1], image_widths[number])
                break

    # Keep every table number and title with its table. Rows are protected from
    # splitting so each of the manuscript's compact tables remains on one page.
    blocks = list(iter_body_blocks(doc))
    pending_caption_paragraphs = []
    for kind, block in blocks:
        if kind == "p":
            text = norm(block.text)
            if re.fullmatch(r"Table\s+\d+", text, flags=re.I):
                pending_caption_paragraphs = [block]
                compact_paragraph(block, True)
            elif pending_caption_paragraphs:
                pending_caption_paragraphs.append(block)
                compact_paragraph(block, True)
        elif pending_caption_paragraphs:
            for row_index, row in enumerate(block.rows):
                set_row_cant_split(row)
                keep_with_next = row_index < len(block.rows) - 1
                for cell in row.cells:
                    for cell_paragraph in cell.paragraphs:
                        cell_paragraph.paragraph_format.keep_with_next = keep_with_next
            pending_caption_paragraphs = []

    # Remove spacer paragraphs that had accumulated immediately before the
    # Chapter 4 section break. The section break already starts the chapter on
    # a new page, so the extra empty paragraphs created a fully blank page.
    refreshed_paragraphs = doc.paragraphs
    chapter_four_index = next(
        i for i, p in enumerate(refreshed_paragraphs) if norm(p.text) == "Chapter 4"
    )
    section_break_index = next(
        i
        for i in range(chapter_four_index - 1, -1, -1)
        if refreshed_paragraphs[i]._p.xpath("./w:pPr/w:sectPr")
    )
    for i in range(section_break_index - 1, -1, -1):
        p = refreshed_paragraphs[i]
        if norm(p.text) or drawing_count(p):
            break
        p._element.getparent().remove(p._element)

    doc.save(DOCX)
    print(DOCX)


if __name__ == "__main__":
    main()
