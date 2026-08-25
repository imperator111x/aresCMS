<?php

namespace App\Support;

/**
 * Resolves a PHP CLI binary suitable for Process/Composer.
 * Under php-fpm, PHP_BINARY often points at php-fpm (not executable as CLI).
 */
final class PhpCliBinary
{
    public static function resolve(): string
    {
        $configured = trim((string) (
            getenv('PHP_CLI_BINARY')
            ?: ($_ENV['PHP_CLI_BINARY'] ?? '')
            ?: (config('cms.php_cli_binary') ?? '')
        ));
        if ($configured !== '' && self::isUsableCli($configured)) {
            return $configured;
        }

        if (self::isUsableCli(PHP_BINARY)) {
            return PHP_BINARY;
        }

        $derived = self::deriveFromCurrentBinary(PHP_BINARY);
        if ($derived !== null) {
            return $derived;
        }

        $fromPath = self::findOnPath('php');
        if ($fromPath !== null) {
            return $fromPath;
        }

        foreach ([
            '/usr/bin/php',
            '/usr/local/bin/php',
            'C:\\xampp\\php\\php.exe',
        ] as $candidate) {
            if (self::isUsableCli($candidate)) {
                return $candidate;
            }
        }

        return 'php';
    }

    public static function isUsableCli(string $path): bool
    {
        $path = trim($path);
        if ($path === '') {
            return false;
        }

        $base = strtolower(basename(str_replace('\\', '/', $path)));
        if (
            str_contains($base, 'php-fpm')
            || str_contains($base, 'php-cgi')
            || str_ends_with($base, '-fpm')
            || str_ends_with($base, '-cgi')
        ) {
            return false;
        }

        if (! str_contains($base, 'php')) {
            return false;
        }

        if ($path === 'php' || $path === 'php.exe') {
            return true;
        }

        return is_file($path) && (is_executable($path) || PHP_OS_FAMILY === 'Windows');
    }

    private static function deriveFromCurrentBinary(string $binary): ?string
    {
        $normalized = str_replace('\\', '/', $binary);
        if ($normalized === '') {
            return null;
        }

        $candidates = [];

        // /opt/lima-php/8.5/sbin/php-fpm → /opt/lima-php/8.5/bin/php
        if (preg_match('#^(.*)/sbin/(php(?:-fpm)?[^/]*)$#i', $normalized, $m)) {
            $prefix = $m[1];
            $candidates[] = $prefix.'/bin/php';
            $candidates[] = $prefix.'/bin/php'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;
            $candidates[] = $prefix.'/bin/php'.PHP_MAJOR_VERSION;
        }

        $dir = dirname($normalized);
        $parent = dirname($dir);
        $candidates[] = $parent.'/bin/php';
        $candidates[] = $dir.'/php';
        $candidates[] = preg_replace('#php-fpm(\.exe)?$#i', 'php$1', $normalized) ?: null;

        foreach (array_filter(array_unique($candidates)) as $candidate) {
            if (is_string($candidate) && self::isUsableCli($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function findOnPath(string $name): ?string
    {
        $command = PHP_OS_FAMILY === 'Windows'
            ? ['where', $name]
            : ['command', '-v', $name];

        try {
            $process = new \Symfony\Component\Process\Process($command);
            $process->setTimeout(5);
            $process->run();
            if (! $process->isSuccessful()) {
                return null;
            }

            $line = trim(explode("\n", str_replace("\r\n", "\n", $process->getOutput()))[0] ?? '');
            if ($line !== '' && self::isUsableCli($line)) {
                return $line;
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
