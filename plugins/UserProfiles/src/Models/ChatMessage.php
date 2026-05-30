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
}
