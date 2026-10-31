// What «محرّر النص» (editor.js) and مرسم الحروف (studio.js) share: the empty-preview text, the tool
// tabs, and the word picker that marks words to emphasise (sent as highlights[] with the form).

export const PLACEHOLDER = 'اكتب اقتباسك ليظهر هنا…';

export function initTabs(form) {
    const tabs = [...form.querySelectorAll('[data-tab]')];
    tabs.forEach((tab) => tab.addEventListener('click', () => tabs.forEach((other) => {
        other.setAttribute('aria-selected', String(other === tab));
        form.querySelector(`[data-panel="${other.dataset.tab}"]`).classList.toggle('hidden', other !== tab);
    })));
}

/**
 * The words of the quote as buttons; a tap toggles a word and calls onChange.
 * `surface` is the background class of a word button (each page has its own).
 */
export function createWordPicker(form, { surface, onChange }) {
    const wordPicker = form.querySelector('[data-word-picker]');
    const highlightInputs = form.querySelector('[data-highlight-inputs]');
    let highlighted = new Set([...highlightInputs.querySelectorAll('input')].map((input) => Number(input.value)));
    let lastWords = '';

    wordPicker.addEventListener('click', (event) => {
        const button = event.target.closest('[data-word]');
        if (!button) return;
        const index = Number(button.dataset.word);
        highlighted.has(index) ? highlighted.delete(index) : highlighted.add(index);
        onChange();
    });

    return {
        get highlighted() {
            return highlighted;
        },

        // Rebuild the buttons when the words change, and the hidden inputs from what is marked.
        sync(list) {
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
                    button.className = `min-h-11 rounded-lg border border-line ${surface} px-3 py-1.5 text-sm transition hover:border-accent aria-pressed:border-accent aria-pressed:bg-accent aria-pressed:text-paper`;
                    return button;
                }));
            }
            wordPicker.querySelectorAll('[data-word]').forEach((button) => {
                button.setAttribute('aria-pressed', String(highlighted.has(Number(button.dataset.word))));
            });
        },
    };
}
