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
document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');

        try {
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        } catch {
            // Storage can be blocked (private mode); the toggle still works for this page.
        }
    });
});
