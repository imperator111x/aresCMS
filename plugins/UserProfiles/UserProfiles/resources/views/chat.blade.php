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
        'e2eStatusUrl' => $e2eStatusUrl ?? route('profiles.e2e.status', $friendship),
        'csrf' => csrf_token(),
        'peerPublicKeyJwk' => $peerPublicKeyJwk,
        'myPublicKeyJwk' => $myPublicKeyJwk,
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
        <a href="{{ route('profiles.show', $peer) }}" class="text-sm text-primary-500 hover:text-primary-600 shrink-0">{{ __('Profile') }}</a>
    </div>

    <div id="e2e-banner" class="hidden mb-4 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 px-4 py-3 text-sm text-amber-900 dark:text-amber-100"></div>

    <div id="messages-scroll" class="flex-1 overflow-y-auto space-y-3 mb-4 max-h-[min(56vh,520px)] pr-1">
        @foreach($messages as $message)
            @php $mine = (int) $message->sender_id === (int) auth()->id(); @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}" data-msg-row="{{ $message->id }}">
                <div class="max-w-[85%] rounded-2xl px-4 py-2 {{ $mine ? 'bg-gradient-to-r from-primary-500 to-purple-600 text-white' : 'bg-gray-100 dark:bg-dark-700 text-gray-900 dark:text-white' }}">
                    @unless($mine)
                        <p class="text-xs opacity-70 mb-1">{{ $message->sender->name ?? '' }}</p>
                    @endunless
                    @if($message->is_e2e)
                        <p class="msg-text text-sm whitespace-pre-wrap break-words font-mono text-xs opacity-90 e2e-msg"
                           data-e2e="1"
                           data-raw="{{ $message->body }}"
                           data-msg-id="{{ $message->id }}">{{ __('Decrypting…') }}</p>
                        <p class="text-[10px] mt-1 opacity-70"><i class="fas fa-lock"></i> {{ __('End-to-end encrypted') }}</p>
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
                <input type="radio" name="chat_mode" value="standard" class="rounded-full border-gray-400 text-primary-500 focus:ring-primary-500" checked>
                {{ __('Standard chat') }}
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300">
                <input type="radio" name="chat_mode" value="e2e" class="rounded-full border-gray-400 text-primary-500 focus:ring-primary-500">
                {{ __('End-to-end encrypted') }}
            </label>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __("Anyone can open this chat. End-to-end sending needs the other person's key too — they must open this chat once. Standard chat always works.") }}</p>

        <div class="flex gap-2">
            <textarea id="chat-input" rows="2" maxlength="60000"
                class="flex-1 rounded-xl border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-800 text-gray-900 dark:text-white px-4 py-3 focus:ring-2 focus:ring-primary-500"
                placeholder="{{ __('Write a message…') }}"></textarea>
            <button type="submit" class="self-end px-5 py-3 rounded-xl bg-gradient-to-r from-primary-500 to-purple-500 text-white font-semibold shrink-0">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const cfg = @json($chatCfg);
    const storageKeyPriv = 'userprofiles_e2e_v2_'+ cfg.myUserId;
    const scrollEl = document.getElementById('messages-scroll');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const banner = document.getElementById('e2e-banner');

    let chatKeyPair = null;

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

    async function loadOrCreateIdentity() {
        const legacyKey = 'userprofiles_e2e_priv_'+ cfg.myUserId;
        let stored = sessionStorage.getItem(storageKeyPriv) || sessionStorage.getItem(legacyKey);
        let parsed = parseStoredIdentity(stored);
        let privJwk;
        let pubJwk;

        if (parsed) {
            privJwk = parsed.privJwk;
            pubJwk = parsed.pubJwk;
        } else {
            const kp = await crypto.subtle.generateKey({ name: 'ECDH', namedCurve: 'P-256' }, true, ['deriveBits']);
            privJwk = await crypto.subtle.exportKey('jwk', kp.privateKey);
            pubJwk = await crypto.subtle.exportKey('jwk', kp.publicKey);
            sessionStorage.setItem(storageKeyPriv, JSON.stringify({ v: 2, priv: privJwk, pub: pubJwk }));
            sessionStorage.removeItem(legacyKey);
        }

        sessionStorage.setItem(storageKeyPriv, JSON.stringify({ v: 2, priv: privJwk, pub: pubJwk }));
        sessionStorage.removeItem(legacyKey);

        const privateKey = await crypto.subtle.importKey('jwk', privJwk, { name: 'ECDH', namedCurve: 'P-256' }, false, ['deriveBits']);
        const publicKey = await crypto.subtle.importKey('jwk', pubJwk, { name: 'ECDH', namedCurve: 'P-256' }, true, []);

        await syncPublicKeyToServer(pubJwk);

        return { privateKey, publicKey };
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
        const els = document.querySelectorAll('.e2e-msg');
        for (const el of els) {
            const raw = el.getAttribute('data-raw');
            if (!raw || !aes) {
                el.textContent = cfg.peerPublicKeyJwk ? '{{ __("Could not decrypt.") }}' : '{{ __("Waiting for encryption keys…") }}';
                continue;
            }
            try {
                el.textContent = await decryptPayload(raw, aes);
            } catch (e) {
                el.textContent = '{{ __("Could not decrypt.") }}';
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

    let maxId = {{ $messages->max('id') ?? 0 }};

    function appendMessage(m, keyPair) {
        const mine = Number(m.sender_id) === cfg.myUserId;
        const wrap = document.createElement('div');
        wrap.className = 'flex ' + (mine ? 'justify-end' : 'justify-start');
        wrap.setAttribute('data-msg-row', m.id);
        const bubble = document.createElement('div');
        bubble.className = 'max-w-[85%] rounded-2xl px-4 py-2 ' + (mine ? 'bg-gradient-to-r from-primary-500 to-purple-600 text-white' : 'bg-gray-100 dark:bg-dark-700 text-gray-900 dark:text-white');
        const text = document.createElement('p');
        text.className = 'msg-text text-sm whitespace-pre-wrap break-words';
        if (!mine && m.sender_name) {
            const sn = document.createElement('p');
            sn.className = 'text-xs opacity-70 mb-1';
            sn.textContent = m.sender_name;
            bubble.appendChild(sn);
        }
        if (m.is_e2e) {
            text.classList.add('e2e-msg');
            text.dataset.e2e = '1';
            text.dataset.raw = m.body;
            text.textContent = '{{ __("Decrypting…") }}';
            buildAesIfPossible(keyPair).then(async (aes) => {
                if (!aes) { text.textContent = '{{ __("Could not decrypt.") }}'; return; }
                try { text.textContent = await decryptPayload(m.body, aes); }
                catch { text.textContent = '{{ __("Could not decrypt.") }}'; }
            });
            const small = document.createElement('p');
            small.className = 'text-[10px] mt-1 opacity-70';
            small.innerHTML = '<i class="fas fa-lock"></i> {{ __("End-to-end encrypted") }}';
            bubble.appendChild(text);
            bubble.appendChild(small);
        } else {
            text.textContent = m.body;
            bubble.appendChild(text);
        }
        const ts = document.createElement('p');
        ts.className = 'text-[10px] mt-1 opacity-60';
        ts.textContent = m.created_at ? new Date(m.created_at).toLocaleString() : '';
        bubble.appendChild(ts);
        wrap.appendChild(bubble);
        scrollEl.appendChild(wrap);
        scrollEl.scrollTop = scrollEl.scrollHeight;
    }

    async function poll(keyPair) {
        try {
            const res = await fetch(cfg.fetchUrl + '?after=' + maxId, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            for (const m of data.messages || []) {
                if (m.id > maxId) {
                    appendMessage(m, keyPair);
                    maxId = m.id;
                }
            }
        } catch (e) { /* ignore */ }
    }

    (async function init() {
        showE2eBanner();
        try {
            chatKeyPair = await loadOrCreateIdentity();
            await refreshE2eConfig(chatKeyPair);
            await decryptExistingMessages(chatKeyPair);
        } catch (e) {
            console.error(e);
        }
        scrollEl.scrollTop = scrollEl.scrollHeight;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;
            const mode = form.querySelector('input[name="chat_mode"]:checked')?.value || 'standard';
            let body = text;
            let isE2e = mode === 'e2e';
            try {
                if (isE2e) {
                    await refreshE2eConfig(chatKeyPair);
                    const aes = await buildAesIfPossible(chatKeyPair);
                    if (!aes || !cfg.peerPublicKeyJwk) {
                        alert(@json(__('End-to-end send is not ready: the other user has not published an encryption key yet. Use standard chat, or wait until they open this chat (this page updates automatically).')));
                        return;
                    }
                    body = await encryptPayload(text, aes);
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
                input.value = '';
                await poll(chatKeyPair);
            } catch (err) {
                console.error(err);
                alert((err && err.message) ? err.message : '{{ __("Could not send.") }}');
            }
        });

        setInterval(async () => {
            await poll(chatKeyPair);
            await refreshE2eConfig(chatKeyPair);
        }, 3500);
    })();
})();
</script>
@endsection
