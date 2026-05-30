# Deploy-Artefakte

## Lizenz-Host (key.luetcke.eu)

- **Mit Login & Key-Verwaltung (ohne DB):** **`deploy/key-portal/`** – JSON-Datei `data/licenses.json`, Document Root = **`key-portal/public`**. Siehe **`deploy/key-portal/README.md`**.
- **HTTP 404 auf `/api/validate`:** oft kein **mod_rewrite** / kein **AllowOverride**. Dann Ordner **`deploy/key-host/`** nutzen (siehe **`deploy/key-host/README.md`**): entweder **`api/validate/index.php`** hochladen oder eine Datei **`validate-license.php`** im Root + URL in der CMS-`.env` anpassen.
- Alternativ: **`key-server-validate.example.php`** → `key-server-validate.php` + **`key.luetcke.eu.htaccess`** als `.htaccess` ins Document Root.

Details: **`docs/LICENSE_SERVER.md`**.

---

## Update-Manifest & ZIP

### Datei `manifest.example.json`

1. Kopieren nach `manifest.json` (oder direkt unter deiner öffentlichen URL bereitstellen).
2. **`package_url`** auf dein echtes HTTPS-ZIP setzen.
3. **`version`** höher setzen als auf den Ziel-Sites installiert (siehe Admin → System updates).
4. **`sha256`** (empfohlen): nach ZIP-Erzeugung ausfüllen:
   - Linux/macOS: `sha256sum news-portal-1.0.1.zip`
   - Windows PowerShell: `Get-FileHash -Algorithm SHA256 .\news-portal-1.0.1.zip`
5. Manifest per **HTTPS** ausliefern (statische Datei, CDN, oder kleines PHP, das JSON zurückgibt).

Standard nutzt **`https://update.luetcke.eu/manifest.json`** (`config/cms.php`). Nur bei eigener Quelle in der `.env` setzen:

```env
CMS_UPDATE_MANIFEST_URL=https://dein-server.de/pfad/manifest.json
```

### Prüfsumme weglassen

Feld **`sha256`** leer lassen (`""`) oder ganz entfernen — dann prüft das CMS die Datei nicht per Hash (nur HTTPS-Vertrauen).

### Format

Siehe auch **`docs/UPDATES.md`**.

Pflichtfelder im JSON: **`version`**, **`package_url`**.
