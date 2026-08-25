<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Geplante Backups (Artisan-Scheduler)
    |--------------------------------------------------------------------------
    */

    'schedule_enabled' => filter_var(env('BACKUP_SCHEDULE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'schedule_time' => env('BACKUP_SCHEDULE_TIME', '02:00'),

    /*
    |--------------------------------------------------------------------------
    | mysqldump (nur MySQL / MariaDB)
    |--------------------------------------------------------------------------
    | Windows/XAMPP z. B.: C:\xampp\mysql\bin\mysqldump.exe
    | Linux: meist "mysqldump" im PATH
    */

    'mysqldump_path' => env('BACKUP_MYSQLDUMP_PATH', 'mysqldump'),

    'mysql_path' => env('BACKUP_MYSQL_PATH', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Inhalt
    |--------------------------------------------------------------------------
    */

    'include_public_storage' => filter_var(env('BACKUP_INCLUDE_PUBLIC_STORAGE', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Aufbewahrung (*.zip in storage/app/backups)
    |--------------------------------------------------------------------------
    */

    'keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Wiederherstellung
    |--------------------------------------------------------------------------
    */

    'restore_safety_backup' => filter_var(env('BACKUP_RESTORE_SAFETY_BACKUP', true), FILTER_VALIDATE_BOOLEAN),

    'restore_max_upload_mb' => (int) env('BACKUP_RESTORE_MAX_UPLOAD_MB', 512),

];
