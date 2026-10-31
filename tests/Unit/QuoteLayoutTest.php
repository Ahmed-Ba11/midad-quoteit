<?php

use App\Support\QuoteLayout;

/**
 * The shared cases in tests/fixtures/quote-layout.json: the PHP layout (the source of truth) and the
 * JS live preview (tests/js/quote-layout.test.mjs) must both pass every one.
 *
 * @return array<string, array{0: array<string, mixed>}>
 */
function quoteLayoutCases(): array
{
    $cases = json_decode(file_get_contents(dirname(__DIR__).'/fixtures/quote-layout.json'), true);

    return collect($cases)->mapWithKeys(fn (array $case) => [$case['name'] => [$case]])->all();
}

test('the layout is read from how people type verse and prose', function (array $case) {
    $layout = QuoteLayout::parse($case['input'], $case['forced'] ?? 'auto');

    expect($layout['mode'])->toBe($case['mode']);

    if (isset($case['verses'])) {
        expect($layout['verses'])->toBe($case['verses']);
    }

    if (isset($case['lines'])) {
        expect($layout['lines'])->toBe($case['lines']);
    }

    if (isset($case['attribution'])) {
        expect($layout['attribution'])->toBe($case['attribution']);
    }
})->with(quoteLayoutCases());

test('forcing verse on a single line splits it near the middle, keeping every word', function () {
    $line = 'العلم يرفع بيتاً لا عماد له والجهل يهدم بيت العز والشرف';
    $verse = QuoteLayout::parse($line, 'verse')['verses'][0];

    expect($verse['sadr'].' '.$verse['ajuz'])->toBe($line)
        ->and(QuoteLayout::words($verse['sadr']))->toBeGreaterThan(3)
        ->and(QuoteLayout::words($verse['ajuz']))->toBeGreaterThan(3);
});

test('the same text gives the same layout from the browser and from the server', function () {
    $node = trim((string) shell_exec(PHP_OS_FAMILY === 'Windows' ? 'where node 2>NUL' : 'command -v node 2>/dev/null'));

    if ($node === '') {
        $this->markTestSkipped('Node.js is not installed here.');
    }

    $output = [];
    exec('node '.escapeshellarg(dirname(__DIR__).'/js/quote-layout.test.mjs').' 2>&1', $output, $status);

    expect($status)->toBe(0, implode("\n", $output));
});
