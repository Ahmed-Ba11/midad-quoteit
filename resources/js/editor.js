// «محرّر النص» — the text quote's tabs, word emphasis and live preview.
// The preview is the real quote markup (resources/views/components/quote-art.blade.php, text branch);
// its words are rebuilt by quote-words.js exactly as the server will set them.
import { initFontPicker } from './font-picker';
import { layoutWords, renderWords } from './quote-words';

const PLACEHOLDER = 'اكتب اقتباسك ليظهر هنا…';

export function initEditor(form) {
    const config = JSON.parse(form.dataset.config);
    const figure = form.querySelector('.quote-text');
    const words = figure.querySelector('[data-text-words]');
    const byline = figure.querySelector('[data-text-byline]');
    const hint = form.querySelector('[data-layout-hint]');
    const wordPicker = form.querySelector('[data-word-picker]');
    const highlightInputs = form.querySelector('[data-highlight-inputs]');
    const summary = form.querySelector('[data-details-summary]');
    const customSwatch = form.querySelector('[data-custom-swatch]');
    const customColor = form.querySelector('[data-custom-color]');

    const field = (name) => form.elements.namedItem(name);
    const checked = (name) => form.querySelector(`[name="${name}"]:checked`)?.value;
    let highlighted = new Set([...highlightInputs.querySelectorAll('input')].map((input) => Number(input.value)));
    let lastWords = '';

    // Tabs
    const tabs = [...form.querySelectorAll('[data-tab]')];
    tabs.forEach((tab) => tab.addEventListener('click', () => {
        tabs.forEach((other) => {
            other.setAttribute('aria-selected', String(other === tab));
            form.querySelector(`[data-panel="${other.dataset.tab}"]`).classList.toggle('hidden', other !== tab);
        });
    }));

    // «لون من اختيارك»: the colour input feeds its own swatch.
    customColor?.addEventListener('input', () => {
        customSwatch.value = customColor.value.toUpperCase();
        customSwatch.checked = true;
        render();
    });

    wordPicker.addEventListener('click', (event) => {
        const button = event.target.closest('[data-word]');
        if (!button) return;
        const index = Number(button.dataset.word);
        highlighted.has(index) ? highlighted.delete(index) : highlighted.add(index);
        render();
    });

    function syncWordPicker(list) {
        highlighted = new Set([...highlighted].filter((index) => index < list.length));
        highlightInputs.replaceChildren(...[...highlighted].sort((a, b) => a - b).map((index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'highlights[]';
            input.value = index;
            return input;
        }));

        if (list.join(' ') !== lastWords) {
            lastWords = list.join(' ');
            wordPicker.replaceChildren(...list.map((word, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.word = index;
                button.textContent = word;
                button.className = 'min-h-11 rounded-lg border border-line bg-paper px-3 py-1.5 text-sm transition hover:border-accent aria-pressed:border-accent aria-pressed:bg-accent aria-pressed:text-paper';
                return button;
            }));
        }
        wordPicker.querySelectorAll('[data-word]').forEach((button) => {
            button.setAttribute('aria-pressed', String(highlighted.has(Number(button.dataset.word))));
        });
    }

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

        syncWordPicker(layoutWords(content, layout));

        if (content.trim() === '') {
            words.replaceChildren(PLACEHOLDER);
            words.style.opacity = '0.45';
            hint.textContent = '';
        } else {
            words.style.opacity = '';
            const detected = renderWords(words, content, {
                layout,
                highlighted,
                marks: markStyle === 'guillemets' ? ['«', '»'] : null,
                author,
            });
            hint.textContent = (layout === 'auto' ? 'اكتُشف — ' : '') + describe(detected);
        }

        const font = config.fonts[checked('font')] ?? config.fonts.amiri;
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

    initFontPicker(form.querySelector('[data-font-picker]'), render);
    form.addEventListener('input', render);
    form.addEventListener('change', render);
    render();
}
