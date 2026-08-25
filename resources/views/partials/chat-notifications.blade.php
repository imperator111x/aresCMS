@auth
    @if(\Illuminate\Support\Facades\Route::has('profiles.unread-summary'))
        <div id="chat-toast-host" class="fixed bottom-4 right-4 z-[60] flex flex-col gap-2 max-w-sm pointer-events-none"></div>
        <script>
        (function () {
            const summaryUrl = @json(route('profiles.unread-summary'));
            const inboxUrl = @json(route('profiles.inbox'));
            let lastTotal = parseInt(sessionStorage.getItem('chat_unread_total') || '0', 10) || 0;
            let polling = false;

            function updateBadges(total) {
                document.querySelectorAll('[data-chat-unread-badge]').forEach(function (el) {
                    if (total > 0) {
                        el.textContent = total > 99 ? '99+' : String(total);
                        el.classList.remove('hidden');
                        el.classList.add('inline-flex');
                    } else {
                        el.textContent = '';
                        el.classList.add('hidden');
                        el.classList.remove('inline-flex');
                    }
                });
            }

            function showToast(peerName, preview, chatUrl) {
                const host = document.getElementById('chat-toast-host');
                if (!host) return;
                const card = document.createElement('a');
                card.href = chatUrl;
                card.className = 'pointer-events-auto block rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 shadow-xl px-4 py-3 text-sm text-gray-800 dark:text-gray-100 hover:border-primary-500/50 transition-colors';
                card.innerHTML = '<p class="font-semibold text-gray-900 dark:text-white">' + escapeHtml(peerName) + '</p><p class="text-gray-600 dark:text-gray-400 truncate mt-0.5">' + escapeHtml(preview) + '</p>';
                host.appendChild(card);
                setTimeout(function () { card.remove(); }, 8000);
            }

            function escapeHtml(s) {
                const d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            function notifyBrowser(peerName, preview) {
                if (!('Notification' in window) || Notification.permission !== 'granted') return;
                if (document.visibilityState === 'visible' && window.location.pathname.indexOf('/friendships/') !== -1) return;
                try {
                    const n = new Notification(peerName, {
                        body: preview,
                        tag: 'chat-summary',
                        icon: @json(asset('favicon.ico')),
                    });
                    n.onclick = function () {
                        window.focus();
                        window.location.href = inboxUrl;
                        n.close();
                    };
                } catch (e) { /* ignore */ }
            }

            async function refresh() {
                if (polling) return;
                polling = true;
                try {
                    const res = await fetch(summaryUrl, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    const total = Number(data.total_unread) || 0;
                    updateBadges(total);

                    const conv = data.conversations || [];
                    if (total > lastTotal && conv.length) {
                        const first = conv[0];
                        if (document.visibilityState === 'hidden' || !window.location.pathname.includes('/friendships/')) {
                            notifyBrowser(first.peer_name, first.preview);
                            showToast(first.peer_name, first.preview, first.chat_url);
                        }
                    }

                    lastTotal = total;
                    sessionStorage.setItem('chat_unread_total', String(total));
                } catch (e) { /* ignore */ }
                finally { polling = false; }
            }

            window.chatNotifyRefresh = refresh;

            if ('Notification' in window && Notification.permission === 'default') {
                document.addEventListener('click', function requestNotifOnce() {
                    Notification.requestPermission().catch(function () {});
                    document.removeEventListener('click', requestNotifOnce);
                }, { once: true });
            }

            refresh();
            setInterval(refresh, 12000);
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) refresh();
            });
        })();
        </script>
    @endif
@endauth
