' Startet den Update-Builder ohne UAC-Schild auf der Verknüpfung.
Set fso = CreateObject("Scripting.FileSystemObject")
root = fso.GetParentFolderName(fso.GetParentFolderName(WScript.ScriptFullName))
bat = root & "\Update-Paket bauen.bat"
CreateObject("WScript.Shell").Run "cmd /c """ & bat & """", 1, False
