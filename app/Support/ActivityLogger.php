<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class ActivityLogger
{
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $subject = null,
        array $properties = []
    ): void {
        $payload = [
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description ? Str::limit($description, 500) : null,
            'properties' => $properties !== [] ? $properties : null,
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 2000),
        ];

        try {
            ActivityLog::query()->create($payload);
        } catch (Throwable) {
            // Fallback for installations where the DB column is shorter than expected.
            $payload['description'] = $payload['description'] !== null
                ? Str::limit((string) $payload['description'], 190)
                : null;

            try {
                ActivityLog::query()->create($payload);
            } catch (Throwable) {
                // Logging must never break user-facing requests.
            }
        }
    }
}
