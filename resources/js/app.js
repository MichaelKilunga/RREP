/**
 * RREP front-end enhancements.
 *
 * This module is loaded with `@vite` as a deferred ES module, so it always runs
 * after the classic vendor scripts (jQuery, Bootstrap, SweetAlert2) that the
 * layouts include from the CDN. Everything here is progressive enhancement:
 * pages keep working if it fails to load.
 */

const lang = (document.documentElement.getAttribute('lang') || 'sw').toLowerCase();
const t = (sw, en) => (lang === 'sw' ? sw : en);

/**
 * Stack tables into cards on small screens by copying header text onto cells.
 * Opt-in with `<table class="table table-stack">`.
 */
function stackTables(root = document) {
    root.querySelectorAll('table.table-stack').forEach((table) => {
        const headers = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
        table.querySelectorAll('tbody tr').forEach((row) => {
            Array.from(row.children).forEach((cell, index) => {
                if (!cell.hasAttribute('data-label') && headers[index]) {
                    cell.setAttribute('data-label', headers[index]);
                }
            });
        });
    });
}

/**
 * Show a loading state on submit buttons so users on slow connections know the
 * request is in flight. Forms can opt out with `data-no-loading`.
 */
function formLoadingStates() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-loading') || event.defaultPrevented) {
            return;
        }

        window.setTimeout(() => {
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                button.classList.add('btn-loading');
                button.setAttribute('aria-busy', 'true');
                button.disabled = true;
            });
        }, 0);
    });
}

/**
 * Offline / connectivity banner (spec Volume IV, chapter 85).
 */
function connectivityBanner() {
    let banner = null;

    const show = () => {
        if (banner) {
            return;
        }
        banner = document.createElement('div');
        banner.className = 'offline-banner';
        banner.setAttribute('role', 'status');
        banner.innerHTML = `<i class="bi bi-wifi-off me-2"></i>${t('Hauna mtandao kwa sasa. Mabadiliko hayatahifadhiwa hadi urejee mtandaoni.', 'You are offline. Changes will not be saved until you reconnect.')}`;
        document.body.appendChild(banner);
    };

    const hide = () => {
        banner?.remove();
        banner = null;
    };

    window.addEventListener('offline', show);
    window.addEventListener('online', hide);

    if (navigator.onLine === false) {
        show();
    }
}

/**
 * Normalise Tanzanian phone numbers on blur (+255 7XX XXX XXX) for inputs that
 * carry `data-tz-phone`. The raw digits are kept in a hidden companion when the
 * input declares `data-tz-phone="e164"` so backends receive 2557XXXXXXXX.
 */
function tanzanianPhoneInputs(root = document) {
    root.querySelectorAll('input[data-tz-phone]').forEach((input) => {
        if (input.dataset.tzPhoneBound) {
            return;
        }
        input.dataset.tzPhoneBound = '1';
        input.setAttribute('inputmode', 'tel');
        input.setAttribute('autocomplete', 'tel');

        input.addEventListener('blur', () => {
            const digits = normaliseTzDigits(input.value);
            if (!digits) {
                return;
            }
            input.value = input.dataset.tzPhone === 'e164' ? digits : formatTzDisplay(digits);
            input.classList.toggle('is-invalid', digits.length !== 12);
        });
    });
}

function normaliseTzDigits(value) {
    let digits = String(value || '').replace(/\D+/g, '');
    if (!digits) {
        return '';
    }
    if (digits.startsWith('2550')) {
        digits = '255' + digits.slice(4);
    } else if (digits.startsWith('0')) {
        digits = '255' + digits.slice(1);
    } else if (digits.length === 9) {
        digits = '255' + digits;
    }
    return digits;
}

function formatTzDisplay(digits) {
    if (digits.length !== 12) {
        return digits;
    }
    return `+${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6, 9)} ${digits.slice(9)}`;
}

/**
 * Lazy-load images that do not declare a loading strategy.
 */
function lazyImages(root = document) {
    root.querySelectorAll('img:not([loading])').forEach((img) => {
        img.setAttribute('loading', 'lazy');
        img.setAttribute('decoding', 'async');
    });
}

/**
 * Tiny toast helper that uses SweetAlert2 when present.
 */
function toast(title, icon = 'success') {
    if (window.Swal) {
        window.Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2600, timerProgressBar: true, icon, title });
    } else {
        window.alert(title);
    }
}

/**
 * Horizontal scrollers: add "has-overflow" so CSS can show an edge fade.
 */
function scrollers(root = document) {
    root.querySelectorAll('.scroll-x').forEach((el) => {
        const update = () => el.classList.toggle('has-overflow', el.scrollWidth > el.clientWidth + 4);
        update();
        window.addEventListener('resize', update, { passive: true });
    });
}

/**
 * Progressive Web App: register the service worker and offer installation
 * with a dismissible banner. "Later" snoozes the prompt for 24 hours.
 */
function progressiveWebApp() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    const DEFER_KEY = 'rrep_pwa_defer_until';
    const isSnoozed = () => {
        try {
            return Date.now() < Number(localStorage.getItem(DEFER_KEY) || 0);
        } catch (e) {
            return false;
        }
    };
    const isStandalone =
        window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    let deferredPrompt = null;

    const hideBanner = () => document.getElementById('pwaInstallBanner')?.remove();

    const showBanner = () => {
        if (document.getElementById('pwaInstallBanner')) {
            return;
        }
        const el = document.createElement('div');
        el.id = 'pwaInstallBanner';
        el.className = 'pwa-banner';
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-label', t('Weka programu', 'Install app'));
        el.innerHTML = `
            <div class="pwa-banner-text">
                <strong>${t('Weka programu hii', 'Install this app')}</strong>
                <span>${t('Ifungue moja kwa moja kutoka skrini yako ya mwanzo.', 'Open it straight from your home screen.')}</span>
            </div>
            <div class="pwa-banner-actions">
                <button type="button" class="btn btn-primary btn-sm fw-bold" id="pwaInstallNow">${t('Install', 'Install now')}</button>
                <button type="button" class="btn btn-light btn-sm border" id="pwaInstallLater">${t('Baadaye', 'Later')}</button>
            </div>`;
        document.body.appendChild(el);

        el.querySelector('#pwaInstallNow').addEventListener('click', async () => {
            hideBanner();
            if (!deferredPrompt) {
                return;
            }
            deferredPrompt.prompt();
            try {
                await deferredPrompt.userChoice;
            } catch (e) {
                /* dismissed */
            }
            deferredPrompt = null;
        });

        el.querySelector('#pwaInstallLater').addEventListener('click', () => {
            try {
                localStorage.setItem(DEFER_KEY, String(Date.now() + 24 * 60 * 60 * 1000));
            } catch (e) {
                /* storage unavailable */
            }
            hideBanner();
        });
    };

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        if (isStandalone || isSnoozed()) {
            return;
        }
        deferredPrompt = event;
        showBanner();
    });

    window.addEventListener('appinstalled', () => {
        hideBanner();
        try {
            localStorage.removeItem(DEFER_KEY);
        } catch (e) {
            /* storage unavailable */
        }
    });
}

window.RREP = Object.assign(window.RREP || {}, {
    toast,
    t,
    stackTables,
    normaliseTzDigits,
    formatTzDisplay,
    tanzanianPhoneInputs,
});

document.addEventListener('DOMContentLoaded', () => {
    stackTables();
    formLoadingStates();
    connectivityBanner();
    tanzanianPhoneInputs();
    lazyImages();
    scrollers();
    progressiveWebApp();
});
