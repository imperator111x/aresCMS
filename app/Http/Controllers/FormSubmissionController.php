<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class FormSubmissionController extends Controller
{
    public function store(Request $request, string $slug): RedirectResponse
    {
        $form = Form::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Simple honeypot to block bots (field must stay empty / hidden in the UI).
        if (filled((string) $request->input('website'))) {
            return back()->with('success', $form->success_message ?: __('Thank you! Your message has been sent.'));
        }

        if (! $this->verifyTurnstile($request)) {
            return back()->withErrors([
                'form_'.$form->id => __('CAPTCHA verification failed. Please try again.'),
            ])->withInput();
        }

        $rules = [];
        $messages = [];
        foreach ((array) ($form->fields ?? []) as $field) {
            if (! is_array($field) || empty($field['name'])) {
                continue;
            }

            $name = (string) $field['name'];
            $required = ! empty($field['required']);
            $type = (string) ($field['type'] ?? 'text');
            $fieldRules = $required ? ['required'] : ['nullable'];
            $fieldRules[] = 'string';
            $fieldRules[] = 'max:2000';
            if ($type === 'email') {
                $fieldRules[] = 'email';
            }
            $rules['fields.'.$name] = $fieldRules;
            $messages['fields.'.$name.'.required'] = __(':label is required.', ['label' => (string) ($field['label'] ?? $name)]);
        }

        if (filled(config('services.cloudflare.turnstile.secret_key'))) {
            $rules['cf-turnstile-response'] = ['required', 'string'];
        }
        $rules['accept_terms'] = ['accepted'];
        $messages['accept_terms.accepted'] = __('Please accept the terms and conditions.');

        $validated = $request->validate($rules, $messages);
        $payload = (array) ($validated['fields'] ?? []);

        $form->submissions()->create([
            'payload' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        $recipient = (string) ($form->recipient_email ?: Setting::getValue('legal_email', config('mail.from.address')));
        if ($recipient !== '') {
            $lines = [];
            $lines[] = 'Form: '.$form->name.' ('.$form->slug.')';
            $lines[] = '---';
            foreach ((array) ($form->fields ?? []) as $field) {
                if (! is_array($field)) {
                    continue;
                }
                $name = (string) ($field['name'] ?? '');
                if ($name === '') {
                    continue;
                }
                $label = (string) ($field['label'] ?? $name);
                $value = (string) ($payload[$name] ?? '');
                $lines[] = $label.': '.$value;
            }
            $lines[] = '---';
            $lines[] = 'IP: '.((string) $request->ip());

            try {
                Mail::raw(implode("\n", $lines), static function ($message) use ($recipient, $form): void {
                    $message->to($recipient)->subject('Neue Anfrage: '.$form->name);
                });
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', $form->success_message ?: __('Thank you! Your message has been sent.'));
    }

    private function verifyTurnstile(Request $request): bool
    {
        $secretKey = config('services.cloudflare.turnstile.secret_key');
        if (empty($secretKey)) {
            return true;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secretKey,
            'response' => $request->input('cf-turnstile-response'),
            'remoteip' => $request->ip(),
        ]);

        $result = $response->json();

        return (bool) ($result['success'] ?? false);
    }
}

