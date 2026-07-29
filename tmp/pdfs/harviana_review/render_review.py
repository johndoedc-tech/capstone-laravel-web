from __future__ import annotations

import argparse
from pathlib import Path

import pypdfium2 as pdfium
from PIL import Image, ImageDraw, ImageFont


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("input", type=Path)
    parser.add_argument("output_dir", type=Path)
    args = parser.parse_args()
    args.output_dir.mkdir(parents=True, exist_ok=True)

    pdf = pdfium.PdfDocument(str(args.input))
    thumb_w = 330
    thumb_h = 427
    label_h = 24
    cols = 4
    rows = 4
    per_sheet = cols * rows
    font = ImageFont.load_default()

    for sheet_start in range(0, len(pdf), per_sheet):
        sheet = Image.new("RGB", (cols * thumb_w, rows * (thumb_h + label_h)), "#d7d7d7")
        draw = ImageDraw.Draw(sheet)
        for offset in range(per_sheet):
            page_index = sheet_start + offset
            if page_index >= len(pdf):
                break
            page = pdf[page_index]
            bitmap = page.render(scale=1.1)
            image = bitmap.to_pil().convert("RGB")
            image.thumbnail((thumb_w - 10, thumb_h - 10))
            x0 = (offset % cols) * thumb_w
            y0 = (offset // cols) * (thumb_h + label_h)
            x = x0 + (thumb_w - image.width) // 2
            y = y0 + label_h + (thumb_h - image.height) // 2
            sheet.paste(image, (x, y))
            draw.text((x0 + 6, y0 + 5), f"PDF page {page_index + 1}", fill="black", font=font)
        sheet_num = sheet_start // per_sheet + 1
        sheet.save(args.output_dir / f"contact-{sheet_num:02d}.jpg", quality=88)

    # High-resolution renders for the manuscript pages most relevant to the adviser checklist.
    selected = [3, 4, 19, 20, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
                41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56,
                57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72,
                73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88,
                89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103,
                104, 105, 106, 107, 108]
    for page_number in selected:
        if 1 <= page_number <= len(pdf):
            page = pdf[page_number - 1]
            image = page.render(scale=1.8).to_pil().convert("RGB")
            image.save(args.output_dir / f"page-{page_number:03d}.png")

    print(f"pages={len(pdf)} sheets={(len(pdf)+per_sheet-1)//per_sheet}")


if __name__ == "__main__":
    main()
