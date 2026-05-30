<?php

namespace Plugins\BreakingNewsMode\Services;

use App\Models\Setting;

class BreakingNewsService
{
    /**
     * @return array{enabled:bool,badge:string,title:string,text:string,url:string,theme:string,display_mode:string}
     */
    public function config(): array
    {
        $defaults = [
            'enabled' => false,
            'badge' => 'Hinweis',
            'title' => 'Wichtige Information',
            'text' => '',
            'url' => '',
            'theme' => 'red',
            'display_mode' => 'banner',
        ];

        $raw = Setting::getValue('breaking_news_mode_config');
        if (! is_string($raw) || trim($raw) === '') {
            return $defaults;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $defaults;
        }

        $theme = (string) ($decoded['theme'] ?? $defaults['theme']);
        if (! in_array($theme, ['red', 'amber', 'orange', 'blue'], true)) {
            $theme = 'red';
        }
        $displayMode = (string) ($decoded['display_mode'] ?? $defaults['display_mode']);
        if (! in_array($displayMode, ['banner', 'popup'], true)) {
            $displayMode = 'banner';
        }

        return [
            'enabled' => (bool) ($decoded['enabled'] ?? false),
            'badge' => trim((string) ($decoded['badge'] ?? $defaults['badge'])),
            'title' => trim((string) ($decoded['title'] ?? $defaults['title'])),
            'text' => trim((string) ($decoded['text'] ?? '')),
            'url' => trim((string) ($decoded['url'] ?? '')),
            'theme' => $theme,
            'display_mode' => $displayMode,
        ];
    }

    public function renderBanner(): string
    {
        $config = $this->config();
        if (! $config['enabled'] || $config['title'] === '') {
            return '';
        }

        $themes = [
            'red' => 'linear-gradient(90deg, #dc2626 0%, #e11d48 100%)',
            'amber' => 'linear-gradient(90deg, #f59e0b 0%, #f97316 100%)',
            'orange' => 'linear-gradient(90deg, #ea580c 0%, #f97316 100%)',
            'blue' => 'linear-gradient(90deg, #2563eb 0%, #4f46e5 100%)',
        ];
        $themeStyle = $themes[$config['theme']] ?? $themes['red'];

        $badge = e($config['badge'] !== '' ? $config['badge'] : 'Info');
        $title = e($config['title']);
        $text = trim((string) ($config['text'] ?? ''));
        $textHtml = $text !== '' ? nl2br(e($text), false) : '';
        $url = $config['url'];
        $barInner = '<div style="background: '.$themeStyle.';" class="text-white"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center gap-3"><span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-white/20">'.$badge.'</span><span class="text-sm font-medium">'.$title.'</span></div></div>';

        if ($config['display_mode'] === 'popup') {
            $dismissKey = 'site_banner_popup_dismissed_'.sha1($config['badge'].'|'.$config['title'].'|'.$text.'|'.$config['url'].'|'.$config['theme']);
            $safeDismissKey = e($dismissKey);
            $popupContent = '<div style="position:fixed;inset:0;z-index:2147483000;background:rgba(0,0,0,0.4);" data-banner-popup-overlay></div>'
                .'<div style="position:fixed;inset:0;z-index:2147483001;display:flex;align-items:center;justify-content:center;padding:1rem;" role="dialog" aria-modal="true" aria-label="'.$badge.'">'
                .'<div style="width:min(92vw,640px);max-width:640px;display:block;" class="rounded-xl overflow-hidden shadow-2xl border border-white/20 bg-white dark:bg-dark-800">'
                .'<div style="background: '.$themeStyle.';" class="text-white px-4 py-3 flex items-start gap-3">'
                .'<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-white/20 mt-0.5">'.$badge.'</span>'
                .'<div class="min-w-0 flex-1">';

            if ($url !== '') {
                $popupContent .= '<a href="'.e($url).'" class="text-sm font-semibold underline underline-offset-2 hover:opacity-90">'.$title.'</a>';
            } else {
                $popupContent .= '<p class="text-sm font-semibold">'.$title.'</p>';
            }

            $popupContent .= '</div>'
                .'<button type="button" class="ml-2 text-white/90 hover:text-white text-lg leading-none" aria-label="Schließen" data-banner-popup-close>&times;</button>'
                .'</div>'
                .($textHtml !== '' ? '<div class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200 leading-relaxed">'.$textHtml.'</div>' : '')
                .'</div>'
                .'</div>';

            $script = '<script>(function(){var key="'.$safeDismissKey.'";try{if(localStorage.getItem(key)==="1"){return;}}catch(e){}'
                .'var host=document.currentScript&&document.currentScript.previousElementSibling;if(!host){return;}'
                .'var close=function(){try{localStorage.setItem(key,"1");}catch(e){}host.remove();};'
                .'var btn=host.querySelector("[data-banner-popup-close]");if(btn){btn.addEventListener("click",close);}'
                .'var overlay=host.querySelector("[data-banner-popup-overlay]");if(overlay){overlay.addEventListener("click",close);}'
                .'document.addEventListener("keydown",function(ev){if(ev.key==="Escape"){close();}},{once:true});'
                .'})();</script>';

            return '<div data-banner-popup-root>'.$popupContent.'</div>'.$script;
        }

        if ($url !== '') {
            $safeUrl = e($url);
            return '<a href="'.$safeUrl.'" class="block hover:opacity-95 transition-opacity">'.$barInner.'</a>';
        }

        return $barInner;
    }
}
