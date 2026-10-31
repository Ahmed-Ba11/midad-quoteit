<?php

use App\Models\Quote;
use App\Models\User;
use Database\Seeders\LibrarySeeder;
use Illuminate\Support\Facades\Cache;

/*
 * The owner's mobile round (31 Oct): the card always in sight while designing, no shaking bars,
 * highlighting easy to reach, no double-tap zoom, fonts that switch cleanly, a Ruqʿa verse on the
 * sign-in tablet, and the maker's colophon in the footer.
 */

test('on a phone the title bar and the preview are one fixed dock, in both the editor and the studio', function (string $way) {
    $html = $this->actingAs(User::factory()->create())->get(route('quotes.create', ['as' => $way]))->assertOk()->getContent();

    // The dock opens before the tools, holds the title bar and the preview, and the publish bar is fixed.
    expect($html)->toContain('class="workbench-dock"')
        ->and(strpos($html, 'class="workbench-dock"'))->toBeLessThan(strpos($html, 'role="tablist"'))
        ->and(strpos($html, 'workbench-preview'))->toBeLessThan(strpos($html, 'role="tablist"'))
        ->and($html)->toContain('workbench-actions');

    // The site bar steps aside on phones here (its own title bar is in the dock).
    expect($html)->toContain('site-header sticky top-0 z-40 bg-paper md:bg-paper/90 md:backdrop-blur-md max-lg:hidden');
})->with(['text', 'card']);

test('the dock is sticky and solid on phones and dissolves on wide screens', function () {
    $css = file_get_contents(resource_path('css/studio.css'));

    expect($css)->toContain("@media (max-width: 1023.98px) {\n    .workbench-dock {\n        position: sticky;\n        top: 0;")
        ->toContain("    .workbench-actions {\n        position: fixed;")
        ->toContain("@media (min-width: 1024px) {\n    .workbench-dock {\n        display: contents;");
});

test('editing a saved quote uses the same dock', function () {
    $quote = Quote::factory()->for($owner = User::factory()->create())->create(['presentation' => 'card']);

    $this->actingAs($owner)->get(route('quotes.edit', $quote))->assertSee('class="workbench-dock"', false);
    $this->actingAs($owner)->get(route('quotes.edit', [$quote, 'as' => 'text']))->assertSee('class="workbench-dock"', false);
});

test('the site bar stays on every other page', function () {
    $this->get(route('home'))->assertDontSee('max-lg:hidden', false);
});

test('highlighting has its own tab in the studio, and the words come first in both tools', function (string $way) {
    $html = $this->actingAs(User::factory()->create())->get(route('quotes.create', ['as' => $way]))->getContent();
    $panel = substr($html, strpos($html, 'id="panel-emphasis"'));

    expect($html)->toContain('id="tab-emphasis"')
        ->and(strpos($panel, 'data-word-picker'))->toBeLessThan(strpos($panel, 'name="highlight_style"'));
})->with(['text', 'card']);

test('the «نص / بطاقة» switch answers the tap at once', function () {
    $this->actingAs(User::factory()->create())->get(route('quotes.create', ['as' => 'card']))->assertSee('data-view-switch', false);

    expect(file_get_contents(resource_path('js/app.js')))->toContain("document.querySelectorAll('[data-view-switch]')");
});

test('a double tap never zooms the page, and phone fields are 16px so focusing them never zooms', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain("[role=\"tab\"] {\n    touch-action: manipulation;")
        ->toContain('font-size: 16px;');

    // Pinch-zoom stays allowed for those who need it.
    $this->get(route('home'))->assertSee('content="width=device-width, initial-scale=1, viewport-fit=cover"', false)
        ->assertDontSee('user-scalable=no', false);
});

test('a chosen font reaches the card only once it is really loaded', function () {
    $picker = file_get_contents(resource_path('js/font-picker.js'));

    // Whole families only: the stylesheet link never carries a `&text=` subset.
    expect($picker)->toContain('link.onload = link.onerror = resolve;')
        ->toContain('link.href = `https://fonts.googleapis.com/css2?family=${font.google}&display=swap`;');
    foreach (['studio.js', 'editor.js'] as $script) {
        expect(file_get_contents(resource_path('js/'.$script)))->toContain('config.fonts[shownFont]');
    }
});

test('the sign-in tablet writes its verse in Ruqʿa, loaded once and whole', function () {
    $this->seed(LibrarySeeder::class);
    Cache::flush();
    $html = $this->get(route('login'))->assertOk()->getContent();

    expect(substr_count($html, 'family=Aref+Ruqaa:wght@400;700&display=swap'))->toBe(1)
        ->and(strpos($html, 'Aref+Ruqaa'))->toBeLessThan(strpos($html, '</head>'))
        ->and(file_get_contents(resource_path('css/backdrops.css')))->toContain("font-family: 'Aref Ruqaa', 'Amiri', serif;");
});

test('the footer closes with its maker: أحمد, software engineering student, X and LinkedIn', function () {
    $this->get(route('home'))
        ->assertSee('رَقَمَه')->assertSee('أحمد')->assertSee('هندسة البرمجيات')
        ->assertSee('href="https://x.com/SWEa_7"', false)->assertDontSee('github.com/Ahmed-Ba11', false)
        ->assertSee('href="https://www.linkedin.com/in/ahmed-basharaf-9633b6435"', false)
        ->assertDontSee('utm_source', false)
        ->assertSee('rel="me noopener noreferrer"', false);
});

test('on a phone the footer and the tab-bar padding step aside on the workbench, and stay everywhere else', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('quotes.create', ['as' => 'card']))->getContent();

    expect($html)->toContain('<footer class="foot mt-20 pt-6 max-lg:hidden">')
        ->and($html)->not->toContain('pb-[calc(4rem+env(safe-area-inset-bottom))]');

    $this->get(route('home'))->assertSee('<footer class="foot mt-20 pt-6">', false)
        ->assertSee('pb-[calc(4rem+env(safe-area-inset-bottom))]', false);
});

test('typing, short screens and the font list never squeeze or hide the tools on a phone', function () {
    $css = file_get_contents(resource_path('css/studio.css'));

    expect($css)->toContain('.writing-desk:has(textarea:focus')
        ->toContain('@media (max-width: 1023.98px) and (max-height: 520px)')
        ->toContain(".writing-desk .font-list {\n        max-height: none;")
        ->toContain("@media (hover: hover) {\n    .swatch:hover {");
});

test('the card is sized once per width, fonts never race, and publishing happens once', function () {
    expect(file_get_contents(resource_path('js/studio.js')))->toContain('const screenHeight = stableHeight;')
        ->and(file_get_contents(resource_path('js/font-picker.js')))->toContain('if (choice.checked) onChange?.(choice.value);')
        ->toContain('// ثمانية has no Google stylesheet')
        ->and(file_get_contents(resource_path('js/app.js')))->toContain('form.dataset.submitting')
        ->toContain('event.persisted');
});

test('the font filters, back button and the «نص / بطاقة» switch are 44px taps', function () {
    $this->actingAs(User::factory()->create())->get(route('quotes.create', ['as' => 'text']))
        ->assertSee('class="h-11 shrink-0 rounded-full border border-line px-3.5', false)
        ->assertSee('data-back class="grid size-11', false)
        ->assertSee('inline-flex h-11 flex-1 items-center', false);
});
