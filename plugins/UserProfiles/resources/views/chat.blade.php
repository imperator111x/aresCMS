@extends('layouts.app')

@section('title', __('Chat with :name', ['name' => $peer->name]))

@section('content')
@php
    $chatCfg = [
        'friendshipId' => (int) $friendship->id,
        'myUserId' => (int) auth()->id(),
        'peerUserId' => (int) $peer->id,
        'peerName' => $peer->name,
        'myName' => auth()->user()->name,
        'fetchUrl' => route('profiles.messages.fetch', $friendship),
        'sendUrl' => route('profiles.messages.store', $friendship),
        'keyUrl' => route('profiles.e2e.key'),
        'e2eStatusUrl' => $e2eStatusUrl ?? url('/friendships/'.$friendship->id.'/e2e-status'),
        'clearMessagesUrl' => $clearMessagesUrl ?? url('/friendships/'.$friendship->id.'/messages/clear'),
        'markReadUrl' => $markReadUrl ?? route('profiles.messages.mark-read', $friendship),
        'inboxUrl' => $inboxUrl ?? route('profiles.inbox'),
        'csrf' => csrf_token(),
        'peerPublicKeyJwk' => $peerPublicKeyJwk,
        'myPublicKeyJwk' => $myPublicKeyJwk,
        'e2eCipherById' => $messages->filter(fn ($m) => $m->is_e2e)->mapWithKeys(fn ($m) => [(string) $m->id => $m->body])->all(),
    ];
@endphp

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col min-h-[70vh]" id="profile-chat-root">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3 min-w-0">
            @if($peer->avatar)
                <img src="{{ asset('storage/'.$peer->avatar) }}" alt="" class="w-12 h-12 rounded-xl object-cover shrink-0">
            @else
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white font-bold shrink-0">
                    {{ strtoupper(substr($peer->name, 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white truncate">{{ $peer->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Direct messages') }}</p>
            </div>
        </div>
        <div class="flex flex-col items-end gap-1 shrink-0 text-right">
            <a href="{{ route('profiles.inbox') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-500"><i class="fas fa-inbox mr-1"></i>{{ __('All messages') }}</a>
            <a href="{{ route('profiles.show', $peer) }}" class="text-sm text-primary-500 hover:text-primary-600">{{ __('Profile') }}</a>
            <button type="button" id="clear-chat-btn" class="text-sm text-red-600 dark:text-red-400 hover:underline">{{ __('Delete conversation') }}</button>
        </div>
    </div>

    <div id="e2e-banner" class="hidden mb-4 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-900 dark:text-amber-100"></div>

    <div id="e2e-recovery" class="hidden mb-4 rounded-xl border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/40 px-4 py-3 text-sm text-red-900 dark:text-red-100 space-y-3">
        <p id="e2e-recovery-text"></p>
        <div class="flex flex-wrap gap-2">
            <button type="button" id="e2e-import-btn" class="px-3 py-2 rounded-lg bg-white dark:bg-dark-800 border border-red-200 dark:border-red-800 text-sm font-medium">{{ __('Import backup…') }}</button>
            <button type="button" id="e2e-new-key-btn" class="px-3 py-2 rounded-lg bg-red-700 text-white text-sm font-medium hover:bg-red-800">{{ __('Create new encryption key') }}</button>
        </div>
    </div>

    <div id="messages-scroll" class="flex-1 overflow-y-auto space-y-3 mb-4 max-h-[min(56vh,520px)] pr-1">
        @foreach($messages as $message)
            @php
                $mine = (int) $message->sender_id === (int) auth()->id();
                $trimBody = trim($message->body);
                $env = (!$message->is_e2e && str_starts_with($trimBody, '{"v":1')) ? json_decode($trimBody, true) : null;
            @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}" data-msg-row="{{ $message->id }}">
                <div class="max-w-[85%] rounded-2xl px-4 py-2 {{ $mine ? 'bg-gradient-to-r from-primary-500 to-purple-600 text-white' : 'bg-gray-100 dark:bg-dark-700 text-gray-900 dark:text-white' }}">
                    @unless($mine)
                        <p class="text-xs opacity-70 mb-1">{{ $message->sender->name ?? '' }}</p>
                    @endunless
                    @if($message->is_e2e)
                        <div data-e2e-wrap="1" data-msg-id="{{ $message->id }}" class="space-y-1 min-w-0 max-w-full">
                            <p class="text-sm opacity-90">{{ __('Decrypting…') }}</p>
                        </div>
                        <p class="text-[10px] mt-1 opacity-70"><i class="fas fa-lock"></i> {{ __('End-to-end encrypted') }}</p>
                    @elseif(is_array($env) && ($env['v'] ?? null) === 1 && ($env['k'] ?? '') === 'img')
                        <div class="space-y-1 min-w-0">
                            <img src="data:{{ $env['m'] }};base64,{{ $env['d'] }}" alt="" class="max-w-full rounded-lg max-h-80 object-contain" loading="lazy">
                            @if(!empty($env['c']))
                                <p class="msg-text text-sm whitespace-pre-wrap break-words">{{ $env['c'] }}</p>
                            @endif
                        </div>
                    @elseif(is_array($env) && ($env['v'] ?? null) === 1 && ($env['k'] ?? '') === 'text')
                        <p class="msg-text text-sm whitespace-pre-wrap break-words">{{ $env['t'] ?? '' }}</p>
                    @else
                        <p class="msg-text text-sm whitespace-pre-wrap break-words">{{ $message->body }}</p>
                    @endif
                    <p class="text-[10px] mt-1 opacity-60">{{ $message->created_at?->format('d.m.Y H:i') }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <form id="chat-form" class="space-y-3 border-t border-gray-200 dark:border-dark-700 pt-4">
        <div class="flex flex-wrap gap-4 items-center text-sm">
            <label class="inline-flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300">
                <input type="radio" name="chat_mode" value="standard" class="rounded-full border-gray-400 text-primary-500 focus:ring-primary-500">
                {{ __('Standard chat') }}
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300">
                <input type="radio" name="chat_mode" value="e2e" class="rounded-full border-gray-400 text-primary-500 focus:ring-primary-500">
                {{ __('End-to-end encrypted') }}
            </label>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __("Anyone can open this chat. End-to-end sending needs the other person's key too — they must open this chat once. Standard chat always works.") }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            <button type="button" id="e2e-export-btn" class="text-primary-600 dark:text-primary-400 hover:underline">{{ __('Export encryption backup…') }}</button>
            <span class="mx-1 text-gray-400">·</span>
            <button type="button" id="e2e-import-inline-btn" class="text-primary-600 dark:text-primary-400 hover:underline">{{ __('Import backup…') }}</button>
        </p>

        <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-end">
            <div class="flex-1 flex flex-col gap-2 min-w-0 relative">
                <div id="chat-image-preview" class="hidden flex items-start gap-2 p-2 rounded-lg bg-gray-100 dark:bg-dark-700 border border-gray-200 dark:border-dark-600">
                    <img id="chat-image-preview-img" src="" alt="" class="h-20 w-auto rounded-md object-cover max-w-[40%]">
                    <div class="flex flex-col gap-1 min-w-0">
                        <span id="chat-image-preview-name" class="text-xs text-gray-600 dark:text-gray-300 truncate"></span>
                        <button type="button" id="chat-image-remove" class="text-xs text-red-600 dark:text-red-400 hover:underline text-left">{{ __('Remove image') }}</button>
                    </div>
                </div>
                <textarea id="chat-input" rows="2" maxlength="60000"
                    class="w-full rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-white px-4 py-3 focus:ring-2 focus:ring-primary-500"
                    placeholder="{{ __('Write a message…') }}"></textarea>
                <div class="flex flex-wrap items-center gap-2 relative">
                    <button type="button" id="chat-emoji-btn" class="h-10 w-10 shrink-0 inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-dark-600 text-xl leading-none bg-white dark:bg-dark-800" title="{{ __('Emoji') }}" aria-expanded="false">😊</button>
                    <div id="chat-emoji-panel" class="hidden absolute bottom-full left-0 mb-1 z-30 p-2 max-w-[min(100%,20rem)] max-h-48 overflow-y-auto rounded-xl border border-gray-200 dark:border-dark-600 bg-white dark:bg-dark-800 shadow-lg flex flex-wrap gap-1"></div>
                    <button type="button" id="chat-image-btn" class="h-10 w-10 shrink-0 inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-700 dark:text-gray-200" title="{{ __('Image') }}" aria-label="{{ __('Image') }}"><i class="fas fa-image"></i></button>
                    <input type="file" id="chat-image-input" accept="image/*" class="hidden" tabindex="-1">
                </div>
            </div>
            <button type="submit" class="px-5 py-3 rounded-xl bg-gradient-to-r from-primary-500 to-purple-500 text-white font-semibold shrink-0 self-end">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </form>
    <input type="file" id="e2e-import-file" accept="application/json,.json" class="hidden" tabindex="-1">
</div>

<script>
(function () {
    const cfg = @json($chatCfg);
    const storageKeyPriv = 'userprofiles_e2e_v2_'+ cfg.myUserId;
    const chatModeStorageKey = 'userprofiles_chat_mode_'+ cfg.friendshipId;
    const scrollEl = document.getElementById('messages-scroll');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const banner = document.getElementById('e2e-banner');
    let maxId = {{ $messages->max('id') ?? 0 }};

    let chatKeyPair = null;
    let pendingChatImage = null;
    let userNearBottom = true;

    function isNearBottom(el, threshold) {
        if (!el) return true;
        return el.scrollHeight - el.scrollTop - el.clientHeight < (threshold || 120);
    }

    if (scrollEl) {
        scrollEl.addEventListener('scroll', function () {
            userNearBottom = isNearBottom(scrollEl, 120);
        });
    }

    function scrollToBottomIfNeeded(force) {
        if (!scrollEl) return;
        if (force || userNearBottom) {
            scrollEl.scrollTop = scrollEl.scrollHeight;
            userNearBottom = true;
        }
    }

    async function markConversationRead(maxId) {
        if (!cfg.markReadUrl) return;
        try {
            const fd = new FormData();
            fd.append('_token', cfg.csrf);
            if (maxId) fd.append('max_message_id', String(maxId));
            await fetch(cfg.markReadUrl, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-CSRF-TOKEN': cfg.csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            if (window.chatNotifyRefresh) window.chatNotifyRefresh();
        } catch (e) { /* ignore */ }
    }

    function notifyIncomingMessage(m) {
        if (Number(m.sender_id) === cfg.myUserId) return;
        const title = cfg.peerName;
        const body = m.preview || @json(__('New message'));
        if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
            try {
                const n = new Notification(title, { body: body, tag: 'chat-' + cfg.friendshipId });
                n.onclick = function () { window.focus(); n.close(); };
            } catch (e) { /* ignore */ }
        }
        if (!document.hidden) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const o = ctx.createOscillator();
            const g = ctx.createGain();
            o.connect(g);
            g.connect(ctx.destination);
            o.frequency.value = 880;
            g.gain.value = 0.04;
            o.start();
            setTimeout(function () { o.stop(); ctx.close(); }, 120);
        } catch (e) { /* ignore */ }
    }

    (function applyStoredChatMode() {
        const rStd = form.querySelector('input[name="chat_mode"][value="standard"]');
        const rE2e = form.querySelector('input[name="chat_mode"][value="e2e"]');
        const saved = localStorage.getItem(chatModeStorageKey);
        if (saved === 'e2e' && rE2e) {
            rE2e.checked = true;
        } else if (rStd) {
            rStd.checked = true;
        }
    })();
    form.querySelectorAll('input[name="chat_mode"]').forEach(function (el) {
        el.addEventListener('change', function () {
            localStorage.setItem(chatModeStorageKey, el.value);
        });
    });

    const clearBtn = document.getElementById('clear-chat-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', async function () {
            if (!confirm(@json(__('Delete the entire conversation for both users? This cannot be undone.')))) return;
            try {
                const fd = new FormData();
                fd.append('_token', cfg.csrf);
                const res = await fetch(cfg.clearMessagesUrl, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-CSRF-TOKEN': cfg.csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    alert(@json(__('Could not delete chat.')));
                    return;
                }
                if (scrollEl) scrollEl.innerHTML = '';
                maxId = 0;
                alert(@json(__('Conversation deleted.')));
            } catch (err) {
                console.error(err);
                alert(@json(__('Could not delete chat.')));
            }
        });
    }

    function ecPublicJwkFromPrivateJwk(privJwk) {
        return {
            kty: 'EC',
            crv: privJwk.crv,
            x: privJwk.x,
            y: privJwk.y,
            ext: true,
        };
    }

    function parseStoredIdentity(raw) {
        if (!raw) return null;
        try {
            const parsed = JSON.parse(raw);
            if (parsed && parsed.v === 2 && parsed.priv && parsed.pub) {
                return { privJwk: parsed.priv, pubJwk: parsed.pub };
            }
            if (parsed && parsed.kty === 'EC' && parsed.d) {
                return { privJwk: parsed, pubJwk: ecPublicJwkFromPrivateJwk(parsed) };
            }
        } catch (e) {}
        return null;
    }

    async function syncPublicKeyToServer(pubJwkObj) {
        const bodyStr = typeof pubJwkObj === 'string' ? pubJwkObj : JSON.stringify(pubJwkObj);
        const res = await fetch(cfg.keyUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ public_key_jwk: bodyStr }),
            credentials: 'same-origin',
        });
        return res.ok;
    }

    function b64(buf) {
        const u8 = buf instanceof Uint8Array ? buf : new Uint8Array(buf);
        let bin = '';
        for (let i = 0; i < u8.byteLength; i++) bin += String.fromCharCode(u8[i]);
        return btoa(bin);
    }
    function b64decode(str) {
        const bin = atob(str);
        const bytes = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
        return bytes.buffer;
    }

    async function sha256(parts) {
        const buf = await crypto.subtle.digest('SHA-256', parts);
        return buf;
    }

    async function deriveAesKey(friendshipId, sharedSecretBuffer) {
        const enc = new TextEncoder();
        const salt = enc.encode('profile-chat:' + friendshipId);
        const first = new Uint8Array(sharedSecretBuffer.byteLength + salt.byteLength);
        first.set(new Uint8Array(sharedSecretBuffer), 0);
        first.set(salt, sharedSecretBuffer.byteLength);
        const raw = await sha256(first);
        return crypto.subtle.importKey('raw', raw.slice(0, 32), { name: 'AES-GCM', length: 256 }, false, ['encrypt', 'decrypt']);
    }

    const legacyE2eSessionKey = 'userprofiles_e2e_priv_'+ cfg.myUserId;

    function persistIdentityLocal(blobStr) {
        localStorage.setItem(storageKeyPriv, blobStr);
        sessionStorage.removeItem(storageKeyPriv);
        sessionStorage.removeItem(legacyE2eSessionKey);
    }

    function importIdentityFromJsonText(text) {
        let data;
        try {
            data = JSON.parse(text);
        } catch (err) {
            alert(@json(__('Invalid backup file.')));
            return false;
        }
        let inner = data.identity != null ? data.identity : data;
        const innerStr = typeof inner === 'string' ? inner : JSON.stringify(inner);
        const parsed = parseStoredIdentity(innerStr);
        if (!parsed) {
            alert(@json(__('Invalid backup file.')));
            return false;
        }
        if (data.userId != null && Number(data.userId) !== cfg.myUserId) {
            alert(@json(__('This backup is for a different user account.')));
            return false;
        }
        persistIdentityLocal(JSON.stringify({ v: 2, priv: parsed.privJwk, pub: parsed.pubJwk }));
        return true;
    }

    function exportEncryptionBackup() {
        const raw = localStorage.getItem(storageKeyPriv);
        if (!raw) {
            alert(@json(__('No local encryption key to export.')));
            return;
        }
        let identity;
        try {
            identity = JSON.parse(raw);
        } catch (e) {
            return;
        }
        const payload = JSON.stringify({
            version: 1,
            userId: cfg.myUserId,
            exportedAt: new Date().toISOString(),
            identity: identity,
        }, null, 2);
        const blob = new Blob([payload], { type: 'application/json' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'userprofiles-e2e-backup-'+ cfg.myUserId + '.json';
        a.click();
        URL.revokeObjectURL(a.href);
    }

    async function createNewEncryptionKey() {
        if (!confirm(@json(__('This creates a new encryption key on this device. End-to-end messages encrypted with your previous key will remain unreadable here. Continue?')))) return;
        const kp = await crypto.subtle.generateKey({ name: 'ECDH', namedCurve: 'P-256' }, true, ['deriveBits']);
        const privJwk = await crypto.subtle.exportKey('jwk', kp.privateKey);
        const pubJwk = await crypto.subtle.exportKey('jwk', kp.publicKey);
        persistIdentityLocal(JSON.stringify({ v: 2, priv: privJwk, pub: pubJwk }));
        await syncPublicKeyToServer(pubJwk);
        location.reload();
    }

    async function loadOrCreateIdentity() {
        let stored = localStorage.getItem(storageKeyPriv) || sessionStorage.getItem(storageKeyPriv) || sessionStorage.getItem(legacyE2eSessionKey);
        if (stored && !localStorage.getItem(storageKeyPriv)) {
            localStorage.setItem(storageKeyPriv, stored);
        }
        sessionStorage.removeItem(storageKeyPriv);
        sessionStorage.removeItem(legacyE2eSessionKey);
        stored = localStorage.getItem(storageKeyPriv);

        const parsed = parseStoredIdentity(stored);
        if (parsed) {
            persistIdentityLocal(JSON.stringify({ v: 2, priv: parsed.privJwk, pub: parsed.pubJwk }));
            const privateKey = await crypto.subtle.importKey('jwk', parsed.privJwk, { name: 'ECDH', namedCurve: 'P-256' }, false, ['deriveBits']);
            const publicKey = await crypto.subtle.importKey('jwk', parsed.pubJwk, { name: 'ECDH', namedCurve: 'P-256' }, true, []);
            await syncPublicKeyToServer(parsed.pubJwk);
            return { ok: true, privateKey, publicKey };
        }

        const serverHasKey = !!(cfg.myPublicKeyJwk && String(cfg.myPublicKeyJwk).length > 2);
        if (serverHasKey) {
            return { ok: false, needsRecovery: true };
        }

        const kp = await crypto.subtle.generateKey({ name: 'ECDH', namedCurve: 'P-256' }, true, ['deriveBits']);
        const privJwk = await crypto.subtle.exportKey('jwk', kp.privateKey);
        const pubJwk = await crypto.subtle.exportKey('jwk', kp.publicKey);
        persistIdentityLocal(JSON.stringify({ v: 2, priv: privJwk, pub: pubJwk }));
        const privateKey = await crypto.subtle.importKey('jwk', privJwk, { name: 'ECDH', namedCurve: 'P-256' }, false, ['deriveBits']);
        const publicKey = await crypto.subtle.importKey('jwk', pubJwk, { name: 'ECDH', namedCurve: 'P-256' }, true, []);
        await syncPublicKeyToServer(pubJwk);
        return { ok: true, privateKey, publicKey };
    }

    async function refreshE2eConfig(keyPair) {
        try {
            const res = await fetch(cfg.e2eStatusUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const data = await res.json();
            const prevPeer = cfg.peerPublicKeyJwk;
            cfg.peerPublicKeyJwk = data.peer_public_key_jwk || null;
            cfg.myPublicKeyJwk = data.my_public_key_jwk || null;
            if (prevPeer !== cfg.peerPublicKeyJwk) {
                cachedPeerPub = null;
                cachedAes = null;
                if (keyPair && cfg.peerPublicKeyJwk) {
                    await decryptExistingMessages(keyPair);
                }
            }
            showE2eBanner();
        } catch (e) {}
    }

    async function encryptPayload(plainText, aesKey) {
        const iv = crypto.getRandomValues(new Uint8Array(12));
        const enc = new TextEncoder();
        const ct = await crypto.subtle.encrypt({ name: 'AES-GCM', iv }, aesKey, enc.encode(plainText));
        return JSON.stringify({ iv: b64(iv.buffer), ct: b64(ct) });
    }

    async function decryptPayload(jsonStr, aesKey) {
        const { iv, ct } = JSON.parse(jsonStr);
        const ivBuf = b64decode(iv);
        const ctBuf = b64decode(ct);
        const pt = await crypto.subtle.decrypt({ name: 'AES-GCM', iv: ivBuf }, aesKey, ctBuf);
        return new TextDecoder().decode(pt);
    }

    function renderChatEnvelopeOrPlain(container, plainOrBody) {
        container.innerHTML = '';
        const trim = typeof plainOrBody === 'string' ? plainOrBody.trim() : '';
        let env = null;
        try {
            if (trim.startsWith('{')) env = JSON.parse(trim);
        } catch (e) {}
        if (env && env.v === 1 && env.k === 'img' && env.m && env.d) {
            const img = document.createElement('img');
            img.src = 'data:' + env.m + ';base64,' + env.d;
            img.className = 'max-w-full rounded-lg max-h-80 object-contain';
            img.alt = '';
            container.appendChild(img);
            if (env.c) {
                const cap = document.createElement('p');
                cap.className = 'msg-text text-sm whitespace-pre-wrap break-words mt-1';
                cap.textContent = env.c;
                container.appendChild(cap);
            }
            return;
        }
        if (env && env.v === 1 && env.k === 'text') {
            const p = document.createElement('p');
            p.className = 'msg-text text-sm whitespace-pre-wrap break-words';
            p.textContent = env.t || '';
            container.appendChild(p);
            return;
        }
        const p = document.createElement('p');
        p.className = 'msg-text text-sm whitespace-pre-wrap break-words';
        p.textContent = plainOrBody;
        container.appendChild(p);
    }

    function buildOutgoingPayload(textTrimmed, pendingImg) {
        if (pendingImg && pendingImg.b64) {
            const o = { v: 1, k: 'img', m: pendingImg.mime, d: pendingImg.b64 };
            if (textTrimmed) o.c = textTrimmed;
            return JSON.stringify(o);
        }
        return JSON.stringify({ v: 1, k: 'text', t: textTrimmed });
    }

    async function chatCompressImageFile(file) {
        if (!file.type.startsWith('image/')) {
            alert(@json(__('Please choose an image file.')));
            return null;
        }
        const maxTarget = 2400000;
        const img = new Image();
        const url = URL.createObjectURL(file);
        try {
            await new Promise(function (res, rej) {
                img.onload = res;
                img.onerror = rej;
                img.src = url;
            });
            const maxW = 1280;
            let w = img.naturalWidth;
            let h = img.naturalHeight;
            const scale = Math.min(1, maxW / w);
            w = Math.round(w * scale);
            h = Math.round(h * scale);
            const canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, w, h);
            let quality = 0.85;
            let dataUrl = canvas.toDataURL('image/jpeg', quality);
            while (dataUrl.length > maxTarget && quality > 0.38) {
                quality -= 0.07;
                dataUrl = canvas.toDataURL('image/jpeg', quality);
            }
            if (dataUrl.length > maxTarget) {
                alert(@json(__('Image too large after compression. Choose a smaller image.')));
                return null;
            }
            const comma = dataUrl.indexOf(',');
            const b64 = dataUrl.slice(comma + 1);
            return { mime: 'image/jpeg', b64: b64 };
        } finally {
            URL.revokeObjectURL(url);
        }
    }

    function insertAtCursor(textarea, snippet) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const v = textarea.value;
        textarea.value = v.slice(0, start) + snippet + v.slice(end);
        const pos = start + snippet.length;
        textarea.selectionStart = textarea.selectionEnd = pos;
        textarea.focus();
    }

    function clearPendingImage() {
        pendingChatImage = null;
        const pv = document.getElementById('chat-image-preview');
        const pi = document.getElementById('chat-image-preview-img');
        if (pv) pv.classList.add('hidden');
        if (pi) pi.removeAttribute('src');
    }

    let cachedPeerPub = null;
    let cachedAes = null;

    async function buildAesIfPossible(keyPair) {
        if (!cfg.peerPublicKeyJwk || !keyPair) return null;
        if (cachedAes) return cachedAes;
        cachedPeerPub = await crypto.subtle.importKey('jwk', JSON.parse(cfg.peerPublicKeyJwk), { name: 'ECDH', namedCurve: 'P-256' }, false, []);
        const bits = await crypto.subtle.deriveBits({ name: 'ECDH', public: cachedPeerPub }, keyPair.privateKey, 256);
        cachedAes = await deriveAesKey(cfg.friendshipId, bits);
        return cachedAes;
    }

    async function decryptExistingMessages(keyPair) {
        const aes = await buildAesIfPossible(keyPair);
        const wraps = document.querySelectorAll('[data-e2e-wrap="1"]');
        for (let i = 0; i < wraps.length; i++) {
            const wrap = wraps[i];
            const id = wrap.getAttribute('data-msg-id');
            const raw = cfg.e2eCipherById && cfg.e2eCipherById[id];
            wrap.innerHTML = '';
            if (!raw || !aes) {
                const p = document.createElement('p');
                p.className = 'text-sm';
                p.textContent = cfg.peerPublicKeyJwk ? '{{ __("Could not decrypt.") }}' : '{{ __("Waiting for encryption keys…") }}';
                wrap.appendChild(p);
                continue;
            }
            try {
                const plain = await decryptPayload(raw, aes);
                renderChatEnvelopeOrPlain(wrap, plain);
            } catch (e) {
                const p = document.createElement('p');
                p.className = 'text-sm';
                p.textContent = '{{ __("Could not decrypt.") }}';
                wrap.appendChild(p);
            }
        }
    }

    function showE2eBanner() {
        if (!cfg.peerPublicKeyJwk) {
            banner.textContent = '{{ __("Waiting for the other user to publish an encryption key on the server (usually a few seconds after they open this chat).") }}';
            banner.classList.remove('hidden');
        } else if (!cfg.myPublicKeyJwk) {
            banner.textContent = '{{ __("Syncing your encryption key to the server…") }}';
            banner.classList.remove('hidden');
        } else {
            banner.classList.add('hidden');
        }
    }

    function appendMessage(m, keyPair) {
        if (scrollEl && scrollEl.querySelector('[data-msg-row="' + m.id + '"]')) return;
        const mine = Number(m.sender_id) === cfg.myUserId;
        const wrap = document.createElement('div');
        wrap.className = 'flex ' + (mine ? 'justify-end' : 'justify-start');
        wrap.setAttribute('data-msg-row', m.id);
        const bubble = document.createElement('div');
        bubble.className = 'max-w-[85%] rounded-2xl px-4 py-2 ' + (mine ? 'bg-gradient-to-r from-primary-500 to-purple-600 text-white' : 'bg-gray-100 dark:bg-dark-700 text-gray-900 dark:text-white');
        if (!mine && m.sender_name) {
            const sn = document.createElement('p');
            sn.className = 'text-xs opacity-70 mb-1';
            sn.textContent = m.sender_name;
            bubble.appendChild(sn);
        }
        if (m.is_e2e) {
            const inner = document.createElement('div');
            inner.setAttribute('data-e2e-wrap', '1');
            inner.setAttribute('data-msg-id', String(m.id));
            const ph = document.createElement('p');
            ph.className = 'text-sm opacity-90';
            ph.textContent = '{{ __("Decrypting…") }}';
            inner.appendChild(ph);
            buildAesIfPossible(keyPair).then(async function (aes) {
                inner.innerHTML = '';
                if (!aes) {
                    const er = document.createElement('p');
                    er.className = 'text-sm';
                    er.textContent = '{{ __("Could not decrypt.") }}';
                    inner.appendChild(er);
                    return;
                }
                try {
                    const plain = await decryptPayload(m.body, aes);
                    renderChatEnvelopeOrPlain(inner, plain);
                } catch (err) {
                    const er = document.createElement('p');
                    er.className = 'text-sm';
                    er.textContent = '{{ __("Could not decrypt.") }}';
                    inner.appendChild(er);
                }
            });
            const small = document.createElement('p');
            small.className = 'text-[10px] mt-1 opacity-70';
            small.innerHTML = '<i class="fas fa-lock"></i> {{ __("End-to-end encrypted") }}';
            bubble.appendChild(inner);
            bubble.appendChild(small);
        } else {
            const contentWrap = document.createElement('div');
            contentWrap.className = 'min-w-0 space-y-1';
            renderChatEnvelopeOrPlain(contentWrap, m.body);
            bubble.appendChild(contentWrap);
        }
        const ts = document.createElement('p');
        ts.className = 'text-[10px] mt-1 opacity-60';
        ts.textContent = m.created_at ? new Date(m.created_at).toLocaleString() : '';
        bubble.appendChild(ts);
        wrap.appendChild(bubble);
        scrollEl.appendChild(wrap);
        scrollToBottomIfNeeded(false);
    }

    async function poll(keyPair) {
        try {
            const res = await fetch(cfg.fetchUrl + '?after=' + maxId + '&mark_read=1', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            let hadIncoming = false;
            for (const m of data.messages || []) {
                if (m.id > maxId) {
                    appendMessage(m, keyPair);
                    maxId = m.id;
                    if (Number(m.sender_id) !== cfg.myUserId) hadIncoming = true;
                }
            }
            if (hadIncoming) notifyIncomingMessage(data.messages[data.messages.length - 1]);
            if ((data.messages || []).length) {
                if (window.chatNotifyRefresh) window.chatNotifyRefresh();
            }
        } catch (e) { /* ignore */ }
    }

    const importFileEl = document.getElementById('e2e-import-file');
    document.getElementById('e2e-export-btn').addEventListener('click', exportEncryptionBackup);
    document.getElementById('e2e-import-btn').addEventListener('click', function () { importFileEl.click(); });
    document.getElementById('e2e-import-inline-btn').addEventListener('click', function () { importFileEl.click(); });
    document.getElementById('e2e-new-key-btn').addEventListener('click', function () {
        createNewEncryptionKey().catch(function (err) { console.error(err); });
    });
    importFileEl.addEventListener('change', function (ev) {
        const f = ev.target.files && ev.target.files[0];
        ev.target.value = '';
        if (!f) return;
        f.text().then(function (text) {
            if (importIdentityFromJsonText(text)) location.reload();
        });
    });

    (function setupChatMediaUi() {
        const chatImageInput = document.getElementById('chat-image-input');
        const chatImageBtn = document.getElementById('chat-image-btn');
        const chatImagePreview = document.getElementById('chat-image-preview');
        const chatEmojiBtn = document.getElementById('chat-emoji-btn');
        const chatEmojiPanel = document.getElementById('chat-emoji-panel');
        const chatImageRemove = document.getElementById('chat-image-remove');
        if (!chatImageInput || !chatEmojiPanel || !chatEmojiBtn) return;
        if (chatImageRemove) chatImageRemove.addEventListener('click', clearPendingImage);
        if (chatImageBtn) chatImageBtn.addEventListener('click', function () { chatImageInput.click(); });
        chatImageInput.addEventListener('change', function (ev) {
            const f = ev.target.files && ev.target.files[0];
            ev.target.value = '';
            if (!f) return;
            chatCompressImageFile(f).then(function (part) {
                if (!part) return;
                pendingChatImage = part;
                document.getElementById('chat-image-preview-img').src = 'data:' + part.mime + ';base64,' + part.b64;
                document.getElementById('chat-image-preview-name').textContent = f.name;
                if (chatImagePreview) chatImagePreview.classList.remove('hidden');
            }).catch(function (err) {
                console.error(err);
                alert(@json(__('Could not process image.')));
            });
        });
        const emojis = ['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','😉','😍','🥰','😘','😗','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','😐','😑','😶','😏','😒','🙄','😬','😮','😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','👍','👎','👏','🙏','💪','🔥','❤️','💔','✨','⭐','🎉','👋','🤝'];
        emojis.forEach(function (ch) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'text-xl leading-none p-1 rounded hover:bg-gray-100 dark:hover:bg-dark-700';
            b.textContent = ch;
            b.addEventListener('click', function (ev) {
                ev.stopPropagation();
                insertAtCursor(input, ch);
                chatEmojiPanel.classList.add('hidden');
                chatEmojiBtn.setAttribute('aria-expanded', 'false');
            });
            chatEmojiPanel.appendChild(b);
        });
        chatEmojiBtn.addEventListener('click', function (ev) {
            ev.stopPropagation();
            const willOpen = chatEmojiPanel.classList.contains('hidden');
            chatEmojiPanel.classList.toggle('hidden', !willOpen);
            chatEmojiBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
        document.addEventListener('click', function () {
            chatEmojiPanel.classList.add('hidden');
            chatEmojiBtn.setAttribute('aria-expanded', 'false');
        });
    })();

    (async function init() {
        showE2eBanner();
        try {
            const loaded = await loadOrCreateIdentity();
            if (!loaded.ok && loaded.needsRecovery) {
                document.getElementById('e2e-recovery-text').textContent = @json(__('Your encryption key is not stored on this browser. Import the backup file you exported on another device, or create a new key (then older E2E messages cannot be read here).'));
                document.getElementById('e2e-recovery').classList.remove('hidden');
                banner.classList.add('hidden');
                document.querySelectorAll('[data-e2e-wrap="1"]').forEach(function (wrap) {
                    wrap.innerHTML = '';
                    const p = document.createElement('p');
                    p.className = 'text-sm';
                    p.textContent = @json(__('Encrypted messages need your key on this device — use Import backup.'));
                    wrap.appendChild(p);
                });
            } else {
                chatKeyPair = { privateKey: loaded.privateKey, publicKey: loaded.publicKey };
                await refreshE2eConfig(chatKeyPair);
                await decryptExistingMessages(chatKeyPair);
            }
        } catch (e) {
            console.error(e);
        }
        scrollToBottomIfNeeded(true);
        markConversationRead(maxId);
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().catch(function () {});
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text && !pendingChatImage) return;
            const plainPayload = buildOutgoingPayload(text, pendingChatImage);
            const mode = form.querySelector('input[name="chat_mode"]:checked')?.value || 'standard';
            let body = plainPayload;
            let isE2e = mode === 'e2e';
            try {
                if (isE2e) {
                    if (!chatKeyPair) {
                        alert(@json(__('Set up encryption on this device first: import a backup or create a new key.')));
                        return;
                    }
                    await refreshE2eConfig(chatKeyPair);
                    const aes = await buildAesIfPossible(chatKeyPair);
                    if (!aes || !cfg.peerPublicKeyJwk) {
                        alert(@json(__('End-to-end send is not ready: the other user has not published an encryption key yet. Use standard chat, or wait until they open this chat (this page updates automatically).')));
                        return;
                    }
                    body = await encryptPayload(plainPayload, aes);
                }
                const fd = new FormData();
                fd.append('body', body);
                fd.append('is_e2e', isE2e ? '1' : '0');
                fd.append('_token', cfg.csrf);
                const res = await fetch(cfg.sendUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': cfg.csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: fd,
                    credentials: 'same-origin',
                });
                if (res.status === 422) {
                    const err = await res.json().catch(() => ({}));
                    const msg = err.errors?.body?.[0] || err.message || '{{ __("Could not send.") }}';
                    alert(msg);
                    return;
                }
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    alert(err.message || '{{ __("Could not send.") }}');
                    return;
                }
                const payload = await res.json().catch(() => ({}));
                input.value = '';
                clearPendingImage();
                if (payload.message) {
                    appendMessage(payload.message, chatKeyPair);
                    maxId = Math.max(maxId, Number(payload.message.id) || maxId);
                    scrollToBottomIfNeeded(true);
                    markConversationRead(maxId);
                } else {
                    await poll(chatKeyPair);
                }
            } catch (err) {
                console.error(err);
                alert((err && err.message) ? err.message : '{{ __("Could not send.") }}');
            }
        });

        setInterval(async function () {
            await poll(chatKeyPair);
            await refreshE2eConfig(chatKeyPair);
        }, 2500);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                poll(chatKeyPair);
            }
        });
    })();
})();
</script>
@endsection
