<?php

namespace Plugins\UserProfiles\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $table = 'profile_chat_messages';

    const UPDATED_AT = null;

    protected $fillable = [
        'friendship_id',
        'sender_id',
        'body',
        'is_e2e',
    ];

    protected function casts(): array
    {
        return [
            'is_e2e' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Friendship, ChatMessage>
     */
    public function friendship(): BelongsTo
    {
        return $this->belongsTo(Friendship::class, 'friendship_id');
    }

    /**
     * @return BelongsTo<User, ChatMessage>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public static function notificationPreview(string $body, bool $isE2e, bool $fromMe = false): string
    {
        if ($isE2e) {
            return $fromMe
                ? __('You: :preview', ['preview' => __('Encrypted message')])
                : __(':preview', ['preview' => __('New encrypted message')]);
        }

        $trimmed = trim($body);
        if (str_starts_with($trimmed, '{"v":1')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded) && ($decoded['v'] ?? null) === 1) {
                $kind = $decoded['k'] ?? '';
                if ($kind === 'img') {
                    $label = __('Image');
                    if (! empty($decoded['c'])) {
                        $label .= ': '.\Illuminate\Support\Str::limit(strip_tags((string) $decoded['c']), 40);
                    }

                    return $fromMe ? __('You: :preview', ['preview' => $label]) : $label;
                }
                if ($kind === 'text') {
                    $text = \Illuminate\Support\Str::limit(strip_tags((string) ($decoded['t'] ?? '')), 80);

                    return $fromMe ? __('You: :preview', ['preview' => $text]) : $text;
                }
            }
        }

        $text = \Illuminate\Support\Str::limit(strip_tags($trimmed), 80);

        return $fromMe ? __('You: :preview', ['preview' => $text]) : $text;
    }
}
