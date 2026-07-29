$ErrorActionPreference = 'Stop'

$sourcePath = 'C:\xampp\htdocs\capstone-laravel-web\.codex_tmp\harviana_work\harviana_pre_header_fix.docx'
$workPath = 'C:\xampp\htdocs\capstone-laravel-web\.codex_tmp\harviana_work\harviana_header_working.docx'
$pdfPath = 'C:\xampp\htdocs\capstone-laravel-web\.codex_tmp\harviana_work\harviana_header_working.pdf'

Copy-Item -LiteralPath $sourcePath -Destination $workPath -Force

$word = [System.Runtime.InteropServices.Marshal]::GetActiveObject('Word.Application')
$word.DisplayAlerts = 0
$doc = $word.Documents.Open($workPath)

function Get-LastMatchStart {
    param(
        [Parameter(Mandatory = $true)] $Document,
        [Parameter(Mandatory = $true)] [string] $Text
    )

    # Word range positions match indices in the main document story. Using
    # LastIndexOf avoids Word Find repeatedly returning the same match.
    $contentText = [string] $Document.Content.Text
    $last = $contentText.LastIndexOf($Text, [System.StringComparison]::Ordinal)

    if ($last -lt 0) {
        throw "Could not find required section marker: $Text"
    }

    return $last
}

function Get-SectionForMarker {
    param(
        [Parameter(Mandatory = $true)] $Document,
        [Parameter(Mandatory = $true)] [string] $Text
    )

    $position = Get-LastMatchStart -Document $Document -Text $Text
    $start = $position
    $end = $position
    $range = $Document.Range([ref] $start, [ref] $end)
    return $range.Sections.Item(1)
}

function Copy-Story {
    param(
        [Parameter(Mandatory = $true)] $Source,
        [Parameter(Mandatory = $true)] $Destination
    )

    $Destination.LinkToPrevious = $false
    # Header stories containing anchored shapes cannot always be assigned
    # through FormattedText. Word's native copy/paste preserves the logo,
    # green rules, field codes, and paragraph formatting across stories.
    [void] $Source.Range.Copy()
    [void] $Destination.Range.Paste()
}

function Set-HeaderLabel {
    param(
        [Parameter(Mandatory = $true)] $Header,
        [Parameter(Mandatory = $true)] [string] $Label
    )

    $range = $Header.Range.Duplicate
    $find = $range.Find
    $find.ClearFormatting()
    $find.Text = 'Appendices'
    $find.Forward = $true
    $find.Wrap = 0
    $find.Format = $false
    if (-not $find.Execute()) {
        throw "The clean template header did not contain the expected label."
    }
    $range.Text = $Label
    [void] $Header.Range.Fields.Update()
}

function Apply-StandardFurniture {
    param(
        [Parameter(Mandatory = $true)] $Section,
        [Parameter(Mandatory = $true)] $TemplateSection,
        [Parameter(Mandatory = $true)] [string] $Label
    )

    $Section.PageSetup.DifferentFirstPageHeaderFooter = -1

    Copy-Story -Source $TemplateSection.Headers.Item(1) -Destination $Section.Headers.Item(1)
    $Section.Headers.Item(2).LinkToPrevious = $true
    Set-HeaderLabel -Header $Section.Headers.Item(1) -Label $Label
}

function Apply-CurriculumVitaeFurniture {
    param(
        [Parameter(Mandatory = $true)] $Section,
        [Parameter(Mandatory = $true)] $TemplateSection,
        [Parameter(Mandatory = $true)] $BlankSection
    )

    $Section.PageSetup.DifferentFirstPageHeaderFooter = -1

    Copy-Story -Source $TemplateSection.Headers.Item(1) -Destination $Section.Headers.Item(1)
    Set-HeaderLabel -Header $Section.Headers.Item(1) -Label 'Curriculum Vitae'
    $Section.Headers.Item(2).LinkToPrevious = $true
}

function Set-ParagraphPageNumber {
    param(
        [Parameter(Mandatory = $true)] $Paragraph,
        [Parameter(Mandatory = $true)] [int] $PageNumber
    )

    $paragraphRange = $Paragraph.Range.Duplicate
    $text = $paragraphRange.Text.TrimEnd([char] 13, [char] 7)
    $match = [regex]::Match($text, '\d+\s*$')
    if ($match.Success) {
        $start = $paragraphRange.Start + $match.Index
        $end = $start + $match.Length
        $numberRange = $Paragraph.Range.Document.Range([ref] $start, [ref] $end)
        $numberRange.Text = [string] $PageNumber
    }
    else {
        $insertAt = $paragraphRange.End - 1
        $start = $insertAt
        $end = $insertAt
        $numberRange = $Paragraph.Range.Document.Range([ref] $start, [ref] $end)
        $numberRange.Text = "  $PageNumber"
    }
}

try {
    # Insert continuous section breaks at headings that already begin on new pages.
    # Reverse-order insertion keeps all previously located character positions valid.
    $markers = @(
        'LIST OF FIGURES',
        'Appendix F',
        'Appendix G',
        'Appendix H',
        'Appendix I',
        'Appendix J',
        'Appendix K',
        'Appendix L',
        'Appendix M',
        'Appendix N',
        'CURRICULUM VITAE',
        'LORD ALFREY T. BATERINA'
    )

    $breaks = foreach ($marker in $markers) {
        [pscustomobject]@{
            Marker = $marker
            Start = Get-LastMatchStart -Document $doc -Text $marker
        }
    }

    foreach ($item in ($breaks | Sort-Object Start -Descending)) {
        $start = $item.Start
        $end = $item.Start
        $range = $doc.Range([ref] $start, [ref] $end)
        $range.InsertBreak(3)
    }

    # Appendix A already has the clean, approved header/footer pattern.
    $templateSection = Get-SectionForMarker -Document $doc -Text 'Appendix A'
    $blankSection = $doc.Sections.Item(1)

    # Front matter headers.
    Apply-StandardFurniture -Section $doc.Sections.Item(6) -TemplateSection $templateSection -Label 'Table of Contents'
    Apply-StandardFurniture -Section (Get-SectionForMarker -Document $doc -Text 'LIST OF FIGURES') -TemplateSection $templateSection -Label 'List of Figures'
    Apply-StandardFurniture -Section (Get-SectionForMarker -Document $doc -Text 'LIST OF TABLES') -TemplateSection $templateSection -Label 'List of Tables'

    # Every appendix opening page uses the first-page header without a label or page number.
    foreach ($marker in @('Appendix A', 'Appendix B', 'Appendix C', 'Appendix D', 'Appendix E', 'Appendix F', 'Appendix G', 'Appendix H', 'Appendix I', 'Appendix J', 'Appendix K', 'Appendix L', 'Appendix M', 'Appendix N')) {
        Apply-StandardFurniture -Section (Get-SectionForMarker -Document $doc -Text $marker) -TemplateSection $templateSection -Label 'Appendices'
    }

    # Each team member's CV starts without a header; continuation pages receive the CV header and page number.
    foreach ($marker in @('CURRICULUM VITAE', 'LORD ALFREY T. BATERINA')) {
        Apply-CurriculumVitaeFurniture -Section (Get-SectionForMarker -Document $doc -Text $marker) -TemplateSection $templateSection -BlankSection $blankSection
    }

    # Correct the manual Table of Contents page numbers against the current 152-page layout.
    $tocSection = $doc.Sections.Item(6)
    $tocPages = [ordered]@{
        9 = 12
        10 = 14
        13 = 15
        14 = 28
        15 = 31
        16 = 32
        18 = 37
        19 = 44
        20 = 63
        21 = 65
        22 = 67
        23 = 69
        25 = 74
        26 = 78
        27 = 91
        28 = 102
        30 = 118
        31 = 120
        32 = 122
        34 = 129
        35 = 130
        36 = 131
        37 = 133
        38 = 136
        39 = 137
        40 = 138
        41 = 139
        42 = 141
        43 = 142
        44 = 143
        45 = 144
        46 = 145
        47 = 146
        48 = 149
    }

    foreach ($entry in $tocPages.GetEnumerator()) {
        Set-ParagraphPageNumber -Paragraph $tocSection.Range.Paragraphs.Item([int] $entry.Key) -PageNumber ([int] $entry.Value)
    }

    $doc.Repaginate()
    foreach ($section in $doc.Sections) {
        [void] $section.Headers.Item(1).Range.Fields.Update()
    }
    $doc.Save()
    $doc.ExportAsFixedFormat($pdfPath, 17)
    Write-Output "WORK_DOC=$workPath"
    Write-Output "WORK_PDF=$pdfPath"
    Write-Output "SECTIONS=$($doc.Sections.Count)"
    Write-Output "PAGES=$($doc.ComputeStatistics(2))"
}
finally {
    $saveOption = 0
    $doc.Close([ref] $saveOption)
}
