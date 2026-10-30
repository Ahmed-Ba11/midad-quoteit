@props(['context' => null, 'occasion' => null, 'layout' => 'line', 'align' => 'start'])

{{--
    An epigraph: one verse from خزانة الأدب, different on each visit. "context" picks from the verses
    chosen for that page (config/library.php → epigraphs); "occasion" from one occasion. Only verified
    library verses are ever shown, and the ~140 verses are cached, so picking one costs no query.
    layout: "line" — صدر ◆ عجز — poet, in one line;  "verse" — the large stacked verse (x-verse).
--}}
@php
    $verses = \Illuminate\Support\Facades\Cache::remember('epigraph-verses.v2', now()->addHour(), fn () => \App\Models\Verse::query()
        ->join('works', 'works.id', '=', 'verses.work_id')
        ->join('poets', 'poets.id', '=', 'works.poet_id')
        ->get(['verses.sadr', 'verses.ajuz', 'verses.occasion', 'poets.name as poet', 'poets.slug as poet_slug'])
        ->map(fn ($verse) => [...$verse->only(['sadr', 'ajuz', 'occasion', 'poet', 'poet_slug']), 'key' => \App\Models\Verse::normalizeArabic($verse->sadr)])
        ->all());

    $starts = config('library.epigraphs.'.$context, []);
    $pool = array_values(array_filter($verses, fn (array $verse) => match (true) {
        $starts !== [] => collect($starts)->contains(fn (string $start) => str_starts_with($verse['key'], $start)),
        $occasion !== null => in_array($verse['occasion'], (array) $occasion, true),
        default => true,
    }));
    $epigraph = $pool === [] ? null : $pool[array_rand($pool)];
@endphp

@if ($epigraph && $layout === 'verse')
    <x-verse {{ $attributes }} :sadr="$epigraph['sadr']" :ajuz="$epigraph['ajuz']" :poet="$epigraph['poet']" />
@elseif ($epigraph)
    <p {{ $attributes->class(['font-text text-sm leading-relaxed text-gold', 'text-center' => $align === 'center']) }}>
        <span>{{ $epigraph['sadr'] }}</span>
        <span aria-hidden="true" class="mx-1.5 inline-block size-1.5 -translate-y-0.5 rotate-45 bg-current opacity-70"></span>
        <span>{{ $epigraph['ajuz'] }}</span>
        <a href="{{ route('library.poet', $epigraph['poet_slug']) }}" class="whitespace-nowrap text-muted transition hover:text-accent">— {{ $epigraph['poet'] }}</a>
    </p>
@endif
