// A quote's words for the live previews — the same markup as resources/views/components/quote-words.blade.php:
// verse as .bayt (.hemi.sadr / .sep / .hemi.ajuz), free verse as .line, prose as .para; highlighted words as .hl.
import { parseLayout } from './quote-layout';

const splitWords = (text) => text.trim().split(/\s+/u).filter(Boolean);

/**
 * The words in reading order, separators left out — the indexes highlights[] refers to.
 */
export function layoutWords(content, layout) {
    const parsed = parseLayout(content, layout);
    if (parsed.mode === 'verse') return parsed.verses.flatMap((verse) => [...splitWords(verse.sadr), ...splitWords(verse.ajuz)]);
    if (parsed.mode === 'lines') return parsed.lines.flatMap(splitWords);
    return parsed.paragraphs.flatMap((paragraph) => paragraph.split('\n').flatMap(splitWords));
}

/**
 * Fill `container` with the words of `content`, set as `layout` (auto | verse | prose).
 * @returns {{mode: string, verses: number, lines: number}} what was detected, for the hint under «تلقائي».
 */
export function renderWords(container, content, { layout = 'auto', highlighted = new Set(), marks = null, author = '' } = {}) {
    const parsed = parseLayout(content, layout);
    let index = 0;

    // Words become pieces; neighbouring words with the same state merge, so adjacent highlights are ONE span.
    const pieces = (text) => {
        const fragment = document.createDocumentFragment();
        const runs = [];
        splitWords(text).forEach((word) => {
            const isHighlighted = highlighted.has(index++);
            const last = runs.at(-1);
            if (last && last.highlighted === isHighlighted) last.text += ' ' + word;
            else runs.push({ text: word, highlighted: isHighlighted });
        });
        runs.forEach((run, position) => {
            if (position > 0) fragment.append(' ');
            if (run.highlighted) {
                const span = document.createElement('span');
                span.className = 'hl';
                span.textContent = run.text;
                fragment.append(span);
            } else {
                fragment.append(run.text);
            }
        });
        return fragment;
    };
    const element = (tag, className, ...children) => {
        const node = document.createElement(tag);
        node.className = className;
        node.append(...children);
        return node;
    };
    const mark = (text) => element('span', 'words-mark', text);

    const nodes = [];

    if (parsed.mode === 'verse') {
        const poem = element('div', 'poem');
        parsed.verses.forEach((verse, i) => {
            const isFirst = i === 0;
            const isLast = i === parsed.verses.length - 1;
            const sadr = element('p', 'hemi sadr');
            if (isFirst && marks) sadr.append(mark(marks[0]));
            sadr.append(pieces(verse.sadr));
            const bayt = element('div', 'bayt', sadr);

            if (verse.ajuz !== '') {
                const separator = element('span', 'sep');
                separator.setAttribute('aria-hidden', 'true');
                const ajuz = element('p', 'hemi ajuz', pieces(verse.ajuz));
                if (isLast && marks) ajuz.append(mark(marks[1]));
                bayt.append(separator, element('span', 'sr-only', ' … '), ajuz);
            } else if (isLast && marks) {
                sadr.append(mark(marks[1]));
            }
            poem.append(bayt);
        });
        nodes.push(poem);
    } else if (parsed.mode === 'lines') {
        const lines = element('div', 'free-lines');
        parsed.lines.forEach((line, i) => {
            const node = element('p', 'line');
            if (i === 0 && marks) node.append(mark(marks[0]));
            node.append(pieces(line));
            if (i === parsed.lines.length - 1 && marks) node.append(mark(marks[1]));
            lines.append(node);
        });
        nodes.push(lines);
    } else {
        parsed.paragraphs.forEach((paragraph, i) => {
            const node = element('p', 'para');
            if (i === 0 && marks) node.append(mark(marks[0]));
            paragraph.split('\n').forEach((line, lineIndex) => {
                if (lineIndex > 0) node.append(document.createElement('br'));
                node.append(pieces(line));
            });
            if (i === parsed.paragraphs.length - 1 && marks) node.append(mark(marks[1]));
            nodes.push(node);
        });
    }

    if (parsed.attribution && author === '') nodes.push(element('p', 'words-attribution', `— ${parsed.attribution}`));

    const wrapper = element('div', 'quote-words', ...nodes);
    wrapper.dataset.mode = parsed.mode;
    container.replaceChildren(wrapper);

    return { mode: parsed.mode, verses: parsed.verses.length, lines: parsed.lines.length };
}
