{{-- Cookie-Einwilligung: localStorage + Cookie cookie_consent (essential|all); funktioniert ohne Tailwind (z. B. Guest-Layout). --}}
<style>
#cookie-consent-root.cc-banner-root {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 10000;
    padding: 12px 16px 16px;
    box-sizing: border-box;
    display: none;
    pointer-events: none;
    font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
}
#cookie-consent-root.cc-banner-root.cc-banner-open {
    display: block;
}
#cookie-consent-root .cc-banner-inner {
    pointer-events: auto;
    max-width: 56rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-radius: 1rem;
    background: rgba(17, 24, 39, 0.96);
    color: #e5e7eb;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
}
@media (min-width: 640px) {
    #cookie-consent-root .cc-banner-inner {
        flex-direction: row;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1.5rem;
    }
}
#cookie-consent-root .cc-banner-title {
    font-weight: 600;
    font-size: 0.95rem;
    color: #fff;
    margin: 0 0 0.35rem 0;
}
#cookie-consent-root .cc-banner-text {
    margin: 0;
    font-size: 0.875rem;
    line-height: 1.55;
    color: #d1d5db;
}
#cookie-consent-root .cc-banner-link {
    margin: 0.5rem 0 0 0;
}
#cookie-consent-root .cc-banner-link a {
    color: #93c5fd;
    font-weight: 500;
    text-decoration: none;
}
#cookie-consent-root .cc-banner-link a:hover {
    text-decoration: underline;
}
#cookie-consent-root .cc-banner-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    flex-shrink: 0;
    align-items: stretch;
}
@media (min-width: 640px) {
    #cookie-consent-root .cc-banner-actions {
        align-items: flex-end;
    }
}
#cookie-consent-root .cc-btn {
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0.65rem 1rem;
    border-radius: 0.75rem;
    border: none;
    white-space: nowrap;
    transition: opacity 0.15s, background 0.15s;
}
#cookie-consent-root .cc-btn:hover {
    opacity: 0.92;
}
#cookie-consent-root .cc-btn-secondary {
    background: transparent;
    color: #e5e7eb;
    border: 1px solid #6b7280;
    font-weight: 500;
}
#cookie-consent-root .cc-btn-primary {
    background: #6366f1;
    color: #fff;
}
#cookie-consent-root .cc-btn-dismiss {
    background: transparent;
    color: #9ca3af;
    border: 1px solid #4b5563;
    font-weight: 500;
}
#cookie-consent-root .cc-banner-actions-row {
    display: flex;
    flex-direction: column-reverse;
    gap: 0.5rem;
    flex-shrink: 0;
    align-items: stretch;
}
@media (min-width: 640px) {
    #cookie-consent-root .cc-banner-actions-row {
        flex-direction: row;
        align-items: center;
    }
}
#cookie-consent-root #cookie-consent-dismiss {
    display: none;
}
#cookie-consent-root #cookie-consent-dismiss.cc-dismiss-visible {
    display: block;
}
@media (min-width: 640px) {
    #cookie-consent-root #cookie-consent-dismiss.cc-dismiss-visible {
        display: inline-block;
    }
}
</style>
<div id="cookie-consent-root"
    class="cc-banner-root"
    role="dialog"
    aria-labelledby="cookie-consent-title"
    aria-live="polite"
    data-title-initial="{{ __('Cookie consent') }}"
    data-title-settings="{{ __('Cookie settings') }}">
    <div class="cc-banner-inner">
        <div>
            <p id="cookie-consent-title" class="cc-banner-title">{{ __('Cookie consent') }}</p>
            <p class="cc-banner-text">{{ __('We use cookies and similar technologies for essential functions, language and session. With your consent, optional services (e.g. CAPTCHA) may load as described in the privacy policy.') }}</p>
            <p class="cc-banner-link">
                <a href="{{ route('legal.privacy') }}#cookies">{{ __('Cookie privacy link') }}</a>
            </p>
        </div>
        <div class="cc-banner-actions">
            <button type="button" id="cookie-consent-dismiss" class="cc-btn cc-btn-dismiss">{{ __('Close') }}</button>
            <div class="cc-banner-actions-row">
                <button type="button" id="cookie-consent-essential" class="cc-btn cc-btn-secondary">{{ __('Only necessary') }}</button>
                <button type="button" id="cookie-consent-all" class="cc-btn cc-btn-primary">{{ __('Accept all') }}</button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var storageKey = 'cookie_consent';
    var root = document.getElementById('cookie-consent-root');
    if (!root) {
        return;
    }
    var titleEl = document.getElementById('cookie-consent-title');
    var initialTitle = root.getAttribute('data-title-initial') || (titleEl ? titleEl.textContent : '');
    var settingsTitle = root.getAttribute('data-title-settings') || initialTitle;
    var dismissBtn = document.getElementById('cookie-consent-dismiss');

    function hasChoice() {
        try {
            var v = localStorage.getItem(storageKey);
            return v === 'essential' || v === 'all';
        } catch (e) {
            return false;
        }
    }

    function setDismissVisible(visible) {
        if (!dismissBtn) {
            return;
        }
        if (visible) {
            dismissBtn.classList.add('cc-dismiss-visible');
        } else {
            dismissBtn.classList.remove('cc-dismiss-visible');
        }
    }

    function openBanner(settingsMode) {
        settingsMode = !!settingsMode;
        if (titleEl) {
            titleEl.textContent = settingsMode ? settingsTitle : initialTitle;
        }
        setDismissVisible(settingsMode && hasChoice());
        root.classList.add('cc-banner-open');
    }

    function closeBanner() {
        root.classList.remove('cc-banner-open');
        setDismissVisible(false);
        if (titleEl) {
            titleEl.textContent = initialTitle;
        }
    }

    function setConsent(mode) {
        try {
            localStorage.setItem(storageKey, mode);
        } catch (e) {}
        var maxAge = 365 * 24 * 60 * 60;
        document.cookie = 'cookie_consent=' + encodeURIComponent(mode) + ';path=/;max-age=' + maxAge + ';SameSite=Lax';
        closeBanner();
        try {
            window.dispatchEvent(new CustomEvent('cookie-consent-changed', { detail: mode }));
        } catch (e) {}
    }

    if (!hasChoice()) {
        openBanner(false);
    }

    var btnAll = document.getElementById('cookie-consent-all');
    var btnEss = document.getElementById('cookie-consent-essential');
    if (btnAll) {
        btnAll.addEventListener('click', function () { setConsent('all'); });
    }
    if (btnEss) {
        btnEss.addEventListener('click', function () { setConsent('essential'); });
    }
    if (dismissBtn) {
        dismissBtn.addEventListener('click', function () { closeBanner(); });
    }

    window.openCookieConsentSettings = function () {
        openBanner(true);
    };

    document.querySelectorAll('a.js-open-cookie-settings, button.js-open-cookie-settings').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            window.openCookieConsentSettings();
        });
    });
})();
</script>
