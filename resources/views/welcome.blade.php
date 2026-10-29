@extends('Layout.app')

@section('title', 'Home')
@section('content')
    @php
        $editingId = request()->filled('edit') ? (int) request('edit') : null;
    @endphp

    <div class="mx-auto max-w-3xl p-8">

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-100 p-3 text-sm text-red-700">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="space-y-4">
            <h2 class="text-2xl font-bold text-white">Quotes</h2>

            @forelse ($quotes as $quote)
                @php $isMine = auth()->user()?->can('update', $quote) ?? false; @endphp

                @if ($isMine && $quote->id === $editingId)
                    {{-- Edit mode (my quote only) --}}
                    <form method="post" action="{{ route('edit_quote', $quote) }}"
                        class="space-y-3 rounded-2xl bg-tuwaiq-purple p-5 shadow">
                        @csrf
                        @method('PUT')

                        <textarea name="content" rows="3" required minlength="3" maxlength="155" class="input" autofocus>{{ old('content', $quote->content) }}</textarea>
                        <p class="text-xs text-white/80">من ٣ إلى ١٥٥ حرفاً</p>

                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-secondary">Save</button>
                            <a href="{{ route('home') }}" class="btn text-white hover:underline">Cancel</a>
                        </div>
                    </form>
                @elseif ($isMine)
                    {{-- My quote: purple, with edit/delete --}}
                    <blockquote class="relative rounded-2xl bg-tuwaiq-purple p-5 text-white shadow">
                        <p class="text-lg">“{{ $quote->content }}”</p>
                        <p class="mt-2 text-sm font-semibold opacity-80">— {{ $quote->user->name }}
                            <span class="text-xs opacity-70">· {{ $quote->created_at->format('Y-m-d') }}</span>
                        </p>

                        <div class="absolute right-4 bottom-5 flex items-center gap-3">
                            <a href="{{ route('home', ['edit' => $quote->id]) }}" class="hover:text-tuwaiq-orange"
                                aria-label="Edit">✎</a>

                            <form method="POST" action="{{ route('delete_quote', $quote) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hover:text-tuwaiq-orange" aria-label="Delete">⌫</button>
                            </form>
                        </div>
                    </blockquote>
                @else
                    {{-- Someone else's quote --}}
                    <blockquote class="rounded-2xl bg-white p-5 text-tuwaiq-purple shadow">
                        <p class="text-lg">“{{ $quote->content }}”</p>
                        <p class="mt-2 text-sm">
                            <span class="font-semibold">— {{ $quote->user->name }}</span>
                            <span class="text-xs opacity-70">· {{ $quote->created_at->format('Y-m-d') }}</span>
                        </p>
                    </blockquote>
                @endif
            @empty
                <p class="text-gray-500">No quotes yet. Be the first to share one!</p>
            @endforelse
        </section>

        <div class="mt-6">
            {{ $quotes->links() }}
        </div>

    </div>

    @auth
        {{-- Floating "+" button --}}
        <button type="button" popovertarget="quote-popup" aria-label="Share a quote"
            class="fixed bottom-8 right-8 z-40 flex h-16 w-16 items-center justify-center rounded-full bg-tuwaiq-purple text-white shadow-xl transition hover:scale-110 hover:bg-tuwaiq-purple-light focus:outline-none focus-visible:ring-4 focus-visible:ring-tuwaiq-purple/30">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor" class="h-8 w-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </button>

        {{-- Pop-up form --}}
        <div id="quote-popup" popover
            class="m-auto w-[90%] max-w-lg rounded-3xl bg-white p-0 shadow-2xl backdrop:bg-black/40 backdrop:backdrop-blur-sm">

            <form method="POST" action="{{ route('store_quote') }}" class="space-y-4 p-6">
                @csrf
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-tuwaiq-purple">Share a quote</h2>
                    <button type="button" popovertarget="quote-popup" popovertargetaction="hide" aria-label="Close"
                        class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div>
                    <textarea id="quote-body" name="content" rows="5" required minlength="3" maxlength="155" autofocus
                        placeholder="Write your quote…" class="input peer resize-none user-invalid:border-red-500">{{ old('content') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 peer-user-invalid:text-red-600">من ٣ إلى ١٥٥ حرفاً</p>
                </div>

                <button type="submit" class="btn btn-primary w-full">Send</button>
            </form>
        </div>
    @else
        <a href="{{ route('login') }}"
            class="fixed bottom-8 right-8 z-40 rounded-full bg-tuwaiq-purple px-6 py-4 text-white shadow-xl hover:bg-tuwaiq-purple-light">
            Log in to share a quote
        </a>
    @endauth
@endsection