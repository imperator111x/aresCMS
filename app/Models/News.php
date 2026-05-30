<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class News extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'category',
        'content',
        'image',
        'published',
        'comments_enabled',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'comments_enabled' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function commentsEnabled(): bool
    {
        if ($this->comments_enabled === null) {
            return true;
        }

        return (bool) $this->comments_enabled;
    }

    /**
     * Für die öffentliche Liste / Startseite: veröffentlicht und (kein Termin oder Termin erreicht).
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        $query->where('published', true);

        $table = $query->getModel()->getTable();
        if (Schema::hasColumn($table, 'published_at')) {
            $query->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', Carbon::now());
            });
        }

        return $query;
    }

    public function isPubliclyVisible(): bool
    {
        if (! $this->published) {
            return false;
        }

        $publishedAt = $this->parseDate($this->published_at);
        if ($publishedAt === null) {
            return true;
        }

        return $publishedAt->lte(Carbon::now());
    }

    /**
     * Geplant: veröffentlicht, aber published_at liegt in der Zukunft.
     */
    public function isScheduled(): bool
    {
        $publishedAt = $this->parseDate($this->published_at);
        if (! $this->published || $publishedAt === null) {
            return false;
        }

        return $publishedAt->isFuture();
    }

    /**
     * Get the user that owns the news article.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments for the news article.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Top-level comments (no parent), ordered for display.
     */
    public function rootComments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->orderBy('created_at');
    }

    /**
     * Get the excerpt of the content.
     */
    public function getExcerptAttribute(): string
    {
        $content = (string) ($this->content ?? '');
        $viewerName = auth()->check() ? (string) (auth()->user()->name ?? '') : '';
        $viewerName = trim($viewerName) !== '' ? $viewerName : (string) __('Visitor');

        $content = str_replace('{{current_user_name}}', $viewerName, $content);

        return \Str::limit(strip_tags($content), 200);
    }

    /**
     * Get the formatted date.
     */
    public function getFormattedDateAttribute(): string
    {
        $d = $this->parseDate($this->published_at) ?? $this->parseDate($this->created_at);

        return $d ? $d->format('d.m.Y H:i') : '—';
    }

    /**
     * Wert für HTML datetime-local (Y-m-d\TH:i), unabhängig davon ob published_at als Carbon oder String vorliegt.
     */
    public function publishedAtForDatetimeLocal(): string
    {
        $d = $this->parseDate($this->published_at);

        return $d ? $d->format('Y-m-d\TH:i') : '';
    }

    /**
     * published_at / created_at können je nach DB-Treiber oder Rohdaten als String anliegen.
     */
    protected function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        if ($value instanceof Carbon) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }
        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
