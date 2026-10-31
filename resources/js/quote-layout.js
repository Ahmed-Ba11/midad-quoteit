// How a quote's words are laid out — verse, lines or prose — for the live preview.
// The same algorithm as app/Support/QuoteLayout.php (the source of truth, used when the quote is shown);
// both are checked against tests/fixtures/quote-layout.json.

const SEPARATORS = [
    /\t+/gu,
    /\s+(?:\*{1,3}|⁂|٭|✽|✳|※|◆|…+|\.{3,}|-{1,3}|–|—|\/{1,2}|\|{1,2})\s+/gu,
    / {2,}/gu,
];
const DIACRITICS = /[ؐ-ًؚ-ٰٟۖ-ۭـ]/gu;

export const letters = (text) => (text.replace(DIACRITICS, '').match(/\p{L}/gu) || []).length;
export const words = (text) => text.trim().split(/\s+/u).filter(Boolean).length;

const isHemistich = (half) => words(half) >= 2 || /^ـ|ـ$/u.test(half.trim());
const balance = (a, b) => Math.min(letters(a), letters(b)) / Math.max(letters(a), letters(b), 1);

function rhymeLetter(text) {
    const base = text.replace(DIACRITICS, '').replace(/[^\p{L}]/gu, '');
    const stripped = base.replace(/[اىيوهة]+$/u, '');
    return [...(stripped || base)].at(-1) ?? '';
}

function variation(values) {
    const mean = values.reduce((sum, value) => sum + value, 0) / values.length;
    const variance = values.reduce((sum, value) => sum + (value - mean) ** 2, 0) / values.length;
    return Math.sqrt(variance) / (mean || 1);
}

export function normalize(text) {
    return text
        .normalize('NFC')
        .replace(/\r\n?/g, '\n')
        .replace(/[  -   　]/g, ' ')
        .replace(/[​﻿]/g, '')
        .split('\n')
        .map((line) => line.replace(/^[ \t]+|[ \t]+$/g, ''))
        .join('\n')
        .replace(/\n{3,}/g, '\n\n')
        .trim();
}

// PHP measures the middle in UTF-8 bytes; so does this, so both pick the same separator.
const bytes = (text) => new TextEncoder().encode(text).length;

export function splitInline(line, minBalance = 0.55) {
    for (const pattern of SEPARATORS) {
        const hits = [...line.matchAll(pattern)];
        if (hits.length === 0) continue;

        const middle = bytes(line) / 2;
        const centre = (hit) => bytes(line.slice(0, hit.index)) + bytes(hit[0]) / 2;
        hits.sort((a, b) => Math.abs(centre(a) - middle) - Math.abs(centre(b) - middle));

        const hit = hits[0];
        const sadr = line.slice(0, hit.index).trim();
        const ajuz = line.slice(hit.index + hit[0].length).trim();

        if (isHemistich(sadr) && isHemistich(ajuz) && balance(sadr, ajuz) >= minBalance) {
            return { sadr, ajuz };
        }
    }

    return null;
}

/**
 * @param {string} raw
 * @param {'auto'|'verse'|'prose'} forced
 * @returns {{mode: 'verse'|'lines'|'prose', verses: {sadr: string, ajuz: string}[], lines: string[], paragraphs: string[], attribution: string|null, confidence: number}}
 */
export function parseLayout(raw, forced = 'auto') {
    let text = normalize(raw);
    let attribution = null;
    const allLines = text.split('\n');
    const last = allLines.at(-1);

    if (allLines.length > 1 && /^[—–-]\s*\S.{0,40}$/u.test(last)) {
        attribution = last.replace(/^[—–-]\s*/u, '');
        allLines.pop();
        text = allLines.join('\n').trim();
    }

    const nonEmpty = text.split('\n').filter((line) => line.trim() !== '');
    const result = (mode, parts, confidence) => ({
        mode,
        verses: parts.verses ?? [],
        lines: parts.lines ?? [],
        paragraphs: parts.paragraphs ?? [],
        attribution,
        confidence,
    });
    const prose = result('prose', { paragraphs: text.split(/\n\s*\n/u).map((p) => p.trim()).filter(Boolean) }, 0.5);

    if (forced === 'prose' || nonEmpty.length === 0) return prose;

    const loose = forced === 'verse';

    const splits = nonEmpty.map((line) => splitInline(line, loose ? 0.35 : 0.55));
    const hits = splits.filter(Boolean).length;

    if (hits > 0 && (hits / nonEmpty.length >= 0.8 || loose)) {
        return result('verse', { verses: nonEmpty.map((line, i) => splits[i] ?? { sadr: line, ajuz: '' }) }, 0.95);
    }

    const blocks = text.split(/\n\s*\n/u).map((block) => block.split('\n').filter((line) => line.trim() !== ''));
    const allPairs = blocks.length > 1 && blocks.every((block) => block.length === 2);
    const count = nonEmpty.length;
    const wordCounts = nonEmpty.map(words);
    const letterCounts = nonEmpty.map(letters);
    const shortEven = count >= 2 && count % 2 === 0
        && Math.min(...wordCounts) >= 2 && Math.max(...wordCounts) <= 10 && Math.max(...letterCounts) <= 55;

    const pairs = [];
    for (let i = 0; i + 1 < count; i += 2) pairs.push({ sadr: nonEmpty[i], ajuz: nonEmpty[i + 1] });
    const balanced = pairs.every((pair) => balance(pair.sadr, pair.ajuz) >= 0.6);

    if (allPairs || (shortEven && variation(letterCounts) <= 0.35 && balanced) || (loose && count >= 2)) {
        let confidence = allPairs ? 0.9 : (count === 2 ? 0.6 : 0.7);

        if (count >= 4) {
            const rhymes = {};
            pairs.forEach((pair) => {
                const letter = rhymeLetter(pair.ajuz);
                rhymes[letter] = (rhymes[letter] ?? 0) + 1;
            });
            confidence += Math.max(...Object.values(rhymes)) / pairs.length >= 0.7 ? 0.2 : (allPairs ? 0 : -0.2);
        }

        if (count === 2 && !allPairs && !(wordCounts[0] <= 8 && wordCounts[1] <= 8) && !loose) confidence = 0.3;
        if (count % 2 === 1 && loose) pairs.push({ sadr: nonEmpty[count - 1], ajuz: '' });

        if (confidence >= 0.55 || loose) return result('verse', { verses: pairs }, Math.min(confidence, 1));
    }

    if (loose && count === 1) {
        const characters = [...nonEmpty[0]];
        const middle = characters.length / 2;
        let best = null;
        characters.forEach((character, index) => {
            if (character === ' ' && (best === null || Math.abs(index - middle) < Math.abs(best - middle))) best = index;
        });

        if (best !== null && best > 0) {
            return result('verse', { verses: [{
                sadr: characters.slice(0, best).join('').trim(),
                ajuz: characters.slice(best).join('').trim(),
            }] }, 0.3);
        }
    }

    const sorted = [...wordCounts].sort((a, b) => a - b);
    if (count >= 2 && sorted[Math.floor(count / 2)] <= 12 && Math.max(...wordCounts) <= 25) {
        return result('lines', { lines: nonEmpty }, 0.6);
    }

    return prose;
}
