# aresCMS auf Webspace installieren

Die Installation nutzt **PHP 8.1+** und **MySQL** (oder MariaDB). Der öffentliche Teil liegt unter **`public/`** – das **Document Root** (Webroot) muss darauf zeigen.

---

## Webbasiert: `public/install.php`

Wenn du **kein SSH** hast oder Artisan nicht per Konsole ausführen willst:

1. Wie unten: `composer install --no-dev`, `npm run build`, Upload inkl. `vendor/` und `public/build/`.
2. Document Root auf **`public/`**.
3. Im Browser: **`https://deine-domain.de/install.php`**
4. Formular: **APP_URL**, Datenbank, optional Admin-Passwort, optional Lizenz.
5. Nach Erfolg: **`public/install.php` auf dem Server löschen** (Sicherheit).

Der Installer legt **`.env`** an, führt **Migrationen**, **Admin-Benutzer** (oder Seeder) und **`storage:link`** aus und sperrt sich über **`storage/app/installer.lock`**.

---

## 1. Voraussetzungen beim Hoster

| Anforderung | Typisch |
|-------------|---------|
| PHP | **8.1 oder höher** (8.2/8.3 ok) |
| Erweiterungen | `openssl`, `pdo`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo` |
| Datenbank | **MySQL** / MariaDB, leere Datenbank + Zugangsdaten |
| Composer | Lokal auf dem PC **oder** SSH/Terminal auf dem Server |
| Optional | **SSH** (sehr empfohlen für `php artisan …`) |

---

## 2. Projekt vorbereiten (auf deinem PC)

1. Aktuellen Stand des Projekts haben (Ordner `news-portal`).
2. Im Projektordner:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

3. **Nicht** mit hochladen (spart Platz): `node_modules/`, `.git/` (optional), Tests nur wenn du `--no-dev` nicht nutzt.

4. Auf dem Server brauchst du mindestens: `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `vendor/`, `artisan`, `composer.json`, `composer.lock`.

---

## 3. Dateien hochladen

- Per **FTP/SFTP** alles in ein Verzeichnis legen, z. B. `htdocs/news-portal/` oder direkt ins Webroot.
- **Wichtig:** Im Hosting-Panel das **Document Root** auf den Ordner **`public`** stellen, z. B.  
  `…/news-portal/public`  
  (nicht auf den übergeordneten Projektordner oberhalb von `public/`.)

---

## 4. `.env` auf dem Server

1. `.env.example` nach **`.env`** kopieren (falls noch nicht vorhanden).
2. Eintragen:

```env
APP_NAME="aresCMS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://deine-domain.de

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dein_db_name
DB_USERNAME=dein_db_user
DB_PASSWORD=dein_db_passwort
```

3. **APP_KEY** erzeugen (nur mit SSH/Terminal im **Projektroot**, eine Ebene über `public/`):

```bash
php artisan key:generate
```

Ohne SSH: Lokal `php artisan key:generate` ausführen und den Wert von `APP_KEY=` aus der lokalen `.env` in die **Online-`.env`** übernehmen.

---

## 5. Rechte (Schreibzugriff)

Diese Ordner müssen für den Webserver **beschreibbar** sein (oft `755` Ordner, `644` Dateien; manche Hoster wollen `775` auf `storage` und `bootstrap/cache`):

- `storage/`
- `bootstrap/cache/`

---

## 6. Datenbank

**Installer / Webspace:** Wenn die Anwendung **`SQLSTATE[HY000] [2002] Connection refused`** meldet, ist **`DB_HOST`** fast immer falsch für den Provider. Lokal/XAMPP: `127.0.0.1`. Bei **Shared Hosting** den MySQL-Server-Host aus dem Kundenmenü übernehmen (z. B. **Lima-City:** `mysql.lima-city.de` oder `deinuser.lima-db.de` – steht bei der Datenbankverwaltung). Anschließend `.env` anpassen oder löschen und Installation erneut starten.

Im Projektroot (SSH):

```bash
php artisan migrate --force
php artisan db:seed --class=AdminSeeder --force
php artisan storage:link
```

---

## 7. Lizenz (falls aktiv)

In der `.env` Schlüssel und URL wie von **key.luetcke.eu** / deiner Installation:

```env
CMS_LICENSE_KEY=dein-schluessel
CMS_LICENSE_VALIDATE_URL=https://key.luetcke.eu/validate-license.php
```

Domain des Webspace muss beim Schlüssel hinterlegt sein. Siehe **`docs/LICENSE_SERVER.md`**.

---

## 8. Cache (Produktion)

Nach Änderungen an `.env` oder Config:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Zum Debuggen vorübergehend:

```bash
php artisan optimize:clear
```

---

## 9. Erster Login (nach AdminSeeder)

- **E-Mail:** `admin@example.com`  
- **Passwort:** `password`  

**Sofort im Admin-Bereich ändern.**

---

## 10. Häufige Probleme

| Problem | Lösung |
|--------|--------|
| Weiße Seite / 500 | `APP_DEBUG=true` kurz setzen **nur zum Testen**, Log: `storage/logs/laravel.log` |
| 404 auf allen Seiten | Document Root = **`public/`**, nicht Projektroot |
| CSS/JS fehlen | `npm run build` lokal, Ordner `public/build/` mit hochladen |
| Kein SSH | Key lokal generieren; Migrationen: Hoster-Support nach „PHP Artisan“ fragen oder einmaligen SSH-Zugang |

---

## 11. E-Mail, Turnstile, Backups

Siehe **`.env.example`** (SMTP, Cloudflare Turnstile) und **`docs/BETRIEB.md`** (Backups, Wartungsmodus).

---

## Kurz-Checkliste

- [ ] `composer install --no-dev` + `npm run build`
- [ ] Upload inkl. `vendor/` und `public/build/`
- [ ] Document Root → **`public`**
- [ ] `.env` mit DB + `APP_URL` + `APP_KEY`
- [ ] Rechte `storage/`, `bootstrap/cache/`
- [ ] `php artisan migrate --force` + `db:seed` + `storage:link`
- [ ] Lizenz in `.env` (falls verwendet)
- [ ] `APP_DEBUG=false`, Admin-Passwort geändert
