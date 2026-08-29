@php
    /** @var \App\Models\Form $form */
    $submitLabel = $submitLabel ?? __('Send message');
    $turnstileSiteKey = $turnstileSiteKey ?? null;
@endphp
<form method="POST" action="{{ route('forms.submit', $form->slug) }}" class="space-y-4" novalidate>
    @csrf
    {{-- Honeypot: bots fill this; humans never see it --}}
    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
        <label for="website_url">Website</label>
        <input type="text" name="website" id="website_url" value="" tabindex="-1" autocomplete="off">
    </div>

    @foreach((array) ($form->fields ?? []) as $field)
        @php
            $fieldName = (string) ($field['name'] ?? '');
            $fieldType = (string) ($field['type'] ?? 'text');
            $required = ! empty($field['required']);
            $label = (string) ($field['label'] ?? $fieldName);
            $fieldErrorKey = 'fields.'.$fieldName;
            $fieldHasError = $errors->has($fieldErrorKey);
            $inputClass = 'w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500'.($fieldHasError ? ' border-red-500' : '');
        @endphp
        @if($fieldName !== '')
            <div>
                <label for="field_{{ $fieldName }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ $label }}
                    @if($required)<span class="text-red-500">*</span>@endif
                </label>
                @if($fieldType === 'textarea')
                    <textarea
                        id="field_{{ $fieldName }}"
                        name="fields[{{ $fieldName }}]"
                        rows="5"
                        @if($required) required @endif
                        class="{{ $inputClass }}"
                    >{{ old($fieldErrorKey) }}</textarea>
                @else
                    <input
                        id="field_{{ $fieldName }}"
                        type="{{ $fieldType === 'email' ? 'email' : 'text' }}"
                        name="fields[{{ $fieldName }}]"
                        value="{{ old($fieldErrorKey) }}"
                        @if($required) required @endif
                        class="{{ $inputClass }}"
                    >
                @endif
                @error($fieldErrorKey)
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        @endif
    @endforeach

    @if($turnstileSiteKey)
        <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
        @error('cf-turnstile-response')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    @endif

    <label class="inline-flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" name="accept_terms" value="1" class="mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ old('accept_terms') ? 'checked' : '' }} required>
        <span>
            {{ __('I accept the') }}
            <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener" class="text-primary-600 dark:text-primary-400 hover:underline">
                {{ __('legal.terms.page_title') }}
            </a>
            <span class="text-red-500">*</span>
        </span>
    </label>
    @error('accept_terms')
        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    @error('form_'.$form->id)
        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white">
        <i class="fas fa-paper-plane"></i>
        {{ $submitLabel }}
    </button>
</form>
@if($turnstileSiteKey)
    <script data-cfasync="false" src="https://challenges.cloudflare.com/turnstile/v0/api.js" async></script>
@endif
