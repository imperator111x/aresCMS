@echo off
REM Doppelklick-Shortcut im Projektroot — ruft das eigentliche Skript auf.
call "%~dp0scripts\build-update-package.bat" %*
