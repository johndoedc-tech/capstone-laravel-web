from __future__ import annotations

import argparse
import io
import zipfile
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("input", type=Path)
    parser.add_argument("output_dir", type=Path)
    args = parser.parse_args()
    args.output_dir.mkdir(parents=True, exist_ok=True)

    entries = []
    with zipfile.ZipFile(args.input) as zf:
        for name in sorted(n for n in zf.namelist() if n.startswith("word/media/")):
            data = zf.read(name)
            out = args.output_dir / Path(name).name
            out.write_bytes(data)
            try:
                img = Image.open(io.BytesIO(data)).convert("RGB")
                entries.append((Path(name).name, img.copy()))
            except Exception:
                continue

    tile_w, tile_h = 360, 270
    cols = 4
    rows = (len(entries) + cols - 1) // cols
    sheet = Image.new("RGB", (cols * tile_w, rows * tile_h), "white")
    draw = ImageDraw.Draw(sheet)
    font = ImageFont.load_default()
    for i, (name, img) in enumerate(entries):
        x = (i % cols) * tile_w
        y = (i // cols) * tile_h
        img.thumbnail((tile_w - 16, tile_h - 38))
        ix = x + (tile_w - img.width) // 2
        iy = y + 22 + (tile_h - 38 - img.height) // 2
        sheet.paste(img, (ix, iy))
        draw.rectangle((x, y, x + tile_w - 1, y + tile_h - 1), outline="#999")
        draw.text((x + 6, y + 5), f"{name} {img.width}x{img.height}", fill="black", font=font)

    sheet.save(args.output_dir / "contact_sheet.jpg", quality=90)
    print(f"images={len(entries)} sheet={args.output_dir / 'contact_sheet.jpg'}")


if __name__ == "__main__":
    main()
