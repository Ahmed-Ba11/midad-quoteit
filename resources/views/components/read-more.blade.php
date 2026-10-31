@props(['poet', 'work' => null])

{{--
    "أكمل القراءة": the library holds selected verses; the whole diwan is read on الديوان (aldiwan.net),
    linked only, never copied — the poet's page there, or a search for their name.
--}}
<div {{ $attributes->class('flex flex-col gap-4 rounded-2xl border border-gold/40 bg-paper p-5 sm:flex-row sm:items-center sm:justify-between') }}>
    <p class="flex items-center gap-2 font-display text-lg font-bold">
        <x-icon name="book-open" class="size-5 text-gold" />
        {{ $work?->type === 'muallaqa' ? 'اقرأ المعلقة كاملة' : 'أكمل قراءة الديوان' }}
    </p>
    <x-external-link :href="\App\Support\Aldiwan::poet($poet)"
        class="h-11 justify-center rounded-full bg-accent px-5 text-sm font-medium text-paper no-underline hover:opacity-90 hover:no-underline">
        شعر {{ $poet->name }} في الديوان
    </x-external-link>
</div>
