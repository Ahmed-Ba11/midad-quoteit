@props(['quote', 'showReplies' => true])

@php
    $iconButton = 'inline-flex h-11 min-w-11 items-center justify-center gap-1.5 rounded-full px-2.5 text-sm text-muted transition hover:bg-accent-soft hover:text-accent';
    $isLiked = (bool) $quote->liked_by_viewer;
    $isSaved = (bool) $quote->saved_by_viewer;
@endphp

<div {{ $attributes->class('flex items-center gap-0.5') }}>
    @auth
        <form method="POST" action="{{ route('quotes.like', $quote) }}">
            @csrf
            <button type="submit" aria-pressed="{{ $isLiked ? 'true' : 'false' }}"
                aria-label="{{ $isLiked ? 'إلغاء الإعجاب' : 'أعجبني' }}" title="أعجبني"
                @class([$iconButton, 'text-accent' => $isLiked])>
                <x-icon name="heart" @class(['size-[18px]', 'fill-current' => $isLiked]) />
                <span>{{ $quote->likes_count }}</span>
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" aria-label="سجّل دخولك لتعجب بالاقتباس" title="أعجبني" class="{{ $iconButton }}">
            <x-icon name="heart" class="size-[18px]" />
            <span>{{ $quote->likes_count }}</span>
        </a>
    @endauth

    @if ($showReplies)
        <a href="{{ route('quotes.show', $quote) }}#replies" aria-label="الردود ({{ $quote->replies_count }})" title="الردود"
            class="{{ $iconButton }}">
            <x-icon name="message" class="size-[18px]" />
            <span>{{ $quote->replies_count }}</span>
        </a>
    @endif

    @auth
        <form method="POST" action="{{ route('quotes.bookmark', $quote) }}">
            @csrf
            <button type="submit" aria-pressed="{{ $isSaved ? 'true' : 'false' }}"
                aria-label="{{ $isSaved ? 'إزالة من المحفوظات' : 'احفظ' }}" title="المحفوظات"
                @class([$iconButton, 'text-accent' => $isSaved])>
                <x-icon name="bookmark" @class(['size-[18px]', 'fill-current' => $isSaved]) />
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" aria-label="سجّل دخولك لتحفظ الاقتباس" title="المحفوظات" class="{{ $iconButton }}">
            <x-icon name="bookmark" class="size-[18px]" />
        </a>
    @endauth
</div>
