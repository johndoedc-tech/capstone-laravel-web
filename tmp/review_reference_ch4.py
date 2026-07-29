from __future__ import annotations

import re
import sys
import zipfile
from pathlib import Path
from xml.etree import ElementTree as ET

from pypdf import PdfReader


W = "{http://schemas.openxmlformats.org/wordprocessingml/2006/main}"


def docx_paragraphs(path: Path) -> list[str]:
    with zipfile.ZipFile(path) as archive:
        root = ET.fromstring(archive.read("word/document.xml"))
    paragraphs: list[str] = []
    for paragraph in root.iter(W + "p"):
        text = "".join(node.text or "" for node in paragraph.iter(W + "t")).strip()
        if text:
            paragraphs.append(re.sub(r"\s+", " ", text))
    return paragraphs


def print_chapter_context(label: str, paragraphs: list[str]) -> None:
    matches = [
        index
        for index, text in enumerate(paragraphs)
        if re.search(r"\bchapter\s*(4|iv)\b", text, re.I)
        or re.fullmatch(r"(summary|conclusions?|recommendations?|summary of findings|conclusions? and recommendations?)", text.strip(), re.I)
    ]
    print(f"===== {label} =====")
    for index in matches[-12:]:
        start = max(0, index - 2)
        end = min(len(paragraphs), index + 10)
        print(f"--- match paragraph {index}: {paragraphs[index]} ---")
        for current in range(start, end):
            print(f"{current}: {paragraphs[current]}")


def pdf_paragraphs(path: Path) -> list[str]:
    reader = PdfReader(str(path))
    pages = [page.extract_text() or "" for page in reader.pages]
    paragraphs: list[str] = []
    for page_number, page_text in enumerate(pages, start=1):
        for line in page_text.splitlines():
            line = re.sub(r"\s+", " ", line).strip()
            if line:
                paragraphs.append(f"[p{page_number}] {line}")
    return paragraphs


def main() -> None:
    docx = Path(sys.argv[1])
    pdf = Path(sys.argv[2])
    print_chapter_context("TULONGLEGAL DOCX", docx_paragraphs(docx))
    print_chapter_context("SMARTHARVEST PDF", pdf_paragraphs(pdf))


if __name__ == "__main__":
    main()
