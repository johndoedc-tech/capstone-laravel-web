from __future__ import annotations

import copy
import os
import shutil
import zipfile
from pathlib import Path

from lxml import etree


SOURCE = Path(
    r"C:\Users\JDnStefs\Downloads\Capstone 2\Harviana_Figure_Sequence_Revised.docx"
)
IMAGE = Path(
    r"C:\Users\JDnStefs\Downloads\Capstone 2\Harviana Overall Low-Fidelity Prototype Simplified.png"
)
OUTPUT = Path(
    r"C:\Users\JDnStefs\Downloads\Capstone 2\Harviana_Figure_Sequence_Revised_Overall_Prototype.docx"
)
BACKUP = Path(
    r"C:\Users\JDnStefs\Downloads\Capstone 2\Harviana_Figure_Sequence_Revised_backup_before_overall_prototype.docx"
)

W_NS = "http://schemas.openxmlformats.org/wordprocessingml/2006/main"
R_NS = "http://schemas.openxmlformats.org/officeDocument/2006/relationships"
A_NS = "http://schemas.openxmlformats.org/drawingml/2006/main"
WP_NS = "http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
PR_NS = "http://schemas.openxmlformats.org/package/2006/relationships"

NS = {
    "w": W_NS,
    "r": R_NS,
    "a": A_NS,
    "wp": WP_NS,
    "pr": PR_NS,
}


def qn(namespace: str, local: str) -> str:
    return f"{{{namespace}}}{local}"


def paragraph_text(paragraph: etree._Element) -> str:
    return "".join(paragraph.xpath(".//w:t/text()", namespaces=NS))


def find_paragraph(
    paragraphs: list[etree._Element], prefix: str
) -> etree._Element:
    for paragraph in paragraphs:
        if paragraph_text(paragraph).startswith(prefix):
            return paragraph
    raise RuntimeError(f"Paragraph not found: {prefix!r}")


def make_run(
    text: str,
    *,
    underline: bool = False,
    preserve_space: bool = False,
) -> etree._Element:
    run = etree.Element(qn(W_NS, "r"))
    properties = etree.SubElement(run, qn(W_NS, "rPr"))
    fonts = etree.SubElement(properties, qn(W_NS, "rFonts"))
    for attr in ("ascii", "eastAsia", "hAnsi", "cs"):
        fonts.set(qn(W_NS, attr), "Courier New")
    if underline:
        underline_node = etree.SubElement(properties, qn(W_NS, "u"))
        underline_node.set(qn(W_NS, "val"), "single")
    text_node = etree.SubElement(run, qn(W_NS, "t"))
    if preserve_space or text.startswith(" ") or text.endswith(" "):
        text_node.set(
            "{http://www.w3.org/XML/1998/namespace}space",
            "preserve",
        )
    text_node.text = text
    return run


def replace_paragraph_runs(
    paragraph: etree._Element,
    runs: list[etree._Element],
    *,
    leading_tab: bool = False,
) -> None:
    for child in list(paragraph):
        if child.tag != qn(W_NS, "pPr"):
            paragraph.remove(child)

    if leading_tab:
        tab_run = etree.Element(qn(W_NS, "r"))
        tab_properties = etree.SubElement(tab_run, qn(W_NS, "rPr"))
        tab_fonts = etree.SubElement(tab_properties, qn(W_NS, "rFonts"))
        for attr in ("ascii", "eastAsia", "hAnsi", "cs"):
            tab_fonts.set(qn(W_NS, attr), "Courier New")
        etree.SubElement(tab_run, qn(W_NS, "tab"))
        paragraph.append(tab_run)

    for run in runs:
        paragraph.append(run)


def set_body_text(
    paragraph: etree._Element,
    text: str,
    *,
    leading_tab: bool = True,
) -> None:
    replace_paragraph_runs(
        paragraph,
        [make_run(text)],
        leading_tab=leading_tab,
    )


def set_prototype_heading(paragraph: etree._Element, body: str) -> None:
    replace_paragraph_runs(
        paragraph,
        [
            make_run("Prototype", underline=True),
            make_run(". "),
            make_run(body),
        ],
        leading_tab=True,
    )


def ensure_keep_with_next(paragraph: etree._Element) -> None:
    ppr = paragraph.find("w:pPr", NS)
    if ppr is None:
        ppr = etree.Element(qn(W_NS, "pPr"))
        paragraph.insert(0, ppr)
    if ppr.find("w:keepNext", NS) is None:
        ppr.insert(0, etree.Element(qn(W_NS, "keepNext")))


def main() -> None:
    if not SOURCE.exists():
        raise FileNotFoundError(SOURCE)
    if not IMAGE.exists():
        raise FileNotFoundError(IMAGE)

    if not BACKUP.exists():
        shutil.copy2(SOURCE, BACKUP)

    with zipfile.ZipFile(SOURCE, "r") as source_zip:
        document_xml = etree.fromstring(source_zip.read("word/document.xml"))
        relationships_xml = etree.fromstring(
            source_zip.read("word/_rels/document.xml.rels")
        )

        paragraphs = document_xml.xpath("//w:body/w:p", namespaces=NS)
        prototype_heading = find_paragraph(
            paragraphs, "Prototype. Prototype is the stage"
        )
        prototype_activity = find_paragraph(
            paragraphs, "In this stage, the researchers created low-fidelity"
        )
        figure_leadin = find_paragraph(
            paragraphs, "Figure 6 presents the Landing Page prototype"
        )
        figure_number = find_paragraph(paragraphs, "Figure 6")
        figure_title = find_paragraph(
            paragraphs, "Landing Page Overview Prototype"
        )
        figure_explanation = find_paragraph(
            paragraphs, "The Landing Page serves as the first screen"
        )

        image_paragraph = None
        figure_title_index = paragraphs.index(figure_title)
        for paragraph in paragraphs[figure_title_index + 1 :]:
            if paragraph.xpath(".//w:drawing", namespaces=NS):
                image_paragraph = paragraph
                break
        if image_paragraph is None:
            raise RuntimeError("Figure 6 image paragraph not found")

        set_prototype_heading(
            prototype_heading,
            (
                "The Prototype phase transformed the proposed solutions into "
                "a visual representation of Harviana before full development. "
                "It allowed the researchers to examine the planned interface "
                "structure, navigation, and role-based interactions of Farmers, "
                "LGU Validators, and DA Administrators."
            ),
        )

        set_body_text(
            prototype_activity,
            (
                "During this phase, the researchers prepared an overall "
                "low-fidelity prototype that summarized the main functions "
                "assigned to each user role. The prototype showed how Farmers "
                "create agricultural records, how LGU Validators review "
                "submitted information, and how DA Administrators use validated "
                "records for monitoring, forecasting, map-based visualization, "
                "and reporting. Individual interface prototypes were also "
                "prepared to provide a more detailed view of the supporting "
                "and role-specific functions."
            ),
        )

        set_body_text(
            figure_leadin,
            (
                "Figure 6 presents the overall low-fidelity prototype of "
                "Harviana, showing the main functions assigned to Farmers, "
                "LGU Validators, and DA Administrators and the flow of "
                "agricultural information among these users."
            ),
        )
        ensure_keep_with_next(figure_leadin)
        ensure_keep_with_next(figure_number)

        set_body_text(
            figure_title,
            "Overall Low-Fidelity Prototype of Harviana’s Main Features",
            leading_tab=False,
        )
        ensure_keep_with_next(figure_title)

        set_body_text(
            figure_explanation,
            (
                "The overall prototype organizes the principal functions of "
                "Harviana according to user role. Farmers use the system for "
                "crop planning, production estimation, and the recording of "
                "crop damage and actual harvests. LGU Validators review "
                "submissions, examine supporting evidence, and approve or "
                "return records, while DA Administrators access dashboards, "
                "municipal supply forecasts, GIS and weather information, and "
                "report-generation functions. The arrows illustrate how "
                "Farmer-submitted information passes through LGU validation "
                "before becoming available for DA administrative monitoring "
                "and decision support."
            ),
        )

        blips = image_paragraph.xpath(".//a:blip", namespaces=NS)
        if not blips:
            raise RuntimeError("Figure 6 image relationship not found")
        relationship_id = blips[0].get(qn(R_NS, "embed"))
        if not relationship_id:
            raise RuntimeError("Figure 6 image relationship id is missing")

        relationship = relationships_xml.xpath(
            f"./pr:Relationship[@Id='{relationship_id}']",
            namespaces=NS,
        )
        if len(relationship) != 1:
            raise RuntimeError(
                f"Expected one image relationship for {relationship_id}"
            )
        media_target = relationship[0].get("Target")
        if not media_target:
            raise RuntimeError("Figure 6 image target is missing")
        media_path = "word/" + media_target.replace("\\", "/").lstrip("/")

        width_emu = int(5.75 * 914400)
        height_emu = round(width_emu * 934 / 1680)

        for extent in image_paragraph.xpath(
            ".//wp:extent | .//a:xfrm/a:ext",
            namespaces=NS,
        ):
            extent.set("cx", str(width_emu))
            extent.set("cy", str(height_emu))

        for doc_properties in image_paragraph.xpath(
            ".//wp:docPr", namespaces=NS
        ):
            doc_properties.set(
                "name", "Harviana Overall Low-Fidelity Prototype"
            )
            doc_properties.set(
                "descr",
                (
                    "Simplified low-fidelity prototype showing the main "
                    "Farmer, LGU Validator, and DA Administrator functions."
                ),
            )

        document_bytes = etree.tostring(
            document_xml,
            xml_declaration=True,
            encoding="UTF-8",
            standalone=True,
        )

        temp_output = OUTPUT.with_suffix(".tmp.docx")
        if temp_output.exists():
            temp_output.unlink()

        with zipfile.ZipFile(
            temp_output, "w", compression=zipfile.ZIP_DEFLATED
        ) as output_zip:
            for item in source_zip.infolist():
                if item.filename == "word/document.xml":
                    output_zip.writestr(item, document_bytes)
                elif item.filename == media_path:
                    output_zip.writestr(item, IMAGE.read_bytes())
                else:
                    output_zip.writestr(item, source_zip.read(item.filename))

    os.replace(temp_output, OUTPUT)
    print(f"OUTPUT={OUTPUT}")
    print(f"BACKUP={BACKUP}")


if __name__ == "__main__":
    main()
