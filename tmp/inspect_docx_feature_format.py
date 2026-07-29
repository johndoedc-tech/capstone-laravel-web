from pathlib import Path
from docx import Document

DOCX = Path(r"C:\Users\JDnStefs\Downloads\Harviana chap 1, 2, 3, & 4 - Copy Final.docx")
doc = Document(DOCX)
for i in [565,566,567,568,569,570,571,572,573,574,575,576,577,578,579,580,581,582,583,584,585,600,601,602,603,604,605,606,607,608,609,610,611,612,613,614,615,616,617,618,619,620,621]:
    p=doc.paragraphs[i]
    pf=p.paragraph_format
    print('\nP',i,'style',p.style.name,'align',p.alignment,'keep_next',pf.keep_with_next,'page_before',pf.page_break_before,'left',pf.left_indent,'first',pf.first_line_indent,'before',pf.space_before,'after',pf.space_after,'line',pf.line_spacing)
    print('text:',repr(p.text[:300]))
    for j,r in enumerate(p.runs):
        f=r.font
        print(' run',j,repr(r.text[:120]),'bold',r.bold,'italic',r.italic,'size',f.size,'name',f.name,'color',f.color.rgb if f.color and f.color.type else None,'breaks',len(r._r.xpath('.//w:br')))
