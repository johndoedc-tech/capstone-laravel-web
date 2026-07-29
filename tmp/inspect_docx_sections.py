from pathlib import Path
from docx import Document

DOCX = Path(r"C:\Users\JDnStefs\Downloads\Harviana chap 1, 2, 3, & 4 - Copy Final.docx")
doc = Document(DOCX)
print('sections', len(doc.sections))
for i, s in enumerate(doc.sections):
    def txt(part):
        return ' | '.join(' '.join(p.text.split()) for p in part.paragraphs if p.text.strip())
    print('\nSECTION', i)
    print('start_type', s.start_type, 'different_first', s.different_first_page_header_footer)
    print('header_linked', s.header.is_linked_to_previous, 'header', repr(txt(s.header)))
    print('first_header_linked', s.first_page_header.is_linked_to_previous, 'first_header', repr(txt(s.first_page_header)))
    print('footer_linked', s.footer.is_linked_to_previous, 'footer', repr(txt(s.footer)))
    print('first_footer_linked', s.first_page_footer.is_linked_to_previous, 'first_footer', repr(txt(s.first_page_footer)))
