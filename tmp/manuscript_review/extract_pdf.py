from __future__ import annotations

import argparse
from pathlib import Path

from pypdf import PdfReader


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("input", type=Path)
    parser.add_argument("output", type=Path)
    args = parser.parse_args()
    reader = PdfReader(args.input)
    parts = []
    for index, page in enumerate(reader.pages, start=1):
        parts.append(f"\n===== PDF PAGE {index} =====\n")
        parts.append(page.extract_text() or "")
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text("\n".join(parts), encoding="utf-8")
    print(f"pages={len(reader.pages)}")


if __name__ == "__main__":
    main()
