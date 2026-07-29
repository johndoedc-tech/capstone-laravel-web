param(
    [Parameter(Mandatory = $true)]
    [string]$DocumentPath,

    [Parameter(Mandatory = $true)]
    [string]$PdfPath,

    [Parameter(Mandatory = $true)]
    [string]$StatusPath
)

$ErrorActionPreference = 'Stop'
$word = $null
$document = $null

try {
    "STARTING" | Set-Content -LiteralPath $StatusPath
    $word = New-Object -ComObject Word.Application
    "WORD_CREATED" | Set-Content -LiteralPath $StatusPath
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $word.ScreenUpdating = $false
    $word.AutomationSecurity = 3
    $word.Options.UpdateLinksAtOpen = $false

    $document = $word.Documents.Open($DocumentPath, $false, $true, $false)
    "DOCUMENT_OPENED" | Set-Content -LiteralPath $StatusPath

    $document.Repaginate()
    "REPAGINATED" | Set-Content -LiteralPath $StatusPath
    $pageCount = $document.ComputeStatistics(2)
    $document.ExportAsFixedFormat($PdfPath, 17)
    "SUCCESS`nPages=$pageCount`nPdf=$PdfPath" | Set-Content -LiteralPath $StatusPath
}
catch {
    "ERROR`n$($_.Exception.ToString())" | Set-Content -LiteralPath $StatusPath
    exit 1
}
finally {
    if ($document -ne $null) {
        $document.Close(0)
        [System.Runtime.InteropServices.Marshal]::ReleaseComObject($document) | Out-Null
    }
    if ($word -ne $null) {
        $word.Quit()
        [System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
    }
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}
