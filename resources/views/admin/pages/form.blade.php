@php
    $initialBlocks = old('blocks', $page->blocks ?? []);
    $heroTheme = old('hero_theme', $page->hero_theme ?? 'blue');
    $heroOverlayStrength = old('hero_overlay_strength', $page->hero_overlay_strength ?? 'medium');
    $heroHeight = old('hero_height', $page->hero_height ?? 'md');
    $availablePageTemplates = ($pageTemplates ?? collect())->map(static function ($template): array {
        $templateBlocks = is_array($template->blocks ?? null) ? $template->blocks : [];
        return [
            'id' => (int) $template->id,
            'title' => (string) $template->title,
            'show_hero' => (bool) ($template->show_hero ?? false),
            'hero_badge' => (string) ($template->hero_badge ?? ''),
            'hero_heading' => (string) ($template->hero_heading ?? ''),
            'hero_subheading' => (string) ($template->hero_subheading ?? ''),
            'hero_theme' => (string) ($template->hero_theme ?? 'blue'),
            'hero_background_image' => (string) ($template->hero_background_image ?? ''),
            'hero_overlay_strength' => (string) ($template->hero_overlay_strength ?? 'medium'),
            'hero_height' => (string) ($template->hero_height ?? 'md'),
            'hero_primary_button_text' => (string) ($template->hero_primary_button_text ?? ''),
            'hero_primary_button_url' => (string) ($template->hero_primary_button_url ?? ''),
            'hero_secondary_button_text' => (string) ($template->hero_secondary_button_text ?? ''),
            'hero_secondary_button_url' => (string) ($template->hero_secondary_button_url ?? ''),
            'blocks' => $templateBlocks,
        ];
    })->values();
    $availableForms = ($forms ?? collect())->map(static fn ($form) => [
        'id' => (int) $form->id,
        'name' => (string) $form->name,
        'slug' => (string) $form->slug,
    ])->values();
    $pageBuilderI18n = [
        'block' => __('Block'),
        'dragToReorder' => __('Drag to reorder'),
        'remove' => __('Remove'),
        'title' => __('Title'),
        'layout' => __('Layout'),
        'singleColumn' => __('Single column'),
        'twoColumns' => __('Two columns'),
        'textAlignment' => __('Text alignment'),
        'left' => __('Left'),
        'center' => __('Center'),
        'background' => __('Background'),
        'none' => __('None'),
        'gray' => __('Gray'),
        'primary' => __('Primary'),
        'padding' => __('Padding'),
        'small' => __('Small'),
        'medium' => __('Medium'),
        'large' => __('Large'),
        'content' => __('Content'),
        'leftColumnContent' => __('Left column content'),
        'rightColumnContent' => __('Right column content'),
        'imageUrl' => __('Image URL'),
        'buttonText' => __('Button text'),
        'buttonUrl' => __('Button URL'),
        'imageUpload' => __('Upload image'),
        'blockWidth' => __('Block width'),
        'fullWidth' => __('Full width'),
        'halfWidth' => __('Half width (2 per row)'),
        'imageSize' => __('Image size'),
        'textHintButtons' => __('Button in text: [Label](https://example.com)'),
        'insertButton' => __('Insert button'),
        'insertImage' => __('Insert image'),
        'insertToken' => __('Insert token'),
        'selectToken' => __('Select token'),
        'tokenTeamList' => __('Team list'),
        'tokenNewsBoxes3' => __('News boxes (3)'),
        'tokenNewsBoxes6' => __('News boxes (6)'),
        'tokenCurrentUserName' => __('Current user name (or visitor)'),
        'buttonSize' => __('Button size'),
        'textHintButtonsWithSize' => __('Button size in text: [Label|sm](https://example.com), [Label|md](...), [Label|lg](...)'),
        'buttonColor' => __('Button color'),
        'primaryColor' => __('Primary'),
        'secondaryColor' => __('Secondary'),
        'outlineColor' => __('Outline'),
        'noneBg' => __('No background'),
        'textHintButtonsWithColor' => __('Button color in text: [Label|primary](...), [Label|secondary](...), [Label|outline](...)'),
        'promptButtonLabel' => __('Button label'),
        'promptButtonUrl' => __('Button URL'),
        'promptButtonSize' => __('Button size (sm, md, lg)'),
        'promptButtonColor' => __('Button color (primary, secondary, outline, none)'),
        'imageHintSyntax' => __('Image in text: ![img|sm](https://example.com), ![img|md](...), ![img|lg](...)'),
        'imageModalTitle' => __('Insert image'),
        'imageUrl' => __('Image URL'),
        'imageSize' => __('Image size'),
        'imageUploadFile' => __('Upload image file'),
        'uploading' => __('Uploading...'),
        'form' => __('Form'),
        'selectForm' => __('Select form'),
        'submitButtonLabel' => __('Submit button label'),
        'defaultSubmitLabel' => __('Send message'),
        'applyTemplate' => __('Apply template'),
        'selectPageTemplate' => __('Select page template'),
        'templateWillReplace' => __('Template will replace hero settings and all content blocks.'),
        'templateApplied' => __('Template applied.'),
        'templatePreview' => __('Template preview'),
        'noTemplateSelected' => __('No template selected yet.'),
        'hero' => __('Hero'),
        'blocksCount' => __('Blocks: :count'),
        'previewBlocksLabel' => __('Preview blocks'),
        'previewNotAvailable' => __('No preview available.'),
        'templateTokenValues' => __('Template placeholder values'),
        'templateTokenHelp' => __('Fill values for placeholders from the selected template.'),
        'noTemplateTokens' => __('No placeholders in this template.'),
        'dynamicTemplateTokens' => __('Dynamic tokens for text blocks:'),
    ];
@endphp

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <style>
        .page-builder-quill {
            display: flex;
            flex-direction: column;
            min-height: 260px;
            height: 260px;
        }
        .page-builder-quill .ql-toolbar {
            border-color: rgb(209 213 219) !important;
            border-radius: 0.5rem 0.5rem 0 0;
            background: rgb(249 250 251);
            flex: 0 0 auto;
        }
        .dark .page-builder-quill .ql-toolbar {
            border-color: rgb(71 85 105) !important;
            background: rgb(30 41 59);
        }
        .dark .page-builder-quill .ql-toolbar .ql-stroke { stroke: rgb(203 213 225); }
        .dark .page-builder-quill .ql-toolbar .ql-fill { fill: rgb(203 213 225); }
        .dark .page-builder-quill .ql-toolbar .ql-picker-label { color: rgb(203 213 225); }
        .page-builder-quill .ql-container {
            border-color: rgb(209 213 219) !important;
            border-radius: 0 0 0.5rem 0.5rem;
            flex: 1 1 auto;
            min-height: 0;
            height: auto;
            background: #fff;
        }
        .page-builder-quill .ql-editor {
            min-height: 100%;
            max-height: 100%;
            overflow-y: auto;
        }
        .dark .page-builder-quill .ql-container {
            border-color: rgb(71 85 105) !important;
            background: rgb(15 23 42);
            color: rgb(241 245 249);
        }
    </style>
@endpush

<div class="space-y-6">
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5 space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Slug') }}</label>
                <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="my-landing-page" required class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">{{ __('Will be available under /page/:slug') }}</p>
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-[1fr_auto] items-end">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Select page template') }}</label>
                <select id="pageTemplateSelect" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">
                    <option value="">{{ __('Select page template') }}</option>
                    @foreach($availablePageTemplates as $template)
                        <option value="{{ $template['id'] }}">{{ $template['title'] }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">{{ __('Template will replace hero settings and all content blocks.') }}</p>
            </div>
            <button type="button" id="applyPageTemplateButton" class="px-4 py-2 rounded-lg border border-primary-300 text-primary-600 dark:text-primary-400 text-sm">
                {{ __('Apply template') }}
            </button>
        </div>
        <div id="pageTemplatePreview" class="hidden rounded-lg border border-gray-200 dark:border-dark-700 bg-gray-50 dark:bg-dark-900/40 p-4 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Template preview') }}</h4>
                <span id="templatePreviewBlocksCount" class="text-xs text-gray-500"></span>
            </div>
            <div id="templatePreviewHero" class="rounded-lg border border-gray-200 dark:border-dark-700 p-3 text-xs"></div>
            <div>
                <div class="text-xs text-gray-500 mb-1">{{ __('Preview blocks') }}</div>
                <div id="templatePreviewBlocks" class="flex flex-wrap gap-2"></div>
            </div>
        </div>
        <p id="pageTemplatePreviewEmpty" class="text-xs text-gray-500">{{ __('No template selected yet.') }}</p>
        <div id="pageTemplateTokenPanel" class="hidden rounded-lg border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-900/30 p-4 space-y-3">
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Template placeholder values') }}</h4>
                <p class="text-xs text-gray-500 mt-1">{{ __('Fill values for placeholders from the selected template.') }}</p>
            </div>
            <div id="pageTemplateTokenInputs" class="grid gap-3 md:grid-cols-3"></div>
            <p class="text-xs text-gray-500">
                {{ __('Dynamic tokens for text blocks:') }}
                <code>@{{team_list}}</code>,
                <code>@{{news_boxes_3}}</code>,
                <code>@{{news_boxes_6}}</code>
            </p>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))>
                <span>{{ __('Published') }}</span>
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="show_hero" value="1" @checked(old('show_hero', $page->show_hero ?? false))>
                <span>{{ __('Show hero header') }}</span>
            </label>
        </div>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero badge') }}</label>
                <input type="text" name="hero_badge" value="{{ old('hero_badge', $page->hero_badge ?? '') }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2" placeholder="{{ __('Our Team') }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero heading') }}</label>
                <input type="text" name="hero_heading" value="{{ old('hero_heading', $page->hero_heading ?? '') }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2" placeholder="{{ __('Meet the amazing people') }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero subheading') }}</label>
                <input type="text" name="hero_subheading" value="{{ old('hero_subheading', $page->hero_subheading ?? '') }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2" placeholder="{{ __('Get to know the talented individuals who make everything possible.') }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero color theme') }}</label>
                <select name="hero_theme" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">
                    <option value="blue" @selected($heroTheme === 'blue')>{{ __('Blue') }}</option>
                    <option value="green" @selected($heroTheme === 'green')>{{ __('Green') }}</option>
                    <option value="purple" @selected($heroTheme === 'purple')>{{ __('Purple') }}</option>
                    <option value="orange" @selected($heroTheme === 'orange')>{{ __('Orange') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero background image URL') }}</label>
                <input type="url" name="hero_background_image" value="{{ old('hero_background_image', $page->hero_background_image ?? '') }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2" placeholder="https://...">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero overlay strength') }}</label>
                <select name="hero_overlay_strength" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">
                    <option value="light" @selected($heroOverlayStrength === 'light')>{{ __('Light') }}</option>
                    <option value="medium" @selected($heroOverlayStrength === 'medium')>{{ __('Medium') }}</option>
                    <option value="strong" @selected($heroOverlayStrength === 'strong')>{{ __('Strong') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero height') }}</label>
                <select name="hero_height" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">
                    <option value="sm" @selected($heroHeight === 'sm')>{{ __('Small') }}</option>
                    <option value="md" @selected($heroHeight === 'md')>{{ __('Medium') }}</option>
                    <option value="lg" @selected($heroHeight === 'lg')>{{ __('Large') }}</option>
                    <option value="full" @selected($heroHeight === 'full')>{{ __('Full screen') }}</option>
                </select>
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero primary button text') }}</label>
                <input type="text" name="hero_primary_button_text" value="{{ old('hero_primary_button_text', $page->hero_primary_button_text ?? '') }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2" placeholder="{{ __('Learn more') }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero primary button URL') }}</label>
                <input type="url" name="hero_primary_button_url" value="{{ old('hero_primary_button_url', $page->hero_primary_button_url ?? '') }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2" placeholder="https://...">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero secondary button text') }}</label>
                <input type="text" name="hero_secondary_button_text" value="{{ old('hero_secondary_button_text', $page->hero_secondary_button_text ?? '') }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2" placeholder="{{ __('Contact') }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Hero secondary button URL') }}</label>
                <input type="url" name="hero_secondary_button_url" value="{{ old('hero_secondary_button_url', $page->hero_secondary_button_url ?? '') }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2" placeholder="https://...">
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="show_in_navigation" value="1" @checked(old('show_in_navigation', $page->show_in_navigation ?? false))>
                <span>{{ __('Show in navigation') }}</span>
            </label>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Navigation label (optional)') }}</label>
                <input type="text" name="navigation_label" value="{{ old('navigation_label', $page->navigation_label ?? '') }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Navigation icon') }}</label>
                @php
                    $selectedIcon = old('navigation_icon', $page->navigation_icon ?? 'fas fa-file-alt');
                    $iconOptions = [
                        'fas fa-file-alt' => __('Default'),
                        'fas fa-house' => __('Home'),
                        'fas fa-circle-info' => __('Info'),
                        'fas fa-star' => __('Highlight'),
                        'fas fa-address-card' => __('Contact'),
                        'fas fa-newspaper' => __('News'),
                        'fas fa-briefcase' => __('Services'),
                        'fas fa-images' => __('Gallery'),
                        'fas fa-question-circle' => __('FAQ'),
                        'fas fa-envelope' => __('Email'),
                    ];
                @endphp
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs text-gray-500">{{ __('Icon') }}:</span>
                    <span id="navIconPreview" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 dark:border-dark-600">
                        <i class="{{ $selectedIcon }}"></i>
                    </span>
                </div>
                <select id="navigationIconSelect" name="navigation_icon" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">
                    @foreach($iconOptions as $iconClass => $iconLabel)
                        <option value="{{ $iconClass }}" @selected($selectedIcon === $iconClass)>{{ $iconLabel }} ({{ $iconClass }})</option>
                    @endforeach
                </select>
                <div id="navigationIconGrid" class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($iconOptions as $iconClass => $iconLabel)
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 px-2 py-1.5 text-xs rounded-lg border {{ $selectedIcon === $iconClass ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300' }}"
                            data-nav-icon="{{ $iconClass }}"
                            title="{{ $iconLabel }}"
                        >
                            <i class="{{ $iconClass }}"></i>
                            <span class="truncate">{{ $iconLabel }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Navigation order') }}</label>
                <input type="number" min="0" max="9999" name="navigation_order" value="{{ old('navigation_order', $page->navigation_order ?? 0) }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2">
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5">
        <div class="flex flex-wrap gap-2 mb-4">
            <button type="button" data-add-block="text" class="px-3 py-2 rounded-lg bg-primary-500 hover:bg-primary-600 text-white text-sm">{{ __('Add Text Block') }}</button>
            <button type="button" data-add-block="image" class="px-3 py-2 rounded-lg bg-primary-500 hover:bg-primary-600 text-white text-sm">{{ __('Add Image Block') }}</button>
            <button type="button" data-add-block="button" class="px-3 py-2 rounded-lg bg-primary-500 hover:bg-primary-600 text-white text-sm">{{ __('Add Button Block') }}</button>
            <button type="button" data-add-block="form" class="px-3 py-2 rounded-lg bg-primary-500 hover:bg-primary-600 text-white text-sm">{{ __('Add Form Block') }}</button>
        </div>

        <div id="builderBlocks" class="space-y-3"></div>
        <p id="emptyBuilderText" class="text-sm text-gray-500">{{ __('No blocks yet. Add your first block above.') }}</p>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Live preview') }}</h3>
            <select id="previewMode" class="rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-2 py-1 text-xs">
                <option value="desktop">{{ __('Desktop') }}</option>
                <option value="tablet">{{ __('Tablet') }}</option>
                <option value="mobile">{{ __('Mobile') }}</option>
            </select>
        </div>
        <div id="previewFrame" class="mx-auto border border-gray-200 dark:border-dark-700 rounded-xl p-3 transition-all duration-200">
            <div id="builderPreview" class="flex flex-wrap gap-3 text-sm"></div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white">{{ $submitLabel }}</button>
        <a href="{{ route('admin.pages.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600">{{ __('Cancel') }}</a>
    </div>
</div>

<div id="inlineButtonModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/50" data-modal-close></div>
    <div class="relative z-[101] min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 p-5 shadow-2xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Insert button') }}</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs mb-1">{{ __('Button label') }}</label>
                    <input id="inlineButtonLabel" type="text" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" value="Button">
                </div>
                <div>
                    <label class="block text-xs mb-1">{{ __('Button URL') }}</label>
                    <input id="inlineButtonUrl" type="text" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" value="https://example.com">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs mb-1">{{ __('Button size') }}</label>
                        <select id="inlineButtonSize" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                            <option value="sm">{{ __('Small') }}</option>
                            <option value="md" selected>{{ __('Medium') }}</option>
                            <option value="lg">{{ __('Large') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs mb-1">{{ __('Button color') }}</label>
                        <select id="inlineButtonColor" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                            <option value="primary">{{ __('Primary') }}</option>
                            <option value="secondary">{{ __('Secondary') }}</option>
                            <option value="outline">{{ __('Outline') }}</option>
                            <option value="none">{{ __('No background') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 text-sm" data-modal-close>{{ __('Cancel') }}</button>
                <button type="button" id="inlineButtonInsertConfirm" class="px-3 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm">{{ __('Insert button') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="inlineImageModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/50" data-image-modal-close></div>
    <div class="relative z-[101] min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 p-5 shadow-2xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Insert image') }}</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs mb-1">{{ __('Image URL') }}</label>
                    <input id="inlineImageUrl" type="text" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" value="https://example.com/image.jpg">
                </div>
                <div>
                    <label class="block text-xs mb-1">{{ __('Upload image file') }}</label>
                    <input id="inlineImageFile" type="file" accept="image/*" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs mb-1">{{ __('Image size') }}</label>
                    <select id="inlineImageSize" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                        <option value="sm">{{ __('Small') }}</option>
                        <option value="md" selected>{{ __('Medium') }}</option>
                        <option value="lg">{{ __('Large') }}</option>
                    </select>
                </div>
            </div>
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 text-sm" data-image-modal-close>{{ __('Cancel') }}</button>
                <button type="button" id="inlineImageInsertConfirm" class="px-3 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm">{{ __('Insert image') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="dynamicTokenModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/50" data-token-modal-close></div>
    <div class="relative z-[101] min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 p-5 shadow-2xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Insert token') }}</h3>
            <div>
                <label class="block text-xs mb-1">{{ __('Select token') }}</label>
                <select id="dynamicTokenSelect" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                    <option value="@{{team_list}}">{{ __('Team list') }} (@{{team_list}})</option>
                    <option value="@{{news_boxes_3}}">{{ __('News boxes (3)') }} (@{{news_boxes_3}})</option>
                    <option value="@{{news_boxes_6}}">{{ __('News boxes (6)') }} (@{{news_boxes_6}})</option>
                    <option value="@{{current_user_name}}">{{ __('Current user name (or visitor)') }} (@{{current_user_name}})</option>
                </select>
            </div>
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 text-sm" data-token-modal-close>{{ __('Cancel') }}</button>
                <button type="button" id="dynamicTokenInsertConfirm" class="px-3 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm">{{ __('Insert token') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    (function () {
        const initialBlocks = @json($initialBlocks);
        const availableForms = @json($availableForms);
        const availablePageTemplates = @json($availablePageTemplates);
        const i18n = @json($pageBuilderI18n);
        const blocksRoot = document.getElementById('builderBlocks');
        const emptyText = document.getElementById('emptyBuilderText');
        const previewRoot = document.getElementById('builderPreview');
        const previewFrame = document.getElementById('previewFrame');
        const previewMode = document.getElementById('previewMode');
        const pageTemplateSelect = document.getElementById('pageTemplateSelect');
        const applyPageTemplateButton = document.getElementById('applyPageTemplateButton');
        const pageTemplatePreview = document.getElementById('pageTemplatePreview');
        const pageTemplatePreviewEmpty = document.getElementById('pageTemplatePreviewEmpty');
        const templatePreviewHero = document.getElementById('templatePreviewHero');
        const templatePreviewBlocks = document.getElementById('templatePreviewBlocks');
        const templatePreviewBlocksCount = document.getElementById('templatePreviewBlocksCount');
        const pageTemplateTokenPanel = document.getElementById('pageTemplateTokenPanel');
        const pageTemplateTokenInputs = document.getElementById('pageTemplateTokenInputs');
        const navigationIconSelect = document.getElementById('navigationIconSelect');
        const navigationIconPreview = document.getElementById('navIconPreview');
        const navigationIconGrid = document.getElementById('navigationIconGrid');
        const inlineButtonModal = document.getElementById('inlineButtonModal');
        const inlineButtonLabel = document.getElementById('inlineButtonLabel');
        const inlineButtonUrl = document.getElementById('inlineButtonUrl');
        const inlineButtonSize = document.getElementById('inlineButtonSize');
        const inlineButtonColor = document.getElementById('inlineButtonColor');
        const inlineButtonInsertConfirm = document.getElementById('inlineButtonInsertConfirm');
        const inlineImageModal = document.getElementById('inlineImageModal');
        const inlineImageUrl = document.getElementById('inlineImageUrl');
        const inlineImageFile = document.getElementById('inlineImageFile');
        const inlineImageSize = document.getElementById('inlineImageSize');
        const inlineImageInsertConfirm = document.getElementById('inlineImageInsertConfirm');
        const dynamicTokenModal = document.getElementById('dynamicTokenModal');
        const dynamicTokenSelect = document.getElementById('dynamicTokenSelect');
        const dynamicTokenInsertConfirm = document.getElementById('dynamicTokenInsertConfirm');
        const inlineImageUploadEndpoint = @json(route('admin.pages.inline-image.upload'));
        const quillInstances = new Map();
        let inlineButtonTargetId = null;
        let inlineImageTargetId = null;
        let dynamicTokenTargetId = null;
        let dragSourceIndex = null;
        const dynamicTemplateTokens = new Set(['team_list', 'news_boxes_3', 'news_boxes_6']);
        const templateTokenValues = {};

        function blockCard(index, block) {
            const wrapper = document.createElement('div');
            wrapper.className = 'rounded-lg border border-gray-300 dark:border-dark-600 p-4 bg-gray-50 dark:bg-dark-900/40';
            wrapper.draggable = true;
            wrapper.dataset.index = String(index);
            const type = block.type || 'text';

            wrapper.innerHTML = `
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="text-sm font-semibold">${type.toUpperCase()} ${i18n.block} #${index + 1}</div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">${i18n.dragToReorder}</span>
                        <button type="button" data-remove-index="${index}" class="text-red-600 text-xs">${i18n.remove}</button>
                    </div>
                </div>
                <input type="hidden" name="blocks[${index}][type]" value="${type}">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs mb-1">${i18n.title}</label>
                        <input type="text" name="blocks[${index}][title]" value="${escapeHtml(block.title || '')}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs mb-1">${i18n.blockWidth}</label>
                            <select name="blocks[${index}][block_width]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                                <option value="full" ${(block.block_width || 'full') === 'full' ? 'selected' : ''}>${i18n.fullWidth}</option>
                                <option value="half" ${(block.block_width || '') === 'half' ? 'selected' : ''}>${i18n.halfWidth}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs mb-1">${i18n.layout}</label>
                            <select name="blocks[${index}][layout]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                                <option value="full" ${(block.layout || 'full') === 'full' ? 'selected' : ''}>${i18n.singleColumn}</option>
                                <option value="two_columns" ${(block.layout || '') === 'two_columns' ? 'selected' : ''}>${i18n.twoColumns}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs mb-1">${i18n.textAlignment}</label>
                            <select name="blocks[${index}][alignment]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                                <option value="left" ${(block.alignment || 'left') === 'left' ? 'selected' : ''}>${i18n.left}</option>
                                <option value="center" ${(block.alignment || '') === 'center' ? 'selected' : ''}>${i18n.center}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs mb-1">${i18n.background}</label>
                            <select name="blocks[${index}][background]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                                <option value="none" ${(block.background || 'none') === 'none' ? 'selected' : ''}>${i18n.none}</option>
                                <option value="gray" ${(block.background || '') === 'gray' ? 'selected' : ''}>${i18n.gray}</option>
                                <option value="primary" ${(block.background || '') === 'primary' ? 'selected' : ''}>${i18n.primary}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs mb-1">${i18n.padding}</label>
                            <select name="blocks[${index}][padding]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                                <option value="sm" ${(block.padding || '') === 'sm' ? 'selected' : ''}>${i18n.small}</option>
                                <option value="md" ${(block.padding || 'md') === 'md' ? 'selected' : ''}>${i18n.medium}</option>
                                <option value="lg" ${(block.padding || '') === 'lg' ? 'selected' : ''}>${i18n.large}</option>
                            </select>
                        </div>
                    </div>
                    <div class="${type === 'text' ? '' : 'hidden'}" data-type-field="text">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <label class="block text-xs">${i18n.content}</label>
                            <div class="flex items-center gap-1">
                                <button type="button" class="text-[11px] px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400" data-insert-inline-image="${index}">
                                    ${i18n.insertImage}
                                </button>
                                <button type="button" class="text-[11px] px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400" data-insert-inline-button="${index}">
                                    ${i18n.insertButton}
                                </button>
                                <button type="button" class="text-[11px] px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400" data-insert-dynamic-token="${index}">
                                    ${i18n.insertToken}
                                </button>
                            </div>
                        </div>
                        <textarea id="page-rich-input-${index}" name="blocks[${index}][content]" rows="6" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" data-rich-fallback>${escapeHtml(block.content || '')}</textarea>
                        <div id="page-rich-editor-${index}" class="page-builder-quill rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-900 overflow-hidden" data-rich-text-editor data-rich-target="page-rich-input-${index}"></div>
                        <p class="text-[11px] text-gray-500 mt-1">${i18n.textHintButtons}<br>${i18n.textHintButtonsWithSize}<br>${i18n.textHintButtonsWithColor}<br>${i18n.imageHintSyntax}</p>
                    </div>
                    <div class="${type === 'text' ? '' : 'hidden'} grid gap-3 sm:grid-cols-2" data-type-field="text-columns">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <label class="block text-xs">${i18n.leftColumnContent}</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" class="text-[11px] px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400" data-insert-inline-image-left="${index}">
                                        ${i18n.insertImage}
                                    </button>
                                    <button type="button" class="text-[11px] px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400" data-insert-inline-button-left="${index}">
                                        ${i18n.insertButton}
                                    </button>
                                    <button type="button" class="text-[11px] px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400" data-insert-dynamic-token-left="${index}">
                                        ${i18n.insertToken}
                                    </button>
                                </div>
                            </div>
                            <textarea id="page-rich-input-left-${index}" name="blocks[${index}][content_left]" rows="6" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" data-rich-fallback>${escapeHtml(block.content_left || '')}</textarea>
                            <div id="page-rich-editor-left-${index}" class="page-builder-quill rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-900 overflow-hidden" data-rich-text-editor data-rich-target="page-rich-input-left-${index}"></div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <label class="block text-xs">${i18n.rightColumnContent}</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" class="text-[11px] px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400" data-insert-inline-image-right="${index}">
                                        ${i18n.insertImage}
                                    </button>
                                    <button type="button" class="text-[11px] px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400" data-insert-inline-button-right="${index}">
                                        ${i18n.insertButton}
                                    </button>
                                    <button type="button" class="text-[11px] px-2 py-1 rounded border border-primary-300 text-primary-600 dark:text-primary-400" data-insert-dynamic-token-right="${index}">
                                        ${i18n.insertToken}
                                    </button>
                                </div>
                            </div>
                            <textarea id="page-rich-input-right-${index}" name="blocks[${index}][content_right]" rows="6" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" data-rich-fallback>${escapeHtml(block.content_right || '')}</textarea>
                            <div id="page-rich-editor-right-${index}" class="page-builder-quill rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-900 overflow-hidden" data-rich-text-editor data-rich-target="page-rich-input-right-${index}"></div>
                        </div>
                    </div>
                    <div class="${type === 'image' ? '' : 'hidden'}" data-type-field="image">
                        <label class="block text-xs mb-1">${i18n.imageUrl}</label>
                        <input type="url" name="blocks[${index}][image_url]" value="${escapeHtml(block.image_url || '')}" placeholder="https://..." class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                        <label class="block text-xs mt-2 mb-1">${i18n.imageSize}</label>
                        <select name="blocks[${index}][image_size]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                            <option value="sm" ${(block.image_size || '') === 'sm' ? 'selected' : ''}>${i18n.small}</option>
                            <option value="md" ${(block.image_size || '') === 'md' ? 'selected' : ''}>${i18n.medium}</option>
                            <option value="lg" ${(block.image_size || '') === 'lg' ? 'selected' : ''}>${i18n.large}</option>
                            <option value="full" ${(block.image_size || 'full') === 'full' ? 'selected' : ''}>${i18n.fullWidth}</option>
                        </select>
                        <label class="block text-xs mt-2 mb-1">${i18n.imageUpload}</label>
                        <input type="file" name="block_images[${index}]" accept="image/*" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-xs">
                    </div>
                    <div class="${type === 'button' ? '' : 'hidden'} grid gap-3 sm:grid-cols-2" data-type-field="button">
                        <div>
                            <label class="block text-xs mb-1">${i18n.buttonText}</label>
                            <input type="text" name="blocks[${index}][button_text]" value="${escapeHtml(block.button_text || '')}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs mb-1">${i18n.buttonUrl}</label>
                            <input type="url" name="blocks[${index}][button_url]" value="${escapeHtml(block.button_url || '')}" placeholder="https://..." class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs mb-1">${i18n.buttonSize}</label>
                            <select name="blocks[${index}][button_size]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                                <option value="sm" ${(block.button_size || '') === 'sm' ? 'selected' : ''}>${i18n.small}</option>
                                <option value="md" ${(block.button_size || 'md') === 'md' ? 'selected' : ''}>${i18n.medium}</option>
                                <option value="lg" ${(block.button_size || '') === 'lg' ? 'selected' : ''}>${i18n.large}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs mb-1">${i18n.buttonColor}</label>
                            <select name="blocks[${index}][button_color]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                                <option value="primary" ${(block.button_color || 'primary') === 'primary' ? 'selected' : ''}>${i18n.primaryColor}</option>
                                <option value="secondary" ${(block.button_color || '') === 'secondary' ? 'selected' : ''}>${i18n.secondaryColor}</option>
                                <option value="outline" ${(block.button_color || '') === 'outline' ? 'selected' : ''}>${i18n.outlineColor}</option>
                                <option value="none" ${(block.button_color || '') === 'none' ? 'selected' : ''}>${i18n.noneBg}</option>
                            </select>
                        </div>
                    </div>
                    <div class="${type === 'form' ? '' : 'hidden'} grid gap-3 sm:grid-cols-2" data-type-field="form">
                        <div>
                            <label class="block text-xs mb-1">${i18n.selectForm}</label>
                            <select name="blocks[${index}][form_id]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                                <option value="">${i18n.selectForm}</option>
                                ${availableForms.map((form) => `<option value="${form.id}" ${String(block.form_id || '') === String(form.id) ? 'selected' : ''}>${escapeHtml(form.name)} (${escapeHtml(form.slug)})</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs mb-1">${i18n.submitButtonLabel}</label>
                            <input type="text" name="blocks[${index}][form_submit_label]" value="${escapeHtml(block.form_submit_label || i18n.defaultSubmitLabel)}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>
            `;

            // Enforce field visibility by type (safer than relying on class string only).
            const textField = wrapper.querySelector('[data-type-field="text"]');
            const textColumnsField = wrapper.querySelector('[data-type-field="text-columns"]');
            const imageField = wrapper.querySelector('[data-type-field="image"]');
            const buttonField = wrapper.querySelector('[data-type-field="button"]');
            const formField = wrapper.querySelector('[data-type-field="form"]');
            if (textField) {
                const layoutSelect = wrapper.querySelector(`select[name="blocks[${index}][layout]"]`);
                const isTwoColumns = (layoutSelect ? layoutSelect.value : (block.layout || 'full')) === 'two_columns';
                textField.classList.toggle('hidden', type !== 'text' || isTwoColumns);
            }
            if (textColumnsField) {
                const layoutSelect = wrapper.querySelector(`select[name="blocks[${index}][layout]"]`);
                const isTwoColumns = (layoutSelect ? layoutSelect.value : (block.layout || 'full')) === 'two_columns';
                textColumnsField.classList.toggle('hidden', type !== 'text' || !isTwoColumns);
                if (layoutSelect) {
                    layoutSelect.addEventListener('change', () => {
                        const showColumns = layoutSelect.value === 'two_columns';
                        if (textField) {
                            textField.classList.toggle('hidden', showColumns);
                        }
                        textColumnsField.classList.toggle('hidden', !showColumns);
                    });
                }
            }
            if (imageField) imageField.classList.toggle('hidden', type !== 'image');
            if (buttonField) buttonField.classList.toggle('hidden', type !== 'button');
            if (formField) formField.classList.toggle('hidden', type !== 'form');

            wrapper.addEventListener('dragstart', () => {
                dragSourceIndex = index;
            });
            wrapper.addEventListener('dragover', (event) => event.preventDefault());
            wrapper.addEventListener('drop', (event) => {
                event.preventDefault();
                if (dragSourceIndex === null || dragSourceIndex === index) {
                    return;
                }
                const source = blocks.splice(dragSourceIndex, 1)[0];
                blocks.splice(index, 0, source);
                render();
            });

            return wrapper;
        }

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        }

        const blocks = Array.isArray(initialBlocks) ? initialBlocks : [];

        function render() {
            blocksRoot.innerHTML = '';
            blocks.forEach((block, index) => {
                blocksRoot.appendChild(blockCard(index, block));
            });

            emptyText.classList.toggle('hidden', blocks.length > 0);

            blocksRoot.querySelectorAll('[data-remove-index]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.removeIndex);
                    if (Number.isNaN(index)) {
                        return;
                    }
                    blocks.splice(index, 1);
                    render();
                });
            });

            blocksRoot.querySelectorAll('[data-insert-inline-button]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.insertInlineButton);
                    insertInlineButtonAt(`page-rich-input-${index}`, index);
                });
            });

            blocksRoot.querySelectorAll('[data-insert-inline-button-left]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.insertInlineButtonLeft);
                    insertInlineButtonAt(`page-rich-input-left-${index}`, index);
                });
            });

            blocksRoot.querySelectorAll('[data-insert-inline-button-right]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.insertInlineButtonRight);
                    insertInlineButtonAt(`page-rich-input-right-${index}`, index);
                });
            });
            blocksRoot.querySelectorAll('[data-insert-inline-image]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.insertInlineImage);
                    insertInlineImageAt(`page-rich-input-${index}`, index);
                });
            });
            blocksRoot.querySelectorAll('[data-insert-inline-image-left]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.insertInlineImageLeft);
                    insertInlineImageAt(`page-rich-input-left-${index}`, index);
                });
            });
            blocksRoot.querySelectorAll('[data-insert-inline-image-right]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.insertInlineImageRight);
                    insertInlineImageAt(`page-rich-input-right-${index}`, index);
                });
            });
            blocksRoot.querySelectorAll('[data-insert-dynamic-token]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.insertDynamicToken);
                    insertDynamicTokenAt(`page-rich-input-${index}`, index);
                });
            });
            blocksRoot.querySelectorAll('[data-insert-dynamic-token-left]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.insertDynamicTokenLeft);
                    insertDynamicTokenAt(`page-rich-input-left-${index}`, index);
                });
            });
            blocksRoot.querySelectorAll('[data-insert-dynamic-token-right]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.insertDynamicTokenRight);
                    insertDynamicTokenAt(`page-rich-input-right-${index}`, index);
                });
            });

            initRichTextEditors();
            syncBlocksFromDom();
            renderPreview();
        }

        function setInputValue(name, value) {
            const input = document.querySelector(`[name="${name}"]`);
            if (!input) return;
            if (input.type === 'checkbox') {
                input.checked = Boolean(value);
                return;
            }
            input.value = value ?? '';
        }

        function renderTemplatePreview(templateId) {
            const template = availablePageTemplates.find((item) => String(item.id) === String(templateId));
            if (!template || !pageTemplatePreview || !templatePreviewHero || !templatePreviewBlocks || !templatePreviewBlocksCount) {
                if (pageTemplatePreview) pageTemplatePreview.classList.add('hidden');
                if (pageTemplatePreviewEmpty) pageTemplatePreviewEmpty.classList.remove('hidden');
                return;
            }

            const blocks = Array.isArray(template.blocks) ? template.blocks : [];
            pageTemplatePreview.classList.remove('hidden');
            if (pageTemplatePreviewEmpty) pageTemplatePreviewEmpty.classList.add('hidden');
            templatePreviewBlocksCount.textContent = (i18n.blocksCount || 'Blocks: :count').replace(':count', String(blocks.length));

            const heroHeading = template.hero_heading || template.title || 'Page';
            const heroSubheading = template.hero_subheading || '';
            const heroBadge = template.hero_badge || '';
            const showHero = Boolean(template.show_hero);
            templatePreviewHero.innerHTML = showHero
                ? `
                    <div class="font-semibold text-gray-900 dark:text-white mb-1">${escapeHtml(i18n.hero || 'Hero')}</div>
                    ${heroBadge ? `<div class="text-[11px] text-primary-600 dark:text-primary-400 mb-1">${escapeHtml(heroBadge)}</div>` : ''}
                    <div class="text-sm font-medium text-gray-900 dark:text-white">${escapeHtml(heroHeading)}</div>
                    ${heroSubheading ? `<div class="text-xs text-gray-500 mt-1">${escapeHtml(heroSubheading)}</div>` : ''}
                `
                : `<div class="text-xs text-gray-500">${escapeHtml(i18n.previewNotAvailable || 'No preview available.')}</div>`;

            if (!blocks.length) {
                templatePreviewBlocks.innerHTML = `<span class="text-xs text-gray-500">${escapeHtml(i18n.previewNotAvailable || 'No preview available.')}</span>`;
                return;
            }

            templatePreviewBlocks.innerHTML = blocks.slice(0, 8).map((block, index) => {
                const type = String(block?.type || 'text').toUpperCase();
                return `<span class="inline-flex items-center rounded-full border border-gray-300 dark:border-dark-600 px-2 py-1 text-[11px] text-gray-700 dark:text-gray-300">${index + 1}. ${escapeHtml(type)}</span>`;
            }).join('');
        }

        function collectTemplateTokens(template) {
            const tokenRegex = /\{\{\s*([a-z0-9_]+)\s*\}\}/gi;
            const tokens = new Set();
            const scanValue = (value) => {
                const source = String(value || '');
                let match = tokenRegex.exec(source);
                while (match) {
                    const key = String(match[1] || '').toLowerCase().trim();
                    if (key !== '' && !dynamicTemplateTokens.has(key)) {
                        tokens.add(key);
                    }
                    match = tokenRegex.exec(source);
                }
            };

            [
                template?.hero_badge,
                template?.hero_heading,
                template?.hero_subheading,
                template?.hero_primary_button_text,
                template?.hero_primary_button_url,
                template?.hero_secondary_button_text,
                template?.hero_secondary_button_url,
            ].forEach(scanValue);

            (Array.isArray(template?.blocks) ? template.blocks : []).forEach((block) => {
                [
                    block?.title,
                    block?.content,
                    block?.content_left,
                    block?.content_right,
                    block?.image_url,
                    block?.button_text,
                    block?.button_url,
                    block?.form_submit_label,
                ].forEach(scanValue);
            });

            return Array.from(tokens).sort();
        }

        function renderTemplateTokenInputs(templateId) {
            if (!pageTemplateTokenPanel || !pageTemplateTokenInputs) {
                return;
            }
            const template = availablePageTemplates.find((item) => String(item.id) === String(templateId));
            if (!template) {
                pageTemplateTokenPanel.classList.add('hidden');
                pageTemplateTokenInputs.innerHTML = '';
                return;
            }

            const tokens = collectTemplateTokens(template);
            pageTemplateTokenPanel.classList.remove('hidden');
            if (!tokens.length) {
                pageTemplateTokenInputs.innerHTML = `<p class="text-xs text-gray-500 md:col-span-3">${escapeHtml(i18n.noTemplateTokens || 'No placeholders in this template.')}</p>`;
                return;
            }

            pageTemplateTokenInputs.innerHTML = tokens.map((token) => {
                const previous = templateTokenValues[token] || '';
                return `
                    <div>
                        <label class="block text-xs mb-1 text-gray-600 dark:text-gray-300">&#123;&#123;${escapeHtml(token)}&#125;&#125;</label>
                        <input
                            type="text"
                            data-template-token="${escapeHtml(token)}"
                            value="${escapeHtml(previous)}"
                            class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm"
                        >
                    </div>
                `;
            }).join('');
        }

        function replaceTemplateTokens(value, tokenMap) {
            return String(value ?? '').replace(/\{\{\s*([a-z0-9_]+)\s*\}\}/gi, (fullMatch, key) => {
                const normalized = String(key || '').toLowerCase().trim();
                if (!normalized || dynamicTemplateTokens.has(normalized)) {
                    return fullMatch;
                }
                const replacement = tokenMap[normalized];
                return replacement !== undefined && replacement !== '' ? String(replacement) : fullMatch;
            });
        }

        function applyTemplate(templateId) {
            const template = availablePageTemplates.find((item) => String(item.id) === String(templateId));
            if (!template) {
                return;
            }
            const tokenMap = {};
            document.querySelectorAll('[data-template-token]').forEach((input) => {
                const token = String(input.getAttribute('data-template-token') || '').toLowerCase().trim();
                if (!token) return;
                tokenMap[token] = input.value || '';
                templateTokenValues[token] = input.value || '';
            });

            setInputValue('show_hero', template.show_hero);
            setInputValue('hero_badge', replaceTemplateTokens(template.hero_badge, tokenMap));
            setInputValue('hero_heading', replaceTemplateTokens(template.hero_heading, tokenMap));
            setInputValue('hero_subheading', replaceTemplateTokens(template.hero_subheading, tokenMap));
            setInputValue('hero_theme', template.hero_theme || 'blue');
            setInputValue('hero_background_image', replaceTemplateTokens(template.hero_background_image || '', tokenMap));
            setInputValue('hero_overlay_strength', template.hero_overlay_strength || 'medium');
            setInputValue('hero_height', template.hero_height || 'md');
            setInputValue('hero_primary_button_text', replaceTemplateTokens(template.hero_primary_button_text || '', tokenMap));
            setInputValue('hero_primary_button_url', replaceTemplateTokens(template.hero_primary_button_url || '', tokenMap));
            setInputValue('hero_secondary_button_text', replaceTemplateTokens(template.hero_secondary_button_text || '', tokenMap));
            setInputValue('hero_secondary_button_url', replaceTemplateTokens(template.hero_secondary_button_url || '', tokenMap));

            blocks.splice(0, blocks.length, ...(Array.isArray(template.blocks) ? template.blocks : []).map((block) => ({
                type: block?.type || 'text',
                title: replaceTemplateTokens(block?.title || '', tokenMap),
                block_width: block?.block_width || 'full',
                layout: block?.layout || 'full',
                alignment: block?.alignment || 'left',
                background: block?.background || 'none',
                padding: block?.padding || 'md',
                image_size: block?.image_size || 'full',
                content: replaceTemplateTokens(block?.content || '', tokenMap),
                content_left: replaceTemplateTokens(block?.content_left || '', tokenMap),
                content_right: replaceTemplateTokens(block?.content_right || '', tokenMap),
                image_url: replaceTemplateTokens(block?.image_url || '', tokenMap),
                button_text: replaceTemplateTokens(block?.button_text || '', tokenMap),
                button_url: replaceTemplateTokens(block?.button_url || '', tokenMap),
                button_size: block?.button_size || 'md',
                button_color: block?.button_color || 'primary',
                form_id: String(block?.form_id || ''),
                form_submit_label: replaceTemplateTokens(block?.form_submit_label || i18n.defaultSubmitLabel, tokenMap),
            })));

            quillInstances.clear();
            render();
            alert(i18n.templateApplied || 'Template applied.');
        }

        function insertInlineButtonAt(targetId, index) {
            if (Number.isNaN(index)) {
                return;
            }
            const textarea = blocksRoot.querySelector(`#${targetId}`);
            if (!textarea) {
                return;
            }
            inlineButtonTargetId = targetId;
            if (inlineButtonLabel) inlineButtonLabel.value = 'Button';
            if (inlineButtonUrl) inlineButtonUrl.value = 'https://example.com';
            if (inlineButtonSize) inlineButtonSize.value = 'md';
            if (inlineButtonColor) inlineButtonColor.value = 'primary';
            openInlineButtonModal();
        }

        function openInlineButtonModal() {
            if (!inlineButtonModal) return;
            inlineButtonModal.classList.remove('hidden');
            if (inlineButtonLabel) inlineButtonLabel.focus();
        }

        function closeInlineButtonModal() {
            if (!inlineButtonModal) return;
            inlineButtonModal.classList.add('hidden');
            inlineButtonTargetId = null;
        }

        function confirmInlineButtonInsert() {
            if (!inlineButtonTargetId) {
                closeInlineButtonModal();
                return;
            }
            const label = (inlineButtonLabel?.value || 'Button').trim() || 'Button';
            const url = (inlineButtonUrl?.value || 'https://example.com').trim() || 'https://example.com';
            const size = ['sm', 'md', 'lg'].includes((inlineButtonSize?.value || '').toLowerCase()) ? inlineButtonSize.value.toLowerCase() : 'md';
            const color = ['primary', 'secondary', 'outline', 'none'].includes((inlineButtonColor?.value || '').toLowerCase()) ? inlineButtonColor.value.toLowerCase() : 'primary';
            const snippet = `[${label}|${size}|${color}](${url})`;

            const textarea = blocksRoot.querySelector(`#${inlineButtonTargetId}`);
            if (!textarea) {
                closeInlineButtonModal();
                return;
            }

            const quill = quillInstances.get(inlineButtonTargetId);
            if (quill) {
                const range = quill.getSelection(true);
                const insertAt = range ? range.index : quill.getLength();
                quill.insertText(insertAt, snippet);
                quill.setSelection(insertAt + snippet.length, 0);
            } else {
                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? textarea.value.length;
                textarea.value = textarea.value.slice(0, start) + snippet + textarea.value.slice(end);
                const caret = start + snippet.length;
                textarea.focus();
                textarea.setSelectionRange(caret, caret);
                renderPreview();
            }

            closeInlineButtonModal();
        }

        function insertInlineImageAt(targetId, index) {
            if (Number.isNaN(index)) return;
            const textarea = blocksRoot.querySelector(`#${targetId}`);
            if (!textarea) return;
            inlineImageTargetId = targetId;
            if (inlineImageUrl) inlineImageUrl.value = 'https://example.com/image.jpg';
            if (inlineImageFile) inlineImageFile.value = '';
            if (inlineImageSize) inlineImageSize.value = 'md';
            openInlineImageModal();
        }

        function insertDynamicTokenAt(targetId, index) {
            if (Number.isNaN(index)) return;
            const textarea = blocksRoot.querySelector(`#${targetId}`);
            if (!textarea) return;
            dynamicTokenTargetId = targetId;
            if (dynamicTokenSelect) {
                dynamicTokenSelect.value = '@{{team_list}}';
            }
            openDynamicTokenModal();
        }

        function openDynamicTokenModal() {
            if (!dynamicTokenModal) return;
            dynamicTokenModal.classList.remove('hidden');
            if (dynamicTokenSelect) dynamicTokenSelect.focus();
        }

        function closeDynamicTokenModal() {
            if (!dynamicTokenModal) return;
            dynamicTokenModal.classList.add('hidden');
            dynamicTokenTargetId = null;
        }

        function confirmDynamicTokenInsert() {
            if (!dynamicTokenTargetId) {
                closeDynamicTokenModal();
                return;
            }
            const token = (dynamicTokenSelect?.value || '@{{team_list}}').trim() || '@{{team_list}}';
            const textarea = blocksRoot.querySelector(`#${dynamicTokenTargetId}`);
            if (!textarea) {
                closeDynamicTokenModal();
                return;
            }
            const quill = quillInstances.get(dynamicTokenTargetId);
            if (quill) {
                const range = quill.getSelection(true);
                const insertAt = range ? range.index : quill.getLength();
                quill.insertText(insertAt, token);
                quill.setSelection(insertAt + token.length, 0);
            } else {
                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? textarea.value.length;
                textarea.value = textarea.value.slice(0, start) + token + textarea.value.slice(end);
                const caret = start + token.length;
                textarea.focus();
                textarea.setSelectionRange(caret, caret);
                renderPreview();
            }
            closeDynamicTokenModal();
        }

        function openInlineImageModal() {
            if (!inlineImageModal) return;
            inlineImageModal.classList.remove('hidden');
            if (inlineImageUrl) inlineImageUrl.focus();
        }

        function closeInlineImageModal() {
            if (!inlineImageModal) return;
            inlineImageModal.classList.add('hidden');
            inlineImageTargetId = null;
        }

        async function confirmInlineImageInsert() {
            if (!inlineImageTargetId) {
                closeInlineImageModal();
                return;
            }
            const selectedFile = inlineImageFile?.files?.[0] || null;
            let url = (inlineImageUrl?.value || 'https://example.com/image.jpg').trim();
            let size = (inlineImageSize?.value || 'md').toLowerCase();
            if (!['sm', 'md', 'lg'].includes(size)) size = 'md';

            if (selectedFile) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const data = new FormData();
                data.append('image', selectedFile);
                if (inlineImageInsertConfirm) {
                    inlineImageInsertConfirm.disabled = true;
                    inlineImageInsertConfirm.textContent = i18n.uploading || 'Uploading...';
                }
                try {
                    const response = await fetch(inlineImageUploadEndpoint, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: data,
                    });
                    if (!response.ok) {
                        throw new Error('Upload failed');
                    }
                    const payload = await response.json();
                    if (!payload?.url) {
                        throw new Error('Upload URL missing');
                    }
                    url = String(payload.url).trim();
                } catch (_error) {
                    alert('Image upload failed.');
                    if (inlineImageInsertConfirm) {
                        inlineImageInsertConfirm.disabled = false;
                        inlineImageInsertConfirm.textContent = i18n.insertImage;
                    }
                    return;
                } finally {
                    if (inlineImageInsertConfirm) {
                        inlineImageInsertConfirm.disabled = false;
                        inlineImageInsertConfirm.textContent = i18n.insertImage;
                    }
                }
            }

            const snippet = `![img|${size}](${url})`;

            const textarea = blocksRoot.querySelector(`#${inlineImageTargetId}`);
            if (!textarea) {
                closeInlineImageModal();
                return;
            }

            const quill = quillInstances.get(inlineImageTargetId);
            if (quill) {
                const range = quill.getSelection(true);
                const insertAt = range ? range.index : quill.getLength();
                quill.insertText(insertAt, snippet);
                quill.setSelection(insertAt + snippet.length, 0);
            } else {
                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? textarea.value.length;
                textarea.value = textarea.value.slice(0, start) + snippet + textarea.value.slice(end);
                const caret = start + snippet.length;
                textarea.focus();
                textarea.setSelectionRange(caret, caret);
                renderPreview();
            }

            closeInlineImageModal();
        }

        function initRichTextEditors() {
            if (typeof Quill === 'undefined') {
                return;
            }

            blocksRoot.querySelectorAll('[data-rich-text-editor]').forEach((editorNode) => {
                const targetId = editorNode.dataset.richTarget;
                if (!targetId || quillInstances.has(targetId)) {
                    return;
                }

                const textarea = document.getElementById(targetId);
                if (!textarea) {
                    return;
                }

                const quill = new Quill(`#${editorNode.id}`, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['blockquote', 'clean']
                        ]
                    },
                    placeholder: i18n.content
                });

                if ((textarea.value || '').trim() !== '') {
                    quill.root.innerHTML = textarea.value;
                }

                quill.on('text-change', () => {
                    textarea.value = quill.root.innerHTML;
                    renderPreview();
                });

                textarea.classList.add('sr-only');
                textarea.style.display = 'none';
                quillInstances.set(targetId, quill);
            });
        }

        function textWithInlineButtons(value) {
            let html = String(value || '');
            html = html.replace(/!\[img\|(sm|md|lg)\]\(((?:https?:\/\/|\/)[^)]+)\)/g, (_match, size, url) => {
                const imageClass = size === 'sm' ? 'w-1/4' : (size === 'lg' ? 'w-3/4' : 'w-1/2');
                return `<img src="${escapeHtml(url)}" alt="" class="${imageClass} h-auto rounded my-2">`;
            });
            return html.replace(/\[([^\]]+)\]\(((?:https?:\/\/|\/)[^)]+)\)/g, (_match, labelSpec, url) => {
                let label = labelSpec;
                let size = 'md';
                let color = 'primary';
                if (labelSpec.includes('|')) {
                    const pieces = labelSpec.split('|');
                    label = pieces[0] || label;
                    for (let i = 1; i < pieces.length; i++) {
                        const token = (pieces[i] || '').toLowerCase().trim();
                        if (['sm', 'md', 'lg'].includes(token)) {
                            size = token;
                        }
                        if (['primary', 'secondary', 'outline', 'none'].includes(token)) {
                            color = token;
                        }
                    }
                }
                const sizeClass = size === 'sm'
                    ? 'px-2 py-0.5 text-[10px]'
                    : (size === 'lg' ? 'px-4 py-2 text-sm' : 'px-3 py-1 text-[11px]');
                const colorClass = color === 'secondary'
                    ? 'bg-gray-600 text-white'
                    : (color === 'outline'
                        ? 'bg-transparent text-primary-600 border border-primary-500'
                        : (color === 'none' ? 'bg-transparent text-primary-600' : 'bg-primary-600 text-white'));
                return `<a href="${escapeHtml(url)}" class="inline-flex items-center rounded mx-1 my-0.5 ${sizeClass} ${colorClass}">${escapeHtml(label)}</a>`;
            });
        }

        function syncBlocksFromDom() {
            blocks.forEach((block, index) => {
                const readValue = (field) => {
                    const input = blocksRoot.querySelector(`[name="blocks[${index}][${field}]"]`);
                    return input ? input.value : (block[field] || '');
                };

                block.title = readValue('title');
                block.block_width = readValue('block_width') || 'full';
                block.layout = readValue('layout') || 'full';
                block.alignment = readValue('alignment') || 'left';
                block.background = readValue('background') || 'none';
                block.padding = readValue('padding') || 'md';
                block.content = readValue('content');
                block.content_left = readValue('content_left');
                block.content_right = readValue('content_right');
                block.image_url = readValue('image_url');
                block.image_size = readValue('image_size') || 'full';
                block.button_text = readValue('button_text');
                block.button_url = readValue('button_url');
                block.button_size = readValue('button_size') || 'md';
                block.button_color = readValue('button_color') || 'primary';
            });
        }

        function renderPreview() {
            syncBlocksFromDom();
            previewRoot.innerHTML = '';
            const showHero = document.querySelector('input[name="show_hero"]')?.checked ?? false;
            const pageTitle = (document.querySelector('input[name="title"]')?.value || '').trim();
            const heroBadge = (document.querySelector('input[name="hero_badge"]')?.value || '').trim();
            const heroHeading = (document.querySelector('input[name="hero_heading"]')?.value || '').trim();
            const heroSubheading = (document.querySelector('input[name="hero_subheading"]')?.value || '').trim();
            const heroTheme = (document.querySelector('select[name="hero_theme"]')?.value || 'blue').trim();
            const heroBackgroundImage = (document.querySelector('input[name="hero_background_image"]')?.value || '').trim();
            const heroOverlayStrength = (document.querySelector('select[name="hero_overlay_strength"]')?.value || 'medium').trim();
            const heroHeight = (document.querySelector('select[name="hero_height"]')?.value || 'md').trim();
            const heroPrimaryButtonText = (document.querySelector('input[name="hero_primary_button_text"]')?.value || '').trim();
            const heroPrimaryButtonUrl = (document.querySelector('input[name="hero_primary_button_url"]')?.value || '').trim();
            const heroSecondaryButtonText = (document.querySelector('input[name="hero_secondary_button_text"]')?.value || '').trim();
            const heroSecondaryButtonUrl = (document.querySelector('input[name="hero_secondary_button_url"]')?.value || '').trim();
            const heroThemes = {
                blue: {
                    gradient: 'from-primary-500/10 via-transparent to-blue-500/10',
                    badge: 'bg-primary-500/10 border-primary-500/20',
                    dot: 'bg-primary-500',
                    text: 'text-primary-600 dark:text-primary-400',
                },
                green: {
                    gradient: 'from-emerald-500/10 via-transparent to-green-500/10',
                    badge: 'bg-emerald-500/10 border-emerald-500/20',
                    dot: 'bg-emerald-500',
                    text: 'text-emerald-600 dark:text-emerald-400',
                },
                purple: {
                    gradient: 'from-purple-500/10 via-transparent to-fuchsia-500/10',
                    badge: 'bg-purple-500/10 border-purple-500/20',
                    dot: 'bg-purple-500',
                    text: 'text-purple-600 dark:text-purple-400',
                },
                orange: {
                    gradient: 'from-orange-500/10 via-transparent to-amber-500/10',
                    badge: 'bg-orange-500/10 border-orange-500/20',
                    dot: 'bg-orange-500',
                    text: 'text-orange-600 dark:text-orange-400',
                },
            };
            const heroClasses = heroThemes[heroTheme] || heroThemes.blue;
            const overlayClass = heroOverlayStrength === 'light'
                ? 'bg-black/20'
                : (heroOverlayStrength === 'strong' ? 'bg-black/60' : 'bg-black/40');
            const heroHeightClass = heroHeight === 'sm'
                ? 'py-6 md:py-8'
                : (heroHeight === 'lg'
                    ? 'py-14 md:py-16'
                    : (heroHeight === 'full' ? 'min-h-[65vh] flex flex-col justify-center' : 'py-8 md:py-10'));
            const heroHeadingTextClass = heroBackgroundImage ? 'text-white' : 'text-gray-900 dark:text-white';
            const heroSubheadingTextClass = heroBackgroundImage ? 'text-white/90' : 'text-gray-600 dark:text-gray-400';
            const heroSectionBackground = heroBackgroundImage
                ? `style="background-image:url('${escapeHtml(heroBackgroundImage)}');background-size:cover;background-position:center;"`
                : '';

            if (showHero) {
                previewRoot.innerHTML += `
                    <section class="w-full relative overflow-hidden rounded-xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800" ${heroSectionBackground}>
                        <div class="absolute inset-0 bg-gradient-to-br ${heroClasses.gradient}"></div>
                        ${heroBackgroundImage ? `<div class="absolute inset-0 ${overlayClass}"></div>` : ''}
                        <div class="relative px-4 text-center ${heroHeightClass}">
                            ${heroBadge ? `
                                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border mb-4 ${heroClasses.badge}">
                                    <span class="w-2 h-2 rounded-full animate-pulse ${heroClasses.dot}"></span>
                                    <span class="text-xs font-medium ${heroClasses.text}">${escapeHtml(heroBadge)}</span>
                                </div>
                            ` : ''}
                            <h2 class="text-2xl md:text-3xl font-bold mb-3 ${heroHeadingTextClass}">
                                ${escapeHtml(heroHeading || pageTitle || 'Page title')}
                            </h2>
                            ${heroSubheading ? `
                                <p class="text-sm md:text-base max-w-2xl mx-auto ${heroSubheadingTextClass}">
                                    ${escapeHtml(heroSubheading)}
                                </p>
                            ` : ''}
                            ${(heroPrimaryButtonText || heroSecondaryButtonText) ? `
                                <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                                    ${heroPrimaryButtonText ? `<a href="${escapeHtml(heroPrimaryButtonUrl || '#')}" class="inline-flex items-center rounded-lg px-4 py-2 text-xs bg-primary-600 hover:bg-primary-700 text-white">${escapeHtml(heroPrimaryButtonText)}</a>` : ''}
                                    ${heroSecondaryButtonText ? `<a href="${escapeHtml(heroSecondaryButtonUrl || '#')}" class="inline-flex items-center rounded-lg px-4 py-2 text-xs border border-white/70 text-white bg-white/10 hover:bg-white/20">${escapeHtml(heroSecondaryButtonText)}</a>` : ''}
                                </div>
                            ` : ''}
                        </div>
                    </section>
                `;
            } else if (pageTitle) {
                previewRoot.innerHTML += `
                    <div class="w-full text-lg font-semibold text-gray-900 dark:text-white mb-1">
                        ${escapeHtml(pageTitle)}
                    </div>
                `;
            }

            if (!blocks.length) {
                previewRoot.innerHTML += '<div class="w-full text-gray-400 text-xs">No content blocks yet.</div>';
                return;
            }

            blocks.forEach((block) => {
                const type = block.type || 'text';
                const layout = block.layout || 'full';
                const alignmentClass = (block.alignment || 'left') === 'center' ? 'text-center' : 'text-left';
                const bgClass = block.background === 'gray'
                    ? 'bg-gray-100 dark:bg-dark-700'
                    : (block.background === 'primary' ? 'bg-primary-50 dark:bg-primary-900/20' : 'bg-white dark:bg-dark-800');
                const paddingClass = block.padding === 'sm' ? 'p-2' : (block.padding === 'lg' ? 'p-5' : 'p-3');

                let bodyHtml = '';
                if (type === 'image') {
                    if (block.image_url) {
                        let imageClass = 'w-full';
                        if (block.image_size === 'sm') imageClass = 'w-1/4';
                        if (block.image_size === 'md') imageClass = 'w-1/2';
                        if (block.image_size === 'lg') imageClass = 'w-3/4';
                        bodyHtml = `<img src="${escapeHtml(block.image_url)}" alt="" class="${imageClass} rounded ${alignmentClass === 'text-center' ? 'mx-auto' : ''}">`;
                    } else {
                        bodyHtml = '<div class="text-gray-400 text-xs">No image selected.</div>';
                    }
                } else if (type === 'button') {
                    if (block.button_text) {
                        const buttonSize = block.button_size || 'md';
                        const buttonColor = block.button_color || 'primary';
                        const buttonSizeClass = buttonSize === 'sm'
                            ? 'px-2 py-1 text-[10px]'
                            : (buttonSize === 'lg' ? 'px-4 py-2 text-sm' : 'px-3 py-1.5 text-xs');
                        const buttonColorClass = buttonColor === 'secondary'
                            ? 'bg-gray-600 text-white'
                            : (buttonColor === 'outline'
                                ? 'bg-transparent text-primary-600 border border-primary-500'
                                : (buttonColor === 'none' ? 'bg-transparent text-primary-600' : 'bg-primary-600 text-white'));
                        bodyHtml = `<span class="inline-flex rounded ${buttonColorClass} ${buttonSizeClass}">${escapeHtml(block.button_text)}</span>`;
                    } else {
                        bodyHtml = '<div class="text-gray-400 text-xs">No button text.</div>';
                    }
                } else if (type === 'form') {
                    const selectedForm = availableForms.find((item) => String(item.id) === String(block.form_id || ''));
                    const formName = selectedForm ? selectedForm.name : i18n.form;
                    const formSlug = selectedForm ? selectedForm.slug : i18n.selectForm;
                    const submitLabel = block.form_submit_label || i18n.defaultSubmitLabel;
                    bodyHtml = `
                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-dark-600 p-3 text-xs">
                            <div class="font-semibold mb-1">${escapeHtml(formName)}</div>
                            <div class="text-gray-500 mb-2">${escapeHtml(formSlug)}</div>
                            <span class="inline-flex rounded bg-primary-600 text-white px-3 py-1">${escapeHtml(submitLabel)}</span>
                        </div>
                    `;
                } else if (layout === 'two_columns') {
                    bodyHtml = `
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>${textWithInlineButtons(block.content_left || '')}</div>
                            <div>${textWithInlineButtons(block.content_right || '')}</div>
                        </div>
                    `;
                } else {
                    bodyHtml = `<div class="text-xs">${textWithInlineButtons(block.content || '')}</div>`;
                }

                previewRoot.innerHTML += `
                    <div class="rounded border border-gray-200 dark:border-dark-700 ${bgClass} ${paddingClass} ${alignmentClass} ${(block.block_width || 'full') === 'half' ? 'md:w-[calc(50%-0.375rem)]' : 'w-full'}">
                        ${block.title ? `<div class="font-semibold mb-1">${escapeHtml(block.title)}</div>` : ''}
                        ${bodyHtml}
                    </div>
                `;
            });
        }

        function setupNavigationIconPicker() {
            if (!navigationIconSelect || !navigationIconPreview) {
                return;
            }

            const updatePreview = () => {
                navigationIconPreview.innerHTML = `<i class="${navigationIconSelect.value}"></i>`;
                if (navigationIconGrid) {
                    navigationIconGrid.querySelectorAll('[data-nav-icon]').forEach((btn) => {
                        const active = btn.getAttribute('data-nav-icon') === navigationIconSelect.value;
                        btn.classList.toggle('border-primary-500', active);
                        btn.classList.toggle('text-primary-600', active);
                        btn.classList.toggle('dark:text-primary-400', active);
                        if (!active) {
                            btn.classList.remove('border-primary-500', 'text-primary-600', 'dark:text-primary-400');
                        }
                    });
                }
            };

            navigationIconSelect.addEventListener('change', updatePreview);
            if (navigationIconGrid) {
                navigationIconGrid.querySelectorAll('[data-nav-icon]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const icon = btn.getAttribute('data-nav-icon');
                        if (!icon) return;
                        navigationIconSelect.value = icon;
                        updatePreview();
                    });
                });
            }
            updatePreview();
        }

        document.querySelectorAll('[data-add-block]').forEach((button) => {
            button.addEventListener('click', () => {
                const type = button.dataset.addBlock;
                blocks.push({
                    type,
                    title: '',
                    block_width: 'full',
                    layout: 'full',
                    alignment: 'left',
                    background: 'none',
                    padding: 'md',
                    image_size: 'full',
                    content: '',
                    content_left: '',
                    content_right: '',
                    image_url: '',
                    button_text: type === 'button' ? 'Button' : '',
                    button_url: type === 'button' ? '#' : '',
                    button_size: 'md',
                    button_color: 'primary',
                    form_id: '',
                    form_submit_label: 'Send message',
                });
                render();
            });
        });

        if (previewMode) {
            previewMode.addEventListener('change', () => {
                const mode = previewMode.value;
                if (mode === 'mobile') {
                    previewFrame.style.maxWidth = '420px';
                } else if (mode === 'tablet') {
                    previewFrame.style.maxWidth = '760px';
                } else {
                    previewFrame.style.maxWidth = '100%';
                }
            });
        }

        blocksRoot.addEventListener('input', () => {
            renderPreview();
        });
        blocksRoot.addEventListener('change', () => {
            renderPreview();
        });
        ['title', 'show_hero', 'hero_badge', 'hero_heading', 'hero_subheading', 'hero_theme', 'hero_background_image', 'hero_overlay_strength', 'hero_height', 'hero_primary_button_text', 'hero_primary_button_url', 'hero_secondary_button_text', 'hero_secondary_button_url'].forEach((fieldName) => {
            document.querySelectorAll(`[name="${fieldName}"]`).forEach((element) => {
                element.addEventListener('input', renderPreview);
                element.addEventListener('change', renderPreview);
            });
        });
        applyPageTemplateButton?.addEventListener('click', () => {
            const selectedTemplateId = pageTemplateSelect?.value || '';
            if (!selectedTemplateId) {
                return;
            }
            applyTemplate(selectedTemplateId);
        });
        pageTemplateSelect?.addEventListener('change', () => {
            renderTemplatePreview(pageTemplateSelect.value || '');
            renderTemplateTokenInputs(pageTemplateSelect.value || '');
        });
        renderTemplatePreview(pageTemplateSelect?.value || '');
        renderTemplateTokenInputs(pageTemplateSelect?.value || '');

        inlineButtonModal?.querySelectorAll('[data-modal-close]').forEach((node) => {
            node.addEventListener('click', closeInlineButtonModal);
        });
        inlineButtonInsertConfirm?.addEventListener('click', confirmInlineButtonInsert);
        inlineImageModal?.querySelectorAll('[data-image-modal-close]').forEach((node) => {
            node.addEventListener('click', closeInlineImageModal);
        });
        inlineImageInsertConfirm?.addEventListener('click', confirmInlineImageInsert);
        dynamicTokenModal?.querySelectorAll('[data-token-modal-close]').forEach((node) => {
            node.addEventListener('click', closeDynamicTokenModal);
        });
        dynamicTokenInsertConfirm?.addEventListener('click', confirmDynamicTokenInsert);
        document.addEventListener('keydown', (event) => {
            if (!inlineButtonModal || inlineButtonModal.classList.contains('hidden')) return;
            if (event.key === 'Escape') closeInlineButtonModal();
            if (event.key === 'Enter' && event.ctrlKey) confirmInlineButtonInsert();
        });
        document.addEventListener('keydown', (event) => {
            if (!inlineImageModal || inlineImageModal.classList.contains('hidden')) return;
            if (event.key === 'Escape') closeInlineImageModal();
            if (event.key === 'Enter' && event.ctrlKey) confirmInlineImageInsert();
        });
        document.addEventListener('keydown', (event) => {
            if (!dynamicTokenModal || dynamicTokenModal.classList.contains('hidden')) return;
            if (event.key === 'Escape') closeDynamicTokenModal();
            if (event.key === 'Enter' && event.ctrlKey) confirmDynamicTokenInsert();
        });

        setupNavigationIconPicker();
        render();
    })();
</script>
@endpush

