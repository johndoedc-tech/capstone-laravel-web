from pathlib import Path
import sys

import pypdfium2 as pdfium


source = Path(sys.argv[1])
output = Path(sys.argv[2])
output.mkdir(parents=True, exist_ok=True)
pdf = pdfium.PdfDocument(str(source))
for page_number in range(3, 7):
    page = pdf[page_number - 1]
    image = page.render(scale=1.8).to_pil().convert("RGB")
    image.save(output / f"page-{page_number:03d}.png")
