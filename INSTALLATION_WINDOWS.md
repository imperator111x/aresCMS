# Installationsanleitung für Windows

Da PHP, Composer und Node.js noch nicht installiert sind, folgen Sie dieser Anleitung:

## 1. PHP installieren

### Option A: XAMPP (Empfohlen für Anfänger)
1. Laden Sie XAMPP herunter: https://www.apachefriends.org/download.html
2. Installieren Sie XAMPP (Standardpfad: `C:\xampp`)
3. Fügen Sie PHP zum PATH hinzu:
   - Systemsteuerung → System → Erweiterte Systemeinstellungen → Umgebungsvariablen
   - Bei "Systemvariablen" finden Sie "Path" und klicken Sie auf "Bearbeiten"
   - Fügen Sie hinzu: `C:\xampp\php`
   - Klicken Sie auf "OK"

### Option B: PHP standalone
1. Laden Sie PHP herunter: https://windows.php.net/download#php-8.2
2. Wählen Sie "Thread Safe" Version (z.B. php-8.2.12-Win32-vs16-x64.zip)
3. Entpacken Sie nach `C:\php`
4. Fügen Sie `C:\php` zum PATH hinzu (siehe oben)
5. Kopieren Sie `php.ini-development` nach `php.ini`
6. Aktivieren Sie in `php.ini` folgende Erweiterungen (entfernen Sie das Semikolon):
   ```
   extension=curl
   extension=fileinfo
   extension=gd
   extension=mbstring
   extension=openssl
   extension=pdo_mysql
   extension=tokenizer
   extension=xml
   ```

## 2. Composer installieren

1. Laden Sie Composer herunter: https://getcomposer.org/Composer-Setup.exe
2. Führen Sie das Installationsprogramm aus
3. Composer wird automatisch zum PATH hinzugefügt

## 3. Node.js installieren

1. Laden Sie Node.js herunter: https://nodejs.org/en/download/
2. Wählen Sie die LTS-Version für Windows
3. Führen Sie das Installationsprogramm aus
4. Node.js und npm werden automatisch installiert

## 4. MySQL installieren

### Option A: XAMPP (bereits installiert)
- MySQL ist bereits in XAMPP enthalten
- Starten Sie MySQL über das XAMPP Control Panel

### Option B: MySQL standalone
1. Laden Sie MySQL herunter: https://dev.mysql.com/downloads/installer/
2. Führen Sie das Installationsprogramm aus
3. Merken Sie sich das Root-Passwort!

## 5. Installation überprüfen

Öffnen Sie eine neue Eingabeaufforderung (cmd) und führen Sie aus:

```cmd
php -v
composer -V
node -v
npm -v
mysql --version
```

Alle Befehle sollten eine Versionsnummer anzeigen.

## 6. Projekt installieren

### 6.1 In das Projektverzeichnis wechseln
```cmd
cd c:\Users\imper\kilo\news-portal
```

### 6.2 PHP-Abhängigkeiten installieren
```cmd
composer install
```

### 6.3 Node.js-Abhängigkeiten installieren
```cmd
npm install
```

### 6.4 Umgebungsdatei konfigurieren
```cmd
copy .env.example .env
```

Bearbeiten Sie die `.env`-Datei mit einem Texteditor (z.B. Notepad):
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_portal
DB_USERNAME=root
DB_PASSWORD=
```

### 6.5 Anwendungsschlüssel generieren
```cmd
php artisan key:generate
```

### 6.6 Datenbank erstellen

#### Mit XAMPP/phpMyAdmin:
1. Öffnen Sie http://localhost/phpmyadmin
2. Klicken Sie auf "Neu"
3. Geben Sie "news_portal" als Namen ein
4. Klicken Sie auf "Erstellen"

#### Mit MySQL-Kommandozeile:
```cmd
mysql -u root -p
CREATE DATABASE news_portal;
EXIT;
```

### 6.7 Datenbank-Migrationen ausführen
```cmd
php artisan migrate
```

### 6.8 Admin-Benutzer erstellen
```cmd
php artisan db:seed --class=AdminSeeder
```

### 6.9 Storage-Link erstellen
```cmd
php artisan storage:link
```

### 6.10 Assets kompilieren
```cmd
npm run build
```

### 6.11 Entwicklungsserver starten
```cmd
php artisan serve
```

Die Anwendung ist jetzt erreichbar unter: **http://localhost:8000**

## 7. Admin-Anmeldedaten

- **E-Mail**: admin@example.com
- **Passwort**: password

## 8. Cloudflare Turnstile CAPTCHA (Optional)

Um CAPTCHA zu aktivieren:

1. Gehen Sie zu https://dash.cloudflare.com/?to=/:account/turnstile
2. Erstellen Sie eine neue Turnstile-Site
3. Kopieren Sie die **Site Key** und **Secret Key**
4. Fügen Sie diese zu Ihrer `.env`-Datei hinzu:

```
CLOUDFLARE_TURNSTILE_SITE_KEY=Ihre_Site_Key
CLOUDFLARE_TURNSTILE_SECRET_KEY=Ihre_Secret_Key
```

## 9. Fehlerbehebung

### Fehler: "php nicht gefunden"
- Stellen Sie sicher, dass PHP zum PATH hinzugefügt wurde
- Starten Sie die Eingabeaufforderung neu

### Fehler: "composer nicht gefunden"
- Stellen Sie sicher, dass Composer zum PATH hinzugefügt wurde
- Starten Sie die Eingabeaufforderung neu

### Fehler: "node nicht gefunden"
- Stellen Sie sicher, dass Node.js zum PATH hinzugefügt wurde
- Starten Sie die Eingabeaufforderung neu

### Fehler: "SQLSTATE[HY000] [2002] Connection refused"
- Stellen Sie sicher, dass MySQL läuft
- Überprüfen Sie die Datenbankeinstellungen in `.env`

### Fehler: "APP_KEY not set"
```cmd
php artisan key:generate
```

## 10. XAMPP starten

1. Öffnen Sie das XAMPP Control Panel
2. Starten Sie "Apache" und "MySQL"
3. Öffnen Sie http://localhost/news-portal/public im Browser

## 11. Virtuellen Host konfigurieren (Optional)

### Apache Virtual Host:
1. Öffnen Sie `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
2. Fügen Sie am Ende hinzu:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/news-portal/public"
    ServerName news-portal.local
    <Directory "C:/xampp/htdocs/news-portal/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Öffnen Sie `C:\Windows\System32\drivers\etc\hosts` als Administrator
4. Fügen Sie am Ende hinzu:
```
127.0.0.1 news-portal.local
```

5. Starten Sie Apache neu
6. Öffnen Sie http://news-portal.local im Browser

## 12. OAuth (Google / Discord) – `.env` (zusätzlich in `conf/extra` nur der Hostname)

Die Anmeldung mit Google/Discord ist **optional**. Wenn Sie sie nutzen möchten, tragen Sie nach der Einrichtung des virtuellen Hosts (Abschnitt 11) in der **Projekt-`.env`** (nicht in `httpd-vhosts.conf`) mindestens ein:

- **`APP_URL`** muss exakt der öffentlichen Basis-URL entsprechen, z. B. `http://news-portal.local` lokal oder `https://www.example.com` online (ohne abschließenden Schrägstrich, außer Sie betreiben die App bewusst in einem Unterordner).
- **`GOOGLE_CLIENT_ID`**, **`GOOGLE_CLIENT_SECRET`**, optional **`GOOGLE_REDIRECT_URI`** (Standard: `{APP_URL}/oauth/google/callback`).
- **`DISCORD_CLIENT_ID`**, **`DISCORD_CLIENT_SECRET`**, optional **`DISCORD_REDIRECT_URI`** (Standard: `{APP_URL}/oauth/discord/callback`).

Vollständige Liste siehe **`.env.example`**. In der **Google Cloud Console** bzw. beim **Discord Developer Portal** müssen dieselben Redirect-URIs wie in der Konfiguration eingetragen sein. Nach Änderungen an `.env`:

```cmd
cd C:\xampp\htdocs\news-portal
php artisan config:clear
```

## 13. Deployment auf Webspace (Shared Hosting)

Typische Webpakete (Apache, PHP, MySQL, kein eigener Server) – so passt Laravel dazu:

1. **`APP_URL`**: Setzen Sie auf die **echte HTTPS-URL** Ihrer Domain (z. B. `https://ihre-domain.de`). Google/Discord erlauben produktiv üblicherweise nur **https**-Redirects.
2. **DocumentRoot / Hauptverzeichnis**: Im Kundenmenü des Hosters auf den Ordner **`public`** des Projekts zeigen lassen (nicht auf das Laravel-Wurzelverzeichnis). Liegt die Seite unter `https://domain.de/unterordner/`, muss `APP_URL` genau diesen Unterordner enthalten und die OAuth-Redirect-URIs in Google/Discord dieselbe Basis haben.
3. **PHP-Version**: Mindestens **PHP 8.1** (wie in `composer.json`). Erweiterungen analog lokaler `php.ini` (u. a. `openssl`, `pdo_mysql`, `mbstring`, `curl`, `fileinfo`).
4. **Dateien hochladen**: Gesamtes Projekt per FTP/SFTP (inkl. `vendor` **oder** auf dem Server per SSH `composer install --no-dev --optimize-autoloader`).
5. **`.env`**: Auf dem Server anlegen (von `.env.example` kopieren), `APP_KEY` mit `php artisan key:generate`, Datenbank-Zugangsdaten eintragen, OAuth-Keys wie in Abschnitt 12.
6. **Migrationen**: Per SSH im Projektordner `php artisan migrate --force` **oder** über den **Weboberflächen-Installer** des CMS, falls vorhanden.
7. **Schreibrechte**: `storage/` und `bootstrap/cache/` müssen für den Webserver beschreibbar sein (z. B. 775).
8. **Kein SSH**: Viele Hoster erlauben nur FTP – dann lokal `composer install` ausführen, den kompletten Ordner inkl. `vendor` hochladen und Migrationen ggf. über ein Installations-Skript oder Support anstoßen.

**Hinweis:** In `apache\conf\extra\httpd-vhosts.conf` (nur lokal/XAMPP) konfigurieren Sie den **Hostnamen**; alle **Secrets und OAuth-Keys** gehören ausschließlich in die **`.env`** auf dem jeweiligen System (lokal oder Webspace).
