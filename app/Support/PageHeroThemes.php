<?php

namespace App\Support;

final class PageHeroThemes
{
    /**
     * @return array<string, array{gradient: string, badge: string, dot: string, text: string}>
     */
    public static function all(): array
    {
        return [
            'blue' => [
                'gradient' => 'from-primary-500/10 via-transparent to-blue-500/10',
                'badge' => 'bg-primary-500/10 border-primary-500/20',
                'dot' => 'bg-primary-500',
                'text' => 'text-primary-600 dark:text-primary-400',
            ],
            'green' => [
                'gradient' => 'from-emerald-500/10 via-transparent to-emerald-500/10',
                'badge' => 'bg-emerald-500/10 border-emerald-500/20',
                'dot' => 'bg-emerald-500',
                'text' => 'text-emerald-600 dark:text-emerald-400',
            ],
            'purple' => [
                'gradient' => 'from-purple-500/10 via-transparent to-fuchsia-500/10',
                'badge' => 'bg-purple-500/10 border-purple-500/20',
                'dot' => 'bg-purple-500',
                'text' => 'text-purple-600 dark:text-purple-400',
            ],
            'orange' => [
                'gradient' => 'from-orange-500/10 via-transparent to-amber-500/10',
                'badge' => 'bg-orange-500/10 border-orange-500/20',
                'dot' => 'bg-orange-500',
                'text' => 'text-orange-600 dark:text-orange-400',
            ],
        ];
    }

    /**
     * @return array{gradient: string, badge: string, dot: string, text: string}
     */
    public static function resolve(string $theme): array
    {
        $themes = self::all();

        return $themes[$theme] ?? $themes['blue'];
    }
}
