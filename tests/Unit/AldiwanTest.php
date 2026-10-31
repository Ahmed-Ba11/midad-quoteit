<?php

use App\Models\Poet;
use App\Support\Aldiwan;

test('a search on الديوان drops tashkeel and tatweel and percent-encodes the term', function () {
    $url = Aldiwan::search('  على قدرِ أهلِ العـزمِ  ');

    expect($url)->toStartWith('https://www.aldiwan.net/search.php?q=')
        ->and(rawurldecode(substr($url, strlen('https://www.aldiwan.net/search.php?q='))))->toBe('على قدر أهل العزم')
        ->and($url)->not->toContain(' ');
});

test('a search term is cut to 100 characters and cannot add parameters', function () {
    $url = Aldiwan::search(str_repeat('ب', 150).'&poet_name=x#frag');

    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

    expect(array_keys($query))->toBe(['q'])
        ->and(mb_strlen($query['q']))->toBe(100)
        ->and(parse_url($url, PHP_URL_FRAGMENT))->toBeNull();
});

test("a poet's diwan link uses their page on الديوان, or searches their name", function () {
    expect(Aldiwan::poet(new Poet(['name' => 'المتنبي', 'aldiwan_url' => 'https://www.aldiwan.net/cat-poet-Mutanabi'])))
        ->toBe('https://www.aldiwan.net/cat-poet-Mutanabi')
        ->and(Aldiwan::poet(new Poet(['name' => 'المتنبي', 'aldiwan_url' => null])))
        ->toBe(Aldiwan::search('المتنبي'))
        ->and(Aldiwan::poet(new Poet(['name' => 'المتنبي', 'aldiwan_url' => 'https://evil.example/aldiwan.net'])))
        ->toBe(Aldiwan::search('المتنبي'));
});
