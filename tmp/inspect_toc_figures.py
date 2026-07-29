from pathlib import Path
from docx import Document

DOCX = Path(r"C:\Users\JDnStefs\Downloads\Harviana chap 1, 2, 3, & 4 - Copy Final.docx")
doc = Document(DOCX)
terms = [
    'features of harviana', 'harviana landing page', 'user login and registration',
    'farmer dashboard', 'farmer calendar', 'ml-based production', 'damage report submission',
    'actual harvest recording', 'lgu report validation', 'administrator dashboard',
    'interactive agricultural map', 'reports and export generation', 'appendix e', 'appendix f',
    'list of figures', 'table of contents'
]
for i,p in enumerate(doc.paragraphs):
    text=' '.join(p.text.split())
    low=text.lower()
    if any(t in low for t in terms):
        print(f"{i:04d} | {p.style.name!r} | {text[:260]}")
