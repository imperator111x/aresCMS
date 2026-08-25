<?php

namespace App\Models;

use App\Support\ReactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class NewsReaction extends Model
{
    protected $fillable = [
        'news_id',
        'user_id',
        'type',
    ];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function tableExists(): bool
    {
        return Schema::hasTable('news_reactions');
    }

    /**
     * @return array<string, int>
     */
    public static function countsForNews(int $newsId): array
    {
        if (! self::tableExists()) {
            return [];
        }

        return static::query()
            ->where('news_id', $newsId)
            ->selectRaw('type, COUNT(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type')
            ->map(static fn ($count) => (int) $count)
            ->all();
    }

    public static function userReactionType(int $newsId, int $userId): ?string
    {
        if (! self::tableExists()) {
            return null;
        }

        $type = static::query()
            ->where('news_id', $newsId)
            ->where('user_id', $userId)
            ->value('type');

        return is_string($type) && ReactionType::isValid($type) ? $type : null;
    }
}
