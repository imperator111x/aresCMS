<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function uploadInlineImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
        ]);

        $path = $request->file('image')->store('page-builder', 'public');

        return response()->json([
            'url' => asset('storage/'.$path),
        ]);
    }

    public function index(): View
    {
        $pages = Page::query()->orderByDesc('updated_at')->paginate(15);

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        $page = new Page([
            'is_published' => false,
            'show_hero' => true,
            'hero_theme' => 'blue',
            'hero_overlay_strength' => 'medium',
            'hero_height' => 'md',
            'show_in_navigation' => false,
            'navigation_icon' => 'fas fa-file-alt',
            'navigation_order' => 0,
            'blocks' => [],
        ]);
        $forms = Form::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']);
        $pageTemplates = Page::query()
            ->orderBy('title')
            ->get([
                'id',
                'title',
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
                'blocks',
            ]);

        return view('admin.pages.create', compact('page', 'forms', 'pageTemplates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['blocks'] = $this->sanitizeBlocks($request->input('blocks'), $request->file('block_images', []));
        $page = Page::query()->create($data);
        $this->createRevision($page, auth()->id());
        $this->clearLayoutCaches();

        return redirect()->route('admin.pages.index')
            ->with('success', __('Page created successfully.'));
    }

    public function edit(Page $page): View
    {
        $revisions = $page->revisions()->limit(20)->get();
        $forms = Form::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']);
        $pageTemplates = Page::query()
            ->where('id', '!=', $page->id)
            ->orderBy('title')
            ->get([
                'id',
                'title',
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
                'blocks',
            ]);

        return view('admin.pages.edit', compact('page', 'revisions', 'forms', 'pageTemplates'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validatedData($request, $page);
        $data['blocks'] = $this->sanitizeBlocks($request->input('blocks'), $request->file('block_images', []));
        $page->update($data);
        $this->createRevision($page, auth()->id());
        $this->clearLayoutCaches();

        return redirect()->route('admin.pages.index')
            ->with('success', __('Page updated successfully.'));
    }

    public function restoreRevision(Page $page, PageRevision $revision): RedirectResponse
    {
        if ($revision->page_id !== $page->id) {
            abort(404);
        }

        $payload = is_array($revision->payload) ? $revision->payload : [];
        $page->update([
            'title' => (string) ($payload['title'] ?? $page->title),
            'slug' => (string) ($payload['slug'] ?? $page->slug),
            'is_published' => (bool) ($payload['is_published'] ?? false),
            'show_hero' => (bool) ($payload['show_hero'] ?? false),
            'hero_badge' => (string) ($payload['hero_badge'] ?? ''),
            'hero_heading' => (string) ($payload['hero_heading'] ?? ''),
            'hero_subheading' => (string) ($payload['hero_subheading'] ?? ''),
            'hero_theme' => (string) ($payload['hero_theme'] ?? 'blue'),
            'hero_background_image' => (string) ($payload['hero_background_image'] ?? ''),
            'hero_overlay_strength' => (string) ($payload['hero_overlay_strength'] ?? 'medium'),
            'hero_height' => (string) ($payload['hero_height'] ?? 'md'),
            'hero_primary_button_text' => (string) ($payload['hero_primary_button_text'] ?? ''),
            'hero_primary_button_url' => (string) ($payload['hero_primary_button_url'] ?? ''),
            'hero_secondary_button_text' => (string) ($payload['hero_secondary_button_text'] ?? ''),
            'hero_secondary_button_url' => (string) ($payload['hero_secondary_button_url'] ?? ''),
            'show_in_navigation' => (bool) ($payload['show_in_navigation'] ?? false),
            'navigation_label' => (string) ($payload['navigation_label'] ?? ''),
            'navigation_icon' => (string) ($payload['navigation_icon'] ?? ''),
            'navigation_order' => (int) ($payload['navigation_order'] ?? 0),
            'blocks' => is_array($payload['blocks'] ?? null) ? $payload['blocks'] : [],
        ]);
        $this->createRevision($page, auth()->id());
        $this->clearLayoutCaches();

        return redirect()->route('admin.pages.edit', $page)
            ->with('success', __('Revision restored successfully.'));
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();
        $this->clearLayoutCaches();

        return redirect()->route('admin.pages.index')
            ->with('success', __('Page deleted successfully.'));
    }

    private function validatedData(Request $request, ?Page $page = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => [
                'required',
                'string',
                'max:190',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages', 'slug')->ignore($page?->id),
            ],
            'is_published' => ['nullable', 'boolean'],
            'show_hero' => ['nullable', 'boolean'],
            'hero_badge' => ['nullable', 'string', 'max:120'],
            'hero_heading' => ['nullable', 'string', 'max:255'],
            'hero_subheading' => ['nullable', 'string', 'max:500'],
            'hero_theme' => ['nullable', 'string', Rule::in(['blue', 'green', 'purple', 'orange'])],
            'hero_background_image' => ['nullable', 'string', 'max:2048'],
            'hero_overlay_strength' => ['nullable', 'string', Rule::in(['light', 'medium', 'strong'])],
            'hero_height' => ['nullable', 'string', Rule::in(['sm', 'md', 'lg', 'full'])],
            'hero_primary_button_text' => ['nullable', 'string', 'max:120'],
            'hero_primary_button_url' => ['nullable', 'string', 'max:2048'],
            'hero_secondary_button_text' => ['nullable', 'string', 'max:120'],
            'hero_secondary_button_url' => ['nullable', 'string', 'max:2048'],
            'show_in_navigation' => ['nullable', 'boolean'],
            'navigation_label' => ['nullable', 'string', 'max:80'],
            'navigation_icon' => ['nullable', 'string', 'max:80', 'regex:/^fa[srb] fa-[a-z0-9-]+$/i'],
            'navigation_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }

    /**
     * @param mixed $blocks
     * @return array<int, array<string, string>>
     */
    private function sanitizeBlocks(mixed $blocks, array $blockImages = []): array
    {
        if (! is_array($blocks)) {
            return [];
        }

        $allowedTypes = ['text', 'image', 'button', 'form'];
        $allowedLayouts = ['full', 'two_columns'];
        $allowedBackgrounds = ['none', 'gray', 'primary'];
        $allowedPaddings = ['sm', 'md', 'lg'];
        $allowedAlignments = ['left', 'center'];
        $allowedBlockWidths = ['full', 'half'];
        $allowedImageSizes = ['sm', 'md', 'lg', 'full'];
        $allowedButtonSizes = ['sm', 'md', 'lg'];
        $allowedButtonColors = ['primary', 'secondary', 'outline', 'none'];
        $result = [];
        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = isset($block['type']) ? trim((string) $block['type']) : '';
            if (! in_array($type, $allowedTypes, true)) {
                continue;
            }

            $item = ['type' => $type];
            $item['title'] = trim((string) ($block['title'] ?? ''));
            $item['content'] = $this->sanitizeRichText((string) ($block['content'] ?? ''));
            $item['image_url'] = trim((string) ($block['image_url'] ?? ''));
            if ($type === 'image' && array_key_exists((string) $index, $blockImages) && $blockImages[(string) $index] !== null) {
                $imageFile = $blockImages[(string) $index];
                if ($imageFile && $imageFile->isValid()) {
                    $item['image_url'] = asset('storage/'.$imageFile->store('page-builder', 'public'));
                }
            }
            $item['button_text'] = trim((string) ($block['button_text'] ?? ''));
            $item['button_url'] = trim((string) ($block['button_url'] ?? ''));
            $buttonSize = trim((string) ($block['button_size'] ?? 'md'));
            $item['button_size'] = in_array($buttonSize, $allowedButtonSizes, true) ? $buttonSize : 'md';
            $buttonColor = trim((string) ($block['button_color'] ?? 'primary'));
            $item['button_color'] = in_array($buttonColor, $allowedButtonColors, true) ? $buttonColor : 'primary';
            $layout = trim((string) ($block['layout'] ?? 'full'));
            $item['layout'] = in_array($layout, $allowedLayouts, true) ? $layout : 'full';
            $background = trim((string) ($block['background'] ?? 'none'));
            $item['background'] = in_array($background, $allowedBackgrounds, true) ? $background : 'none';
            $padding = trim((string) ($block['padding'] ?? 'md'));
            $item['padding'] = in_array($padding, $allowedPaddings, true) ? $padding : 'md';
            $alignment = trim((string) ($block['alignment'] ?? 'left'));
            $item['alignment'] = in_array($alignment, $allowedAlignments, true) ? $alignment : 'left';
            $blockWidth = trim((string) ($block['block_width'] ?? 'full'));
            $item['block_width'] = in_array($blockWidth, $allowedBlockWidths, true) ? $blockWidth : 'full';
            $imageSize = trim((string) ($block['image_size'] ?? 'full'));
            $item['image_size'] = in_array($imageSize, $allowedImageSizes, true) ? $imageSize : 'full';
            $item['content_left'] = $this->sanitizeRichText((string) ($block['content_left'] ?? ''));
            $item['content_right'] = $this->sanitizeRichText((string) ($block['content_right'] ?? ''));
            $item['form_id'] = (int) ($block['form_id'] ?? 0);
            $item['form_submit_label'] = trim((string) ($block['form_submit_label'] ?? ''));

            $result[] = $item;
        }

        return $result;
    }

    private function sanitizeRichText(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('#\s+on\w+\s*=\s*("|\')[^"\']*\1#i', '', $html) ?? '';
        $html = preg_replace('#\s+on\w+\s*=\s*[^\s>]+#i', '', $html) ?? '';
        $html = preg_replace('#href\s*=\s*("|\')\s*javascript:[^"\']*\1#i', 'href="#"', $html) ?? '';

        $allowed = '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><ul><ol><li><blockquote><a>';

        return trim(strip_tags($html, $allowed));
    }

    private function createRevision(Page $page, ?int $userId): void
    {
        $payload = [
            'title' => $page->title,
            'slug' => $page->slug,
            'is_published' => (bool) $page->is_published,
            'show_hero' => (bool) $page->show_hero,
            'hero_badge' => (string) ($page->hero_badge ?? ''),
            'hero_heading' => (string) ($page->hero_heading ?? ''),
            'hero_subheading' => (string) ($page->hero_subheading ?? ''),
            'hero_theme' => (string) ($page->hero_theme ?? 'blue'),
            'hero_background_image' => (string) ($page->hero_background_image ?? ''),
            'hero_overlay_strength' => (string) ($page->hero_overlay_strength ?? 'medium'),
            'hero_height' => (string) ($page->hero_height ?? 'md'),
            'hero_primary_button_text' => (string) ($page->hero_primary_button_text ?? ''),
            'hero_primary_button_url' => (string) ($page->hero_primary_button_url ?? ''),
            'hero_secondary_button_text' => (string) ($page->hero_secondary_button_text ?? ''),
            'hero_secondary_button_url' => (string) ($page->hero_secondary_button_url ?? ''),
            'show_in_navigation' => (bool) $page->show_in_navigation,
            'navigation_label' => (string) ($page->navigation_label ?? ''),
            'navigation_icon' => (string) ($page->navigation_icon ?? ''),
            'navigation_order' => (int) ($page->navigation_order ?? 0),
            'blocks' => is_array($page->blocks) ? $page->blocks : [],
        ];

        $page->revisions()->create([
            'created_by' => $userId,
            'payload' => $payload,
            'created_at' => now(),
        ]);

        // Keep only the last 3 revisions per page.
        $keepIds = $page->revisions()
            ->orderByDesc('id')
            ->limit(3)
            ->pluck('id')
            ->all();
        if ($keepIds !== []) {
            $page->revisions()->whereNotIn('id', $keepIds)->delete();
        } else {
            $page->revisions()->delete();
        }
    }

    private function clearLayoutCaches(): void
    {
        Cache::forget('layout.app.navigation_pages');
    }
}

