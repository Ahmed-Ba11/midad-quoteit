import { initEditor } from './editor';
import { initLibrary } from './library';
import { initStudio } from './studio';
import { initTextOrigin } from './text-origin';

initLibrary();
initTextOrigin();

const studio = document.getElementById('studio');
if (studio) {
    initStudio(studio);
}

const editor = document.getElementById('text-editor');
if (editor) {
    initEditor(editor);
}

// Copy-quote button: copies the text, then shows "نُسخ" for two seconds.
document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(button.dataset.copy);
        } catch {
            return;
        }

        const label = button.querySelector('[data-copy-label]');
        button.querySelector('[data-copy-icon]').classList.add('hidden');
        button.querySelector('[data-copied-icon]').classList.remove('hidden');
        label.textContent = 'نُسخ';

        setTimeout(() => {
            button.querySelector('[data-copy-icon]').classList.remove('hidden');
            button.querySelector('[data-copied-icon]').classList.add('hidden');
            label.textContent = 'انسخ النص';
        }, 2000);
    });
});

// Light/dark toggle — the initial class is set by an inline script in the layout <head> to avoid a flash.
// PWA: the service worker only provides an offline page (public/sw.js); pages always come from the network.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}

// "⋯" menus: one open at a time; a tap outside or Escape closes them.
const menus = [...document.querySelectorAll('details[data-menu]')];
menus.forEach((menu) => menu.addEventListener('toggle', () => {
    if (menu.open) {
        menus.filter((other) => other !== menu).forEach((other) => other.removeAttribute('open'));
    }
}));
document.addEventListener('click', (event) => {
    menus.filter((menu) => menu.open && !menu.contains(event.target)).forEach((menu) => menu.removeAttribute('open'));
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        menus.forEach((menu) => menu.removeAttribute('open'));
    }
});

// Sideways tab bars (data-center-current) open with the current tab in view, not cut off at the edge.
// Measured from the screen, so it works the same right-to-left; only the bar scrolls, never the page.
document.querySelectorAll('[data-center-current]').forEach((bar) => {
    const current = bar.querySelector('[aria-current]');
    if (!current) return;
    const barBox = bar.getBoundingClientRect();
    const tabBox = current.getBoundingClientRect();
    bar.scrollLeft += tabBox.left - barBox.left - (barBox.width - tabBox.width) / 2;
});

// «نص / بطاقة»: the switch goes to the server (to carry every choice), so it answers the tap at once —
// the pill moves to the chosen way and pulses — and a second tap while it travels is ignored.
document.querySelectorAll('[data-view-switch]').forEach((group) => {
    group.addEventListener('click', (event) => {
        const button = event.target.closest('button[name="switch_to"]');
        if (!button) return;
        if (group.dataset.switching) {
            event.preventDefault();
            return;
        }
        group.dataset.switching = button.value;
        group.querySelector('[aria-current]')?.classList.remove('bg-ink', 'font-medium', 'text-paper');
        button.classList.add('bg-ink', 'font-medium', 'text-paper', 'animate-pulse');
        button.classList.remove('text-muted');
    });
});
// Back to a page kept by the browser (bfcache): the pill must show the real current way again.
window.addEventListener('pageshow', (event) => {
    if (event.persisted && document.querySelector('[data-view-switch][data-switching]')) location.reload();
});

// «انشر» / «احفظ» once: a quick double tap must not publish the same quote twice.
document.querySelectorAll('#studio, #text-editor').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (event.submitter?.name === 'switch_to') return;
        if (form.dataset.submitting) {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = '1';
    });
});
window.addEventListener('pageshow', () => document.querySelectorAll('#studio, #text-editor').forEach((form) => delete form.dataset.submitting));

// "Show more": reveal hidden items (data-show-more="<selector>"), then the button retires.
document.querySelectorAll('[data-show-more]').forEach((button) => {
    button.addEventListener('click', () => {
        document.querySelectorAll(button.dataset.showMore).forEach((item) => item.classList.remove('hidden'));
        button.closest('div')?.remove();
    });
});

// Live character count under a text box (data-count-target="<id of the counter>").
document.querySelectorAll('[data-count-target]').forEach((field) => {
    const counter = document.getElementById(field.dataset.countTarget);
    const update = () => {
        counter.textContent = [...field.value].length.toLocaleString('ar-EG');
    };
    field.addEventListener('input', update);
    update();
});

// Toast: fades away after a few seconds, or at once when closed.
document.querySelectorAll('[data-toast]').forEach((toast) => {
    const hide = () => {
        toast.classList.add('toast-leave');
        setTimeout(() => toast.remove(), 300);
    };
    toast.querySelector('[data-toast-close]')?.addEventListener('click', hide);
    setTimeout(hide, 5000);
});

// «رجوع»: back to the very place the reader came from on this site (scroll position included), unless
// that was a form, sign-in, or this same page — then the link's own address (the parent page).
const FORM_PAGES = /^\/(login|signup|quotes\/create|quotes\/draft|diwans\/create)\/?$|\/edit\/?$/;
document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-back]');
    if (!link || event.metaKey || event.ctrlKey || event.shiftKey) return;

    try {
        const from = new URL(document.referrer);
        if (from.origin === location.origin && from.href !== location.href && !FORM_PAGES.test(from.pathname) && history.length > 1) {
            event.preventDefault();
            history.back();
        }
    } catch {
        // No referrer: follow the link.
    }
});

// Sun and crescent: switching to night lights the star; the label always names the mode a click brings.
const themeButtons = document.querySelectorAll('[data-theme-toggle]');
const syncThemeButtons = () => {
    const isDark = document.documentElement.classList.contains('dark');
    themeButtons.forEach((button) => {
        button.setAttribute('aria-pressed', String(isDark));
        button.setAttribute('aria-label', isDark ? 'الوضع النهاري' : 'الوضع الليلي');
        button.title = button.getAttribute('aria-label');
    });
};

themeButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');
        button.classList.toggle('is-lighting', isDark);

        try {
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        } catch {
            // Storage can be blocked (private mode); the toggle still works for this page.
        }

        syncThemeButtons();
    });
});
syncThemeButtons();
