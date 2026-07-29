import re
import sys
from pypdf import PdfReader

reader = PdfReader(sys.argv[1])
texts = [(reader.pages[index].extract_text() or "") for index in range(2, 6)]
combined = "\n".join(texts)
body = combined[combined.find("7.1"):]
pattern = re.compile(r"\b[\w’'-]+\b")
print("body_words", len(pattern.findall(body)))
print("page_words", [len(pattern.findall(text)) for text in texts])
