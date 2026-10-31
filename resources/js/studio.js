// مرسم الحروف — the card studio. The preview is the real card markup (x-card-art): this file only sets
// its data-* attributes and CSS variables from the form, exactly as the server does from quotes.design,
// so what you see is what is published — and what «صورة» saves (modern-screenshot, in the browser).
// The reader's photo and the dedication never leave the browser.
import { domToBlob } from 'modern-screenshot';
import { initFontPicker, loadFont } from './font-picker';
import { layoutWords, renderWords } from './quote-words';

const PLACEHOLDER = 'اكتب اقتباسك ليظهر هنا…';
const INK = '#1E1A17';
const PAPER = '#F5EFE4';

// An image of the card cannot fetch fonts from another site, so each font file Google's stylesheet
// points to is fetched here and written into the stylesheet itself (as a data: URL).
async function inlineFontFiles(css) {
    const files = [...new Set([...css.matchAll(/url\((https:\/\/fonts\.gstatic\.com\/[^)]+)\)/g)].map((match) => match[1]))];
    const inlined = await Promise.all(files.map(async (file) => {
        const blob = await fetch(file).then((response) => response.blob());
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = () => resolve([file, reader.result]);
            reader.readAsDataURL(blob);
        });
    }).map((promise) => promise.catch(() => null)));
    return inlined.filter(Boolean).reduce((result, [file, dataUrl]) => result.replaceAll(file, dataUrl), css);
}

// WCAG contrast between two #RRGGBB colours.
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
    const photo = art.querySelector('[data-card-photo]');
    const frame = form.querySelector('[data-preview-frame]');
    const sizeInput = form.querySelector('[data-font-size]');
    const sizeHint = form.querySelector('[data-size-hint]');
    const wordPicker = form.querySelector('[data-word-picker]');
    const highlightInputs = form.querySelector('[data-highlight-inputs]');
    const summary = form.querySelector('[data-details-summary]');
    const status = form.querySelector('[data-export-status]');

    const field = (name) => form.elements.namedItem(name);
    const checked = (name) => form.querySelector(`[name="${name}"]:checked`)?.value;
    const isOn = (name) => form.querySelector(`input[type="checkbox"][name="${name}"]`)?.checked ?? false;
    const setRadio = (name, value) => {
        const input = form.querySelector(`[name="${name}"][value="${value}"]`);
        if (input) input.checked = true;
    };
    const setSwitch = (name, on) => {
        const input = form.querySelector(`input[type="checkbox"][name="${name}"]`);
        if (input) input.checked = Boolean(on);
    };

    let highlighted = new Set([...highlightInputs.querySelectorAll('input')].map((input) => Number(input.value)));
    let lastWords = '';
    let autoSize = true;
    let photoUrl = null;

    // Tabs
    const tabs = [...form.querySelectorAll('[data-tab]')];
    const openTab = (name) => tabs.forEach((tab) => {
        tab.setAttribute('aria-selected', String(tab.dataset.tab === name));
        form.querySelector(`[data-panel="${tab.dataset.tab}"]`).classList.toggle('hidden', tab.dataset.tab !== name);
    });
    tabs.forEach((tab) => tab.addEventListener('click', () => openTab(tab.dataset.tab)));

    // Tapping the card opens what was tapped: the words → font, the seal → gift, the rest → background.
    art.addEventListener('click', (event) => {
        if (event.target.closest('[data-art-text]')) openTab('font');
        else if (event.target.closest('.card-seal')) openTab('gift');
        else if (event.target.closest('.card-frame')) openTab('frame');
        else openTab('background');
    });

    // ---------- colours ----------
    function applyPalette(key) {
        const palette = config.palettes[key];
        if (!palette) return;
        field('bg_color').value = palette.bg;
        field('design[box_color]').value = palette.box;
        field('design[frame_color]').value = palette.frame;
        art.style.setProperty('--accent', palette.accent);
        field('text_color').value = readableOn(isOn('design[box]') ? palette.box : palette.bg, palette.text);
    }

    const swatchButtons = (selector, target) => form.querySelectorAll(selector).forEach((button) => {
        button.addEventListener('click', () => {
            field(target).value = button.dataset[Object.keys(button.dataset)[0]];
            render();
        });
    });
    swatchButtons('[data-text-color]', 'text_color');
    swatchButtons('[data-highlight-color]', 'highlight_color');
    swatchButtons('[data-box-color]', 'design[box_color]');
    swatchButtons('[data-frame-color]', 'design[frame_color]');

    form.querySelectorAll('[name="design[palette]"]').forEach((input) => input.addEventListener('change', () => applyPalette(input.value)));
    form.querySelector('input[type="checkbox"][name="design[box]"]').addEventListener('change', () => {
        const palette = config.palettes[checked('design[palette]')];
        if (palette) field('text_color').value = readableOn(isOn('design[box]') ? field('design[box_color]').value : field('bg_color').value, palette.text);
    });

    // ---------- templates and «فاجئني» ----------
    function applyTemplate(settings) {
        const design = { ...config.defaults, ...settings };
        setRadio('design[palette]', design.palette);
        ['gradient', 'texture', 'box_style', 'box_padding', 'box_shadow', 'frame', 'divider', 'align'].forEach((key) => setRadio(`design[${key}]`, design[key]));
        ['box', 'box_outline', 'big_marks', 'seal', 'brand'].forEach((key) => setSwitch(`design[${key}]`, design[key]));
        field('design[dim]').value = photoUrl ? Math.max(design.dim, 35) : design.dim;
        if (settings.font) setRadio('font', settings.font);
        if (settings.ratio) setRadio('ratio', settings.ratio);
        applyPalette(design.palette);
        loadFont(config.fonts[checked('font')]).then(render);
        autoSize = true;
        render();
    }

    form.querySelectorAll('[data-template]').forEach((button) => button.addEventListener('click', () => {
        form.querySelectorAll('[data-template]').forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
        applyTemplate(config.templates[button.dataset.template]);
    }));

    // Not random chaos: a palette, a frame that suits it, a box half the time, and only one heavy ornament.
    const pick = (list) => list[Math.floor(Math.random() * list.length)];
    form.querySelector('[data-surprise]').addEventListener('click', () => {
        const frameChoice = pick(['none', 'line', 'double', 'corners', 'arabesque', 'mihrab', 'girih']);
        const heavy = ['mihrab', 'girih'].includes(frameChoice);
        applyTemplate({
            palette: pick(Object.keys(config.palettes)),
            frame: frameChoice,
            texture: heavy ? pick(['none', 'paper']) : pick(['none', 'paper', 'linen', 'kraft', 'pattern']),
            box: !heavy && Math.random() < 0.5,
            box_shadow: pick(['none', 'soft', 'lifted']),
            divider: pick(['none', 'star', 'diamond', 'dots']),
            font: pick(['amiri', 'scheherazade', 'naskh', 'ruqaa', 'reem-kufi', 'messiri', 'mirza', 'katibeh', 'markazi', 'almarai']),
        });
    });

    // ---------- photo (stays in the browser) ----------
    const photoInput = form.querySelector('[data-photo-input]');
    const photoRemove = form.querySelector('[data-photo-remove]');
    photoInput.addEventListener('change', () => {
        const file = photoInput.files?.[0];
        if (!file || !file.type.startsWith('image/')) return;
        if (photoUrl) URL.revokeObjectURL(photoUrl);
        photoUrl = URL.createObjectURL(file);
        photo.src = photoUrl;
        photo.hidden = false;
        art.dataset.photo = 'on';
        photoRemove.hidden = false;
        if (Number(field('design[dim]').value) < 30) field('design[dim]').value = 35;
        // Never sent with the form: the file input has no name, and is cleared.
        photoInput.value = '';
        render();
    });
    photoRemove.addEventListener('click', () => {
        if (photoUrl) URL.revokeObjectURL(photoUrl);
        photoUrl = null;
        photo.removeAttribute('src');
        photo.hidden = true;
        art.dataset.photo = 'off';
        photoRemove.hidden = true;
        render();
    });

    // ---------- dedication (preview and saved image only) ----------
    const giftToggle = form.querySelector('[data-gift-toggle]');
    const giftFields = form.querySelector('[data-gift-fields]');
    giftToggle.addEventListener('change', () => {
        giftFields.hidden = !giftToggle.checked;
        if (giftToggle.checked && !isOn('design[seal]')) setSwitch('design[seal]', true);
        render();
    });

    // ---------- words and highlights ----------
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

    // ---------- size: fit the words to the card, or a step smaller / larger ----------
    const fits = () => body.scrollHeight <= body.clientHeight + 1 && box.scrollHeight <= box.clientHeight + 1 && body.scrollWidth <= body.clientWidth + 1;
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
        return low;
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

    // ---------- render ----------
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
        const gradient = config.gradients[checked('design[gradient]')] ?? {};
        const wax = config.seals[checked('design[seal_color]')] ?? config.seals.qani;
        const font = config.fonts[checked('font')] ?? config.fonts.amiri;
        Object.assign(art.dataset, {
            gradient: checked('design[gradient]') ?? 'none',
            texture: checked('design[texture]') ?? 'none',
            frame: checked('design[frame]') ?? 'none',
            box: isOn('design[box]') ? 'on' : 'off',
            boxStyle: checked('design[box_style]') ?? 'solid',
            padding: checked('design[box_padding]') ?? 'm',
            shadow: checked('design[box_shadow]') ?? 'soft',
            outline: isOn('design[box_outline]') ? 'on' : 'off',
            divider: checked('design[divider]') ?? 'none',
            align: checked('design[align]') ?? 'center',
            brand: isOn('design[brand]') ? 'on' : 'off',
            marks: isOn('design[big_marks]') ? 'on' : 'off',
            seal: isOn('design[seal]') ? 'on' : 'off',
            sealPosition: checked('design[seal_position]') ?? 'corner',
            sealEmblem: checked('design[seal_emblem]') ?? 'nib',
            gift: giftToggle.checked ? 'on' : 'off',
        });
        art.style.aspectRatio = ratio.aspect;
        art.style.setProperty('--base', ratio.width);
        art.style.setProperty('--bg', field('bg_color').value);
        art.style.setProperty('--ink', field('text_color').value);
        art.style.setProperty('--hl', field('highlight_color').value);
        art.style.setProperty('--box', field('design[box_color]').value);
        art.style.setProperty('--frame', field('design[frame_color]').value);
        art.style.setProperty('--dim', Number(field('design[dim]').value) / 100);
        art.style.setProperty('--grad-from', gradient.from ?? 'transparent');
        art.style.setProperty('--grad-to', gradient.to ?? 'transparent');
        art.style.setProperty('--wax-light', wax.light);
        art.style.setProperty('--wax', wax.base);
        art.style.setProperty('--wax-dark', wax.dark);
        frame.style.maxWidth = `${ratio.width}px`;
        text.style.fontFamily = font.stack;
        text.classList.toggle('italic', Boolean(font.italic));
        text.classList.toggle('hl-mark', checked('highlight_style') !== 'color');
        text.classList.toggle('hl-color', checked('highlight_style') === 'color');
        form.querySelector('[data-dim-output]').textContent = `${field('design[dim]').value}٪`;
        form.querySelector('[data-legacy-texture]').value = checked('design[texture]') === 'none' ? 0 : 1;
        form.querySelector('[data-box-options]').classList.toggle('opacity-50', !isOn('design[box]'));
        form.querySelector('[data-seal-options]').classList.toggle('opacity-50', !isOn('design[seal]'));

        // The dedication's lines, shown only when it is on and filled.
        const gift = (key) => (giftToggle.checked ? form.querySelector(`[data-gift="${key}"]`).value.trim() : '');
        [['to', 'إلى: '], ['from', 'من: '], ['message', '']].forEach(([key, prefix]) => {
            const node = art.querySelector(`[data-gift-${key}]`);
            node.textContent = gift(key) ? prefix + gift(key) : '';
            node.hidden = !gift(key);
        });
        const initial = art.querySelector('[data-seal-initial]');
        if (initial) initial.textContent = (gift('from') || config.writer).trim().charAt(0);

        // Size: fit, unless the reader chose a step.
        if (autoSize) fitText();
        else art.style.setProperty('--fs', sizeInput.value);

        const long = content.trim().length > 110 && checked('ratio') !== 'story';
        sizeHint.classList.toggle('hidden', !long);
        sizeHint.textContent = long ? 'النص طويل: مقاس «قصة» ٩:١٦ يعطيه مساحة أكبر.' : '';

        if (summary && content.trim() !== '') summary.textContent = content.replace(/\s+/g, ' ').trim().slice(0, 60);
    }

    // ---------- save as an image / share ----------
    const saveButton = form.querySelector('[data-save-image]');
    const shareButton = form.querySelector('[data-share-image]');
    if (navigator.canShare?.({ files: [new File([''], 'x.png', { type: 'image/png' })] })) shareButton.hidden = false;

    // Google's stylesheets are cross-origin, so the image maker cannot read their @font-face rules; we
    // fetch them (Google Fonts allows it) and hand them over, so the saved card keeps its fonts.
    const fontCss = new Map();
    async function fontFaces() {
        const families = [config.fonts[checked('font')]?.google, 'Amiri:wght@400;700', 'Aref+Ruqaa:wght@400;700', 'IBM+Plex+Sans+Arabic:wght@300;400']
            .filter(Boolean);
        const url = `https://fonts.googleapis.com/css2?${families.map((family) => `family=${family}`).join('&')}&display=swap`;
        if (!fontCss.has(url)) fontCss.set(url, await inlineFontFiles(await fetch(url).then((response) => (response.ok ? response.text() : '')).catch(() => '')));

        // Our own faces (ثمانية) are in same-origin stylesheets, which can be read directly.
        const local = [...document.styleSheets].flatMap((sheet) => {
            try {
                return [...sheet.cssRules].filter((rule) => rule instanceof CSSFontFaceRule).map((rule) => rule.cssText);
            } catch {
                return [];
            }
        });

        return [fontCss.get(url), ...local].join('\n');
    }

    async function cardImage() {
        status.textContent = 'تُجهَّز الصورة…';
        status.classList.remove('hidden');
        await loadFont(config.fonts[checked('font')]);
        await document.fonts.ready;
        const scale = Math.max(2, 1080 / art.offsetWidth);
        const blob = await domToBlob(art, {
            scale,
            type: 'image/png',
            font: { cssText: await fontFaces(), preferredFormat: 'woff2' },
            style: { borderRadius: '0', boxShadow: 'none' },
        });
        status.textContent = '';
        status.classList.add('hidden');
        return new File([blob], `raqeem-${new Date().toISOString().slice(0, 10)}.png`, { type: 'image/png' });
    }

    function stamp() {
        if (art.dataset.seal !== 'on') return;
        art.classList.remove('is-stamping');
        void art.offsetWidth;
        art.classList.add('is-stamping');
    }

    saveButton.addEventListener('click', async () => {
        saveButton.disabled = true;
        try {
            const file = await cardImage();
            stamp();
            const link = document.createElement('a');
            link.href = URL.createObjectURL(file);
            link.download = file.name;
            link.click();
            setTimeout(() => URL.revokeObjectURL(link.href), 4000);
        } catch {
            status.textContent = 'تعذّر حفظ الصورة، جرّب مرة أخرى.';
            status.classList.remove('hidden');
        } finally {
            saveButton.disabled = false;
        }
    });

    shareButton.addEventListener('click', async () => {
        try {
            const file = await cardImage();
            await navigator.share({ files: [file], title: 'رقيم' });
        } catch {
            // Cancelled, or sharing files is not allowed here; the save button still works.
        }
    });

    initFontPicker(form.querySelector('[data-font-picker]'), () => render());
    form.addEventListener('input', (event) => {
        if (event.target.matches('[data-font-size]')) return;
        render();
    });
    form.addEventListener('change', render);
    window.addEventListener('resize', () => autoSize && fitText());
    document.fonts.ready.then(render);
    render();
}
