# Key-Portal (key.luetcke.eu)

Mini-Admin für **Lizenzschlüssel** – **ohne Datenbank**, alles in einer JSON-Datei **`data/licenses.json`**.

## Installation

1. Ordner **`key-portal`** auf den Server (z. B. Webroot von `key.luetcke.eu`).
2. **Document Root** am besten = **`key-portal/public`**.  
   **Wenn** stattdessen das Webroot **eine Ebene höher** ist (Apache zeigt eine **Index-Liste** mit `key-portal/`, `api/`, …): Die Datei **`htaccess`** auf dem Server in **`.htaccess`** umbenennen und **in dieses äußere Webroot** legen (siehe **`DOCUMENT_ROOT_ELTERNORDNER.md`**). Die alte **`validate-license.php` nur im äußeren Root** löschen oder umbenennen, damit die Anfrage zur Portal-Version unter `key-portal/public/` geht.
3. Verzeichnis **`key-portal/data/`** schreibbar: `chmod 775 key-portal/data` (Webserver-User).
4. **`https://key.luetcke.eu/install.php`** aufrufen → Admin-Passwort setzen (mit Root-`.htaccess` wie oben leitet `/install.php` automatisch um).
5. **`install.php` löschen**.
6. Schlüssel unter **`/admin/`** anlegen; Domains zeilenweise (z. B. `localhost`, `kunde.de`).

## CMS (.env)

```env
CMS_LICENSE_VALIDATE_URL=https://key.luetcke.eu/validate-license.php
```

## URLs

| Pfad | Zweck |
|------|--------|
| `/validate-license.php` | POST JSON (vom CMS) |
| `/admin/login.php` | Login |
| `/admin/` | Schlüssel-Übersicht |

## Backup

**`data/licenses.json`** sichern (enthält Passwort-Hash + alle Keys).

## Sicherheit

- Nur **HTTPS**.
- **`install.php`** nach Setup entfernen.
- Ordner **`data/`** liegt **neben** `public/` und ist normalerweise **nicht** öffentlich erreichbar.
