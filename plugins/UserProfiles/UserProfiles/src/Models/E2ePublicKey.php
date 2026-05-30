<?php

namespace Plugins\UserProfiles\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class E2ePublicKey extends Model
{
    protected $table = 'profile_e2e_public_keys';

    public $timestamps = false;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'public_key_jwk',
    ];

    /**
     * @return BelongsTo<User, E2ePublicKey>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
