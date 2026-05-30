@php
    $initialFields = old('fields', $form->fields ?? []);
@endphp

<div class="space-y-6">
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-xs mb-1">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name', $form->name) }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" required>
            </div>
            <div>
                <label class="block text-xs mb-1">{{ __('Slug') }}</label>
                <input type="text" name="slug" value="{{ old('slug', $form->slug) }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" required>
            </div>
            <div>
                <label class="block text-xs mb-1">{{ __('Recipient email') }}</label>
                <input type="email" name="recipient_email" value="{{ old('recipient_email', $form->recipient_email) }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" placeholder="admin@example.com">
            </div>
            <div>
                <label class="block text-xs mb-1">{{ __('Success message') }}</label>
                <input type="text" name="success_message" value="{{ old('success_message', $form->success_message) }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
            </div>
        </div>
        <label class="inline-flex items-center gap-2 mt-4 text-sm">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 dark:border-dark-600" @checked(old('is_active', $form->is_active))>
            <span>{{ __('Active') }}</span>
        </label>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">{{ __('Fields') }}</h2>
            <button type="button" id="addFormField" class="px-3 py-2 rounded-lg bg-primary-500 hover:bg-primary-600 text-white text-sm">{{ __('Add field') }}</button>
        </div>
        <div id="formFields" class="space-y-3"></div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white">{{ $submitLabel }}</button>
        <a href="{{ route('admin.forms.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-600">{{ __('Cancel') }}</a>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const initialFields = @json($initialFields);
        const root = document.getElementById('formFields');
        const addBtn = document.getElementById('addFormField');

        const fields = Array.isArray(initialFields) ? initialFields : [];
        const esc = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const render = () => {
            root.innerHTML = '';
            fields.forEach((field, index) => {
                const name = esc(field.name || '');
                const label = esc(field.label || '');
                const type = esc(field.type || 'text');
                const required = !!field.required;
                const row = document.createElement('div');
                row.className = 'grid gap-3 md:grid-cols-12 border border-gray-200 dark:border-dark-700 rounded-lg p-3';
                row.innerHTML = `
                    <div class="md:col-span-3">
                        <label class="block text-xs mb-1">{{ __('Field key') }}</label>
                        <input type="text" name="fields[${index}][name]" value="${name}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" placeholder="email">
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-xs mb-1">{{ __('Label') }}</label>
                        <input type="text" name="fields[${index}][label]" value="${label}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm" placeholder="{{ __('Email') }}">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs mb-1">{{ __('Type') }}</label>
                        <select name="fields[${index}][type]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                            <option value="text" ${type === 'text' ? 'selected' : ''}>{{ __('Text') }}</option>
                            <option value="email" ${type === 'email' ? 'selected' : ''}>{{ __('Email') }}</option>
                            <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>{{ __('Textarea') }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-end justify-between gap-2">
                        <label class="inline-flex items-center gap-1 text-xs">
                            <input type="checkbox" name="fields[${index}][required]" value="1" ${required ? 'checked' : ''}>
                            <span>{{ __('Required') }}</span>
                        </label>
                        <button type="button" data-remove="${index}" class="text-red-600 text-xs">{{ __('Remove') }}</button>
                    </div>
                `;
                row.querySelector('[data-remove]').addEventListener('click', () => {
                    fields.splice(index, 1);
                    render();
                });
                root.appendChild(row);
            });
        };

        addBtn?.addEventListener('click', () => {
            fields.push({name: '', label: '', type: 'text', required: false});
            render();
        });

        render();
    })();
</script>
@endpush

