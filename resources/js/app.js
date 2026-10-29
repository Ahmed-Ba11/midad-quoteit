import { initStudio } from './studio';

const studio = document.getElementById('studio');
if (studio) {
    initStudio(studio);
}

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
