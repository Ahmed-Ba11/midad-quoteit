// رواق الحرف — tabs, palettes, word highlighting and the live preview.
// The preview mirrors resources/views/components/quote-art.blade.php.

const PLACEHOLDER = 'اكتب اقتباسك ليظهر هنا…';

export function initStudio(form) {
    const config = JSON.parse(form.dataset.config);
    const art = form.querySelector('[data-quote-art]');
    const artText = art.querySelector('[data-art-text]');
    const artByline = art.querySelector('[data-art-byline]');
    const artTexture = art.querySelector('[data-art-texture]');
    const frame = form.querySelector('[data-preview-frame]');
    const wordPicker = form.querySelector('[data-word-picker]');
    const highlightInputs = form.querySelector('[data-highlight-inputs]');
    const storyPreview = form.querySelector('[data-story-preview]');
    const storyText = form.querySelector('[data-story-text]');
    const fontSizeOutput = form.querySelector('[data-output="font_size"]');

    let highlighted = new Set(
        [...highlightInputs.querySelectorAll('input')].map((input) => Number(input.value)),
    );
    let lastWords = '';

    const field = (name) => form.elements.namedItem(name);
    const checkedValue = (name) => form.querySelector(`[name="${name}"]:checked`)?.value;
    const words = () => field('content').value.trim().split(/\s+/).filter(Boolean);

    // Tabs
    const tabs = [...form.querySelectorAll('[data-tab]')];
    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((other) => {
                const isActive = other === tab;
                other.setAttribute('aria-selected', String(isActive));
                form.querySelector(`[data-panel="${other.dataset.tab}"]`).classList.toggle('hidden', !isActive);
            });
        });
    });

    // Ready-made palettes fill the three custom colour inputs.
    form.querySelectorAll('[data-palette]').forEach((button) => {
        button.addEventListener('click', () => {
            const palette = config.palettes[button.dataset.palette];
            field('bg_color').value = palette.bg;
            field('text_color').value = palette.text;
            field('highlight_color').value = palette.highlight;
            render();
        });
    });

    // Word picker: toggling a word updates the hidden highlights[] inputs.
    wordPicker.addEventListener('click', (event) => {
        const button = event.target.closest('[data-word]');
        if (!button) return;

        const index = Number(button.dataset.word);
        highlighted.has(index) ? highlighted.delete(index) : highlighted.add(index);
        render();
    });

    function syncHighlightInputs(wordCount) {
        highlighted = new Set([...highlighted].filter((index) => index < wordCount));
        highlightInputs.replaceChildren(
            ...[...highlighted].sort((a, b) => a - b).map((index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'highlights[]';
                input.value = index;
                return input;
            }),
        );
    }

    function renderWordPicker(list) {
        if (list.join(' ') !== lastWords) {
            lastWords = list.join(' ');
            wordPicker.replaceChildren(
                ...list.map((word, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.word = index;
                    button.textContent = word;
                    button.className =
                        'rounded-lg border border-line bg-paper px-2.5 py-1 text-sm transition hover:border-accent aria-pressed:border-accent aria-pressed:bg-accent aria-pressed:text-paper';
                    return button;
                }),
            );
        }

        wordPicker.querySelectorAll('[data-word]').forEach((button) => {
            button.setAttribute('aria-pressed', String(highlighted.has(Number(button.dataset.word))));
        });
    }

    // Merge neighbouring words with the same highlight state into one piece (one continuous highlight).
    function renderText(list) {
        if (list.length === 0) {
            artText.replaceChildren(PLACEHOLDER);
            artText.style.opacity = '0.45';
            return;
        }

        artText.style.opacity = '';
        const pieces = [];
        list.forEach((word, index) => {
            const isHighlighted = highlighted.has(index);
            const last = pieces.at(-1);
            if (last && last.highlighted === isHighlighted) {
                last.text += ' ' + word;
            } else {
                pieces.push({ text: word, highlighted: isHighlighted });
            }
        });

        const nodes = [];
        pieces.forEach((piece, index) => {
            if (index > 0) nodes.push(' ');
            if (piece.highlighted) {
                const span = document.createElement('span');
                span.className = 'hl';
                span.textContent = piece.text;
                nodes.push(span);
            } else {
                nodes.push(piece.text);
            }
        });
        artText.replaceChildren(...nodes);
    }

    function render() {
        const list = words();
        syncHighlightInputs(list.length);
        renderWordPicker(list);
        renderText(list);

        const font = config.fonts[checkedValue('font')] ?? config.fonts.amiri;
        const ratio = config.ratios[checkedValue('ratio')] ?? config.ratios.wide;
        const fontSize = field('font_size').value;

        art.style.aspectRatio = ratio.aspect;
        art.style.setProperty('--base', ratio.width);
        art.style.setProperty('--fs', fontSize);
        art.style.setProperty('--bg', field('bg_color').value);
        art.style.setProperty('--ink', field('text_color').value);
        art.style.setProperty('--hl', field('highlight_color').value);
        frame.style.maxWidth = `${ratio.width}px`;
        fontSizeOutput.textContent = fontSize;

        artText.style.fontFamily = font.stack;
        artText.classList.toggle('italic', Boolean(font.italic));
        artText.classList.toggle('hl-mark', checkedValue('highlight_style') !== 'color');
        artText.classList.toggle('hl-color', checkedValue('highlight_style') === 'color');
        artTexture.classList.toggle('hidden', !form.querySelector('input[type="checkbox"][name="texture"]').checked);

        const byline = [field('author').value.trim(), field('book').value.trim()].filter(Boolean).join(' · ');
        artByline.textContent = `— ${byline}`;
        artByline.classList.toggle('hidden', byline === '');

        const story = field('story').value.trim();
        storyText.textContent = story;
        storyPreview.classList.toggle('hidden', story === '');
    }

    form.addEventListener('input', render);
    form.addEventListener('change', render);
    render();
}
