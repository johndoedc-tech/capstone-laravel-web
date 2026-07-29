from copy import deepcopy
from pathlib import Path
import sys

from docx import Document
from docx.oxml import OxmlElement
from docx.text.paragraph import Paragraph


def insert_after(anchor: Paragraph, text: str) -> Paragraph:
    new_p = OxmlElement("w:p")
    anchor._p.addnext(new_p)
    paragraph = Paragraph(new_p, anchor._parent)

    if anchor._p.pPr is not None:
        paragraph._p.append(deepcopy(anchor._p.pPr))

    run = paragraph.add_run(text)
    if anchor.runs and anchor.runs[0]._r.rPr is not None:
        run._r.insert(0, deepcopy(anchor.runs[0]._r.rPr))
    return paragraph


def find_paragraph(document: Document, starts_with: str) -> Paragraph:
    matches = [
        paragraph
        for paragraph in document.paragraphs
        if " ".join(paragraph.text.split()).startswith(starts_with)
    ]
    if len(matches) != 1:
        raise RuntimeError(
            f"Expected one paragraph beginning with {starts_with!r}, found {len(matches)}"
        )
    return matches[0]


def main() -> None:
    if len(sys.argv) != 3:
        raise SystemExit("Usage: expand_harviana_chapter1.py INPUT.docx OUTPUT.docx")

    source = Path(sys.argv[1])
    destination = Path(sys.argv[2])
    document = Document(source)

    additions = [
        (
            "Despite Benguet’s contribution to vegetable production",
            [
                (
                    "The usefulness of agricultural information depends not only on the amount of data collected but also on its completeness, consistency, and connection to the activities from which it originated. Crop plans establish the intended production activity, while damage reports and actual-harvest records describe changes and outcomes during the production cycle. If these records are maintained through separate procedures or contain different data fields, agricultural personnel may have difficulty determining which information is current, verified, and suitable for administrative use. Briones et al. (2023) identified fragmented government information systems and the need to harmonize agricultural data and advisory services as continuing concerns in Philippine digital agriculture. This situation supports the need for a common information structure in which crop, location, production period, planted area, damage, and harvest details can be recorded consistently and traced through the appropriate user workflow."
                ),
                (
                    "Validation is also important because farmer-submitted information may influence production monitoring, municipal supply forecasts, and administrative reports. A structured validation process keeps pending submissions separate from records that have already been reviewed and accepted. It also provides an opportunity to examine supporting details, clarify incomplete entries, and return reports that require correction before they are included in validated records. The E-Agriculture Strategy Guide emphasizes that digital agricultural initiatives require coordinated implementation, clearly defined responsibilities, and appropriate monitoring mechanisms (FAO & ITU, 2016). Similarly, the Digital Agriculture Roadmap Playbook highlights the importance of connecting user applications, databases, integration components, and decision-support tools within an organized digital environment (World Bank et al., 2025). For Harviana, these principles support a role-based flow in which Farmers provide farm-level information, LGU Validators review selected submissions, and DA Administrators use organized and validated records for monitoring and planning."
                ),
            ],
        ),
        (
            "Digital agriculture can also support adaptation to changing environmental conditions",
            [
                (
                    "A spatial and temporal perspective is particularly important when monitoring vegetable production in Benguet. Production records originate from different farms and municipalities, while planting, maintenance, damage, and harvesting activities occur at different periods. Provincial totals can indicate the overall volume of vegetables produced, but aggregated figures alone may not show where crop activities are concentrated or when expected production may become available. This distinction is important because Benguet accounts for a substantial share of vegetable production in the Cordillera Administrative Region (Philippine Statistics Authority RSSO-CAR, 2025). GIS can help organize records according to location, while calendar dates and production periods provide the temporal context needed to interpret planting and harvest activity. Weather information provides an additional reference for understanding field conditions that may affect agricultural schedules. Raihan (2024) and Stephenson et al. (2021) emphasized the value of spatial and digital information for agricultural planning and climate adaptation. Combining location, time, and weather information can therefore help users examine production records according to the municipalities and periods to which they relate."
                )
            ],
        ),
        (
            "Local studies further establish the relevance of forecasting and decision-support systems",
            [
                (
                    "The literature reviewed for this study demonstrates that GIS, crop monitoring, and forecasting can each contribute to agricultural decision-making. WebGIS studies have shown how spatial platforms can assist land and agricultural monitoring (Cui et al., 2022; Patel et al., 2023), while Benguet-based studies have demonstrated the applicability of statistical and machine-learning approaches to vegetable-production forecasting (Pilay & Werdenberg, 2024; Quitaleg & Dumlao, 2023). However, in the studies reviewed for this manuscript, these concerns were generally examined as separate components. Spatial visualization alone does not establish whether a displayed farm record has been reviewed, while a production estimate alone does not provide the complete relationship among a farmer’s crop plan, reported damage, and actual harvest outcome. The reviewed literature therefore indicates an opportunity for an integrated workflow that connects the source of agricultural records with their validation and subsequent administrative use. Harviana addresses this gap by organizing farm-level submissions, validation status, location-based visualization, production estimates, municipal supply monitoring, and reporting within a single role-based environment. This integration allows information to move from Farmer submission through LGU review to DA monitoring while preserving the distinction between planned, reported, and validated agricultural records."
                )
            ],
        ),
    ]

    # Insert each group in reverse order so the paragraphs appear in the order listed.
    for anchor_text, paragraphs in additions:
        anchor = find_paragraph(document, anchor_text)
        current = anchor
        for text in paragraphs:
            if any(text[:80] in p.text for p in document.paragraphs):
                raise RuntimeError(f"Expansion paragraph already exists: {text[:80]!r}")
            current = insert_after(current, text)

    destination.parent.mkdir(parents=True, exist_ok=True)
    document.save(destination)
    print(destination)


if __name__ == "__main__":
    main()
