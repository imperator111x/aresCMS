# Theme-System

## Übersicht

Öffentliche Seiten nutzen ein aktives Theme aus dem Ordner `themes/`. Der Admin-Bereich bleibt unverändert.

## Mitgelieferte Themes

| Slug | Name | Beschreibung |
|------|------|--------------|
| `default` | Standard | Blau/violett, Karten-Layout |
| `magazine` | Magazin | Serif-Schrift, rote Akzente, große Startseiten-Story |

## Auswahl im Admin

**Einstellungen → Themes** (`/admin/settings/themes`)

## Neues Theme anlegen

1. Ordner `themes/mein-theme/` erstellen
2. `theme.json` mit `name`, `description`, `version`
3. Optional Views unter `themes/mein-theme/views/` (z. B. `layouts/app.blade.php`, `news/home.blade.php`)
4. Optional CSS unter `public/themes/mein-theme/theme.css`
5. Im Admin aktivieren

Laravel sucht Views zuerst im Theme-Ordner, dann in `resources/views/`.

## Technik

- `App\Services\ThemeManager` — Discovery, aktives Theme, View-Pfade
- Einstellung in DB: `active_theme`
- Gemeinsame Shell: `resources/views/layouts/_public-shell.blade.php`
