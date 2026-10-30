import { initLibrary } from './library';
import { initStudio } from './studio';
import { initTextOrigin } from './text-origin';

initLibrary();
initTextOrigin();

const studio = document.getElementById('studio');
if (studio) {
    initStudio(studio);
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
            label.textContent = 'نسخ';
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

// Destructive forms ask once before going ahead (data-confirm="…").
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
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

// The lamp over the book: lighting it turns on night reading (dark mode); putting it out, day.
const themeButtons = document.querySelectorAll('[data-theme-toggle]');
const syncLamp = () => {
    const isDark = document.documentElement.classList.contains('dark');
    themeButtons.forEach((button) => {
        button.setAttribute('aria-pressed', String(isDark));
        button.setAttribute('aria-label', isDark ? 'أطفئ المصباح: الوضع النهاري' : 'أشعل المصباح: الوضع الليلي');
        button.title = button.getAttribute('aria-label');
    });
    // The phone's status bar follows the chosen theme, not only the system one.
    document.querySelectorAll('meta[name="theme-color"]').forEach((meta) => meta.setAttribute('content', isDark ? '#1B1714' : '#F5EFE4'));
};

themeButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');

        try {
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        } catch {
            // Storage can be blocked (private mode); the toggle still works for this page.
        }

        syncLamp();
    });
});
syncLamp();
