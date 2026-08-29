<?php

namespace Plugins\ContactForm\Services;

use App\Models\Form;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ContactFormService
{
    public function formSlug(): string
    {
        $slug = trim((string) Setting::getValue('contact_form_slug', 'kontakt'));

        return $slug !== '' ? Str::slug($slug) : 'kontakt';
    }

    public function pageTitle(): string
    {
        $title = trim((string) Setting::getValue('contact_form_page_title', ''));

        return $title !== '' ? $title : (string) __('Contact');
    }

    public function pageIntro(): string
    {
        return trim((string) Setting::getValue('contact_form_page_intro', ''));
    }

    public function ensureDefaultForm(): ?Form
    {
        if (! Schema::hasTable('forms')) {
            return null;
        }

        $slug = $this->formSlug();
        $existing = Form::query()->where('slug', $slug)->first();
        if ($existing) {
            return $existing;
        }

        return Form::query()->create([
            'name' => (string) __('Contact'),
            'slug' => $slug,
            'is_active' => true,
            'recipient_email' => (string) Setting::getValue('legal_email', config('mail.from.address')),
            'success_message' => (string) __('Thank you! Your message has been sent.'),
            'fields' => [
                [
                    'name' => 'name',
                    'label' => (string) __('Name'),
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'email',
                    'label' => (string) __('Email'),
                    'type' => 'email',
                    'required' => true,
                ],
                [
                    'name' => 'subject',
                    'label' => (string) __('Subject'),
                    'type' => 'text',
                    'required' => false,
                ],
                [
                    'name' => 'message',
                    'label' => (string) __('Message'),
                    'type' => 'textarea',
                    'required' => true,
                ],
            ],
        ]);
    }

    public function resolveForm(?string $slug = null): ?Form
    {
        if (! Schema::hasTable('forms')) {
            return null;
        }

        $slug = $slug !== null && trim($slug) !== '' ? Str::slug($slug) : $this->formSlug();

        $form = Form::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($form) {
            return $form;
        }

        return $this->ensureDefaultForm();
    }

    public function renderHtml(?string $slug = null, ?string $submitLabel = null): string
    {
        $form = $this->resolveForm($slug);
        if (! $form) {
            return '<p class="text-sm text-gray-500">'.e(__('Contact form is not available.')).'</p>';
        }

        $turnstileSiteKey = config('services.cloudflare.turnstile.site_key');

        return view('contact-form::partials.form', [
            'form' => $form,
            'submitLabel' => $submitLabel ?: __('Send message'),
            'turnstileSiteKey' => filled($turnstileSiteKey) ? $turnstileSiteKey : null,
        ])->render();
    }
}
