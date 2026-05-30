<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'is_published',
        'show_hero',
        'hero_badge',
        'hero_heading',
        'hero_subheading',
        'hero_theme',
        'hero_background_image',
        'hero_overlay_strength',
        'hero_height',
        'hero_primary_button_text',
        'hero_primary_button_url',
        'hero_secondary_button_text',
        'hero_secondary_button_url',
        'show_in_navigation',
        'navigation_label',
        'navigation_order',
        'navigation_icon',
        'blocks',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_hero' => 'boolean',
        'show_in_navigation' => 'boolean',
        'navigation_order' => 'integer',
        'blocks' => 'array',
    ];

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->orderByDesc('id');
    }
}

