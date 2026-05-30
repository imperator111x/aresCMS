# Plugin-System

Das CMS kann lokale Plugins aus dem Verzeichnis `plugins/` laden.

## Struktur

Jedes Plugin liegt in einem eigenen Unterordner:

`plugins/MeinPlugin/plugin.json`

Beispiel `plugin.json`:

```json
{
  "slug": "mein-plugin",
  "name": "Mein Plugin",
  "description": "Kurze Beschreibung",
  "version": "1.0.0",
  "enabled": true,
  "provider": "Plugins\\MeinPlugin\\PluginServiceProvider",
  "provider_file": "src/PluginServiceProvider.php",
  "routes_file": "routes/web.php"
}
```

## Felder

- `enabled` (`true|false`): Plugin aktivieren/deaktivieren
- `provider` (optional): FQCN eines ServiceProviders
- `provider_file` (optional): Datei, die vor `provider` geladen wird
- `routes_file` (optional): Zusätzliche Web-Routen des Plugins

## Sicherheit

- `provider_file` und `routes_file` werden nur geladen, wenn sie innerhalb des jeweiligen Plugin-Ordners liegen.

## Admin-Übersicht

- Im Admin-Bereich unter `Plugins` wird der Discovery-Status angezeigt.
- Plugins können als ZIP über den Admin hochgeladen werden.
- Aktivierung/Deaktivierung ist direkt im Admin pro Plugin möglich.

