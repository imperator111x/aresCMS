@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion
title aresCMS — Update-Paket bauen
color 07

call :init_ansi

set "PROJECT_ROOT=%~dp0.."
cd /d "%PROJECT_ROOT%"

set "PACKAGE_BASE=https://update.luetcke.eu"
set "DEPLOY_DIR=deploy\system-upgrade"
set "UPDATE_DIR=public\update"
set "DEFAULT_RELEASE=1.0.2"

call :banner
call :choose_version
if errorlevel 1 goto :fail

call :ensure_release_notes
if errorlevel 1 goto :fail

set "NOTES_FILE=%DEPLOY_DIR%\RELEASE_NOTES_%RELEASE%.txt"
set "ZIP_NAME=news-portal-update-%RELEASE%.zip"

cls
call :banner
echo !ESC![37m
echo.
echo   Version:      %RELEASE%
echo   Update-Host:  %PACKAGE_BASE%
echo   Projekt:      %CD%
echo.

call :find_php
if errorlevel 1 goto :fail

call :prepare_project
if errorlevel 1 goto :fail

if not exist "%NOTES_FILE%" (
    echo   [FEHLER] Release-Notes fehlen trotz Anlage:
    echo            %NOTES_FILE%
    goto :fail
)

"%PHP_EXE%" -r "exit(version_compare(PHP_VERSION,'8.4.0','>=')?0:1);" >nul 2>&1
if errorlevel 1 (
    echo   [Hinweis] PHP ^< 8.4 — Fallback-Skript wird genutzt.
    echo.
    goto :fallback
)

echo   Erzeuge Paket mit Artisan ...
echo.

"%PHP_EXE%" artisan cms:create-update ^
    --release=%RELEASE% ^
    --package-base=%PACKAGE_BASE% ^
    --notes-file=%NOTES_FILE% ^
    --force

if errorlevel 1 goto :fail
goto :copy_deploy

:fallback
if not exist "scripts\build-cms-update.php" (
    echo   [FEHLER] Weder Artisan noch scripts\build-cms-update.php verfügbar.
    goto :fail
)
echo   Erzeuge Paket mit build-cms-update.php ...
echo.
"%PHP_EXE%" -d extension=zip scripts\build-cms-update.php ^
    --release=%RELEASE% ^
    --package-base=%PACKAGE_BASE% ^
    --notes-file=%NOTES_FILE%
if errorlevel 1 goto :fail

:copy_deploy
if not exist "%DEPLOY_DIR%" mkdir "%DEPLOY_DIR%"
if exist "%UPDATE_DIR%\manifest.json" copy /Y "%UPDATE_DIR%\manifest.json" "%DEPLOY_DIR%\" >nul
if exist "%UPDATE_DIR%\%ZIP_NAME%" copy /Y "%UPDATE_DIR%\%ZIP_NAME%" "%DEPLOY_DIR%\" >nul

echo.
echo   ========================================================================
echo     Fertig!
echo   ========================================================================
echo.
echo   ZIP:      %UPDATE_DIR%\%ZIP_NAME%
echo   Manifest: %UPDATE_DIR%\manifest.json
echo   Upload:   %DEPLOY_DIR%\
echo.
echo   Hochladen auf %PACKAGE_BASE%/
echo     - %ZIP_NAME%
echo     - manifest.json
echo.
explorer "%CD%\%DEPLOY_DIR%"
goto :end

:fail
echo.
echo   [ABBRUCH] Paket konnte nicht erstellt werden.
echo.

:end
echo !ESC![0m
pause
endlocal
exit /b 0

REM ---------------------------------------------------------------------------
:init_ansi
for /F %%a in ('echo prompt $E ^| cmd') do set "ESC=%%a"
reg add HKCU\Console /v VirtualTerminalLevel /t REG_DWORD /d 1 /f >nul 2>&1
exit /b 0

REM ---------------------------------------------------------------------------
:banner
cls
echo.
echo !ESC![37m   ·······························································
echo !ESC![31m   : █████╗ ██████╗ ███████╗███████╗     ██████╗███╗   ███╗███████╗:
echo   :██╔══██╗██╔══██╗██╔════╝██╔════╝    ██╔════╝████╗ ████║██╔════╝:
echo   :███████║██████╔╝█████╗  ███████╗    ██║     ██╔████╔██║███████╗:
echo   :██╔══██║██╔══██╗██╔══╝  ╚════██║    ██║     ██║╚██╔╝██║╚════██║:
echo   :██║  ██║██║  ██║███████╗███████║    ╚██████╗██║ ╚═╝ ██║███████║:
echo   :╚═╝  ╚═╝╚═╝  ╚═╝╚══════╝╚══════╝     ╚═════╝╚═╝     ╚═╝╚══════╝:
echo !ESC![37m   ·······························································
echo.
echo        ========================================================================
echo !ESC![34m                          U p d a t e - P a k e t   B u i l d e r
echo !ESC![37m        ========================================================================
echo.
exit /b 0

REM ---------------------------------------------------------------------------
:choose_version
set "RELEASE="
set "VERSION_COUNT=0"
echo !ESC![37m

if not "%~1"=="" (
    set "RELEASE=%~1"
    exit /b 0
)

for /f "tokens=2 delims=," %%a in ('findstr /C:"CMS_BUNDLE_VERSION" "config\cms.php" 2^>nul') do (
    set "DEFAULT_RELEASE=%%a"
    set "DEFAULT_RELEASE=!DEFAULT_RELEASE:'=!"
    set "DEFAULT_RELEASE=!DEFAULT_RELEASE: =!"
    set "DEFAULT_RELEASE=!DEFAULT_RELEASE:)=!"
)

echo   Verfügbare Versionen ^(Release-Notes^):
echo.

if not exist "%DEPLOY_DIR%\RELEASE_NOTES_*.txt" (
    echo     [Keine RELEASE_NOTES_*.txt in %DEPLOY_DIR%]
    echo.
) else (
    for %%F in ("%DEPLOY_DIR%\RELEASE_NOTES_*.txt") do (
        set /a VERSION_COUNT+=1
        set "FNAME=%%~nF"
        set "FNAME=!FNAME:RELEASE_NOTES_=!"
        set "VER_!VERSION_COUNT!=!FNAME!"
        echo     [!VERSION_COUNT!]  !FNAME!
    )
    echo.
)

echo   Nummer wählen, Version eingeben ^(z.B. 1.0.3^)
echo   Fehlen Release-Notes, kannst du sie im nächsten Schritt hier eingeben.
echo   oder Enter für Standard [%DEFAULT_RELEASE%]:
echo.
set /p "USER_CHOICE=   ^> "

if "!USER_CHOICE!"=="" (
    set "RELEASE=!DEFAULT_RELEASE!"
    exit /b 0
)

set "PICK=!USER_CHOICE!"
set "PICK=!PICK: =!"

echo !PICK!| findstr /r "^[0-9][0-9]*$" >nul
if not errorlevel 1 (
    if !PICK! GEQ 1 if !PICK! LEQ !VERSION_COUNT! (
        set "RELEASE=!VER_%PICK%!"
        exit /b 0
    )
    echo.
    echo   [FEHLER] Ungültige Nummer. Bitte 1 bis !VERSION_COUNT! wählen.
    pause
    exit /b 1
)

set "RELEASE=!PICK!"
exit /b 0

REM ---------------------------------------------------------------------------
:prepare_project
echo   ========================================================================
echo   Projekt vorbereiten (Composer, Frontend, Laravel)
echo   ========================================================================
echo.

call :prepare_composer
if errorlevel 1 exit /b 1

call :prepare_assets
REM Assets optional — Fehler nur warnen

call :prepare_artisan
if errorlevel 1 exit /b 1

echo.
echo   Vorbereitung abgeschlossen.
echo.
exit /b 0

REM ---------------------------------------------------------------------------
:prepare_composer
echo   [1/3] Composer ^(composer.json / composer.lock^) ...
echo.

if not exist "%PROJECT_ROOT%\composer.json" (
    echo   [FEHLER] composer.json fehlt im Projektroot: %PROJECT_ROOT%
    exit /b 1
)
if exist "%PROJECT_ROOT%\composer.lock" (
    echo   composer.json + composer.lock gefunden.
) else (
    echo   composer.json gefunden ^(composer.lock wird bei update erzeugt^).
)
echo.

set "COMPOSER_PHP_FLAGS="
"%PHP_EXE%" -r "exit(version_compare(PHP_VERSION,'8.4.0','>=')?0:1);" >nul 2>&1
if errorlevel 1 (
    set "COMPOSER_PHP_FLAGS=--no-scripts"
    echo   PHP ^< 8.4 — Composer ohne Artisan-Hooks.
    echo.
)

call :ensure_composer
if errorlevel 1 exit /b 1

echo   Ausführung: composer update --no-dev --optimize-autoloader
echo   Pfad: !COMPOSER_LABEL!
echo.

pushd "%PROJECT_ROOT%"
if "!COMPOSER_MODE!"=="phar" (
    "%PHP_EXE%" "!COMPOSER_TARGET!" update --no-dev --optimize-autoloader --no-interaction !COMPOSER_PHP_FLAGS!
) else (
    call "!COMPOSER_TARGET!" update --no-dev --optimize-autoloader --no-interaction !COMPOSER_PHP_FLAGS!
)
set "COMPOSER_ERR=!errorlevel!"
popd

if !COMPOSER_ERR! NEQ 0 (
    echo   [FEHLER] composer update fehlgeschlagen.
    exit /b 1
)

echo.
exit /b 0

REM ---------------------------------------------------------------------------
:ensure_composer
call :find_composer
if not errorlevel 1 exit /b 0

call :bootstrap_composer_phar
if exist "%PROJECT_ROOT%\composer.phar" (
    set "COMPOSER_MODE=phar"
    set "COMPOSER_TARGET=%PROJECT_ROOT%\composer.phar"
    set "COMPOSER_LABEL=%PROJECT_ROOT%\composer.phar"
    exit /b 0
)

echo   [FEHLER] Composer konnte nicht gestartet werden.
echo            composer.json ist vorhanden — es fehlt nur composer.phar / composer.bat.
echo            Lösung: https://getcomposer.org/download/
echo            Oder COMPOSER_BIN setzen ^(Pfad zu composer.bat^).
exit /b 1

REM ---------------------------------------------------------------------------
:find_composer
set "COMPOSER_MODE="
set "COMPOSER_TARGET="
set "COMPOSER_LABEL="

if exist "%PROJECT_ROOT%\composer.phar" (
    set "COMPOSER_MODE=phar"
    set "COMPOSER_TARGET=%PROJECT_ROOT%\composer.phar"
    set "COMPOSER_LABEL=%PROJECT_ROOT%\composer.phar"
    exit /b 0
)

if defined COMPOSER_BIN if exist "!COMPOSER_BIN!" (
    set "COMPOSER_MODE=bat"
    set "COMPOSER_TARGET=!COMPOSER_BIN!"
    set "COMPOSER_LABEL=!COMPOSER_BIN!"
    exit /b 0
)

where composer.bat >nul 2>&1
if not errorlevel 1 (
    for /f "delims=" %%C in ('where composer.bat 2^>nul') do (
        if not defined COMPOSER_TARGET (
            set "COMPOSER_MODE=bat"
            set "COMPOSER_TARGET=%%C"
            set "COMPOSER_LABEL=%%C"
        )
    )
    if defined COMPOSER_TARGET exit /b 0
)

where composer >nul 2>&1
if not errorlevel 1 (
    for /f "delims=" %%C in ('where composer 2^>nul') do (
        if not defined COMPOSER_TARGET (
            set "COMPOSER_MODE=bat"
            set "COMPOSER_TARGET=%%C"
            set "COMPOSER_LABEL=%%C"
        )
    )
    if defined COMPOSER_TARGET exit /b 0
)

for %%C in (
    "%LOCALAPPDATA%\scoop\shims\composer.cmd"
    "%LOCALAPPDATA%\scoop\apps\composer\current\composer.bat"
    "%ChocolateyInstall%\bin\composer.bat"
    "%APPDATA%\Composer\vendor\bin\composer.bat"
    "%LOCALAPPDATA%\Programs\Composer\composer.bat"
    "%ProgramFiles%\ComposerSetup\bin\composer.bat"
    "%ProgramFiles(x86)%\ComposerSetup\bin\composer.bat"
    "C:\ProgramData\ComposerSetup\bin\composer.bat"
    "%USERPROFILE%\composer.bat"
    "C:\composer\composer.bat"
) do (
    if exist %%C (
        set "COMPOSER_MODE=bat"
        set "COMPOSER_TARGET=%%~fC"
        set "COMPOSER_LABEL=%%~fC"
        exit /b 0
    )
)

for %%P in ("!PHP_EXE!") do set "PHP_DIR=%%~dpP"
if exist "!PHP_DIR!composer.bat" (
    set "COMPOSER_MODE=bat"
    set "COMPOSER_TARGET=!PHP_DIR!composer.bat"
    set "COMPOSER_LABEL=!PHP_DIR!composer.bat"
    exit /b 0
)
if exist "!PHP_DIR!composer.phar" (
    set "COMPOSER_MODE=phar"
    set "COMPOSER_TARGET=!PHP_DIR!composer.phar"
    set "COMPOSER_LABEL=!PHP_DIR!composer.phar"
    exit /b 0
)

if exist "%USERPROFILE%\composer.phar" (
    set "COMPOSER_MODE=phar"
    set "COMPOSER_TARGET=%USERPROFILE%\composer.phar"
    set "COMPOSER_LABEL=%USERPROFILE%\composer.phar"
    exit /b 0
)

exit /b 1

REM ---------------------------------------------------------------------------
:bootstrap_composer_phar
pushd "%PROJECT_ROOT%"
if exist "composer.phar" (
    popd
    exit /b 0
)

echo   Kein globales Composer — lade composer.phar neben composer.json ...
echo.

"%PHP_EXE%" -r "exit(copy('https://getcomposer.org/download/latest-stable/composer.phar','composer.phar')?0:1);"
if exist "composer.phar" (
    popd
    exit /b 0
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "try { Invoke-WebRequest -Uri 'https://getcomposer.org/download/latest-stable/composer.phar' -OutFile 'composer.phar' -UseBasicParsing; exit 0 } catch { exit 1 }"
popd
exit /b 0

REM ---------------------------------------------------------------------------
:prepare_assets
echo   [2/3] Frontend-Assets ^(npm run build^) ...
echo.

if not exist "%PROJECT_ROOT%\package.json" (
    echo   Kein package.json — übersprungen.
    echo.
    exit /b 0
)

where npm >nul 2>&1
if errorlevel 1 (
    echo   [Hinweis] npm nicht im PATH — Build übersprungen.
    echo.
    exit /b 0
)

if not exist "%PROJECT_ROOT%\node_modules" (
    echo   npm install ...
    call npm install --no-audit --no-fund
    if errorlevel 1 (
        echo   [Warnung] npm install fehlgeschlagen — Build übersprungen.
        echo.
        exit /b 0
    )
)

call npm run build
if errorlevel 1 (
    echo   [Warnung] npm run build fehlgeschlagen — Paket wird trotzdem erzeugt.
    echo.
    exit /b 0
)

echo.
exit /b 0

REM ---------------------------------------------------------------------------
:prepare_artisan
echo   [3/3] Laravel ^(migrate, Cache leeren^) ...
echo.

"%PHP_EXE%" -r "exit(version_compare(PHP_VERSION,'8.4.0','>=')?0:1);" >nul 2>&1
if errorlevel 1 (
    echo   PHP ^< 8.4 — Artisan-Schritte übersprungen ^(Paket-Fallback folgt^).
    echo.
    exit /b 0
)

echo   php artisan migrate --force
"%PHP_EXE%" artisan migrate --force
if errorlevel 1 (
    echo   [FEHLER] Migration fehlgeschlagen.
    exit /b 1
)

echo   php artisan optimize:clear
"%PHP_EXE%" artisan optimize:clear
if errorlevel 1 (
    echo   [Warnung] optimize:clear fehlgeschlagen — wird fortgesetzt.
)

echo.
exit /b 0

REM ---------------------------------------------------------------------------
:find_php
set "PHP_EXE="
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if "!PHP_EXE!"=="" (
    for /f "delims=" %%P in ('where php 2^>nul') do (
        if "!PHP_EXE!"=="" set "PHP_EXE=%%P"
    )
)
if "!PHP_EXE!"=="" (
    echo   [FEHLER] PHP nicht gefunden ^(XAMPP: C:\xampp\php\php.exe^).
    exit /b 1
)
echo   PHP: !PHP_EXE!
set "PHPVER="
for /f "delims=" %%V in ('"!PHP_EXE!" -r "echo PHP_VERSION;" 2^>nul') do set "PHPVER=%%V"
if defined PHPVER echo   Version: !PHPVER!
echo.
exit /b 0

REM ---------------------------------------------------------------------------
:ensure_release_notes
set "NOTES_FILE=%DEPLOY_DIR%\RELEASE_NOTES_%RELEASE%.txt"
if exist "%NOTES_FILE%" exit /b 0

echo.
echo   Release-Notes fehlen: %NOTES_FILE%
echo.
set /p "CREATE_NOTES=   Jetzt hier im Tool anlegen? (J/n): "
if /I "!CREATE_NOTES!"=="n" exit /b 1
if /I "!CREATE_NOTES!"=="nein" exit /b 1

call :create_release_notes
if errorlevel 1 exit /b 1
if not exist "%NOTES_FILE%" (
    echo   [FEHLER] Datei wurde nicht gespeichert.
    exit /b 1
)
echo   Release-Notes gespeichert.
echo.
exit /b 0

REM ---------------------------------------------------------------------------
:create_release_notes
if not exist "%DEPLOY_DIR%" mkdir "%DEPLOY_DIR%"

echo.
echo   ========================================================================
echo   Release-Notes für Version %RELEASE%
echo   ========================================================================
echo.

set "NOTE_TITLE=aresCMS %RELEASE% — System-Update"
set /p "NOTE_TITLE=   Titel [%NOTE_TITLE%]: "
if "!NOTE_TITLE!"=="" set "NOTE_TITLE=aresCMS %RELEASE% — System-Update"

> "%NOTES_FILE%" (
    echo !NOTE_TITLE!
    echo.
    echo Neu / verbessert:
)

echo.
echo   Änderungen ^(eine Zeile pro Punkt, leere Zeile = fertig^):
echo.
:notes_input_loop
set "LINE="
set /p "LINE=   • "
if not "!LINE!"=="" (
    echo • !LINE!>> "%NOTES_FILE%"
    goto :notes_input_loop
)

echo.>> "%NOTES_FILE%"
echo Konfiguration ^(optional in .env^):>> "%NOTES_FILE%"
echo.
echo   .env-Hinweise ^(optional, leere Zeile = überspringen^):
echo.
:config_input_loop
set "LINE="
set /p "LINE=   • "
if not "!LINE!"=="" (
    echo • !LINE!>> "%NOTES_FILE%"
    goto :config_input_loop
)

echo.>> "%NOTES_FILE%"
echo Nach Installation:>> "%NOTES_FILE%"
echo • php artisan migrate --force>> "%NOTES_FILE%"
echo • php artisan optimize:clear>> "%NOTES_FILE%"
echo • Prüfen: public/cms-update-verification.txt>> "%NOTES_FILE%"

echo.
echo   --- Vorschau ---
echo.
type "%NOTES_FILE%"
echo.
echo   --- Ende Vorschau ---
echo.
set /p "SAVE_NOTES=   So speichern? (J/n): "
if /I "!SAVE_NOTES!"=="n" (
    del "%NOTES_FILE%" >nul 2>&1
    echo   Abgebrochen — Release-Notes nicht gespeichert.
    exit /b 1
)
if /I "!SAVE_NOTES!"=="nein" (
    del "%NOTES_FILE%" >nul 2>&1
    echo   Abgebrochen — Release-Notes nicht gespeichert.
    exit /b 1
)

exit /b 0
