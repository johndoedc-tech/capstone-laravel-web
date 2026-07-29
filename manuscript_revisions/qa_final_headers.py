import re
from pathlib import Path

from pypdf import PdfReader


PDF = Path(
    r"C:\xampp\htdocs\capstone-laravel-web\manuscript_revisions"
    r"\final_render.pdf"
)

reader = PdfReader(PDF)

expected = {}


def assign(start, end, label):
    for page in range(start, end + 1):
        expected[page] = label


assign(1, 3, None)
assign(4, 5, "Abstract")
assign(6, 6, None)
assign(7, 7, "Acknowledgement")
assign(8, 9, None)
assign(10, 10, "Table of Contents")
assign(11, 11, None)
assign(12, 13, "List of Figures")
assign(14, 15, None)
assign(16, 39, "Introduction")
assign(40, 40, None)
assign(41, 81, "Methodology")
assign(82, 82, None)
assign(83, 128, "Findings of the Study")
assign(129, 129, None)
assign(130, 132, "Conclusions and Recommendations")
assign(133, 133, None)
assign(134, 140, "References")
assign(141, 143, None)
assign(144, 144, "Appendices")
assign(145, 145, None)
assign(146, 147, "Appendices")
assign(148, 152, None)
assign(153, 153, "Appendices")
assign(154, 154, None)
assign(155, 158, "Appendices")
assign(159, 159, None)
assign(160, 161, "Appendices")
assign(162, 162, None)
assign(163, 163, "Curriculum Vitae")
assign(164, 164, None)
assign(165, 165, "Curriculum Vitae")
assign(166, 166, None)
assign(167, 167, "Curriculum Vitae")

known_labels = [
    "Conclusions and Recommendations",
    "Findings of the Study",
    "Table of Contents",
    "List of Figures",
    "List of Tables",
    "Curriculum Vitae",
    "Acknowledgement",
    "Introduction",
    "Methodology",
    "References",
    "Appendices",
    "Dedication",
    "Abstract",
]

problems = []
for page_number, page in enumerate(reader.pages, start=1):
    text = page.extract_text() or ""
    actual = None
    for label in known_labels:
        if re.search(rf"\b{re.escape(label)}\s+{page_number}\b", text):
            actual = label
            break
    wanted = expected.get(page_number)
    if actual != wanted:
        problems.append((page_number, wanted, actual, text[:120].replace("\n", " | ")))

print(f"pages={len(reader.pages)}")
print(f"header_mismatches={len(problems)}")
for problem in problems:
    print(problem)
