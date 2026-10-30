@props(['occasion' => null, 'align' => 'start'])

{{--
    An epigraph: one verse from خزانة الأدب, different on each visit (picked at random, optionally
    from one occasion such as "ilm" or "hikma"). Only verified library verses are ever shown.
    The ~140 verses are cached for an hour, so picking one costs no database query.
--}}
@php
    $verses = \Illuminate\Support\Facades\Cache::remember('epigraph-verses', now()->addHour(), fn () => \App\Models\Verse::query()
        ->join('works', 'works.id', '=', 'verses.work_id')
        ->join('poets', 'poets.id', '=', 'works.poet_id')
        ->get(['verses.sadr', 'verses.ajuz', 'verses.occasion', 'poets.name as poet', 'poets.slug as poet_slug'])
        ->map(fn ($verse) => $verse->only(['sadr', 'ajuz', 'occasion', 'poet', 'poet_slug']))
        ->all());

    $pool = $occasion ? array_values(array_filter($verses, fn (array $verse) => in_array($verse['occasion'], (array) $occasion, true))) : $verses;
    $epigraph = $pool === [] ? null : $pool[array_rand($pool)];
@endphp

@if ($epigraph)
    <p {{ $attributes->class(['font-text text-sm leading-relaxed text-gold', 'text-center' => $align === 'center']) }}>
        <span>{{ $epigraph['sadr'] }}</span>
        <span aria-hidden="true" class="mx-1.5 inline-block size-1.5 -translate-y-0.5 rotate-45 bg-current opacity-70"></span>
        <span>{{ $epigraph['ajuz'] }}</span>
        <a href="{{ route('library.poet', $epigraph['poet_slug']) }}" class="whitespace-nowrap text-muted transition hover:text-accent">— {{ $epigraph['poet'] }}</a>
    </p>
@endif
