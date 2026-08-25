@include('layouts._public-shell', ['themeSlug' => $cmsTheme ?? app(\App\Services\ThemeManager::class)->activeSlug()])
