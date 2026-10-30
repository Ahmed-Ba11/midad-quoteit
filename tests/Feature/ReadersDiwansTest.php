<?php

use App\Models\Diwan;
use App\Models\DiwanVerse;
use App\Models\User;

$validDiwan = fn (array $overrides = []) => [
    'poet_name' => 'ابن زيدون', 'poet_death_year' => 1071, 'title' => 'من نونيته',
    'source' => 'ديوان ابن زيدون، ص ١٢', 'color' => 'mauve', ...$overrides,
];

test('a reader can publish the diwan of a poet who died more than 50 years ago', function () use ($validDiwan) {
    $reader = User::factory()->create();

    $this->actingAs($reader)->post(route('diwans.store'), $validDiwan(['is_public' => '1']))->assertSessionHasNoErrors();

    $diwan = Diwan::sole();
    expect($diwan->is_public)->toBeTrue()->and($diwan->user_id)->toBe($reader->id);
    $this->get(route('diwans.index'))->assertSee('من نونيته');
});

test('a living or recently died poet can only be kept as a private notebook', function (?int $deathYear) use ($validDiwan) {
    $reader = User::factory()->create();

    $this->actingAs($reader)->post(route('diwans.store'), $validDiwan(['poet_death_year' => $deathYear, 'is_public' => '1']))
        ->assertSessionHasErrors('is_public');

    $this->actingAs($reader)->post(route('diwans.store'), $validDiwan(['poet_death_year' => $deathYear]))->assertSessionHasNoErrors();
    expect(Diwan::sole()->is_public)->toBeFalse();
})->with([
    'living' => [null],
    'died 10 years ago' => [(int) date('Y') - 10],
    'died exactly 50 years ago' => [(int) date('Y') - 50],
]);

test('a diwan needs a poet, a title and a source', function (array $input, string $field) use ($validDiwan) {
    $this->actingAs(User::factory()->create())
        ->post(route('diwans.store'), $validDiwan($input))
        ->assertSessionHasErrors($field);
})->with([
    'no poet' => [['poet_name' => ''], 'poet_name'],
    'no title' => [['title' => ''], 'title'],
    'no source' => [['source' => ''], 'source'],
    'future death year' => [['poet_death_year' => (int) date('Y') + 1], 'poet_death_year'],
    'unknown colour' => [['color' => 'neon'], 'color'],
]);

test('private diwans are hidden from everyone but their owner', function () {
    $owner = User::factory()->create();
    $diwan = Diwan::factory()->private()->for($owner)->create(['title' => 'دفتر بدر']);
    $verse = $diwan->verses()->create(['sadr' => 'صدر خاص', 'ajuz' => 'عجز خاص']);

    $this->get(route('diwans.show', $diwan))->assertForbidden();
    $this->actingAs(User::factory()->create())->get(route('diwans.show', $diwan))->assertForbidden();
    $this->get(route('diwans.index'))->assertDontSee('دفتر بدر');
    $this->get(route('quotes.create', ['diwan_verse' => $verse->id]))->assertDontSee('صدر خاص');

    $this->actingAs($owner)->get(route('diwans.show', $diwan))->assertOk()->assertSee('صدر خاص');
    $this->get(route('diwans.index'))->assertSee('دفتر بدر');
});

test('only the owner can add, remove or change verses', function () {
    $diwan = Diwan::factory()->create();
    $verse = $diwan->verses()->create(['sadr' => 'الصدر', 'ajuz' => 'العجز']);
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->post(route('diwans.verses.store', $diwan), ['sadr' => 'دخيل'])->assertForbidden();
    $this->actingAs($stranger)->delete(route('diwans.verses.destroy', [$diwan, $verse]))->assertForbidden();
    $this->actingAs($stranger)->put(route('diwans.update', $diwan), ['title' => 'x'])->assertForbidden();
    $this->actingAs($stranger)->delete(route('diwans.destroy', $diwan))->assertForbidden();

    expect($diwan->verses()->count())->toBe(1);

    $this->actingAs($diwan->user)->post(route('diwans.verses.store', $diwan), ['sadr' => 'بيت جديد', 'ajuz' => 'عجزه'])->assertSessionHasNoErrors();
    expect($diwan->verses()->count())->toBe(2);
});

test('a verse can only be deleted through its own diwan', function () {
    $owner = User::factory()->create();
    $mine = Diwan::factory()->for($owner)->create();
    $someoneElses = Diwan::factory()->create();
    $theirVerse = $someoneElses->verses()->create(['sadr' => 'بيت غيري']);

    $this->actingAs($owner)->delete(route('diwans.verses.destroy', [$mine, $theirVerse]))->assertNotFound();
    expect(DiwanVerse::find($theirVerse->id))->not->toBeNull();
});

test('designing a verse from a public diwan prefills its poet and source', function () {
    $diwan = Diwan::factory()->create(['poet_name' => 'ابن زيدون', 'source' => 'ديوان ابن زيدون']);
    $verse = $diwan->verses()->create(['sadr' => 'أضحى التنائي', 'ajuz' => 'وناب عن طيب']);

    $this->actingAs(User::factory()->create())->get(route('quotes.create', ['diwan_verse' => $verse->id]))
        ->assertOk()->assertSee('أضحى التنائي')->assertSee('ابن زيدون')->assertSee('ديوان ابن زيدون');
});

test('a diwan source shows as a link only when it is a web address', function () {
    $linked = Diwan::factory()->create(['source' => 'https://ar.wikisource.org/wiki/x']);
    $scripted = Diwan::factory()->create(['source' => 'javascript:alert(1)']);

    expect($linked->sourceUrl())->toBe('https://ar.wikisource.org/wiki/x')->and($scripted->sourceUrl())->toBeNull();
    $this->get(route('diwans.show', $scripted))->assertOk()->assertDontSee('href="javascript', false);
});
