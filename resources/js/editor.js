// «محرّر النص» — the text quote's tabs, word emphasis and live preview.
// The preview is the real quote markup (resources/views/components/quote-art.blade.php, text branch);
// its words are rebuilt by quote-words.js exactly as the server will set them.
import { initFontPicker } from './font-picker';
import { layoutWords, renderWords } from './quote-words';
import { PLACEHOLDER, createWordPicker, initTabs } from './workbench';

export function initEditor(form) {
    const config = JSON.parse(form.dataset.config);
    const figure = form.querySelector('.quote-text');
    const words = figure.querySelector('[data-text-words]');
    const byline = figure.querySelector('[data-text-byline]');
    const hint = form.querySelector('[data-layout-hint]');
    const summary = form.querySelector('[data-details-summary]');
    const customSwatch = form.querySelector('[data-custom-swatch]');
    const customColor = form.querySelector('[data-custom-color]');

    const field = (name) => form.elements.namedItem(name);
    const checked = (name) => form.querySelector(`[name="${name}"]:checked`)?.value;
    let shownFont = checked('font');

    initTabs(form);
    const picker = createWordPicker(form, { surface: 'bg-paper', onChange: () => render() });

    // «لون من اختيارك»: the colour input feeds its own swatch.
    customColor?.addEventListener('input', () => {
        customSwatch.value = customColor.value.toUpperCase();
        customSwatch.checked = true;
        render();
    });

    const describe = ({ mode, verses, lines }) => {
        const count = (n, one, two, many) => (n === 1 ? one : n === 2 ? two : `${n} ${many}`);
        if (mode === 'verse') return `شعر: ${count(verses, 'بيت واحد', 'بيتان', 'أبيات')}، كلّ صدرٍ بإزاء عجزه`;
        if (mode === 'lines') return `شعر حرّ: ${count(lines, 'سطر', 'سطران', 'أسطر')} كما كتبتها`;
        return 'نثر: فقرة كما كتبتها';
    };

    function render() {
        const content = field('content').value;
        const layout = checked('layout') ?? 'auto';
        const markStyle = checked('mark_style') ?? 'guillemets';
        const kind = checked('kind');
        const author = {
            quoted: field('author')?.value.trim(),
            original: field('signature')?.value.trim() || `بقلم ${config.writer}`,
        }[kind] ?? (field('author')?.value.trim() ?? '');
        const book = kind === 'anonymous' ? '' : (field('book')?.value.trim() ?? '');

        picker.sync(layoutWords(content, layout));

        if (content.trim() === '') {
            words.replaceChildren(PLACEHOLDER);
            words.style.opacity = '0.45';
            hint.textContent = '';
        } else {
            words.style.opacity = '';
            const detected = renderWords(words, content, {
                layout,
                highlighted: picker.highlighted,
                marks: markStyle === 'guillemets' ? ['«', '»'] : null,
                author,
            });
            hint.textContent = (layout === 'auto' ? 'اكتُشف — ' : '') + describe(detected);
        }

        const font = config.fonts[shownFont] ?? config.fonts.amiri;
        words.style.fontFamily = font.stack;
        words.classList.toggle('italic', Boolean(font.italic));
        words.classList.toggle('hl-mark', checked('highlight_style') !== 'color');
        words.classList.toggle('hl-color', checked('highlight_style') === 'color');
        figure.style.setProperty('--hl', checked('highlight_color') ?? '#8A3324');
        figure.dataset.mark = markStyle;

        const page = field('page')?.value.trim();
        const line = [author, book, page && book ? `ص ${page}` : ''].filter(Boolean).join(' · ');
        byline.textContent = `— ${line}`;
        byline.classList.toggle('hidden', line === '');

        if (summary && content.trim() !== '') summary.textContent = content.replace(/\s+/g, ' ').trim().slice(0, 60);
    }

    // The card changes font only once the new one is loaded: no fallback flash, no jump.
    initFontPicker(form.querySelector('[data-font-picker]'), (key) => {
        shownFont = key;
        render();
    });
    form.addEventListener('input', render);
    form.addEventListener('change', render);
    render();
}
