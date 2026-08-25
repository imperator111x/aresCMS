# Erstellt/aktualisiert die Verknüpfung mit aresCMS-Icon.
# Ziel ist cmd.exe (nicht .bat direkt), damit Windows kein UAC-Schild anzeigt.
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing

function Test-BackgroundPixel {
    param(
        [System.Drawing.Color]$Color,
        [int]$WhiteTolerance = 18,
        [int]$GreyMin = 195,
        [int]$GreyMax = 252
    )

    if ($Color.A -lt 16) {
        return $true
    }

    if ($Color.R -ge (255 - $WhiteTolerance) -and $Color.G -ge (255 - $WhiteTolerance) -and $Color.B -ge (255 - $WhiteTolerance)) {
        return $true
    }

    $min = [Math]::Min($Color.R, [Math]::Min($Color.G, $Color.B))
    $max = [Math]::Max($Color.R, [Math]::Max($Color.G, $Color.B))
    if ($min -ge $GreyMin -and $max -le $GreyMax -and ($max - $min) -le 12) {
        return $true
    }

    return $false
}

function ConvertTo-AlphaBitmap {
    param([System.Drawing.Bitmap]$Bitmap)

    $result = New-Object System.Drawing.Bitmap $Bitmap.Width, $Bitmap.Height, ([System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $sourceData = $Bitmap.LockBits(
        (New-Object System.Drawing.Rectangle 0, 0, $Bitmap.Width, $Bitmap.Height),
        [System.Drawing.Imaging.ImageLockMode]::ReadOnly,
        $Bitmap.PixelFormat
    )
    $targetData = $result.LockBits(
        (New-Object System.Drawing.Rectangle 0, 0, $result.Width, $result.Height),
        [System.Drawing.Imaging.ImageLockMode]::WriteOnly,
        $result.PixelFormat
    )

    try {
        $bytesPerPixel = [System.Drawing.Image]::GetPixelFormatSize($Bitmap.PixelFormat) / 8
        $sourceStride = $sourceData.Stride
        $targetStride = $targetData.Stride
        $sourceBytes = New-Object byte[] ($sourceStride * $Bitmap.Height)
        $targetBytes = New-Object byte[] ($targetStride * $result.Height)
        [System.Runtime.InteropServices.Marshal]::Copy($sourceData.Scan0, $sourceBytes, 0, $sourceBytes.Length)

        for ($y = 0; $y -lt $Bitmap.Height; $y++) {
            for ($x = 0; $x -lt $Bitmap.Width; $x++) {
                $sourceIndex = ($y * $sourceStride) + ($x * $bytesPerPixel)
                $targetIndex = ($y * $targetStride) + ($x * 4)

                $b = $sourceBytes[$sourceIndex]
                $g = $sourceBytes[$sourceIndex + 1]
                $r = $sourceBytes[$sourceIndex + 2]
                $a = if ($bytesPerPixel -ge 4) { $sourceBytes[$sourceIndex + 3] } else { [byte]255 }
                $color = [System.Drawing.Color]::FromArgb($a, $r, $g, $b)

                if (Test-BackgroundPixel -Color $color) {
                    $targetBytes[$targetIndex] = 0
                    $targetBytes[$targetIndex + 1] = 0
                    $targetBytes[$targetIndex + 2] = 0
                    $targetBytes[$targetIndex + 3] = 0
                } else {
                    $targetBytes[$targetIndex] = $b
                    $targetBytes[$targetIndex + 1] = $g
                    $targetBytes[$targetIndex + 2] = $r
                    $targetBytes[$targetIndex + 3] = 255
                }
            }
        }

        [System.Runtime.InteropServices.Marshal]::Copy($targetBytes, 0, $targetData.Scan0, $targetBytes.Length)
    } finally {
        $Bitmap.UnlockBits($sourceData)
        $result.UnlockBits($targetData)
    }

    return $result
}

function Get-OpaqueBounds {
    param([System.Drawing.Bitmap]$Bitmap)

    $data = $Bitmap.LockBits(
        (New-Object System.Drawing.Rectangle 0, 0, $Bitmap.Width, $Bitmap.Height),
        [System.Drawing.Imaging.ImageLockMode]::ReadOnly,
        $Bitmap.PixelFormat
    )

    try {
        $stride = $data.Stride
        $bytes = New-Object byte[] ($stride * $Bitmap.Height)
        [System.Runtime.InteropServices.Marshal]::Copy($data.Scan0, $bytes, 0, $bytes.Length)

        $minX = $Bitmap.Width
        $minY = $Bitmap.Height
        $maxX = 0
        $maxY = 0

        for ($y = 0; $y -lt $Bitmap.Height; $y++) {
            for ($x = 0; $x -lt $Bitmap.Width; $x++) {
                $alpha = $bytes[($y * $stride) + ($x * 4) + 3]
                if ($alpha -gt 16) {
                    if ($x -lt $minX) { $minX = $x }
                    if ($y -lt $minY) { $minY = $y }
                    if ($x -gt $maxX) { $maxX = $x }
                    if ($y -gt $maxY) { $maxY = $y }
                }
            }
        }
    } finally {
        $Bitmap.UnlockBits($data)
    }

    if ($maxX -lt $minX) {
        return $null
    }

    return @{
        X = $minX
        Y = $minY
        Width = ($maxX - $minX + 1)
        Height = ($maxY - $minY + 1)
    }
}

function New-CenterFocusedBitmap {
    param(
        [System.Drawing.Bitmap]$Bitmap,
        [double]$CenterFraction = 0.68
    )

    $cropW = [Math]::Max(1, [int][Math]::Round($Bitmap.Width * $CenterFraction))
    $cropH = [Math]::Max(1, [int][Math]::Round($Bitmap.Height * $CenterFraction))
    $x = [int][Math]::Floor(($Bitmap.Width - $cropW) / 2.0)
    $y = [int][Math]::Floor(($Bitmap.Height - $cropH) / 2.0)

    return $Bitmap.Clone(
        (New-Object System.Drawing.Rectangle $x, $y, $cropW, $cropH),
        [System.Drawing.Imaging.PixelFormat]::Format32bppArgb
    )
}

function New-SquareIconBitmap {
    param(
        [System.Drawing.Bitmap]$Bitmap,
        [int]$PaddingPercent = 2
    )

    $bounds = Get-OpaqueBounds -Bitmap $Bitmap
    if ($null -eq $bounds) {
        throw 'Icon enthält nach Hintergrundentfernung keine sichtbaren Pixel.'
    }

    $crop = $Bitmap.Clone(
        (New-Object System.Drawing.Rectangle $bounds.X, $bounds.Y, $bounds.Width, $bounds.Height),
        [System.Drawing.Imaging.PixelFormat]::Format32bppArgb
    )

    try {
        $focused = New-CenterFocusedBitmap -Bitmap $crop
    } finally {
        $crop.Dispose()
    }

    try {
        $focusedBounds = Get-OpaqueBounds -Bitmap $focused
        if ($null -eq $focusedBounds) {
            throw 'Icon enthält nach Fokus-Zuschnitt keine sichtbaren Pixel.'
        }

        $logo = $focused.Clone(
            (New-Object System.Drawing.Rectangle $focusedBounds.X, $focusedBounds.Y, $focusedBounds.Width, $focusedBounds.Height),
            [System.Drawing.Imaging.PixelFormat]::Format32bppArgb
        )
    } finally {
        $focused.Dispose()
    }

    try {
        $size = [Math]::Max($logo.Width, $logo.Height)
        $padding = [Math]::Max(1, [int][Math]::Round($size * ($PaddingPercent / 100.0)))
        $canvasSize = $size + (2 * $padding)

        $square = New-Object System.Drawing.Bitmap $canvasSize, $canvasSize, ([System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
        $graphics = [System.Drawing.Graphics]::FromImage($square)
        try {
            $graphics.Clear([System.Drawing.Color]::FromArgb(0, 0, 0, 0))
            $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
            $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
            $graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
            $graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality

            $offsetX = [int][Math]::Round(($canvasSize - $logo.Width) / 2.0)
            $offsetY = [int][Math]::Round(($canvasSize - $logo.Height) / 2.0)
            $graphics.DrawImage($logo, $offsetX, $offsetY, $logo.Width, $logo.Height)
        } finally {
            $graphics.Dispose()
        }

        return $square
    } finally {
        $logo.Dispose()
    }
}

function Convert-BitmapToIco {
    param(
        [System.Drawing.Bitmap]$Source,
        [Parameter(Mandatory = $true)][string]$OutputPath,
        [int[]]$Sizes = @(16, 32, 48, 256),
        [hashtable]$SizeZoom = @{ 16 = 1.12; 32 = 1.08; 48 = 1.04; 256 = 1.0 }
    )

    $outputPath = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($OutputPath)
    $bitmaps = New-Object System.Collections.Generic.List[System.Drawing.Bitmap]
    $pngData = New-Object System.Collections.Generic.List[byte[]]

    try {
        foreach ($size in $Sizes) {
            $bmp = New-Object System.Drawing.Bitmap $size, $size, ([System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
            $graphics = [System.Drawing.Graphics]::FromImage($bmp)
            $graphics.Clear([System.Drawing.Color]::FromArgb(0, 0, 0, 0))
            $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
            $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
            $graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality

            $zoom = if ($SizeZoom.ContainsKey($size)) { $SizeZoom[$size] } else { 1.0 }
            $drawSize = [int][Math]::Round($size * $zoom)
            $offset = [int][Math]::Round(($size - $drawSize) / 2.0)
            $graphics.DrawImage($Source, $offset, $offset, $drawSize, $drawSize)
            $graphics.Dispose()
            $bitmaps.Add($bmp)

            $stream = New-Object System.IO.MemoryStream
            $bmp.Save($stream, [System.Drawing.Imaging.ImageFormat]::Png)
            $pngData.Add($stream.ToArray())
            $stream.Dispose()
        }
    } catch {
        foreach ($bmp in $bitmaps) { $bmp.Dispose() }
        throw
    }

    $ms = New-Object System.IO.MemoryStream
    $writer = New-Object System.IO.BinaryWriter $ms

    $writer.Write([UInt16]0)
    $writer.Write([UInt16]1)
    $writer.Write([UInt16]$bitmaps.Count)

    $dataOffset = 6 + (16 * $bitmaps.Count)

    for ($i = 0; $i -lt $bitmaps.Count; $i++) {
        $bmp = $bitmaps[$i]
        $width = if ($bmp.Width -ge 256) { [byte]0 } else { [byte]$bmp.Width }
        $height = if ($bmp.Height -ge 256) { [byte]0 } else { [byte]$bmp.Height }

        $writer.Write($width)
        $writer.Write($height)
        $writer.Write([byte]0)
        $writer.Write([byte]0)
        $writer.Write([UInt16]1)
        $writer.Write([UInt16]32)
        $writer.Write([UInt32]$pngData[$i].Length)
        $writer.Write([UInt32]$dataOffset)
        $dataOffset += $pngData[$i].Length
    }

    foreach ($data in $pngData) {
        $writer.Write($data)
    }

    foreach ($bmp in $bitmaps) {
        $bmp.Dispose()
    }

    $writer.Flush()
    [System.IO.File]::WriteAllBytes($outputPath, $ms.ToArray())
    $writer.Close()
    $ms.Close()
}

function Disable-ShortcutRunAsAdmin {
    param([Parameter(Mandatory = $true)][string]$Path)

    $bytes = [System.IO.File]::ReadAllBytes($Path)
    if ($bytes.Length -le 0x15) {
        return
    }

    $bytes[0x15] = $bytes[0x15] -band 0xDF
    [System.IO.File]::WriteAllBytes($Path, $bytes)
}

function New-ProcessedIconSource {
    param([string]$PngPath)

    $loaded = [System.Drawing.Bitmap]::FromFile((Resolve-Path $PngPath).Path)
    try {
        $alpha = ConvertTo-AlphaBitmap -Bitmap $loaded
        try {
            return New-SquareIconBitmap -Bitmap $alpha
        } finally {
            $alpha.Dispose()
        }
    } finally {
        $loaded.Dispose()
    }
}

$root = Split-Path $PSScriptRoot -Parent
$png = Join-Path $root 'assets\arescms-update-icon.png'
$ico = Join-Path $root 'assets\arescms-update.ico'
$bat = Join-Path $root 'Update-Paket bauen.bat'
$lnk = Join-Path $root 'Update-Paket bauen.lnk'
$cmd = $env:ComSpec

if (-not (Test-Path $bat)) {
    Write-Error "Batch-Datei fehlt: $bat"
}

if (-not (Test-Path $png)) {
    Write-Error "Icon-PNG fehlt: $png"
}

$iconBitmap = New-ProcessedIconSource -PngPath $png
try {
    Convert-BitmapToIco -Source $iconBitmap -OutputPath $ico
} finally {
    $iconBitmap.Dispose()
}

if (Test-Path $lnk) {
    Remove-Item $lnk -Force
}

$shell = New-Object -ComObject WScript.Shell
$shortcut = $shell.CreateShortcut($lnk)
$shortcut.TargetPath = $cmd
$shortcut.Arguments = '/c "' + $bat + '"'
$shortcut.WorkingDirectory = $root
$shortcut.IconLocation = "$ico,0"
$shortcut.Description = 'aresCMS Update-Paket bauen'
$shortcut.WindowStyle = 1
$shortcut.Save()

Disable-ShortcutRunAsAdmin -Path $lnk

$icoInfo = Get-Item $ico
$lnkBytes = [System.IO.File]::ReadAllBytes($lnk)
$verify = $shell.CreateShortcut($lnk)

Write-Host "Verknüpfung erstellt: $lnk"
Write-Host "Ziel: $($verify.TargetPath) $($verify.Arguments)"
Write-Host "Icon: $ico ($([math]::Round($icoInfo.Length / 1KB, 1)) KB)"
Write-Host "LNK Byte 0x15 (RunAsAdmin-Flag): 0x$('{0:X2}' -f $lnkBytes[0x15])"
Write-Host ""
Write-Host "Hinweis: Verknüpfung zeigt auf cmd.exe (kein UAC-Schild für .bat-Ziele)."
Write-Host "Falls das Icon noch alt wirkt: alte Verknüpfung löschen, Skript erneut ausführen,"
Write-Host "dann Icon-Cache aktualisieren: ie4uinit.exe -show"
