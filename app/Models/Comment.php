<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'news_id',
        'parent_id',
        'content',
        'moderation_status',
        'moderation_score',
        'moderation_flags',
        'moderated_at',
        'moderated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'moderation_score' => 'integer',
            'moderation_flags' => 'array',
            'moderated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the news article that the comment belongs to.
     */
    public function news()
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Parent comment (null = top-level).
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Direct replies (one level under a top-level comment).
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by_user_id');
    }

    /**
     * Get the formatted date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d.m.Y H:i');
    }

    public static function supportsModeration(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasColumn('comments', 'moderation_status');
    }

    public function isApproved(): bool
    {
        if (! self::supportsModeration()) {
            return true;
        }

        return (string) $this->moderation_status === 'approved';
    }
}
