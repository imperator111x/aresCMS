<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CMS-Bundle-Version (mit dieser Codebasis ausgeliefert)
    |--------------------------------------------------------------------------
    | Wird genutzt, solange noch keine Datei storage/app/cms/installed_version existiert.
    */

    'bundle_version' => env('CMS_BUNDLE_VERSION', '1.0.1'),

    /*
    |--------------------------------------------------------------------------
    | Update-Quelle
    |--------------------------------------------------------------------------
    | HTTPS-URL zu einer JSON-Datei (Manifest). Standard: offizieller Update-Host.
    | Mit CMS_UPDATE_MANIFEST_URL in der .env überschreibbar; leerer Wert schaltet ab.
    */

    'update_manifest_url' => env('CMS_UPDATE_MANIFEST_URL', 'https://update.luetcke.eu/manifest.json'),

    'update_token' => env('CMS_UPDATE_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Netzwerk-Haertung fuer Updates
    |--------------------------------------------------------------------------
    | update_require_https: blockiert unverschluesselte HTTP-Quellen.
    | update_allowed_hosts: zusaetzliche erlaubte Hosts fuer package_url.
    | Wenn leer, ist mindestens der Host aus update_manifest_url erlaubt.
    */
    'update_require_https' => filter_var(env('CMS_UPDATE_REQUIRE_HTTPS', true), FILTER_VALIDATE_BOOLEAN),
    'update_allowed_hosts' => array_values(array_filter(array_map(
        static fn ($host) => strtolower(trim((string) $host)),
        explode(',', (string) env('CMS_UPDATE_ALLOWED_HOSTS', ''))
    ))),

    'update_enabled' => filter_var(env('CMS_UPDATE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Manifest-Cache (Sekunden)
    |--------------------------------------------------------------------------
    */

    'manifest_cache_ttl' => (int) env('CMS_MANIFEST_CACHE_TTL', 600),

    /*
    |--------------------------------------------------------------------------
    | Pfade, die beim Entpacken eines Updates NICHT überschrieben werden
    |--------------------------------------------------------------------------
    | Relativ zum Projektroot, Schrägstriche. config/ und .env sind immer geschützt.
    */

    'update_path_blacklist' => [
        '.env',
        'config',
        'storage/app/public',
        'storage/app/backups',
        'storage/logs',
        'storage/app/cms',
    ],

];
