from pathlib import Path
from docx import Document

DOCX = Path(r"C:\Users\JDnStefs\Downloads\Harviana chap 1, 2, 3, & 4 - Copy Final.docx")
doc = Document(DOCX)
for i in range(150, 255):
    p=doc.paragraphs[i]
    text=' '.join(p.text.split())
    if text:
        print(f"{i:04d} | {p.style.name!r} | {text}")
