// «بأيّ قلمٍ تكتب؟» (resources/views/components/font-picker.blade.php): group filters, light previews,
// and the chosen font loaded in full. Fonts come from Google Fonts (all SIL OFL); ثمانية is local.

const SAMPLE = 'بأيّ قلمٍ تكتب؟Words that stay';
const loaded = new Set();

const addStylesheet = (href) => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    document.head.append(link);
    return link;
};

/**
 * Load one family in full (once), and resolve when the browser can draw it.
 */
export function loadFont(font) {
    if (!font?.google || loaded.has(font.google)) return Promise.resolve();
    loaded.add(font.google);
    addStylesheet(`https://fonts.googleapis.com/css2?family=${font.google}&display=swap`);
    const family = font.stack.split(',')[0].trim();
    return document.fonts.load(`1em ${family}`, 'بسم').catch(() => {});
}

export function initFontPicker(picker, onChange) {
    const fonts = JSON.parse(picker.dataset.fonts);
    const rows = [...picker.querySelectorAll('[data-font-row]')];
    const filters = [...picker.querySelectorAll('[data-font-filter]')];

    // Previews: one tiny stylesheet with only the sample's letters, fetched when the list first shows.
    const loadPreviews = () => {
        const families = Object.values(fonts).filter((font) => font.google).map((font) => `family=${font.google.split(':')[0]}`);
        addStylesheet(`https://fonts.googleapis.com/css2?${families.join('&')}&text=${encodeURIComponent(SAMPLE)}&display=swap`);
    };
    const observer = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
            loadPreviews();
            observer.disconnect();
        }
    });
    observer.observe(picker);

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
        const font = fonts[event.target.value];
        loadFont(font).then(() => onChange?.(event.target.value));
    });

    // The chosen font is drawn in full from the start; the selected row scrolls into view.
    const checked = picker.querySelector('input:checked');
    if (checked) {
        loadFont(fonts[checked.value]);
        checked.closest('[data-font-row]')?.scrollIntoView({ block: 'nearest' });
    }
}
