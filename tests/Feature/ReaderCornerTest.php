<?php

use App\Models\Anthology;
use App\Models\Poet;
use App\Models\Quote;
use App\Models\Saying;
use App\Models\User;
use App\Models\Verse;
use Database\Seeders\LibrarySeeder;

test('a reader pins one of their own quotes to the top of their corner, and can unpin it', function () {
    $reader = User::factory()->create();
    $quote = Quote::factory()->for($reader)->create(['content' => 'السطر المثبّت في ركني']);

    $this->actingAs($reader)->post(route('profile.pin', $quote));
    expect($reader->fresh()->pinned_quote_id)->toBe($quote->id);

    $this->get(route('profile.show', $reader))->assertOk()->assertSee('مثبّت في ركني')->assertSee('السطر المثبّت في ركني');

    $this->actingAs($reader)->post(route('profile.pin', $quote));
    expect($reader->fresh()->pinned_quote_id)->toBeNull();
});

test("nobody can pin someone else's quote", function () {
    $quote = Quote::factory()->create();

    $this->actingAs(User::factory()->create())->post(route('profile.pin', $quote))->assertForbidden();
});

test('the corner colour must be one of the palettes', function () {
    $reader = User::factory()->create();

    $this->actingAs($reader)->patch(route('profile.bio'), ['bio' => 'قارئ', 'accent' => 'navy'])->assertSessionHasNoErrors();
    expect($reader->fresh()->accent)->toBe('navy');

    $this->actingAs($reader)->patch(route('profile.bio'), ['accent' => 'red; background:url(x)'])->assertSessionHasErrors('accent');
});

test('a reader builds an anthology from a quote page and toggles quotes in and out', function () {
    $reader = User::factory()->create();
    $quote = Quote::factory()->create();

    $this->actingAs($reader)->from(route('quotes.show', $quote))
        ->post(route('anthologies.store'), ['title' => 'أبيات الصبر', 'palette' => 'sage', 'is_public' => '1', 'quote_id' => $quote->id])
        ->assertRedirect(route('quotes.show', $quote).'#anthologies');

    $anthology = Anthology::sole();
    expect($anthology->user_id)->toBe($reader->id)->and($anthology->quotes->modelKeys())->toBe([$quote->id]);

    $this->actingAs($reader)->post(route('anthologies.toggle', [$anthology, $quote]));
    expect($anthology->quotes()->count())->toBe(0);
});

test('only the owner can change, fill or delete an anthology', function () {
    $anthology = Anthology::factory()->create();
    $stranger = User::factory()->create();
    $quote = Quote::factory()->create();

    $this->actingAs($stranger)->post(route('anthologies.toggle', [$anthology, $quote]))->assertForbidden();
    $this->actingAs($stranger)->put(route('anthologies.update', $anthology), ['title' => 'مسروقة'])->assertForbidden();
    $this->actingAs($stranger)->delete(route('anthologies.destroy', $anthology))->assertForbidden();

    expect($anthology->fresh()->title)->not->toBe('مسروقة');
});

test('private anthologies stay with their owner', function () {
    $owner = User::factory()->create();
    $private = Anthology::factory()->private()->for($owner)->create(['title' => 'دفتر سرّي']);
    Anthology::factory()->for($owner)->create(['title' => 'مختارات عامة']);

    $this->get(route('anthologies.show', $private))->assertForbidden();
    $this->actingAs(User::factory()->create())->get(route('anthologies.show', $private))->assertForbidden();
    $this->actingAs($owner)->get(route('anthologies.show', $private))->assertOk();

    auth()->logout();
    $this->get(route('profile.show', [$owner, 'tab' => 'anthologies']))
        ->assertSee('مختارات عامة')->assertDontSee('دفتر سرّي');
});

test('an anthology must have a sensible name and palette', function (array $input, string $field) {
    $this->actingAs(User::factory()->create())
        ->post(route('anthologies.store'), ['title' => 'مختارات', ...$input])
        ->assertSessionHasErrors($field);
})->with([
    'missing title' => [['title' => ''], 'title'],
    'long title' => [['title' => str_repeat('م', 61)], 'title'],
    'unknown palette' => [['palette' => 'neon'], 'palette'],
    'missing quote' => [['quote_id' => 999], 'quote_id'],
]);

test('a reader keeps favourite poets and sees them in their corner', function () {
    $this->seed(LibrarySeeder::class);
    $reader = User::factory()->create();
    $poet = Poet::where('slug', 'al-mutanabbi')->sole();

    $this->actingAs($reader)->post(route('poets.favorite', $poet));
    expect($reader->favoritePoets()->pluck('poets.id')->all())->toBe([$poet->id]);

    $this->get(route('library.poet', $poet))->assertSee('في شعرائي');
    $this->get(route('profile.show', [$reader, 'tab' => 'poets']))->assertSee($poet->name);

    $this->actingAs($reader)->post(route('poets.favorite', $poet));
    expect($reader->favoritePoets()->count())->toBe(0);
});

test('guests cannot favourite poets or build anthologies', function () {
    $this->seed(LibrarySeeder::class);

    $this->post(route('poets.favorite', Poet::first()))->assertRedirect(route('login'));
    $this->post(route('anthologies.store'), ['title' => 'مختارات'])->assertRedirect(route('login'));
});

test("the reader's own writing is signed with their name and needs no source", function () {
    $writer = User::factory()->create(['name' => 'ريم']);

    $this->actingAs($writer)->post(route('store_quote'), [
        'content' => 'كل صباح صفحة بيضاء', 'is_original' => '1', 'author' => 'شخص آخر', 'source' => 'مكان ما',
    ])->assertSessionHasNoErrors();

    $quote = Quote::sole();
    expect($quote->is_original)->toBeTrue()->and($quote->author)->toBeNull()->and($quote->source)->toBeNull();

    $this->get(route('profile.show', [$writer, 'tab' => 'original']))->assertSee('كل صباح صفحة بيضاء')->assertSee('بقلم ريم');
    $this->get(route('home', ['original' => 1]))->assertSee('كل صباح صفحة بيضاء');
});

test('the readers-writing filter leaves out quoted texts', function () {
    Quote::factory()->create(['content' => 'نص من تأليف القارئ', 'is_original' => true]);
    Quote::factory()->create(['content' => 'نص منقول عن غيره', 'author' => 'فلان', 'source' => 'كتاب']);

    $this->get(route('home', ['original' => 1]))->assertSee('نص من تأليف القارئ')->assertDontSee('نص منقول عن غيره');
});

test('library verses can never be marked as the reader\'s own writing', function () {
    $this->seed(LibrarySeeder::class);
    $verse = Verse::first();

    $this->actingAs(User::factory()->create())->post(route('store_quote'), ['verses' => [$verse->id], 'is_original' => '1']);

    $quote = Quote::sole();
    expect($quote->is_original)->toBeFalse()->and($quote->author)->toBe($verse->work->poet->name);
});

test('a reader picks a badge that then stands for them across the site', function () {
    $reader = User::factory()->create(['name' => 'ريم']);
    Quote::factory()->for($reader)->create();

    $this->actingAs($reader)->patch(route('profile.bio'), ['badge' => 'palm'])->assertSessionHasNoErrors();
    expect($reader->fresh()->badge)->toBe('palm');

    $this->get(route('home'))->assertSee('M12 21v-9', false);
    $this->get(route('profile.show', $reader))->assertSee('شارة النخلة');

    // Back to the initial.
    $this->actingAs($reader)->patch(route('profile.bio'), ['badge' => ''])->assertSessionHasNoErrors();
    expect($reader->fresh()->badge)->toBeNull();
});

test('only the listed badges can be chosen', function () {
    $this->actingAs(User::factory()->create())
        ->patch(route('profile.bio'), ['badge' => '<svg onload=alert(1)>'])
        ->assertSessionHasErrors('badge');
});

test('a reader hangs sayings on their wall, and can keep the wall private', function () {
    $this->seed(LibrarySeeder::class);
    $reader = User::factory()->create();
    $saying = Saying::first();

    $this->actingAs($reader)->post(route('sayings.wall', $saying))->assertSessionHas('toast');
    $this->get(route('library.index'))->assertSee('على جداري');
    auth()->logout();
    $this->get(route('profile.show', [$reader, 'tab' => 'wall']))->assertSee($saying->text);

    $this->actingAs($reader)->patch(route('profile.bio'), ['show_wall' => '0']);
    auth()->logout();
    $this->get(route('profile.show', [$reader, 'tab' => 'wall']))->assertDontSee('أنزله من جداري')->assertDontSee('tab=wall');

    $this->post(route('sayings.wall', $saying))->assertRedirect(route('login'));
});
