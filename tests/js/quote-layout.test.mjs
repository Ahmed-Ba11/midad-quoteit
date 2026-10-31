// The live preview's layout (resources/js/quote-layout.js) against the shared cases, so it always
// agrees with the server (app/Support/QuoteLayout.php). Run by tests/Unit/QuoteLayoutTest.php, or alone:
//   node tests/js/quote-layout.test.mjs
import { readFileSync } from 'node:fs';
import { deepStrictEqual } from 'node:assert/strict';
import { parseLayout } from '../../resources/js/quote-layout.js';

const cases = JSON.parse(readFileSync(new URL('../fixtures/quote-layout.json', import.meta.url), 'utf8'));
let failed = 0;

for (const testCase of cases) {
    const layout = parseLayout(testCase.input, testCase.forced ?? 'auto');

    try {
        deepStrictEqual(layout.mode, testCase.mode);
        if (testCase.verses) deepStrictEqual(layout.verses, testCase.verses);
        if (testCase.lines) deepStrictEqual(layout.lines, testCase.lines);
        if (testCase.attribution) deepStrictEqual(layout.attribution, testCase.attribution);
    } catch (error) {
        failed++;
        console.error(`✗ ${testCase.name}: ${error.message}`);
    }
}

console.log(`${cases.length - failed}/${cases.length} layout cases agree`);
process.exit(failed === 0 ? 0 : 1);
