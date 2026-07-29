from pathlib import Path
from pypdf import PdfReader

PDF = Path(r"C:\xampp\htdocs\capstone-laravel-web\tmp\Harviana-features-working.pdf")
reader = PdfReader(PDF)
texts = [" ".join((p.extract_text() or "").split()) for p in reader.pages]

terms = [
    "Features of Harviana",
    "Extent of the Usability of Harviana",
    "CONCLUSIONS AND RECOMMENDATIONS",
    "Conclusions",
    "Recommendations",
    "REFERENCES",
    "Appendix A", "Appendix B", "Appendix C", "Appendix D", "Appendix E", "Appendix F",
    "Figure 26", "Figure 27", "Figure 28", "Figure 29", "Figure 30", "Figure 31", "Figure 32", "Figure 33", "Figure 34", "Figure 35", "Figure 36", "Figure 37", "Figure 38", "Figure 39",
    "Table 5", "Table 6", "Table 7", "Table 8", "Table 9", "Table 10", "Table 11", "Table 12", "Table 13", "Table 14",
]

print("pages", len(reader.pages))
for term in terms:
    hits = [i + 1 for i, text in enumerate(texts) if term.lower() in text.lower()]
    print(term, hits)

print("\nFEATURE PAGES")
for i, text in enumerate(texts, 1):
    if 85 <= i <= 125:
        if any(f"Figure {n}" in text for n in range(25, 37)) or "Features of Harviana" in text or "Extent of the Usability" in text:
            print(i, text[:450])

print("\nAPPENDIX PAGES")
for i, text in enumerate(texts, 1):
    if i >= 125 and any(x in text for x in ["Appendix A", "Appendix B", "Appendix C", "Appendix D", "Appendix E", "Appendix F", "Figure 37", "Figure 38", "Figure 39"]):
        print(i, text[:450])
