<?php

namespace Plugins\SponsorAdSlots\Services;

use Illuminate\Support\Facades\Schema;
use Plugins\SponsorAdSlots\Models\AdSlot;

class AdSlotService
{
    /**
     * @return array<string,string>
     */
    public function availableSlots(): array
    {
        return [
            'home_top' => 'Homepage oberhalb der News-Liste',
            'home_middle' => 'Homepage zwischen News-Grid und Button',
            'home_bottom' => 'Homepage unterhalb des News-Bereichs',
            'news_index_top' => 'News-Archiv oberhalb der Filter',
            'news_index_bottom' => 'News-Archiv unterhalb der Liste',
            'news_show_top' => 'Artikelansicht oberhalb des Inhalts',
            'news_show_bottom' => 'Artikelansicht unterhalb von Inhalt/Kommentaren',
        ];
    }

    public function supportsTable(): bool
    {
        return Schema::hasTable('sponsor_ad_slots');
    }

    public function pickForSlot(string $slotKey): ?AdSlot
    {
        if (! $this->supportsTable()) {
            return null;
        }

        return AdSlot::query()
            ->where('slot_key', trim($slotKey))
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('priority')
            ->orderByDesc('updated_at')
            ->first();
    }

    public function render(string $slotKey): string
    {
        $adSlot = $this->pickForSlot($slotKey);
        if (! $adSlot) {
            return '';
        }

        $adSlot->increment('impressions');

        $targetUrl = route('ads.click', $adSlot);
        $name = e($adSlot->name);

        if (is_string($adSlot->image_path) && trim($adSlot->image_path) !== '') {
            $img = '<img src="'.e(asset('storage/'.$adSlot->image_path)).'" alt="'.$name.'" loading="lazy" decoding="async" class="w-full h-auto rounded-xl border border-gray-200 dark:border-dark-700">';
        } elseif (is_string($adSlot->image_url) && trim($adSlot->image_url) !== '') {
            $img = '<img src="'.e($adSlot->image_url).'" alt="'.$name.'" loading="lazy" decoding="async" class="w-full h-auto rounded-xl border border-gray-200 dark:border-dark-700">';
        } elseif (is_string($adSlot->html_code) && trim($adSlot->html_code) !== '') {
            $img = (string) $adSlot->html_code;
        } else {
            $img = '<div class="rounded-xl border border-gray-200 dark:border-dark-700 bg-gray-100 dark:bg-dark-800 px-4 py-8 text-center text-sm text-gray-600 dark:text-gray-300">'.$name.'</div>';
        }

        return '<div class="my-8"><a href="'.e($targetUrl).'" rel="nofollow sponsored" class="block">'.$img.'</a></div>';
    }
}
