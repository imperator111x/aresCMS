# Installation Guide - News Portal

## Voraussetzungen

Bevor Sie beginnen, stellen Sie sicher, dass Sie Folgendes installiert haben:

- **PHP 8.1 oder höher**
- **Composer** (PHP-Paketmanager)
- **Node.js und npm** (für Vite/Assets)
- **MySQL oder PostgreSQL** (Datenbank)
- **Webserver** (Apache/Nginx) oder XAMPP/WAMP/MAMP

## Schritt-für-Schritt Installation

### 1. Projekt herunterladen

Laden Sie das Projekt herunter oder klonen Sie es in ein Verzeichnis Ihrer Wahl.

### 2. In das Projektverzeichnis wechseln

```bash
cd news-portal
```

### 3. PHP-Abhängigkeiten installieren

```bash
composer install
```

Falls `composer` nicht gefunden wird:
- Laden Sie Composer von https://getcomposer.org/download/ herunter
- Installieren Sie es global oder verwenden Sie `php composer.phar install`

### 4. Node.js-Abhängigkeiten installieren

```bash
npm install
```

### 5. Umgebungsdatei konfigurieren

Kopieren Sie die Beispiel-Umgebungsdatei:

```bash
copy .env.example .env
```

Bearbeiten Sie die `.env`-Datei und passen Sie die Datenbankeinstellungen an:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_portal
DB_USERNAME=root
DB_PASSWORD=Ihr_Passwort
```

### 6. Cloudflare Turnstile CAPTCHA konfigurieren (Optional)

Um CAPTCHA auf Login und Register Seiten zu aktivieren:

1. Gehen Sie zu https://dash.cloudflare.com/?to=/:account/turnstile
2. Erstellen Sie eine neue Turnstile-Site
3. Kopieren Sie die Site Key und Secret Key
4. Fügen Sie diese zu Ihrer `.env`-Datei hinzu:

```env
CLOUDFLARE_TURNSTILE_SITE_KEY=Ihre_Site_Key
CLOUDFLARE_TURNSTILE_SECRET_KEY=Ihre_Secret_Key
```

Wenn keine Keys konfiguriert sind, wird CAPTCHA automatisch deaktiviert.

### 7. Anwendungsschlüssel generieren

```bash
php artisan key:generate
```

### 8. Datenbank erstellen

Erstellen Sie eine neue MySQL-Datenbank namens `news_portal`:

```sql
CREATE DATABASE news_portal;
```

Oder verwenden Sie phpMyAdmin/MySQL Workbench.

### 9. Datenbank-Migrationen ausführen

```bash
php artisan migrate
```

### 10. Admin-Benutzer erstellen

```bash
php artisan db:seed --class=AdminSeeder
```

Dies erstellt einen Admin-Benutzer mit:
- **E-Mail**: admin@example.com
- **Passwort**: password

### 11. Storage-Link erstellen

```bash
php artisan storage:link
```

### 12. Assets kompilieren

```bash
npm run build
```

### 13. Entwicklungsserver starten

```bash
php artisan serve
```

Die Anwendung ist jetzt erreichbar unter: **http://localhost:8000**

## Alternative Installation mit XAMPP/WAMP

### 1. XAMPP/WAMP installieren

Laden Sie XAMPP (https://www.apachefriends.org/) oder WAMP herunter und installieren Sie es.

### 2. Projekt in htdocs kopieren

Kopieren Sie den `news-portal` Ordner in:
- XAMPP: `C:\xampp\htdocs\news-portal`
- WAMP: `C:\wamp64\www\news-portal`

### 3. Apache und MySQL starten

Starten Sie Apache und MySQL über das XAMPP/WAMP Control Panel.

### 4. Datenbank erstellen

Öffnen Sie phpMyAdmin (http://localhost/phpmyadmin) und erstellen Sie eine neue Datenbank namens `news_portal`.

### 5. Installationsschritte ausführen

Führen Sie die Schritte 3-13 oben aus, aber verwenden Sie:
- `http://localhost/news-portal/public` als URL
- Oder konfigurieren Sie einen virtuellen Host

## Virtuellen Host konfigurieren (Optional)

### Apache Virtual Host

Fügen Sie zu Ihrer `httpd-vhosts.conf` hinzu:

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

Fügen Sie zu Ihrer `hosts`-Datei hinzu:
```
127.0.0.1 news-portal.local
```

Dann erreichen Sie die Anwendung unter: **http://news-portal.local**

## Fehlerbehebung

### Fehler: "Class not found"

```bash
composer dump-autoload
```

### Fehler: "Permission denied"

Stellen Sie sicher, dass die `storage` und `bootstrap/cache` Verzeichnisse beschreibbar sind:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Fehler: "APP_KEY not set"

```bash
php artisan key:generate
```

### Fehler: "SQLSTATE[HY000] [2002] Connection refused"

Überprüfen Sie Ihre Datenbankeinstellungen in der `.env`-Datei.

## Nach der Installation

1. Öffnen Sie http://localhost:8000 in Ihrem Browser
2. Melden Sie sich mit dem Admin-Konto an:
   - E-Mail: admin@example.com
   - Passwort: password
3. Erstellen Sie Nachrichten und verwalten Sie Benutzer

## Sprachumschaltung

Die Anwendung unterstützt Deutsch und Englisch:
- Klicken Sie auf "DE" oder "EN" in der Navigationsleiste
- Die Sprache wird in der Session gespeichert

## Cloudflare Turnstile CAPTCHA

Um CAPTCHA zu aktivieren:
1. Gehen Sie zu https://dash.cloudflare.com/?to=/:account/turnstile
2. Erstellen Sie eine neue Turnstile-Site
3. Fügen Sie die Keys zu Ihrer `.env`-Datei hinzu
4. CAPTCHA erscheint automatisch auf Login und Register Seiten

## Support

Bei Problemen überprüfen Sie:
- PHP-Version: `php -v`
- Composer-Version: `composer -V`
- Node-Version: `node -v`
- Datenbankverbindung
