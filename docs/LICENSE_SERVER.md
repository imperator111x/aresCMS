# Lizenzserver API (key.luetcke.eu)

**aresCMS** sendet bei jedem Seitenaufruf (mit Cache) eine **HTTPS-POST**-Anfrage zur Validierung.

## Schlüssel hinterlegen

- **`.env`:** `CMS_LICENSE_KEY=…` (hat Vorrang)
- **Oder** im Browser: Seite **`/license`** – gültiger Schlüssel wird verschlüsselt als `storage/app/cms/.license` abgelegt (Ordner `storage/app/cms/` ist per `.gitignore` von Git ausgeschlossen).

## Nur localhost (Entwicklung)

Im Projekt liegt **`public/dev-license-validate.php`**: liefert nur für Domain **`localhost`**, **`127.0.0.1`** oder **`::1`** / **`[::1]`** und Schlüssel **`LOCALHOST-DEV-KEY`** `{"valid":true}`.

**.env** (Pfade anpassen):

```env
CMS_LICENSE_KEY=LOCALHOST-DEV-KEY
CMS_LICENSE_VALIDATE_URL=http://127.0.0.1:8000/dev-license-validate.php
```

Auf **key.luetcke.eu** kann derselbe Key in **`deploy/key-server-validate.example.php`** bereits für localhost eingetragen sein.

## Endpoint (Standard beim CMS)

- **URL:** konfigurierbar als `CMS_LICENSE_VALIDATE_URL`, Standard: `https://key.luetcke.eu/validate-license.php`
- **Methode:** `POST`
- **Header:** `Content-Type: application/json`, `Accept: application/json`
- **Body (JSON):**

```json
{
  "license_key": "vom-kunden-aus-der-env",
  "domain": "aktueller-host-header"
}
```

`domain` ist der **HTTP-Host** der Anfrage (kleingeschrieben), z. B.:

- `localhost`
- `127.0.0.1`
- `www.beispiel.de`
- `beispiel.de`

(Kein Schema, kein Pfad, kein Port im JSON – der Host wie von `Request::getHost()`.)

## Antwort

**HTTP 200** mit JSON:

```json
{ "valid": true }
```

oder

```json
{
  "valid": false,
  "message": "Kurzer Grund (optional, wird im CMS angezeigt)"
}
```

Pflicht ist nur das boolesche Feld **`valid`**. Alles andere bei `valid: false` ist optional.

## Fehler / Nicht-200

- Antwort **nicht** HTTP 2xx, **Timeout**, oder **kein gültiges JSON** mit `valid`:  
  Das CMS behandelt das wie „Server nicht erreichbar“.  
  War die Lizenz zuvor schon erfolgreich geprüft, kann eine **Grace-Zeit** (`CMS_LICENSE_GRACE_TTL`, Standard 7 Tage) greifen – siehe `config/license.php`.

## Zuordnung Schlüssel → Domain

Auf dem Lizenzserver muss jeder `license_key` einer oder mehreren erlaubten Domains zugeordnet sein. Für Entwicklung **`localhost`** und **`127.0.0.1`** explizit erlauben, wenn gewünscht.

## Sicherheit

- Nur **HTTPS** verwenden (`CMS_LICENSE_VERIFY_SSL=true` in Produktion).
- **Host-Allowlist (optional):** In der CMS-`.env` kann `CMS_LICENSE_ALLOWED_VALIDATE_HOSTS=key.luetcke.eu` gesetzt werden. Dann akzeptiert das CMS nur noch Validierungs-URLs mit genau diesem Host – ein Kunde kann die URL nicht mehr auf ein beliebiges anderes Skript umstellen, ohne mindestens die Allowlist oder den PHP-Code anzupassen.
- **Grenze:** Wer vollen Quellcode und Serverzugriff hat, kann jede clientseitige Prüfung entfernen oder fälschen. Darüber hinaus helfen nur **Rechtliches** (Lizenzvertrag), **IonCube/obfusciertes** Verteilungs-Paket (hoher Support-Aufwand) oder **SaaS** (kritische Logik nur bei euch).

### Stärker (noch nicht im CMS): kryptografisch signierte Antwort

Der Lizenzserver könnte zusätzlich zu `valid` ein Feld `payload` + `signature` (RSA/Ed25519 über den Payload) liefern; im CMS liegt nur der **öffentliche** Schlüssel. Ein simples Fake-Skript ohne euren **privaten** Schlüssel kann keine gültige Signatur erzeugen. Trotzdem kann ein Angreifer die Signaturprüfung im Quelltext löschen – es erhöht nur den Aufwand gegenüber trivialen Fakes.

## HTTP 404 auf key.luetcke.eu/api/validate

Apache findet die URL nicht, meist weil **Rewrite nicht greift** (kein mod_rewrite, `AllowOverride None`) oder die **PHP-Datei fehlt**.

**Schnellfix (ohne .htaccess-Rewrite):**

1. Im Repo **`deploy/key-host/validate-license.php`** ins Document Root von `key.luetcke.eu` legen.
2. Im CMS in der `.env`:

```env
CMS_LICENSE_VALIDATE_URL=https://key.luetcke.eu/validate-license.php
```

3. `php artisan config:clear`

**Oder** den Ordner **`deploy/key-host/api/`** komplett hochladen und verwenden:

```env
CMS_LICENSE_VALIDATE_URL=https://key.luetcke.eu/api/validate/
```

(schließender Slash oft nötig)

Siehe **`deploy/key-host/README.md`**.

## Fehler „Lizenzserver nicht erreichbar“

1. **`CMS_LICENSE_VALIDATE_URL`** muss exakt die URL sein, die **POST** und **JSON** akzeptiert (z. B. `https://key.luetcke.eu/validate-license.php` oder lokal `http://127.0.0.1:8000/dev-license-validate.php`).
2. **XAMPP:** oft `http://localhost/ordner/public/dev-license-validate.php` – nicht `…/public/index.php/…`.
3. **HTTPS lokal mit selbstsigniertem Zertifikat:** in der `.env` `CMS_LICENSE_VERIFY_SSL=false` (nur Entwicklung).
4. **`APP_DEBUG=true`:** zeigt technische Details (Zertifikat, Timeout, HTTP-Status).
5. Logs: `storage/logs/laravel.log` nach `CMS license` durchsuchen.
6. Test: `php artisan license:check` (Host aus `APP_URL`).

## Key-Portal mit Login (ohne Datenbank)

Im Repository: **`deploy/key-portal/`** – PHP-Admin mit Login, Schlüssel anlegen/bearbeiten, Speicherung in **`data/licenses.json`**. Document Root = **`key-portal/public`**. Siehe **`deploy/key-portal/README.md`**.

## Beispiel-Datei (nur PHP, ohne Admin)

**`deploy/key-server-validate.example.php`** – statische `$licenses`-Liste; optional per Rewrite unter `/api/validate`.

**Apache:** Im Repository liegt eine fertige Vorlage **`deploy/key.luetcke.eu.htaccess`** – auf dem Server als **`.htaccess`** ins Document Root von `key.luetcke.eu` kopieren und den PHP-Dateinamen in der `RewriteRule` prüfen (Standard: `key-server-validate.php`).

Kurzform (nur Rewrite):

```apache
RewriteEngine On
RewriteRule ^api/validate/?$ key-server-validate.php [L,QSA]
```
