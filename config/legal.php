<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Impressum / Anbieterkennzeichnung (§ 5 TMG / § 18 MStV)
    |--------------------------------------------------------------------------
    | Fallback, wenn im Admin unter „Impressum“ nichts eingetragen ist.
    | Admin-Einträge (settings) haben Vorrang vor diesen .env-Werten.
    */

    'entity_name' => env('LEGAL_ENTITY_NAME'),
    'representative' => env('LEGAL_REPRESENTATIVE'),
    'street' => env('LEGAL_ADDRESS_STREET'),
    'zip' => env('LEGAL_ADDRESS_ZIP'),
    'city' => env('LEGAL_ADDRESS_CITY'),
    'country' => env('LEGAL_COUNTRY'),
    'email' => env('LEGAL_EMAIL'),
    'phone' => env('LEGAL_PHONE'),
    'vat_id' => env('LEGAL_VAT_ID'),
    'register_info' => env('LEGAL_REGISTER_INFO'),
    'content_responsibility' => env('LEGAL_CONTENT_RESPONSIBILITY'),
];
