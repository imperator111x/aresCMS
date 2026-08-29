<?php

use App\Models\Setting;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\ContactForm\Services\ContactFormService;

Route::middleware('web')->group(function (): void {
    $showContact = static function () {
        /** @var ContactFormService $service */
        $service = app(ContactFormService::class);
        $form = $service->resolveForm();
        $formHtml = $form ? $service->renderHtml($form->slug) : '';

        return view()->file(__DIR__.'/../resources/views/show.blade.php', [
            'pageTitle' => $service->pageTitle(),
            'pageIntro' => $service->pageIntro(),
            'form' => $form,
            'formHtml' => $formHtml,
        ]);
    };

    Route::get('/kontakt', $showContact)->name('contact.show');
    Route::get('/contact', $showContact)->name('contact.show.en');
});

Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin/contact-form')
    ->name('admin.contact-form.')
    ->group(function (): void {
        Route::get('/', static function () {
            if (! auth()->user()->hasAdminPermission('settings')) {
                abort(403);
            }

            app(ContactFormService::class)->ensureDefaultForm();

            $settings = [
                'contact_form_page_title' => (string) Setting::getValue('contact_form_page_title', ''),
                'contact_form_page_intro' => (string) Setting::getValue('contact_form_page_intro', ''),
                'contact_form_slug' => (string) Setting::getValue('contact_form_slug', 'kontakt'),
            ];

            return view()->file(__DIR__.'/../resources/views/admin.blade.php', compact('settings'));
        })->name('index');

        Route::put('/', static function (Request $request) {
            if (! auth()->user()->hasAdminPermission('settings')) {
                abort(403);
            }

            $data = $request->validate([
                'contact_form_page_title' => ['nullable', 'string', 'max:190'],
                'contact_form_page_intro' => ['nullable', 'string', 'max:4000'],
                'contact_form_slug' => ['nullable', 'string', 'max:100'],
            ]);

            $slug = Str::slug((string) ($data['contact_form_slug'] ?? 'kontakt'));
            if ($slug === '') {
                $slug = 'kontakt';
            }

            Setting::setValue('contact_form_page_title', trim((string) ($data['contact_form_page_title'] ?? '')));
            Setting::setValue('contact_form_page_intro', trim((string) ($data['contact_form_page_intro'] ?? '')));
            Setting::setValue('contact_form_slug', $slug);

            app(ContactFormService::class)->ensureDefaultForm();

            ActivityLogger::log('contact_form.settings_updated', __('Contact form settings updated'));

            return back()->with('success', __('Settings updated successfully!'));
        })->name('update');
    });
