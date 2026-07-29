from pathlib import Path
import re
import sys

from pypdf import PdfReader


pdf_path = Path(sys.argv[1])
reader = PdfReader(str(pdf_path))
texts = [" ".join((page.extract_text() or "").split()) for page in reader.pages]

for prefix, count in (("Figure", 39), ("Table", 14)):
    print(prefix.upper())
    for number in range(1, count + 1):
        pattern = re.compile(rf"\b{prefix}\s+{number}\b", re.IGNORECASE)
        pages = [index + 1 for index, text in enumerate(texts) if pattern.search(text)]
        print(number, pages)

print("HEADINGS")
headings = [
    "Background of the Study",
    "Importance of the Study",
    "Objectives of the Study",
    "Definition of Terms",
    "Software Development Methodology",
    "Design Thinking",
    "Scope and Delimitation of the Study",
    "Data Gathering Techniques",
    "Sources of Data",
    "Software Development Tools",
    "Information Requirements for Harviana",
    "Framework Architecture of Harviana",
    "Features of Harviana",
    "Extent of the Usability of Harviana",
    "Conclusions",
    "Recommendations",
    "References",
    "Appendix A",
    "Appendix B",
    "Appendix C",
    "Appendix D",
    "Appendix E",
    "Appendix F",
]
for heading in headings:
    needle = re.compile(re.escape(heading), re.IGNORECASE)
    pages = [index + 1 for index, text in enumerate(texts) if needle.search(text)]
    print(heading, pages)

print("TOTAL", len(reader.pages))
