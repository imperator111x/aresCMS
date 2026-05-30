<?php

namespace Plugins\SponsorAdSlots\Models;

use Illuminate\Database\Eloquent\Model;

class AdSlot extends Model
{
    protected $table = 'sponsor_ad_slots';

    protected $fillable = [
        'name',
        'slot_key',
        'target_url',
        'image_url',
        'image_path',
        'html_code',
        'is_active',
        'starts_at',
        'ends_at',
        'priority',
        'impressions',
        'clicks',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'priority' => 'integer',
            'impressions' => 'integer',
            'clicks' => 'integer',
        ];
    }
}
