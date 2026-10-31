// «بأيّ قلمٍ تكتب؟» (resources/views/components/font-picker.blade.php): group filters, previews,
// and the chosen font. Fonts come from Google Fonts (all SIL OFL); ثمانية is local.
//
// Every family is always loaded WHOLE. (A preview-only subset — Google's `&text=` — registers under the
// family's real name and only holds a few letters; the browser then draws the reader's own text with
// it, falling back letter by letter, and the words look scrambled.)

const loading = new Map();

/**
 * Load one family in full (once), and resolve when the browser can really draw it. The stylesheet
 * must arrive first: asking document.fonts before it is parsed resolves at once with nothing loaded,
 * and the card would then flash the fallback font. Never waits more than 4 s (slow network).
 */
export function loadFont(font) {
    if (!font) return Promise.resolve();
    // ثمانية has no Google stylesheet (its @font-face is already in the page): just wait for its file.
    if (!font.google) {
        const local = document.fonts.load(`1em ${font.stack.split(',')[0].trim()}`, 'بسم الله رقيم').catch(() => {});
        return Promise.race([local, new Promise((resolve) => setTimeout(resolve, 4000))]);
    }
    if (!loading.has(font.google)) {
        const family = font.stack.split(',')[0].trim();
        const stylesheet = new Promise((resolve) => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = `https://fonts.googleapis.com/css2?family=${font.google}&display=swap`;
            link.onload = link.onerror = resolve;
            document.head.append(link);
        });
        const ready = stylesheet.then(() => document.fonts.load(`1em ${family}`, 'بسم الله رقيم')).catch(() => {});
        loading.set(font.google, Promise.race([ready, new Promise((resolve) => setTimeout(resolve, 4000))]));
    }
    return loading.get(font.google);
}

export function initFontPicker(picker, onChange) {
    const fonts = JSON.parse(picker.dataset.fonts);
    const rows = [...picker.querySelectorAll('[data-font-row]')];
    const filters = [...picker.querySelectorAll('[data-font-filter]')];

    // A row's font is fetched when the row comes into view (the list opens, or is scrolled).
    const observer = new IntersectionObserver((entries) => {
        entries.filter((entry) => entry.isIntersecting).forEach((entry) => {
            loadFont(fonts[entry.target.querySelector('input').value]);
            observer.unobserve(entry.target);
        });
    // On phones the list scrolls with the page (no inner scroll), so "in view" means on the screen.
    }, { root: matchMedia('(max-width: 1023.98px)').matches ? null : picker.querySelector('.font-list') });
    rows.forEach((row) => observer.observe(row));

    filters.forEach((filter) => {
        filter.addEventListener('click', () => {
            filters.forEach((other) => other.setAttribute('aria-pressed', String(other === filter)));
            rows.forEach((row) => {
                row.hidden = filter.dataset.fontFilter !== 'all' && row.dataset.group !== filter.dataset.fontFilter;
            });
        });
    });

    picker.addEventListener('change', (event) => {
        if (event.target.name !== 'font') return;
        // Two quick picks: only the one still chosen when its font arrives reaches the card.
        const choice = event.target;
        loadFont(fonts[choice.value]).then(() => {
            if (choice.checked) onChange?.(choice.value);
        });
    });

    const checked = picker.querySelector('input:checked');
    if (checked) loadFont(fonts[checked.value]);
}
