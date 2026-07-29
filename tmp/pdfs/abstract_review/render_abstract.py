from pathlib import Path
import pypdfium2 as pdfium

pdf_path = Path(r"C:\Users\JDnStefs\Downloads\Harviana chap 1,2, and 3.pdf")
out_dir = Path(r"C:\xampp\htdocs\capstone-laravel-web\tmp\pdfs\abstract_review")
out_dir.mkdir(parents=True, exist_ok=True)

pdf = pdfium.PdfDocument(str(pdf_path))
for page_number in range(3, 7):
    page = pdf[page_number - 1]
    bitmap = page.render(scale=2.0)
    bitmap.to_pil().save(out_dir / f"page-{page_number}.png")
