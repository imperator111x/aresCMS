<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\LegalProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = [
            'site_name' => Setting::getValue('site_name', config('app.name')),
            'site_description' => Setting::getValue('site_description', ''),
            'site_logo' => Setting::getValue('site_logo', null),
            'disable_registration' => Setting::getValue('disable_registration', false),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update the settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'disable_registration' => 'nullable|boolean',
        ]);

        // Update site name
        Setting::setValue('site_name', $request->input('site_name'));

        // Update site description
        Setting::setValue('site_description', $request->input('site_description', ''));

        // Update disable registration
        Setting::setValue('disable_registration', $request->has('disable_registration') ? true : false);

        // Handle logo upload
        if ($request->hasFile('site_logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::getValue('site_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $path = $request->file('site_logo')->store('settings', 'public');
            Setting::setValue('site_logo', $path);
        }

        ActivityLogger::log('settings.updated', __('Main settings updated'));

        return redirect()->route('admin.settings.index')
            ->with('success', __('Settings updated successfully!'));
    }

    /**
     * Display the general settings page.
     *
     * @return \Illuminate\View\View
     */
    public function general()
    {
        $settings = [
            'site_name' => Setting::getValue('site_name', config('app.name')),
            'site_url' => Setting::getValue('site_url', url('/')),
            'site_description' => Setting::getValue('site_description', ''),
            'site_logo' => Setting::getValue('site_logo', null),
            'social_twitter' => Setting::getValue('social_twitter', ''),
            'social_facebook' => Setting::getValue('social_facebook', ''),
            'social_instagram' => Setting::getValue('social_instagram', ''),
            'social_youtube' => Setting::getValue('social_youtube', ''),
            'social_twitter_enabled' => (bool) Setting::getValue('social_twitter_enabled', false),
            'social_facebook_enabled' => (bool) Setting::getValue('social_facebook_enabled', false),
            'social_instagram_enabled' => (bool) Setting::getValue('social_instagram_enabled', false),
            'social_youtube_enabled' => (bool) Setting::getValue('social_youtube_enabled', false),
            'disable_registration' => Setting::getBoolValue('disable_registration', false),
        ];

        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Update the general settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_url' => 'required|url|max:255',
            'site_description' => 'nullable|string|max:500',
            'social_twitter' => 'nullable|url|max:255',
            'social_facebook' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'social_twitter_enabled' => 'nullable|boolean',
            'social_facebook_enabled' => 'nullable|boolean',
            'social_instagram_enabled' => 'nullable|boolean',
            'social_youtube_enabled' => 'nullable|boolean',
        ]);

        // Update site name
        Setting::setValue('site_name', $request->input('site_name'));

        // Update site URL
        Setting::setValue('site_url', $request->input('site_url'));

        // Update site description
        Setting::setValue('site_description', $request->input('site_description', ''));

        // Update social media links
        Setting::setValue('social_twitter', $request->input('social_twitter', ''));
        Setting::setValue('social_facebook', $request->input('social_facebook', ''));
        Setting::setValue('social_instagram', $request->input('social_instagram', ''));
        Setting::setValue('social_youtube', $request->input('social_youtube', ''));
        Setting::setValue('social_twitter_enabled', $request->boolean('social_twitter_enabled'));
        Setting::setValue('social_facebook_enabled', $request->boolean('social_facebook_enabled'));
        Setting::setValue('social_instagram_enabled', $request->boolean('social_instagram_enabled'));
        Setting::setValue('social_youtube_enabled', $request->boolean('social_youtube_enabled'));

        ActivityLogger::log('settings.general_updated', __('General settings updated'));

        return redirect()->route('admin.settings.general')
            ->with('success', __('Settings updated successfully!'));
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logo()
    {
        return redirect()->to(route('admin.settings.general').'#settings-logo');
    }

    /**
     * Update the logo settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLogo(Request $request)
    {
        $request->validate([
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('site_logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::getValue('site_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $path = $request->file('site_logo')->store('settings', 'public');
            Setting::setValue('site_logo', $path);

            ActivityLogger::log('settings.logo_updated', __('Logo updated'));
        }

        return redirect()->to(route('admin.settings.general').'#settings-logo')
            ->with('success', __('Settings updated successfully!'));
    }

    /**
     * Display the registration settings page.
     *
     * @return \Illuminate\View\View
     */
    public function registration()
    {
        return redirect()->to(route('admin.settings.general').'#settings-registration');
    }

    /**
     * Update the registration settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRegistration(Request $request)
    {
        $request->validate([
            'disable_registration' => 'nullable|boolean',
        ]);

        // Update disable registration
        Setting::setValue('disable_registration', $request->has('disable_registration') ? true : false);

        ActivityLogger::log('settings.registration_updated', __('Registration settings updated'));

        return redirect()->to(route('admin.settings.general').'#settings-registration')
            ->with('success', __('Settings updated successfully!'));
    }

    /**
     * Impressum / Anbieterdaten (öffentliche Seite / Footer).
     */
    public function legalImprint()
    {
        $legal = LegalProfile::resolved();

        return view('admin.settings.legal-imprint', [
            'legal' => $legal,
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLegalImprint(Request $request)
    {
        $request->validate([
            'legal_entity_name' => 'nullable|string|max:255',
            'legal_representative' => 'nullable|string|max:255',
            'legal_address_street' => 'nullable|string|max:255',
            'legal_address_zip' => 'nullable|string|max:32',
            'legal_address_city' => 'nullable|string|max:128',
            'legal_country' => 'nullable|string|max:128',
            'legal_email' => 'nullable|email|max:255',
            'legal_phone' => 'nullable|string|max:64',
            'legal_vat_id' => 'nullable|string|max:64',
            'legal_register_info' => 'nullable|string|max:2000',
            'legal_content_responsibility' => 'nullable|string|max:500',
        ]);

        $fields = [
            'legal_entity_name',
            'legal_representative',
            'legal_address_street',
            'legal_address_zip',
            'legal_address_city',
            'legal_country',
            'legal_email',
            'legal_phone',
            'legal_vat_id',
            'legal_register_info',
            'legal_content_responsibility',
        ];

        foreach ($fields as $key) {
            Setting::setValue($key, (string) $request->input($key, ''));
        }

        ActivityLogger::log('settings.legal_imprint_updated', __('Legal notice / imprint settings updated'));

        return redirect()->route('admin.settings.legal-imprint')
            ->with('success', __('Settings updated successfully!'));
    }

    /**
     * Sprachdateien (.json) verwalten.
     */
    public function languages(Request $request)
    {
        $locales = $this->availableLocales();
        $selectedLocale = (string) $request->query('locale', app()->getLocale());
        if (! in_array($selectedLocale, $locales, true)) {
            $selectedLocale = $locales[0] ?? 'en';
        }

        $translations = $this->readLocaleJson($selectedLocale);
        ksort($translations);

        return view('admin.settings.languages', [
            'locales' => $locales,
            'selectedLocale' => $selectedLocale,
            'translationsJson' => json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    /**
     * Neue Sprache anhand vorhandener JSON-Datei anlegen.
     */
    public function storeLanguage(Request $request)
    {
        $request->validate([
            'locale_code' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z]{2}([_-][A-Za-z]{2})?$/'],
            'copy_from' => ['nullable', 'string', 'max:10'],
        ]);

        $newLocale = $this->normalizeLocale((string) $request->input('locale_code'));
        if (! $this->isAllowedLocaleCode($newLocale)) {
            return back()->withErrors(['locale_code' => __('Invalid locale code.')])->withInput();
        }

        $existing = $this->availableLocales();
        if (in_array($newLocale, $existing, true)) {
            return back()->withErrors(['locale_code' => __('Language already exists.')])->withInput();
        }

        $source = $this->normalizeLocale((string) $request->input('copy_from', ''));
        if (! in_array($source, $existing, true)) {
            $source = in_array('en', $existing, true) ? 'en' : ($existing[0] ?? null);
        }

        $translations = $source ? $this->readLocaleJson($source) : [];
        $this->writeLocaleJson($newLocale, $translations);

        ActivityLogger::log('settings.language_added', __('Language added: :locale', ['locale' => $newLocale]));

        return redirect()->route('admin.settings.languages', ['locale' => $newLocale])
            ->with('success', __('Language created successfully.'));
    }

    /**
     * Übersetzungs-JSON speichern.
     */
    public function updateLanguage(Request $request)
    {
        $request->validate([
            'locale' => ['required', 'string', 'max:10'],
            'translations_json' => ['required', 'string'],
        ]);

        $locale = $this->normalizeLocale((string) $request->input('locale'));
        if (! in_array($locale, $this->availableLocales(), true)) {
            return back()->withErrors(['locale' => __('Unknown language.')])->withInput();
        }

        try {
            $decoded = json_decode((string) $request->input('translations_json'), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return back()
                ->withErrors(['translations_json' => __('Invalid JSON format: :msg', ['msg' => $e->getMessage()])])
                ->withInput();
        }

        if (! is_array($decoded)) {
            return back()->withErrors(['translations_json' => __('JSON must contain an object (key/value map).')])->withInput();
        }

        ksort($decoded);
        $this->writeLocaleJson($locale, $decoded);

        ActivityLogger::log('settings.language_updated', __('Language updated: :locale', ['locale' => $locale]));

        return redirect()->route('admin.settings.languages', ['locale' => $locale])
            ->with('success', __('Translations saved successfully.'));
    }

    /**
     * @return array<int, string>
     */
    protected function availableLocales(): array
    {
        $files = glob(resource_path('lang/*.json')) ?: [];
        $locales = [];
        foreach ($files as $file) {
            $code = pathinfo($file, PATHINFO_FILENAME);
            if ($this->isAllowedLocaleCode($code)) {
                $locales[] = $this->normalizeLocale($code);
            }
        }

        $locales = array_values(array_unique($locales));
        sort($locales);

        return $locales;
    }

    protected function localeJsonPath(string $locale): string
    {
        return resource_path('lang/'.$this->normalizeLocale($locale).'.json');
    }

    /**
     * @return array<string, string>
     */
    protected function readLocaleJson(string $locale): array
    {
        $path = $this->localeJsonPath($locale);
        if (! File::exists($path)) {
            return [];
        }

        $raw = File::get($path);
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $translations
     */
    protected function writeLocaleJson(string $locale, array $translations): void
    {
        $path = $this->localeJsonPath($locale);
        File::put($path, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);
    }

    protected function normalizeLocale(string $locale): string
    {
        $locale = str_replace('_', '-', trim($locale));
        if (str_contains($locale, '-')) {
            [$lang, $region] = array_pad(explode('-', $locale, 2), 2, '');

            return strtolower($lang).'-'.strtoupper($region);
        }

        return strtolower($locale);
    }

    protected function isAllowedLocaleCode(string $locale): bool
    {
        return (bool) preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $this->normalizeLocale($locale));
    }
}
