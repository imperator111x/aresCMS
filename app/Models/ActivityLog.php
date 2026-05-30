<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [];
    }

    /**
     * @param  mixed  $value
     */
    public function getPropertiesAttribute($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  mixed  $value
     */
    public function setPropertiesAttribute($value): void
    {
        if ($value === null || $value === '' || $value === []) {
            $this->attributes['properties'] = null;

            return;
        }
        if (is_string($value)) {
            $this->attributes['properties'] = $value;

            return;
        }
        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            $this->attributes['properties'] = $encoded === false ? '{}' : $encoded;

            return;
        }
        $this->attributes['properties'] = '{}';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
