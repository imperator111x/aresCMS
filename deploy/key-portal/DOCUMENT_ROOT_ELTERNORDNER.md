# Document Root zeigt auf den Ordner **über** `key-portal/`

So wie auf deinem Screenshot: im Root liegen `key-portal/`, `validate-license.php`, `api/` usw.

## Schnellfix

1. Die Datei **`htaccess`** aus diesem Ordner auf dem Server in **`.htaccess`** umbenennen und in genau dieses **Webroot** legen (dort wo `key-portal/` liegt) – **nicht** nach `key-portal/public/` und **nicht** in den Ordner `key-portal/` selbst.
2. Die alte **`validate-license.php` direkt im Root`** entfernen oder umbenennen (sonst wird sie statt der Portal-Version ausgeführt).
3. **`data/`** muss existieren und beschreibbar sein: liegt bei dir unter **`key-portal/data/`** (neben `key-portal/public/`). Einmal **`https://key.luetcke.eu/install.php`** aufrufen, wenn noch keine `licenses.json` da ist.

## HTTP 500 (Internal Server Error)

Häufig: In der **äußeren** `.htaccess` stand **`Options +FollowSymLinks`** oder **`Options -Indexes`** – das ist bei vielen Hostern in `.htaccess` **nicht erlaubt** und führt sofort zu **500**. Aktuelle Vorlage **`htaccess`** im Repo enthält **kein** `Options` mehr.

Sonst **Apache- bzw. PHP-Errorlog** prüfen (Plesk/cPanel: „Fehlerprotokolle“).

## Danach erreichbar

| URL | |
|-----|---|
| `https://key.luetcke.eu/` | leitet ins Portal (Admin) |
| `https://key.luetcke.eu/validate-license.php` | JSON-API (aus `key-portal/public/`) |
| `https://key.luetcke.eu/admin/…` | Login & Verwaltung |

## Besser (wenn der Hoster es erlaubt)

**Document Root** direkt auf **`…/key-portal/public`** stellen. Dann brauchst du diese Root-`.htaccess` nicht; die ZIP/Ordner im übergeordneten Verzeichnis sollten nicht öffentlich sein.
