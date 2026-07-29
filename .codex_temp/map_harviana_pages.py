from pathlib import Path
import sys

import pdfplumber


FIGURES = [
    "Scrum Framework Applied to Harviana",
    "Design Thinking",
    "Empathy Map",
    "The 5 Why Analysis",
    "Impact-Effort Matrix",
    "Overall Low-Fidelity Prototype of Harviana",
    "Login and Registration Prototype",
    "Crop Planning and Farmer Calendar Prototype",
    "ML-Based Production Estimate Prototype",
    "Damage Report Submission Prototype",
    "Actual Harvest Recording Prototype",
    "LGU Report Validation Prototype",
    "Interactive Map and Weather Prototype",
    "DA Dashboard and Supply Forecast Prototype",
    "Reports and Export Prototype",
    "4+1",
    "Harviana Use Case Diagram",
    "Harviana Class Diagram",
    "Harviana Login Sequence Diagram",
    "Harviana Crop Plan and Production Estimate Sequence Diagram",
    "Harviana Farmer Report and LGU Validation",
    "Harviana Sequence Diagram for DA Administrator",
    "Harviana Sequence Diagram for DA Report Generation",
    "Harviana Package Diagram",
    "Harviana Deployment Diagram",
    "Farmer Dashboard",
    "Farmer Calendar and Crop Planning",
    "ML-Based Production Estimate",
    "Damage Report Submission",
    "Actual Harvest Recording",
    "LGU Report Validation",
    "Admin Dashboard and Supply Forecast",
    "Crop Data Management",
    "Interactive Map and Weather",
    "Reports and Export Generation",
    "Business Model Canvas",
]

TABLES = [
    "Farmer Information",
    "LGU Validator Information",
    "DA-Admin Information",
    "System Requirements",
    "SUS Score Interpretation Scale",
    "Farmers’ SUS Questionnaire Results",
    "Summary of Farmers’ SUS Score",
    "Seven-Point USE Questionnaire",
    "Usefulness: Usability Testing Result",
    "Ease of Use: Usability Testing Result",
    "Ease of Learning: Usability Testing Result",
    "Satisfaction: Usability Testing Result",
    "Overall USE Questionnaire Result",
    "Overall Usability Summary of Harviana",
]


def body_hits(texts: list[str], needle: str, first_page: int = 35) -> list[int]:
    return [
        index
        for index, text in enumerate(texts, start=1)
        if index >= first_page and needle.casefold() in text.casefold()
    ]


def main() -> None:
    pdf_path = Path(sys.argv[1])
    with pdfplumber.open(pdf_path) as pdf:
        texts = [page.extract_text() or "" for page in pdf.pages]

    print("FIGURES")
    for number, title in enumerate(FIGURES, start=1):
        print(f"{number:02d}|{body_hits(texts, title)}|{title}")

    print("TABLES")
    for number, title in enumerate(TABLES, start=1):
        print(f"{number:02d}|{body_hits(texts, title)}|{title}")


if __name__ == "__main__":
    main()
