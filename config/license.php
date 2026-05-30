<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lizenzprüfung aktiv
    |--------------------------------------------------------------------------
    | Nur in Notfällen auf false setzen (z. B. wenn der Lizenzserver ausfällt
    | und du bewusst das Risiko trägst). Standard: aktiv.
    */

    'enabled' => filter_var(env('CMS_LICENSE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Lizenzschlüssel (.env)
    |--------------------------------------------------------------------------
    | Wenn leer, kann der Schlüssel einmalig unter /license eingegeben werden;
    | er wird verschlüsselt in storage/app/cms/.license gespeichert (nicht committen).
    | CMS_LICENSE_KEY in der .env hat Vorrang vor der gespeicherten Datei.
    */

    'key' => env('CMS_LICENSE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Validierungs-URL (HTTPS)
    |--------------------------------------------------------------------------
    | POST mit JSON { "license_key": "…", "domain": "…" } – siehe docs/LICENSE_SERVER.md
    */

    'validate_url' => env('CMS_LICENSE_VALIDATE_URL', 'https://key.luetcke.eu/validate-license.php'),

    /*
    |--------------------------------------------------------------------------
    | Erlaubte Hostnamen für die Validierungs-URL (optional)
    |--------------------------------------------------------------------------
    | Kommagetrennt, z. B. key.luetcke.eu,license.example.com
    | Wenn nicht leer: Nur diese Hosts sind für CMS_LICENSE_VALIDATE_URL erlaubt.
    | erschwert das Umstellen auf ein beliebiges Fake-Skript ohne Code-Änderung.
    | Leer lassen für eigene Lizenz-Server oder lokale Entwicklung.
    */

    'allowed_validate_hosts' => array_values(array_filter(array_map(
        static fn (string $h): string => strtolower(trim($h)),
        explode(',', (string) env('CMS_LICENSE_ALLOWED_VALIDATE_HOSTS', ''))
    ))),

    'timeout' => (int) env('CMS_LICENSE_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | TLS-Zertifikat prüfen (HTTPS)
    |--------------------------------------------------------------------------
    | Bei lokalem Test mit https://localhost und selbstsigniertem Zertifikat
    | auf false setzen. In Produktion immer true.
    */

    'verify_ssl' => filter_var(env('CMS_LICENSE_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Cache bei erfolgreicher Prüfung (Sekunden)
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => max(60, (int) env('CMS_LICENSE_CACHE_TTL', 3600)),

    /*
    |--------------------------------------------------------------------------
    | Grace-Zeit nach letzter gültiger Prüfung (Sekunden)
    |--------------------------------------------------------------------------
    | Wenn der Lizenzserver kurzzeitig nicht erreichbar ist, bleibt die
    | Installation nutzbar, solange innerhalb dieser Frist zuletzt „gültig“ war.
    */

    'grace_ttl' => max(300, (int) env('CMS_LICENSE_GRACE_TTL', 604800)),

];
