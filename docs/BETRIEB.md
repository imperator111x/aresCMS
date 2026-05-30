# Betrieb: Backups & Wartungsmodus

## CMS-Updates (mehrere Webseiten)

Siehe **[docs/UPDATES.md](UPDATES.md)** — Manifest-URL in `.env`, Admin-Menü **System updates**, Schutz von `.env` und `config/`.

---

## Admin-Panel

Als eingeloggter Admin unter **Admin → Betrieb** (`/admin/operations`):

- **Backup jetzt** – führt `php artisan backup:application` aus (ZIP unter `storage/app/backups`).
- **Wartungsmodus ein/aus** – entspricht `php artisan down` / `up` (ohne `--render`). Die Seite **`errors.maintenance`** wird bei jedem Request **dynamisch** von der Middleware gerendert – inkl. aktuellem Link zur Admin-Anmeldung.

Hinweis: **Eingeloggte Admins** können die ganze Seite nutzen; **Gäste** sehen die Wartungsseite. **„Administrator-Anmeldung“** → **`/wartung/admin-anmeldung`**. Optional: **Bypass-URL** mit `--secret` zum Testen der öffentlichen Ansicht.

---

## Automatische Backups

Der Befehl `php artisan backup:application` erstellt unter **`storage/app/backups/`** eine ZIP-Datei mit:

- **MySQL/MariaDB:** SQL-Dump (`database.sql`) per `mysqldump`
- **SQLite:** Kopie der Datenbankdatei
- **Dateien:** Inhalt von `storage/app/public` (Uploads, Logos, News-Bilder …), sofern aktiviert

### Einmalig manuell

```bash
php artisan backup:application
```

### Windows (XAMPP) – Pfad zu mysqldump

Ohne Eintrag in `.env` versucht das Backup nacheinander u. a.:

- `C:\xampp\htdocs\…` → automatisch `C:\xampp\mysql\bin\mysqldump.exe` (zwei Ordner über dem Projekt)
- `C:\xampp\mysql\bin\mysqldump.exe` und `%SystemDrive%\xampp\mysql\bin\mysqldump.exe`
- zuletzt `mysqldump` (nur wenn im **PATH**)

Wenn es trotzdem scheitert, in `.env` festlegen:

```env
BACKUP_MYSQLDUMP_PATH="C:\xampp\mysql\bin\mysqldump.exe"
```

### Geplante Ausführung (Cron / Task Scheduler)

1. In `.env` aktivieren:

```env
BACKUP_SCHEDULE_ENABLED=true
BACKUP_SCHEDULE_TIME=02:00
BACKUP_INCLUDE_PUBLIC_STORAGE=true
BACKUP_KEEP_DAYS=14
```

2. **Linux/macOS:** Cron-Eintrag (einmal pro Minute den Scheduler starten – Laravel prüft intern die Uhrzeit):

```cron
* * * * * cd /pfad/zum/news-portal && php artisan schedule:run >> /dev/null 2>&1
```

3. **Windows:** „Aufgabenplanung“ → neue Aufgabe → **Programm:** `php` → **Argumente:** `C:\xampp\htdocs\news-portal\artisan schedule:run` → **Start in:** Projektordner → Intervall z. B. alle 1 Minute (oder nur nachts, dann in `Kernel.php` die Zeit anpassen).

### Hinweise

- Backups **nicht** nur auf dem gleichen Server lagern; regelmäßig **extern** kopieren (NAS, Cloud, anderer Rechner).
- Bei großen Upload-Ordnern wird die ZIP-Datei entsprechend groß.
- Alte ZIPs werden nach **`BACKUP_KEEP_DAYS`** Tagen automatisch gelöscht (beim nächsten erfolgreichen Backup-Lauf). **`BACKUP_KEEP_DAYS=0`** schaltet die automatische Löschung aus.

---

## Wartungsmodus

Besucher sehen die Wartungsseite aus **`resources/views/errors/maintenance.blade.php`** (wird bei jedem Aufruf gerendert, nicht als altes HTML aus `storage/framework/down`).

### Aktivieren

```bash
php artisan down
```

`--render` ist in diesem Projekt **nicht nötig** (und würde nur unnötig große Daten in `down` speichern); die Middleware zeigt immer die Blade-View.

Optional mit **Geheim-Link**:

```bash
php artisan down --secret="dein-geheimer-token"
```

Dann im Browser z. B.: `https://deine-domain.de/dein-geheimer-token`

### Deaktivieren

```bash
php artisan up
```

### Hinweise

- Während `down` sind Routen für **Gäste** gesperrt. **Eingeloggte Admins** (Session) werden in `App\Http\Middleware\PreventRequestsDuringMaintenance` durchgelassen und sehen die normale Seite. Zusätzlich: fest definierte Ausnahmen (Login, 2FA, Passwort, Sprache) und Bypass mit `--secret` / geheimer URL (siehe unten).
- **CLI** (`php artisan …`) funktioniert weiter.

---

## Checkliste vor Go-Live

- [ ] `.env` `APP_DEBUG=false`, `APP_URL` korrekt
- [ ] `php artisan config:cache` / `route:cache` nach Deployment
- [ ] Backups getestet (`backup:application` + ZIP prüfen)
- [ ] Cron / Task Scheduler für `schedule:run` eingerichtet (wenn Backups automatisch)
- [ ] Wartungsmodus-Prozedur dokumentiert (wer führt `down`/`up` aus)
