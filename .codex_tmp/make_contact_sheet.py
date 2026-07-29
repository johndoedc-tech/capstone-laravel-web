from __future__ import annotations

import pathlib
import sys

from PIL import Image, ImageDraw


source = pathlib.Path(sys.argv[1])
destination = pathlib.Path(sys.argv[2])
pages = [int(value) for value in sys.argv[3:]]
columns = 2
thumb_width = 680
margin = 18
label_height = 28

thumbs = []
for page in pages:
    image = Image.open(source / f"page-{page:03d}.png").convert("RGB")
    ratio = thumb_width / image.width
    thumb = image.resize((thumb_width, round(image.height * ratio)))
    thumbs.append((page, thumb))

rows = (len(thumbs) + columns - 1) // columns
cell_height = max(image.height for _, image in thumbs) + label_height
sheet = Image.new(
    "RGB",
    (
        margin + columns * (thumb_width + margin),
        margin + rows * (cell_height + margin),
    ),
    "#d9d9d9",
)
draw = ImageDraw.Draw(sheet)
for index, (page, image) in enumerate(thumbs):
    row, column = divmod(index, columns)
    x = margin + column * (thumb_width + margin)
    y = margin + row * (cell_height + margin)
    draw.text((x, y + 4), f"Page {page}", fill="black")
    sheet.paste(image, (x, y + label_height))

destination.parent.mkdir(parents=True, exist_ok=True)
sheet.save(destination)
