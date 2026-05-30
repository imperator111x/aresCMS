# Dateien für key.luetcke.eu (Lizenz-API)

## HTTP 404 auf `/api/validate`

Häufig: **mod_rewrite** ist aus oder **AllowOverride** erlaubt keine `.htaccess`. Dann greift die Umleitung nicht.

### Variante A (empfohlen, ohne Rewrite)

1. Den Ordner **`api`** aus diesem Verzeichnis **komplett** ins **Document Root** von `key.luetcke.eu` hochladen (Struktur: `…/api/validate/index.php`).
2. Im Browser testen:  
   `https://key.luetcke.eu/api/validate/`  
   (mit Slash am Ende) – bei POST kommt JSON (GET liefert ggf. 405, ist normal).
3. In der CMS-`.env` **mit Slash** oder ohne – beides testen; Laravel folgt Redirects:

```env
CMS_LICENSE_VALIDATE_URL=https://key.luetcke.eu/api/validate/
```

Falls dein Server **ohne** Slash nur 404 liefert, Slash verwenden.

### Variante B (eine Datei im Root)

1. **`validate-license.php`** ins Document Root legen.
2. `.env` im CMS:

```env
CMS_LICENSE_VALIDATE_URL=https://key.luetcke.eu/validate-license.php
```

### Variante C (Rewrite)

1. **`key-server-validate.example.php`** als **`key-server-validate.php`** ins Root legen.
2. **`key.luetcke.eu.htaccess`** (eine Ebene höher im Repo) als **`.htaccess`** ins Root kopieren.
3. Beim Hoster prüfen: **mod_rewrite** an, **AllowOverride All** für das Verzeichnis.

---

Inhalt von `api/validate/index.php` und `validate-license.php` ist identisch – Keys unter `$licenses` pflegen.
