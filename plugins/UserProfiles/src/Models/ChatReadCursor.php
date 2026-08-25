<?php

namespace Plugins\UserProfiles\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class ChatReadCursor extends Model
{
    protected $table = 'profile_chat_read_cursors';

    protected $fillable = [
        'user_id',
        'friendship_id',
        'last_read_message_id',
    ];

    protected function casts(): array
    {
        return [
            'last_read_message_id' => 'integer',
        ];
    }

    public static function tableExists(): bool
    {
        return Schema::hasTable('profile_chat_read_cursors');
    }

    /**
     * @return BelongsTo<User, ChatReadCursor>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Friendship, ChatReadCursor>
     */
    public function friendship(): BelongsTo
    {
        return $this->belongsTo(Friendship::class, 'friendship_id');
    }
}
