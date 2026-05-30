@php
    $variant = $variant ?? 'desktop';
    $adminSearchJs = [
        'url' => route('admin.search.suggestions'),
        'searchAllUrl' => route('admin.search'),
        'labels' => [
            'news' => __('News'),
            'users' => __('Users'),
            'noSuggestions' => __('No suggestions.'),
            'showAll' => __('Show all results'),
            'loading' => __('Searching...'),
            'published' => __('Published'),
            'draft' => __('Draft'),
        ],
    ];
    $wrapperClass = $variant === 'desktop'
        ? 'relative hidden md:block flex-1 max-w-md min-w-0'
        : 'relative w-full';
@endphp

<div
    class="{{ $wrapperClass }}"
    x-data="adminSearchSuggest({{ \Illuminate\Support\Js::from($adminSearchJs) }})"
>
    <form
        action="{{ route('admin.search') }}"
        method="GET"
        class="flex items-center gap-2 bg-gray-100 dark:bg-dark-700 rounded-lg px-3 py-2"
        @submit="closePanel()"
    >
        <i class="fas fa-search text-gray-400 shrink-0"></i>
        <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            x-ref="searchInput"
            autocomplete="off"
            placeholder="{{ __('Search...') }}"
            @input="onInput()"
            @focus="onFocus()"
            @keydown.escape.prevent="closePanel()"
            class="bg-transparent border-none outline-none text-sm w-full min-w-0 text-gray-700 dark:text-gray-300 placeholder-gray-400"
        >
    </form>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-cloak
        class="absolute left-0 right-0 top-full mt-1 z-[60] rounded-xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 shadow-xl max-h-[min(70vh,22rem)] overflow-y-auto"
        @click.away="closePanel()"
    >
        <template x-if="loading">
            <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                <span x-text="config.labels.loading"></span>
            </div>
        </template>

        <template x-if="!loading && hasSearched && news.length === 0 && users.length === 0">
            <div class="p-4 text-sm">
                <p class="text-gray-500 dark:text-gray-400 mb-3" x-text="config.labels.noSuggestions"></p>
                <a
                    :href="showAllUrl()"
                    class="inline-flex items-center gap-2 text-primary-600 dark:text-primary-400 font-medium hover:underline"
                    @click="closePanel()"
                >
                    <span x-text="config.labels.showAll"></span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </template>

        <template x-if="!loading && (news.length > 0 || users.length > 0)">
            <div class="py-2">
                <template x-if="news.length > 0">
                    <div class="mb-1">
                        <div class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500" x-text="config.labels.news"></div>
                        <template x-for="item in news" :key="'n-' + item.id">
                            <a
                                :href="item.url"
                                class="flex items-center justify-between gap-3 px-3 py-2.5 hover:bg-gray-50 dark:hover:bg-dark-700/80 text-left"
                                @click="closePanel()"
                            >
                                <span class="text-sm font-medium text-gray-900 dark:text-white truncate min-w-0" x-text="item.title"></span>
                                <span
                                    class="shrink-0 text-[10px] uppercase font-semibold px-1.5 py-0.5 rounded"
                                    :class="item.published ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'"
                                    x-text="item.published ? config.labels.published : config.labels.draft"
                                ></span>
                            </a>
                        </template>
                    </div>
                </template>

                <template x-if="users.length > 0">
                    <div>
                        <div class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500" x-text="config.labels.users"></div>
                        <template x-for="item in users" :key="'u-' + item.id">
                            <a
                                :href="item.url"
                                class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50 dark:hover:bg-dark-700/80 text-left"
                                @click="closePanel()"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="item.name"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="item.email"></p>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 text-xs shrink-0"></i>
                            </a>
                        </template>
                    </div>
                </template>

                <div class="border-t border-gray-100 dark:border-dark-700 mt-1 pt-1 px-3 pb-2">
                    <a
                        :href="showAllUrl()"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:underline"
                        @click="closePanel()"
                        x-text="config.labels.showAll"
                    ></a>
                </div>
            </div>
        </template>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('adminSearchSuggest', (config) => ({
                    config,
                    news: [],
                    users: [],
                    open: false,
                    loading: false,
                    hasSearched: false,
                    debounceTimer: null,
                    requestId: 0,

                    onInput() {
                        clearTimeout(this.debounceTimer);
                        const input = this.$refs.searchInput;
                        const q = input ? input.value.trim() : '';
                        if (q.length < 2) {
                            this.news = [];
                            this.users = [];
                            this.hasSearched = false;
                            this.open = false;
                            this.loading = false;
                            return;
                        }
                        this.debounceTimer = setTimeout(() => this.load(q), 200);
                    },

                    onFocus() {
                        const input = this.$refs.searchInput;
                        const q = input ? input.value.trim() : '';
                        if (q.length < 2) {
                            return;
                        }
                        if (!this.hasSearched && !this.loading) {
                            this.load(q);
                        } else {
                            this.open = true;
                        }
                    },

                    async load(q) {
                        const id = ++this.requestId;
                        this.loading = true;
                        this.open = true;
                        this.hasSearched = false;
                        try {
                            const res = await fetch(config.url + '?q=' + encodeURIComponent(q), {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            });
                            if (id !== this.requestId) {
                                return;
                            }
                            const data = await res.json();
                            if (id !== this.requestId) {
                                return;
                            }
                            this.news = data.news || [];
                            this.users = data.users || [];
                            this.hasSearched = true;
                        } catch (e) {
                            if (id === this.requestId) {
                                this.news = [];
                                this.users = [];
                                this.hasSearched = true;
                            }
                        } finally {
                            if (id === this.requestId) {
                                this.loading = false;
                            }
                        }
                    },

                    closePanel() {
                        this.open = false;
                    },

                    showAllUrl() {
                        const input = this.$refs.searchInput;
                        const q = input ? input.value.trim() : '';
                        const base = config.searchAllUrl;
                        return q ? (base + '?q=' + encodeURIComponent(q)) : base;
                    },
                }));
            });
        </script>
    @endpush
@endonce
