@php
    $notifJs = [
        'feedUrl' => route('admin.notifications.feed'),
        'markUrl' => route('admin.notifications.mark-read'),
        'clearUrl' => route('admin.notifications.clear-history'),
        'labels' => [
            'title' => __('Notifications'),
            'posts' => __('New posts'),
            'comments' => __('New comments'),
            'registrations' => __('New registrations'),
            'emptyPosts' => __('No posts yet'),
            'emptyComments' => __('No comments yet'),
            'emptyUsers' => __('No registrations yet'),
            'emptySinceClear' => __('Nothing new since you cleared the list.'),
            'onlySinceClear' => __('Only activity after clearing is shown here.'),
            'clearHistory' => __('Clear history'),
            'clearHistoryConfirm' => __('Clear notification history? The list will stay empty until new posts, comments or registrations arrive.'),
            'newBadge' => __('New'),
            'loading' => __('Loading...'),
            'published' => __('Published'),
            'draft' => __('Draft'),
            'onArticle' => __('On article'),
        ],
    ];
@endphp

<div
    class="relative"
    x-data="adminNotificationBell({{ \Illuminate\Support\Js::from($notifJs) }}, {{ (int) ($adminNotificationCount ?? 0) }})"
>
    <button
        type="button"
        @click="toggle()"
        class="p-2 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-dark-700 relative"
        :aria-expanded="open"
        aria-haspopup="true"
    >
        <i class="fas fa-bell"></i>
        <span
            x-show="badge > 0"
            x-cloak
            class="absolute -top-0.5 -right-0.5 min-w-[1.25rem] h-5 px-1 flex items-center justify-center bg-red-500 rounded-full text-[10px] font-bold text-white leading-none"
            x-text="badge > 9 ? '9+' : badge"
        ></span>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-cloak
        @click.away="close()"
        @keydown.escape.window="if (open) close()"
        class="absolute right-0 mt-2 w-[min(100vw-2rem,22rem)] max-h-[min(80vh,28rem)] overflow-y-auto rounded-xl border border-gray-200 dark:border-dark-700 bg-white dark:bg-dark-800 shadow-xl z-[60]"
    >
        <div class="sticky top-0 z-10 flex items-center justify-between gap-2 px-4 py-3 border-b border-gray-100 dark:border-dark-700 bg-white/95 dark:bg-dark-800/95 backdrop-blur-sm">
            <span class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="config.labels.title"></span>
            <div class="flex items-center shrink-0 gap-0.5">
                <button
                    type="button"
                    @click.stop="clearHistory()"
                    class="p-2 rounded-lg text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-dark-700"
                    :title="config.labels.clearHistory"
                >
                    <i class="fas fa-trash-alt text-sm"></i>
                </button>
                <button type="button" @click="close()" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-dark-700">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>

        <template x-if="loading">
            <div class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                <span x-text="config.labels.loading"></span>
            </div>
        </template>

        <template x-if="!loading && data">
            <div class="py-2">
                <p
                    x-show="data.cleared_since"
                    class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-dark-700"
                    x-text="config.labels.onlySinceClear"
                ></p>
                <!-- Beiträge -->
                <div class="px-3 pb-2">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 px-1 py-2" x-text="config.labels.posts"></div>
                    <template x-if="!data.posts || data.posts.length === 0">
                        <p class="px-2 py-2 text-sm text-gray-500 dark:text-gray-400" x-text="data.cleared_since ? config.labels.emptySinceClear : config.labels.emptyPosts"></p>
                    </template>
                    <template x-if="data.posts && data.posts.length > 0">
                        <div class="space-y-0.5">
                            <template x-for="item in data.posts" :key="'p-' + item.id">
                                <a
                                    :href="item.url"
                                    class="flex flex-col gap-1 rounded-lg px-2 py-2 hover:bg-gray-50 dark:hover:bg-dark-700/80 text-left"
                                    @click="close()"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2" x-text="item.title"></span>
                                        <span
                                            x-show="item.is_new"
                                            class="shrink-0 text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300"
                                            x-text="config.labels.newBadge"
                                        ></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span
                                            class="font-medium"
                                            :class="item.published ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400'"
                                            x-text="item.published ? config.labels.published : config.labels.draft"
                                        ></span>
                                        <span>·</span>
                                        <span x-text="item.created_at_human"></span>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="border-t border-gray-100 dark:border-dark-700 my-1"></div>

                <!-- Kommentare -->
                <div class="px-3 pb-2">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 px-1 py-2" x-text="config.labels.comments"></div>
                    <template x-if="!data.comments || data.comments.length === 0">
                        <p class="px-2 py-2 text-sm text-gray-500 dark:text-gray-400" x-text="data.cleared_since ? config.labels.emptySinceClear : config.labels.emptyComments"></p>
                    </template>
                    <template x-if="data.comments && data.comments.length > 0">
                        <div class="space-y-0.5">
                            <template x-for="item in data.comments" :key="'c-' + item.id">
                                <a
                                    :href="item.url"
                                    class="flex flex-col gap-1 rounded-lg px-2 py-2 hover:bg-gray-50 dark:hover:bg-dark-700/80 text-left"
                                    @click="close()"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="text-sm text-gray-800 dark:text-gray-200 line-clamp-2" x-text="item.excerpt"></span>
                                        <span
                                            x-show="item.is_new"
                                            class="shrink-0 text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300"
                                            x-text="config.labels.newBadge"
                                        ></span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-medium text-gray-700 dark:text-gray-300" x-text="item.user_name"></span>
                                        <span> · </span>
                                        <span x-text="config.labels.onArticle"></span>
                                        <span class="italic truncate" x-text="' ' + item.news_title"></span>
                                    </div>
                                    <div class="text-xs text-gray-400" x-text="item.created_at_human"></div>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="border-t border-gray-100 dark:border-dark-700 my-1"></div>

                <!-- Registrierungen -->
                <div class="px-3 pb-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 px-1 py-2" x-text="config.labels.registrations"></div>
                    <template x-if="!data.users || data.users.length === 0">
                        <p class="px-2 py-2 text-sm text-gray-500 dark:text-gray-400" x-text="data.cleared_since ? config.labels.emptySinceClear : config.labels.emptyUsers"></p>
                    </template>
                    <template x-if="data.users && data.users.length > 0">
                        <div class="space-y-0.5">
                            <template x-for="item in data.users" :key="'u-' + item.id">
                                <a
                                    :href="item.url"
                                    class="flex items-start justify-between gap-2 rounded-lg px-2 py-2 hover:bg-gray-50 dark:hover:bg-dark-700/80 text-left"
                                    @click="close()"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="item.name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="item.email"></p>
                                        <p class="text-xs text-gray-400 mt-0.5" x-text="item.created_at_human"></p>
                                    </div>
                                    <span
                                        x-show="item.is_new"
                                        class="shrink-0 text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 h-fit"
                                        x-text="config.labels.newBadge"
                                    ></span>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('adminNotificationBell', (config, initialBadge) => ({
                    open: false,
                    loading: false,
                    data: null,
                    badge: initialBadge,
                    config,

                    toggle() {
                        this.open = !this.open;
                        if (this.open) {
                            this.refresh();
                        }
                    },

                    close() {
                        this.open = false;
                    },

                    async clearHistory() {
                        if (!window.confirm(this.config.labels.clearHistoryConfirm)) {
                            return;
                        }
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        try {
                            const r = await fetch(config.clearUrl, {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token || '',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                                body: '{}',
                            });
                            if (r.ok) {
                                this.data = await r.json();
                                this.badge = 0;
                            }
                        } catch (e) {
                            /* ignore */
                        }
                    },

                    async refresh() {
                        this.loading = true;
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        try {
                            const r = await fetch(config.feedUrl, {
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            });
                            if (!r.ok) {
                                throw new Error('feed failed');
                            }
                            this.data = await r.json();
                            this.badge = this.data.unread_count ?? 0;

                            const mark = await fetch(config.markUrl, {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token || '',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                                body: '{}',
                            });
                            if (mark.ok) {
                                this.badge = 0;
                            }
                        } catch (e) {
                            this.data = { posts: [], comments: [], users: [] };
                        } finally {
                            this.loading = false;
                        }
                    },
                }));
            });
        </script>
    @endpush
@endonce
