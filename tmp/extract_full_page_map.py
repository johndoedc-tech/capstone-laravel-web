from pathlib import Path
import re
from pypdf import PdfReader

PDF = Path(r"C:\xampp\htdocs\capstone-laravel-web\tmp\Harviana-features-working.pdf")
reader = PdfReader(PDF)
texts = [" ".join((p.extract_text() or "").split()) for p in reader.pages]

print('PAGE COUNT', len(texts))
print('\nFIGURES')
for n in range(1, 40):
    pat = re.compile(rf"\bFigure\s+{n}\b", re.I)
    hits = [i+1 for i,t in enumerate(texts) if i+1 > 14 and pat.search(t)]
    print(n, hits)

print('\nTABLE TITLES')
table_titles = {
    1: 'Farmer Information',
    2: 'LGU Validator Information',
    3: 'DA-Admin Information',
    4: 'System Requirements',
    5: 'SUS Score Interpretation Scale',
    6: 'Farmers’ SUS Questionnaire Results',
    7: 'Summary of Farmers’ SUS Score',
    8: 'USE Questionnaire Interpretation Scale',
    9: 'Usefulness: Usability Testing Result',
    10: 'Ease of Use: Usability Testing Result',
    11: 'Ease of Learning: Usability Testing Result',
    12: 'Satisfaction: Usability Testing Result',
    13: 'Overall USE Questionnaire Result',
    14: 'Overall Usability Summary',
}
for n,title in table_titles.items():
    hits=[i+1 for i,t in enumerate(texts) if title.lower() in t.lower()]
    print(n,title,hits)

print('\nFIGURE TITLES')
figure_titles = {
    1: 'Scrum Development Process of Harviana',
    2: 'Design Thinking',
    3: 'Empathy Map',
    4: 'The 5 Why Analysis',
    5: 'Impact-Effort Matrix',
    6: 'Landing Page Overview Prototype',
    7: 'Login and Registration Prototype',
    8: 'Crop Planning and Farmer Calendar Prototype',
    9: 'ML-Based Production Estimate Prototype',
    10: 'Damage Report Submission Prototype',
    11: 'Actual Harvest Recording Prototype',
    12: 'LGU Report Validation Prototype',
    13: 'Interactive Map and Weather Prototype',
    14: 'DA Dashboard and Supply Forecast Prototype',
    15: 'Reports and Export Prototype',
    16: 'The “4+1” View Model by Kruchten',
    17: 'Harviana Use Case Diagram',
    18: 'Harviana Class Diagram',
    19: 'Harviana Login Sequence Diagram',
    20: 'Harviana Crop Plan and Production Estimate Sequence Diagram',
    21: 'Harviana Farmer Report and LGU Validation Sequence Diagram',
    22: 'Harviana Sequence Diagram for DA Administrator Supply Forecast',
    23: 'Harviana Sequence Diagram for DA Report Generation and Report',
    24: 'Harviana Package Diagram',
    25: 'Harviana Deployment Diagram',
    26: 'Farmer Dashboard and Crop Recommendations',
    27: 'Crop Planning and Farmer Calendar',
    28: 'ML-Based Production Estimate',
    29: 'Damage Report Submission',
    30: 'Actual Harvest Recording',
    31: 'LGU Report Validation',
    32: 'Administrative Dashboard and Municipal Supply Forecast',
    33: 'Crop Data Management',
    34: 'User and Role Management',
    35: 'Interactive Agricultural Map and Weather',
    36: 'Reports and Export Generation',
    37: 'Business Model Canvas',
    38: 'Harviana Landing Page',
    39: 'User Login and Registration',
}
for n,title in figure_titles.items():
    hits=[i+1 for i,t in enumerate(texts) if i+1 > 14 and title.lower() in t.lower()]
    print(n,title,hits)

print('\nHEADINGS')
headings = [
    'LIST OF FIGURES', 'LIST OF TABLES',
    'Background of the Study', 'Importance of the Study', 'Objectives of the Study', 'Definition of Terms',
    'Software Development Methodology', 'Design Thinking', 'Scope and Delimitation', 'Data Gathering Techniques', 'Sources of Data', 'Software Development Tools',
    'Information Requirements for Harviana', 'Architecture Framework of Harviana', 'Features of Harviana', 'Extent of the Usability of Harviana',
    'CONCLUSIONS AND RECOMMENDATIONS', 'REFERENCES',
]
for heading in headings:
    hits=[i+1 for i,t in enumerate(texts) if heading.lower() in t.lower()]
    print(heading,hits)
