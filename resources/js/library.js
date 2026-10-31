// خزانة الأدب — shelf, poets' arcade, "لكلّ مقامٍ مقال" slider and the selection bar.
// Every panel is rendered by Blade; this file only shows the chosen one.

const STORAGE_KEY = 'raqeem.library.selection';
const MAX_VERSES = 2;
const MAX_LENGTH = 155;

export function initLibrary() {
    initShelf();
    initArcade();
    initOccasions();
    initSelection();
}

function initShelf() {
    const shelf = document.querySelector('[data-shelf]');
    const openBook = document.querySelector('[data-open-book]');
    if (!shelf || !openBook) return;

    const books = [...shelf.querySelectorAll('[data-book]')];
    const panels = [...openBook.querySelectorAll('[data-book-panel]')];

    const open = (slug, { scroll = true } = {}) => {
        books.forEach((book) => book.setAttribute('aria-pressed', String(book.dataset.book === slug)));
        shelf.querySelectorAll('[data-shelf-row]').forEach((row) => {
            row.toggleAttribute('data-has-selection', Boolean(row.querySelector('[aria-pressed="true"]')));
        });
        panels.forEach((panel) => (panel.hidden = panel.dataset.bookPanel !== slug));

        // Restart the page-opening animation.
        openBook.removeAttribute('data-opening');
        void openBook.offsetWidth;
        openBook.setAttribute('data-opening', '');

        if (scroll) {
            openBook.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'nearest' });
        }
    };

    books.forEach((book) => book.addEventListener('click', () => open(book.dataset.book)));
    if (books[0]) open(books[0].dataset.book, { scroll: false });
}

function initArcade() {
    const arcade = document.querySelector('[data-arcade]');
    if (!arcade) return;

    const arches = [...arcade.querySelectorAll('[data-poet]')];
    const panels = [...document.querySelectorAll('[data-poet-panel]')];

    // Choosing a poet opens their panel and goes straight to it — the reader never hunts for it.
    arches.forEach((arch) => {
        arch.addEventListener('click', () => {
            arches.forEach((other) => other.setAttribute('aria-pressed', String(other === arch)));
            panels.forEach((panel) => (panel.hidden = panel.dataset.poetPanel !== arch.dataset.poet));
            const panel = panels.find((candidate) => candidate.dataset.poetPanel === arch.dataset.poet);
            panel?.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
        });
    });
}

function initOccasions() {
    const root = document.querySelector('[data-occasions]');
    if (!root) return;

    const tabs = [...root.querySelectorAll('[data-occasion]')];
    const groups = [...root.querySelectorAll('[data-occasion-group]')];
    const dots = root.querySelector('[data-slide-dots]');
    let group = groups.find((candidate) => !candidate.hidden) ?? groups[0];
    let index = 0;

    const slides = () => [...group.querySelectorAll('[data-slide]')];

    const show = (next) => {
        const all = slides();
        index = (next + all.length) % all.length;
        all.forEach((slide, position) => (slide.hidden = position !== index));

        dots.replaceChildren(
            ...all.map((_, position) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.setAttribute('aria-label', `البيت ${position + 1} من ${all.length}`);
                dot.className = `size-2 rounded-full transition ${position === index ? 'w-5 bg-[#C9A45C]' : 'bg-[#F2ECE2]/30 hover:bg-[#F2ECE2]/60'}`;
                dot.addEventListener('click', () => show(position));
                return dot;
            }),
        );
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((other) => other.setAttribute('aria-selected', String(other === tab)));
            groups.forEach((candidate) => (candidate.hidden = candidate.dataset.occasionGroup !== tab.dataset.occasion));
            group = groups.find((candidate) => candidate.dataset.occasionGroup === tab.dataset.occasion);
            show(0);
        });
    });

    // In RTL the "previous" arrow sits on the right.
    root.querySelector('[data-slide-prev]').addEventListener('click', () => show(index - 1));
    root.querySelector('[data-slide-next]').addEventListener('click', () => show(index + 1));
    show(0);
}

/**
 * Selection: up to two verses, or one saying. Kept in sessionStorage so it survives moving
 * between the library, a diwan page and a poet page.
 */
function initSelection() {
    const bar = document.querySelector('[data-selection-bar]');
    if (!bar) return;

    let selection = load();

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-pick]');
        if (!button) return;

        const [type, id] = button.dataset.pick.split(':');
        const key = `${type}:${id}`;
        const existing = selection.findIndex((item) => item.key === key);

        if (existing !== -1) {
            selection.splice(existing, 1);
        } else if (type === 'saying') {
            selection = [{ key, type, id, text: button.dataset.pickText }];
        } else {
            selection = selection.filter((item) => item.type === 'verse').slice(-(MAX_VERSES - 1));
            selection.push({ key, type, id, text: button.dataset.pickText });
        }

        save();
        render();
    });

    // The selection travels in the link (to «محرّر النص» or مرسم الحروف); once used, start fresh next time.
    bar.querySelectorAll('[data-selection-go]').forEach((go) => go.addEventListener('click', () => {
        selection = [];
        save();
    }));

    bar.querySelector('[data-selection-clear]').addEventListener('click', () => {
        selection = [];
        save();
        render();
    });

    function render() {
        document.querySelectorAll('[data-pick]').forEach((button) => {
            button.setAttribute('aria-pressed', String(selection.some((item) => item.key === button.dataset.pick)));
        });

        bar.hidden = selection.length === 0;
        if (selection.length === 0) return;

        const isSaying = selection[0].type === 'saying';
        const tooLong = selection.map((item) => item.text.replace(' / ', '\n')).join('\n').length > MAX_LENGTH;
        bar.querySelectorAll('[data-selection-go]').forEach((go) => {
            const url = new URL(go.href, location.origin);
            url.search = '';
            selection.forEach((item) => url.searchParams.append(isSaying ? 'saying' : 'verses[]', item.id));
            url.searchParams.set('as', go.dataset.as);
            go.href = url.toString();
            go.setAttribute('aria-disabled', String(tooLong));
        });

        bar.querySelector('[data-selection-count]').textContent = isSaying
            ? 'مأثور مختار'
            : selection.length === 1
              ? 'بيت مختار (يمكنك إضافة بيت ثانٍ)'
              : 'بيتان مختاران';
        bar.querySelector('[data-selection-preview]').textContent = selection.map((item) => item.text).join('  ◆  ');
        bar.querySelector('[data-selection-warning]').classList.toggle('hidden', !tooLong);
    }

    function load() {
        try {
            return JSON.parse(sessionStorage.getItem(STORAGE_KEY)) ?? [];
        } catch {
            return [];
        }
    }

    function save() {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(selection));
        } catch {
            // Storage blocked: the selection still works on this page.
        }
    }

    render();
}
