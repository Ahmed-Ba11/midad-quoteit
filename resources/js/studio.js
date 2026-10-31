// مرسم الحروف — a simple card studio. The preview is the real card markup (x-card-art): this file only
// sets its data-* attributes and CSS variables from the form, exactly as the server does from
// quotes.design, so what you see is what is published.
import { initFontPicker, loadFont } from './font-picker';
import { layoutWords, renderWords } from './quote-words';

const PLACEHOLDER = 'اكتب اقتباسك ليظهر هنا…';
const INK = '#1E1A17';
const PAPER = '#F5EFE4';

// WCAG contrast between two #RRGGBB colours, to keep the words readable on any background.
function luminance(hex) {
    const [r, g, b] = [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16) / 255)
        .map((c) => (c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4));
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}
const contrast = (a, b) => {
    const [x, y] = [luminance(a), luminance(b)].sort((m, n) => n - m);
    return (x + 0.05) / (y + 0.05);
};
const readableOn = (surface, preferred) => {
    if (contrast(preferred, surface) >= 4.5) return preferred;
    return contrast(INK, surface) >= contrast(PAPER, surface) ? INK : PAPER;
};

export function initStudio(form) {
    const config = JSON.parse(form.dataset.config);
    const art = form.querySelector('[data-quote-art]');
    const text = art.querySelector('[data-art-text]');
    const byline = art.querySelector('[data-art-byline]');
    const body = art.querySelector('.card-body');
    const box = art.querySelector('.card-box');
    const stage = form.querySelector('[data-preview-stage]');
    const frame = form.querySelector('[data-preview-frame]');
    const sizeInput = form.querySelector('[data-font-size]');
    const wordPicker = form.querySelector('[data-word-picker]');
    const highlightInputs = form.querySelector('[data-highlight-inputs]');
    const summary = form.querySelector('[data-details-summary]');

    const field = (name) => form.elements.namedItem(name);
    const checked = (name) => form.querySelector(`[name="${name}"]:checked`)?.value;
    const isOn = (name) => form.querySelector(`input[type="checkbox"][name="${name}"]`)?.checked ?? false;

    let highlighted = new Set([...highlightInputs.querySelectorAll('input')].map((input) => Number(input.value)));
    let lastWords = '';
    let autoSize = true;

    // Tabs
    const tabs = [...form.querySelectorAll('[data-tab]')];
    tabs.forEach((tab) => tab.addEventListener('click', () => tabs.forEach((other) => {
        other.setAttribute('aria-selected', String(other === tab));
        form.querySelector(`[data-panel="${other.dataset.tab}"]`).classList.toggle('hidden', other !== tab);
    })));

    // A palette sets the background, the box and the accent, and a text colour that reads on them.
    function applyPalette(key) {
        const palette = config.palettes[key];
        if (!palette) return;
        field('bg_color').value = palette.bg;
        field('design[box_color]').value = palette.box;
        art.style.setProperty('--accent', palette.accent);
        field('text_color').value = readableOn(isOn('design[box]') ? palette.box : palette.bg, palette.text);
    }
    form.querySelectorAll('[name="design[palette]"]').forEach((input) => input.addEventListener('change', () => applyPalette(input.value)));
    form.querySelector('input[type="checkbox"][name="design[box]"]').addEventListener('change', () => {
        field('text_color').value = readableOn(isOn('design[box]') ? field('design[box_color]').value : field('bg_color').value, field('text_color').value);
    });

    // Colour swatches fill their colour input.
    form.querySelectorAll('[data-set]').forEach((button) => button.addEventListener('click', () => {
        field(button.dataset.set).value = button.dataset.value;
        render();
    }));

    // Words to highlight
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
                button.className = 'rounded-lg border border-line bg-surface px-2.5 py-1 text-sm transition hover:border-accent aria-pressed:border-accent aria-pressed:bg-accent aria-pressed:text-paper';
                return button;
            }));
        }
        wordPicker.querySelectorAll('[data-word]').forEach((button) => button.setAttribute('aria-pressed', String(highlighted.has(Number(button.dataset.word)))));
    }

    // The card always fits the screen whatever its shape: as wide as it can be, but never taller than
    // about half the screen on a phone (two thirds on a wide screen), so the tools stay in reach.
    function fitFrame(ratio) {
        const [w, h] = ratio.aspect.split('/').map(Number);
        const maxHeight = innerWidth >= 1024 ? Math.min(innerHeight * 0.68, 608) : Math.min(innerHeight * 0.5, 460);
        const width = Math.min(stage.clientWidth, ratio.width, maxHeight * (w / h));
        frame.style.width = `${Math.floor(width)}px`;
    }

    // The words take the largest size that fits the card (unless the reader chose a step).
    // A hemistich is never broken over two lines: verse is sized so each صدر and عجز fits on one.
    const oneLine = (element) => element.offsetHeight <= parseFloat(getComputedStyle(element).lineHeight) * 1.4;
    const fits = () => body.scrollHeight <= body.clientHeight + 1 && box.scrollHeight <= box.clientHeight + 1
        && body.scrollWidth <= body.clientWidth + 1 && [...text.querySelectorAll('.hemi')].every(oneLine);
    function fitText() {
        const { min, max } = config.fontSize;
        let low = min;
        let high = max;
        while (low < high) {
            const mid = Math.ceil((low + high) / 2);
            art.style.setProperty('--fs', mid);
            if (fits()) low = mid;
            else high = mid - 1;
        }
        art.style.setProperty('--fs', low);
        sizeInput.value = low;
    }
    form.querySelectorAll('[data-size-step]').forEach((button) => button.addEventListener('click', () => {
        autoSize = false;
        const { min, max } = config.fontSize;
        sizeInput.value = Math.max(min, Math.min(max, Number(sizeInput.value) + Number(button.dataset.sizeStep) * 3));
        render();
    }));
    form.querySelector('[data-size-auto]').addEventListener('click', () => {
        autoSize = true;
        render();
    });

    function render() {
        const content = field('content').value;
        const layout = checked('layout') ?? 'auto';
        const kind = checked('kind');
        const author = { quoted: field('author')?.value.trim(), original: field('signature')?.value.trim() || `بقلم ${config.writer}` }[kind]
            ?? (field('author')?.value.trim() ?? '');
        const book = kind === 'anonymous' ? '' : (field('book')?.value.trim() ?? '');

        syncWordPicker(layoutWords(content, layout));
        if (content.trim() === '') {
            text.replaceChildren(PLACEHOLDER);
            text.style.opacity = '0.45';
        } else {
            text.style.opacity = '';
            renderWords(text, content, { layout, highlighted, author });
        }

        const page = field('page')?.value.trim();
        const line = [author, book, page && book ? `ص ${page}` : ''].filter(Boolean).join(' · ');
        byline.textContent = `— ${line}`;
        byline.classList.toggle('hidden', line === '');

        // Design → the card's attributes, exactly as x-card-art writes them.
        const ratio = config.ratios[checked('ratio')] ?? config.ratios.wide;
        const font = config.fonts[checked('font')] ?? config.fonts.amiri;
        Object.assign(art.dataset, {
            texture: checked('design[texture]') ?? 'none',
            divider: checked('design[divider]') ?? 'none',
            align: checked('design[align]') ?? 'center',
            box: isOn('design[box]') ? 'on' : 'off',
            padding: checked('design[box_padding]') ?? 'm',
            shadow: checked('design[box_shadow]') ?? 'soft',
            outline: isOn('design[box_outline]') ? 'on' : 'off',
        });
        art.style.aspectRatio = ratio.aspect;
        art.style.setProperty('--base', ratio.width);
        art.style.setProperty('--bg', field('bg_color').value);
        art.style.setProperty('--ink', field('text_color').value);
        art.style.setProperty('--hl', field('highlight_color').value);
        art.style.setProperty('--box', field('design[box_color]').value);
        art.style.setProperty('--dim', Number(field('design[dim]').value) / 100);
        text.style.fontFamily = font.stack;
        text.classList.toggle('italic', Boolean(font.italic));
        text.classList.toggle('hl-mark', checked('highlight_style') !== 'color');
        text.classList.toggle('hl-color', checked('highlight_style') === 'color');
        form.querySelector('[data-dim-output]').textContent = `${field('design[dim]').value}٪`;
        form.querySelector('[data-legacy-texture]').value = checked('design[texture]') === 'none' ? 0 : 1;
        form.querySelector('[data-box-options]').classList.toggle('opacity-50', !isOn('design[box]'));

        fitFrame(ratio);
        if (autoSize) fitText();
        else art.style.setProperty('--fs', sizeInput.value);

        if (summary && content.trim() !== '') summary.textContent = content.replace(/\s+/g, ' ').trim().slice(0, 60);
    }

    initFontPicker(form.querySelector('[data-font-picker]'), render);
    form.addEventListener('input', (event) => {
        if (!event.target.matches('[data-font-size]')) render();
    });
    form.addEventListener('change', render);
    window.addEventListener('resize', render);
    // Size the words again once the chosen font is really drawn (its letters are wider or narrower).
    loadFont(config.fonts[checked('font')]).then(render);
    render();
}
