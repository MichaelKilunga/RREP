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
 * Automatic slideshows (no user interaction).
 * 1. Page heroes: inject the three slideshow images from <meta name="rrep-hero-slides">
 *    behind every section.page-hero and crossfade them.
 * 2. Card thumbnails: any [data-card-slideshow] cycles its stacked .card-slide images.
 */
function autoSlideshows() {
    const cycle = (slides, intervalMs) => {
        let current = 0;
        setInterval(() => {
            slides[current].classList.remove('is-active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('is-active');
        }, intervalMs);
    };

    const meta = document.querySelector('meta[name="rrep-hero-slides"]');
    const urls = meta ? meta.content.split('|').filter(Boolean) : [];
    if (urls.length > 1) {
        document.querySelectorAll('section.page-hero').forEach((hero) => {
            if (hero.querySelector('.hero-slides')) return;
            const wrap = document.createElement('div');
            wrap.className = 'hero-slides';
            wrap.setAttribute('aria-hidden', 'true');
            urls.forEach((url, i) => {
                const slide = document.createElement('div');
                slide.className = `hero-slide${i === 0 ? ' is-active' : ''}`;
                slide.style.backgroundImage = `url('${url}')`;
                wrap.appendChild(slide);
            });
            hero.prepend(wrap);
            hero.classList.add('has-slides');
            cycle(wrap.children, 1000);
        });
    }

    document.querySelectorAll('[data-card-slideshow]').forEach((wrap) => {
        const slides = wrap.querySelectorAll('.card-slide');
        if (slides.length > 1) cycle(slides, 1000);
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
        el.setAttribute('aria-label', t('Install programu', 'Install app'));
        el.innerHTML = `
            <div class="pwa-banner-text">
                <strong>${t('Install programu hii', 'Install this app')}</strong>
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

/**
 * Staff device notifications: register the push subscription once permission
 * is granted, and surface an "enable" bell while the choice is still open.
 */
function staffPush() {
    const vapidMeta = document.querySelector('meta[name="rrep-vapid"]');
    const urlMeta = document.querySelector('meta[name="rrep-push-url"]');
    if (!vapidMeta || !urlMeta || !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toKey = (value) => {
        const padded = value + '='.repeat((4 - (value.length % 4)) % 4);
        const raw = atob(padded.replace(/-/g, '+').replace(/_/g, '/'));
        return Uint8Array.from(raw, (ch) => ch.charCodeAt(0));
    };

    const subscribe = async () => {
        const registration = await navigator.serviceWorker.ready;
        let subscription = await registration.pushManager.getSubscription();
        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: toKey(vapidMeta.content),
            });
        }
        const json = subscription.toJSON();
        await fetch(urlMeta.content, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            body: JSON.stringify({ endpoint: subscription.endpoint, p256dh: json.keys.p256dh, auth: json.keys.auth }),
        });
    };

    const button = document.getElementById('enablePushBtn');

    if (Notification.permission === 'granted') {
        subscribe().catch(() => {});
        return;
    }
    if (Notification.permission === 'denied' || !button) {
        return;
    }

    button.classList.remove('d-none');
    button.classList.add('d-flex');
    button.addEventListener('click', async () => {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            return;
        }
        button.classList.add('d-none');
        try {
            await subscribe();
            toast(t('Arifa za kifaa zimewashwa', 'Device notifications enabled'));
        } catch (e) {
            toast(t('Imeshindikana kuwasha arifa', 'Could not enable notifications'), 'error');
        }
    });
}

/**
 * Bulk selection for lists. Row checkboxes carry `data-bulk-item="group"`; any
 * `data-bulk-all="group"` checkbox or button toggles the whole group, so "select
 * all" stays reachable on phones where stacked tables hide the header row.
 * `data-bulk-count`, `data-bulk-bar` and `data-bulk-clear` follow the selection,
 * and `form[data-bulk-form="group"]` posts the chosen values as ids[]. Submit
 * buttons may ask first with `data-confirm` (":count" is filled in).
 */
function bulkSelection() {
    const items = (group) => Array.from(document.querySelectorAll(`[data-bulk-item="${group}"]`)).filter((box) => !box.disabled);
    const selected = (group) => items(group).filter((box) => box.checked);

    const sync = (group) => {
        const all = items(group);
        const count = selected(group).length;
        const full = all.length > 0 && count === all.length;

        document.querySelectorAll(`[data-bulk-count="${group}"]`).forEach((el) => {
            el.textContent = String(count);
        });
        document.querySelectorAll(`[data-bulk-bar="${group}"]`).forEach((bar) => {
            bar.classList.toggle('d-none', count === 0);
            bar.classList.toggle('d-flex', count > 0);
        });
        document.querySelectorAll(`[data-bulk-all="${group}"]`).forEach((toggle) => {
            if (toggle instanceof HTMLInputElement) {
                toggle.checked = full;
                toggle.indeterminate = count > 0 && !full;
                return;
            }
            toggle.setAttribute('aria-pressed', full ? 'true' : 'false');
            toggle.classList.toggle('active', full);
            const label = toggle.querySelector('[data-bulk-all-label]');
            if (label) {
                label.textContent = full
                    ? (toggle.dataset.labelNone || t('Ondoa zote', 'Deselect all'))
                    : `${toggle.dataset.labelAll || t('Chagua zote', 'Select all')} (${all.length})`;
            }
        });
    };

    const setAll = (group, checked) => {
        items(group).forEach((box) => {
            if (box.checked !== checked) {
                box.checked = checked;
                box.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        sync(group);
    };

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement)) {
            return;
        }
        if (target.dataset.bulkAll) {
            setAll(target.dataset.bulkAll, target.checked);
        } else if (target.dataset.bulkItem) {
            sync(target.dataset.bulkItem);
        }
    });

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('button[data-bulk-all]');
        if (toggle) {
            event.preventDefault();
            const group = toggle.dataset.bulkAll;
            const all = items(group);
            setAll(group, !(all.length > 0 && all.every((box) => box.checked)));
            return;
        }
        const clear = event.target.closest('[data-bulk-clear]');
        if (clear) {
            event.preventDefault();
            setAll(clear.dataset.bulkClear, false);
        }
    });

    // Capture phase, so a cancelled confirm also stops the loading-state handler.
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.bulkForm) {
            return;
        }
        const ids = selected(form.dataset.bulkForm).map((box) => box.value);
        const question = event.submitter?.dataset.confirm || form.dataset.confirm;
        if (ids.length === 0 || (question && !window.confirm(question.replace(':count', String(ids.length))))) {
            event.preventDefault();
            return;
        }
        form.querySelectorAll('input[data-bulk-id]').forEach((input) => input.remove());
        ids.forEach((id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            input.dataset.bulkId = '1';
            form.appendChild(input);
        });
    }, true);

    const groups = new Set(Array.from(document.querySelectorAll('[data-bulk-item], [data-bulk-all]')).map((el) => el.dataset.bulkItem || el.dataset.bulkAll));
    groups.forEach(sync);
}

/**
 * Image inputs (`input[type=file][data-image-upload]`) are checked in the
 * browser before upload. Unsupported formats such as iPhone HEIC get a clear
 * message, and photos larger than `data-max-bytes` (the server's upload limit)
 * are re-encoded as JPEG until they fit. For multi-file inputs the files also
 * share `data-max-total-bytes`. Feedback goes to the element named in
 * `data-image-status`; `data-preview-target` shows the first image.
 */
function imageUploads(root = document) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    const allowedExtensions = /\.(jpe?g|png|webp|gif)$/i;
    const mb = (bytes) => `${(bytes / 1048576).toFixed(bytes < 1048576 ? 2 : 1)} MB`;

    const encode = (file, maxSide, quality) => new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            const scale = Math.min(1, maxSide / Math.max(img.naturalWidth, img.naturalHeight));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(img.naturalWidth * scale));
            canvas.height = Math.max(1, Math.round(img.naturalHeight * scale));
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            URL.revokeObjectURL(url);
            canvas.toBlob((blob) => {
                if (!blob) {
                    reject(new Error('encode'));
                    return;
                }
                resolve(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg', lastModified: Date.now() }));
            }, 'image/jpeg', quality);
        };
        img.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('decode'));
        };
        img.src = url;
    });

    const shrink = async (file, maxBytes) => {
        for (const [side, quality] of [[2000, 0.85], [1600, 0.8], [1280, 0.75], [1024, 0.7]]) {
            const candidate = await encode(file, side, quality);
            if (candidate.size <= maxBytes) {
                return candidate;
            }
        }
        return null;
    };

    root.querySelectorAll('input[type="file"][data-image-upload]').forEach((input) => {
        if (input.dataset.imageUploadBound) {
            return;
        }
        input.dataset.imageUploadBound = '1';
        const status = input.dataset.imageStatus ? document.querySelector(input.dataset.imageStatus) : null;
        const preview = input.dataset.previewTarget ? document.querySelector(input.dataset.previewTarget) : null;
        const maxBytes = Number(input.dataset.maxBytes || 2097152);
        const maxTotal = Number(input.dataset.maxTotalBytes || 0);

        const report = (message, isError) => {
            input.classList.toggle('is-invalid', isError);
            input.setCustomValidity(isError ? message : '');
            if (status) {
                status.textContent = message;
                status.classList.toggle('text-danger', isError);
                status.classList.toggle('text-success', !isError && message !== '');
                status.classList.toggle('fw-semibold', message !== '');
            }
        };

        input.addEventListener('change', async () => {
            const files = Array.from(input.files || []);
            if (files.length === 0) {
                report('', false);
                return;
            }
            const perFile = maxTotal > 0 ? Math.min(maxBytes, Math.floor(maxTotal / files.length)) : maxBytes;
            const ready = [];
            let resized = 0;
            report(t('Inaandaa picha...', 'Preparing image...'), false);

            for (const file of files) {
                const supported = allowedTypes.includes(file.type) || (file.type === '' && allowedExtensions.test(file.name));
                if (!supported) {
                    input.value = '';
                    report(t(
                        `"${file.name}" si picha inayokubalika. Tumia JPG, PNG au WebP (picha za HEIC za iPhone: piga screenshot au badilisha kamera iwe "Most Compatible").`,
                        `"${file.name}" is not a supported image. Use JPG, PNG or WebP (for iPhone HEIC photos, take a screenshot or set the camera to "Most Compatible").`
                    ), true);
                    return;
                }
                if (file.size <= perFile) {
                    ready.push(file);
                    continue;
                }
                const smaller = await shrink(file, perFile).catch(() => null);
                if (!smaller) {
                    input.value = '';
                    report(t(
                        `"${file.name}" (${mb(file.size)}) ni kubwa mno na haikuweza kupunguzwa. Chagua picha isiyozidi ${mb(perFile)}.`,
                        `"${file.name}" (${mb(file.size)}) is too large and could not be reduced. Choose an image under ${mb(perFile)}.`
                    ), true);
                    return;
                }
                ready.push(smaller);
                resized++;
            }

            if (typeof DataTransfer !== 'undefined') {
                const transfer = new DataTransfer();
                ready.forEach((file) => transfer.items.add(file));
                input.files = transfer.files;
            }

            const total = ready.reduce((sum, file) => sum + file.size, 0);
            report(resized > 0
                ? t(`Picha imepunguzwa ili ipakiwe: ${mb(total)} sasa.`, `Image reduced so it can upload: now ${mb(total)}.`)
                : t(`Tayari kupakiwa (${mb(total)}).`, `Ready to upload (${mb(total)}).`), false);

            if (preview && ready[0]) {
                preview.src = URL.createObjectURL(ready[0]);
                preview.classList.remove('d-none');
                preview.closest('.d-none')?.classList.remove('d-none');
            }
        });
    });
}

window.RREP = Object.assign(window.RREP || {}, {
    toast,
    t,
    stackTables,
    normaliseTzDigits,
    formatTzDisplay,
    tanzanianPhoneInputs,
    imageUploads,
});

document.addEventListener('DOMContentLoaded', () => {
    stackTables();
    formLoadingStates();
    connectivityBanner();
    tanzanianPhoneInputs();
    bulkSelection();
    imageUploads();
    lazyImages();
    scrollers();
    autoSlideshows();
    progressiveWebApp();
    staffPush();
});
