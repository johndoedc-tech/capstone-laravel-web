from pathlib import Path
from docx import Document

DOCX = Path(r"C:\Users\JDnStefs\Downloads\Harviana chap 1, 2, 3, & 4 - Copy Final.docx")
doc = Document(DOCX)
for i in range(565, 621):
    p = doc.paragraphs[i]
    for drawing in p._p.xpath('.//w:drawing'):
        blips = drawing.xpath('.//a:blip')
        extents = drawing.xpath('.//wp:extent')
        rid = blips[0].get('{http://schemas.openxmlformats.org/officeDocument/2006/relationships}embed') if blips else None
        partname = doc.part.related_parts[rid].partname if rid in doc.part.related_parts else None
        extent = (extents[0].get('cx'), extents[0].get('cy')) if extents else None
        print(i, rid, partname, extent)
