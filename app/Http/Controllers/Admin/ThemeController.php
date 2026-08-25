<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThemeManager;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function index(ThemeManager $themes): View
    {
        return view('admin.settings.themes', [
            'themes' => $themes->all(),
            'activeTheme' => $themes->activeSlug(),
        ]);
    }

    public function update(Request $request, ThemeManager $themes): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9][a-z0-9_-]*$/'],
        ]);

        $slug = (string) $validated['theme'];
        if (! array_key_exists($slug, $themes->all())) {
            return back()->withErrors(['theme' => __('Please select a valid theme.')]);
        }

        $themes->setActive($slug);

        ActivityLogger::log('theme.updated', __('Active theme set to :name', [
            'name' => (string) ($themes->meta($slug)['name'] ?? $slug),
        ]));

        return redirect()
            ->route('admin.settings.themes')
            ->with('success', __('Theme updated successfully. The new design is visible on the public site.'));
    }
}
