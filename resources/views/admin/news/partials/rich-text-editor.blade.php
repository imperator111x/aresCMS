{{--
  Quill 1.x (BSD-3-Clause) – free, client-side only, loaded from jsDelivr CDN.
  Expects: $initialHtml (string) – raw HTML for the editor
--}}
@php
    $initialHtml = $initialHtml ?? '';
@endphp

<textarea name="content" id="news-content-input" rows="1" class="sr-only" aria-hidden="true">{{ $initialHtml }}</textarea>
<div class="mb-2 flex flex-wrap gap-2">
    <button type="button" id="news-insert-token-button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-primary-300 text-primary-600 dark:text-primary-400 text-xs">
        <i class="fas fa-code"></i>
        {{ __('Insert token') }}
    </button>
</div>
<div id="news-quill-editor" class="news-quill-editor rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-900 min-h-[300px] overflow-hidden"></div>

<div id="news-token-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/50" data-news-token-close></div>
    <div class="relative z-[101] min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 p-5 shadow-2xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Insert token') }}</h3>
            <div>
                <label class="block text-xs mb-1">{{ __('Select token') }}</label>
                <select id="news-token-select" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 px-3 py-2 text-sm">
                    <option value="@{{team_list}}">{{ __('Team list') }} (@{{team_list}})</option>
                    <option value="@{{news_boxes_3}}">{{ __('News boxes (3)') }} (@{{news_boxes_3}})</option>
                    <option value="@{{news_boxes_6}}">{{ __('News boxes (6)') }} (@{{news_boxes_6}})</option>
                    <option value="@{{current_user_name}}">{{ __('Current user name (or visitor)') }} (@{{current_user_name}})</option>
                </select>
            </div>
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 text-sm" data-news-token-close>{{ __('Cancel') }}</button>
                <button type="button" id="news-token-insert-confirm" class="px-3 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm">{{ __('Insert token') }}</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <style>
        .news-quill-editor .ql-toolbar {
            border-color: rgb(209 213 219) !important;
            border-radius: 0.5rem 0.5rem 0 0;
            background: rgb(249 250 251);
        }
        .dark .news-quill-editor .ql-toolbar {
            border-color: rgb(71 85 105) !important;
            background: rgb(30 41 59);
        }
        .dark .news-quill-editor .ql-toolbar .ql-stroke {
            stroke: rgb(203 213 225);
        }
        .dark .news-quill-editor .ql-toolbar .ql-fill {
            fill: rgb(203 213 225);
        }
        .dark .news-quill-editor .ql-toolbar .ql-picker-label {
            color: rgb(203 213 225);
        }
        .dark .news-quill-editor .ql-toolbar button:hover .ql-stroke,
        .dark .news-quill-editor .ql-toolbar button.ql-active .ql-stroke {
            stroke: rgb(96 165 250);
        }
        .dark .news-quill-editor .ql-toolbar button:hover .ql-fill,
        .dark .news-quill-editor .ql-toolbar button.ql-active .ql-fill {
            fill: rgb(96 165 250);
        }
        .news-quill-editor .ql-container {
            border-color: rgb(209 213 219) !important;
            border-radius: 0 0 0.5rem 0.5rem;
            font-size: 1rem;
            min-height: 240px;
        }
        .dark .news-quill-editor .ql-container {
            border-color: rgb(71 85 105) !important;
            background: rgb(15 23 42);
            color: rgb(241 245 249);
        }
        .dark .news-quill-editor .ql-editor.ql-blank::before {
            color: rgb(100 116 139);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var textarea = document.getElementById('news-content-input');
            var mount = document.getElementById('news-quill-editor');
            var tokenButton = document.getElementById('news-insert-token-button');
            var tokenModal = document.getElementById('news-token-modal');
            var tokenSelect = document.getElementById('news-token-select');
            var tokenInsertConfirm = document.getElementById('news-token-insert-confirm');
            if (!textarea || !mount || typeof Quill === 'undefined') return;

            var quill = new Quill('#news-quill-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'clean']
                    ]
                },
                placeholder: @json(__('Write your article…'))
            });

            var initial = textarea.value || '';
            if (initial.trim() !== '') {
                quill.root.innerHTML = initial;
            }

            var form = textarea.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    textarea.value = quill.root.innerHTML;
                });
            }

            var openTokenModal = function () {
                if (!tokenModal) return;
                tokenModal.classList.remove('hidden');
                if (tokenSelect) tokenSelect.focus();
            };
            var closeTokenModal = function () {
                if (!tokenModal) return;
                tokenModal.classList.add('hidden');
            };
            var insertSelectedToken = function () {
                var token = (tokenSelect && tokenSelect.value ? tokenSelect.value : '@{{team_list}}').trim();
                if (!token) token = '@{{team_list}}';
                var range = quill.getSelection(true);
                var insertAt = range ? range.index : quill.getLength();
                quill.insertText(insertAt, token);
                quill.setSelection(insertAt + token.length, 0);
                closeTokenModal();
            };

            tokenButton && tokenButton.addEventListener('click', openTokenModal);
            tokenModal && tokenModal.querySelectorAll('[data-news-token-close]').forEach(function (node) {
                node.addEventListener('click', closeTokenModal);
            });
            tokenInsertConfirm && tokenInsertConfirm.addEventListener('click', insertSelectedToken);
            document.addEventListener('keydown', function (event) {
                if (!tokenModal || tokenModal.classList.contains('hidden')) return;
                if (event.key === 'Escape') closeTokenModal();
                if (event.key === 'Enter' && event.ctrlKey) insertSelectedToken();
            });
        });
    </script>
@endpush
