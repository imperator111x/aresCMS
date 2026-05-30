<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginHistory extends Model
{
    protected $fillable = [
        'user_id',
        'identifier',
        'success',
        'failure_reason',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function recordSuccess(User $user, Request $request): void
    {
        static::query()->create([
            'user_id' => $user->id,
            'identifier' => $user->email,
            'success' => true,
            'failure_reason' => null,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 2000),
        ]);
    }

    public static function recordFailure(Request $request, string $identifier, ?string $reason = null, ?int $userId = null): void
    {
        static::query()->create([
            'user_id' => $userId,
            'identifier' => Str::limit($identifier, 255),
            'success' => false,
            'failure_reason' => $reason ? Str::limit($reason, 64) : null,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 2000),
        ]);
    }
}
