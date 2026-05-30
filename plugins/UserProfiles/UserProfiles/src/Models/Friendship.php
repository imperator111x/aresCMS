<?php

namespace Plugins\UserProfiles\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Friendship extends Model
{
    protected $table = 'profile_friendships';

    protected $fillable = [
        'requester_id',
        'addressee_id',
        'status',
    ];

    /**
     * @return BelongsTo<User, Friendship>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * @return BelongsTo<User, Friendship>
     */
    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    /**
     * @return HasMany<ChatMessage, Friendship>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'friendship_id');
    }

    public function involvesUser(User $user): bool
    {
        return (int) $user->id === (int) $this->requester_id
            || (int) $user->id === (int) $this->addressee_id;
    }

    public function peerUser(User $viewer): ?User
    {
        if ((int) $viewer->id === (int) $this->requester_id) {
            return $this->addressee;
        }
        if ((int) $viewer->id === (int) $this->addressee_id) {
            return $this->requester;
        }

        return null;
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}
