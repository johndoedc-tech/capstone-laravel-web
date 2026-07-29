Option Explicit

Dim documentPath, pdfPath, statusPath
Dim wordApplication, document, storyRange, currentRange

documentPath = WScript.Arguments(0)
pdfPath = WScript.Arguments(1)
statusPath = WScript.Arguments(2)

On Error Resume Next

Set wordApplication = CreateObject("Word.Application")
If Err.Number <> 0 Then
    WriteStatus "ERROR creating Word: " & Err.Description
    WScript.Quit 1
End If

wordApplication.Visible = False
wordApplication.DisplayAlerts = 0
wordApplication.ScreenUpdating = False
wordApplication.AutomationSecurity = 3
wordApplication.Options.UpdateLinksAtOpen = False

Err.Clear
Set document = wordApplication.Documents.Open( _
    documentPath, _
    False, _
    False, _
    False, _
    "", _
    "", _
    False, _
    "", _
    "", _
    0, _
    0, _
    False, _
    False, _
    0, _
    True _
)
If Err.Number <> 0 Then
    WriteStatus "ERROR opening document: " & Err.Description
    wordApplication.Quit 0
    WScript.Quit 2
End If

document.Repaginate
document.Fields.Update

For Each storyRange In document.StoryRanges
    Set currentRange = storyRange
    Do While Not currentRange Is Nothing
        currentRange.Fields.Update
        Set currentRange = currentRange.NextStoryRange
    Loop
Next

document.Repaginate
document.Save
document.ExportAsFixedFormat pdfPath, 17

If Err.Number <> 0 Then
    WriteStatus "ERROR updating or exporting: " & Err.Description
    document.Close 0
    wordApplication.Quit 0
    WScript.Quit 3
End If

WriteStatus "SUCCESS"
document.Close 0
wordApplication.Quit 0
WScript.Quit 0

Sub WriteStatus(value)
    Dim filesystem, file
    Set filesystem = CreateObject("Scripting.FileSystemObject")
    Set file = filesystem.CreateTextFile(statusPath, True)
    file.WriteLine value
    file.Close
End Sub
