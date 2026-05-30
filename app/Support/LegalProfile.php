<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Impressum / rechtliche Anbieterdaten: zuerst Admin-Einstellungen (settings), sonst .env (config/legal).
 */
class LegalProfile
{
    private const SETTING_KEYS = [
        'legal_entity_name',
        'legal_representative',
        'legal_address_street',
        'legal_address_zip',
        'legal_address_city',
        'legal_country',
        'legal_email',
        'legal_phone',
        'legal_vat_id',
        'legal_register_info',
        'legal_content_responsibility',
    ];

    /**
     * @return array<string, string|null>
     */
    public static function resolved(): array
    {
        $fromDb = Setting::query()
            ->whereIn('key', self::SETTING_KEYS)
            ->pluck('value', 'key');

        $pick = static function ($dbKey, $configKey) use ($fromDb): ?string {
            if ($fromDb->has($dbKey)) {
                $v = $fromDb->get($dbKey);
                if (is_string($v) && trim($v) !== '') {
                    return trim($v);
                }
            }
            $fallback = config($configKey);
            if (is_string($fallback) && trim($fallback) !== '') {
                return trim($fallback);
            }

            return null;
        };

        return [
            'entity_name' => $pick('legal_entity_name', 'legal.entity_name'),
            'representative' => $pick('legal_representative', 'legal.representative'),
            'street' => $pick('legal_address_street', 'legal.street'),
            'zip' => $pick('legal_address_zip', 'legal.zip'),
            'city' => $pick('legal_address_city', 'legal.city'),
            'country' => $pick('legal_country', 'legal.country'),
            'email' => $pick('legal_email', 'legal.email'),
            'phone' => $pick('legal_phone', 'legal.phone'),
            'vat_id' => $pick('legal_vat_id', 'legal.vat_id'),
            'register_info' => $pick('legal_register_info', 'legal.register_info'),
            'content_responsibility' => $pick('legal_content_responsibility', 'legal.content_responsibility'),
        ];
    }
}
