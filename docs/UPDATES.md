# CMS-Updates (mehrere Webseiten)

Jede Installation kann **dieselbe Codebasis** nutzen und über **Admin → System updates** prüfen, ob eine neuere Version auf **deinem** Update-Server liegt. **`.env` und der gesamte Ordner `config/` werden beim Update nicht überschrieben.** Ebenfalls geschützt: `storage/app/public` (Uploads), `storage/app/backups`, `storage/logs`, `storage/app/cms`.

## 1. Konfiguration (pro Website / .env)

**Manifest-URL:** Standard ist **`https://update.luetcke.eu/manifest.json`** (in `config/cms.php`). Du musst `CMS_UPDATE_MANIFEST_URL` in der `.env` **nicht** setzen, außer du willst eine andere Quelle oder Updates abschalten (Variable leer lassen).

```env
# optional: eigene Manifest-URL statt Standard
# CMS_UPDATE_MANIFEST_URL=https://dein-server.example/cms/manifest.json
# optional:
CMS_UPDATE_TOKEN=geheimer-api-token
CMS_UPDATE_ENABLED=true
CMS_MANIFEST_CACHE_TTL=600
CMS_BUNDLE_VERSION=1.0.0
```

Ohne gültige Manifest-URL (Standard überschrieben durch leeren `CMS_UPDATE_MANIFEST_URL`) erscheint im Admin der Hinweis „nicht konfiguriert“.

Die **installierte Version** steht nach dem ersten Update in `storage/app/cms/installed_version` (eine Zeile, z. B. `1.2.0`). Vorher gilt `CMS_BUNDLE_VERSION` aus `.env` / `config/cms.php`.

## 2. Manifest (JSON)

Im Repository liegt eine Vorlage: **`deploy/manifest.example.json`** (siehe **`deploy/README.md`**).

Die URL muss **HTTPS** liefern (empfohlen) und folgendes JSON enthalten:

```json
{
  "version": "1.2.0",
  "min_php": "8.1.0",
  "package_url": "https://dein-server.example/cms/releases/news-portal-1.2.0.zip",
  "sha256": "hex-string-optional-aber-empfohlen",
  "notes": "Kurze Release-Notes (Text)"
}
```

| Feld | Pflicht | Beschreibung |
|------|---------|--------------|
| `version` | ja | Semver-String, wird mit installierter Version verglichen |
| `package_url` | ja | ZIP-Download |
| `min_php` | nein | Mindest-PHP (z. B. `8.1.0`) |
| `sha256` | nein | SHA-256 des ZIP; wenn gesetzt, muss er stimmen |
| `notes` | nein | Anzeige im Admin |

Mit `CMS_UPDATE_TOKEN` sendet das CMS den Header `Authorization: Bearer <token>`. Dein Server kann so Manifest und ZIP schützen.

## 3. Release-ZIP erstellen

- Projekt **ohne** `.env` packen oder `.env` im Archiv belassen — wird beim Entpacken **ignoriert**.
- Ordner **`config/`** im Archiv wird beim Entpacken **ignoriert** (lokale Konfiguration bleibt).
- **`storage/app/public`**, **`storage/app/backups`**, **`storage/logs`**, **`storage/app/cms`** werden nicht überschrieben.

**Empfehlung:** ZIP mit einem **einzigen Root-Ordner** (z. B. `news-portal/`), darunter `app/`, `routes/`, `resources/`, `database/`, `public/`, … — der erkannte Wrapper wird automatisch abgestrippt.

**Vendor:** Entweder `vendor/` ins ZIP legen (groß) oder nur `composer.json` / `composer.lock` aktualisieren und auf dem Server **`composer install --no-dev`** ausführen (der Updater versucht das automatisch, wenn `composer` bzw. `composer.phar` verfügbar ist).

Nach dem Update werden ausgeführt:

- `php artisan migrate --force`
- `php artisan optimize:clear`

## 4. Ablauf im Admin

1. **Update verfügbar**, wenn `version` im Manifest **größer** als installierte Version ist.
2. Button **Install update** → Download → Prüfung `sha256` (falls gesetzt) → Dateien extrahieren (Blacklist) → optional Composer → Migrationen → Cache leeren → Version in `storage/app/cms/installed_version` schreiben.

## 5. Update-Paket mit Artisan erzeugen

```bash
php artisan cms:create-update
# optional: --release=1.0.2 (muss höher sein als installiert)
```

Legt unter **`public/update/`** ein ZIP + **`manifest.json`** an und gibt die passende **`CMS_UPDATE_MANIFEST_URL`** aus. Details: **`public/update/README.md`**.

**Mit `--package-base=…`:** Es wird ein **vollständiges Release-ZIP** erzeugt (Code inkl. `vendor/`, ohne `node_modules`, `.git`, `public/update`, Upload-/Log-Pfade usw.). Ohne diese Option bleibt nur ein kleines Prüf-Paket mit `public/cms-update-verification.txt` (für schnelle lokale Tests).

**Eigener Update-Host:**  
`php artisan cms:create-update --release=1.0.2 --package-base=https://dein-update-server.de --force`  
→ `manifest.json` und ZIP ins Webroot hochladen; `package_url` zeigt z. B. auf `https://dein-update-server.de/news-portal-update-….zip`.  
Auf allen Sites: `CMS_UPDATE_MANIFEST_URL=https://dein-update-server.de/manifest.json`.

## 6. Hinweise

- Vor größeren Updates **Backup** (z. B. Admin → Betrieb).
- **Neue Konfigurationsschlüssel** aus einem Release musst du ggf. **manuell** in deine bestehenden Dateien unter `config/` übernehmen (werden nicht überschrieben).
- Mehrere Domains = mehrere `.env` / Manifest-URLs möglich (z. B. anderer Kanal pro Kunde).
