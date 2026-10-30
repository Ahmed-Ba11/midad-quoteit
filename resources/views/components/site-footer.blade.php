{{--
    The foot of every page: what رقيم means (with its dictionary sources), where to go, and how the
    texts are sourced and licensed — the documentation a reader may want before quoting anything.
--}}
@php
    $heading = 'mb-3 text-xs font-bold tracking-wide text-gold';
    $link = 'transition hover:text-accent';
@endphp

<footer class="mt-20 border-t border-line bg-surface">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1.4fr]">
        {{-- The name and its meaning. --}}
        <section aria-labelledby="footer-about">
            <h2 id="footer-about" class="sr-only">عن رقيم</h2>
            <x-logo tagline class="text-3xl text-accent" />
            <dl class="mt-6 space-y-3 text-sm leading-relaxed">
                <div>
                    <dt class="inline font-text font-bold">الرَّقيم:</dt>
                    <dd class="inline text-muted">الكتاب، واللوح المكتوب. <cite class="not-italic text-xs">(مقاييس اللغة لابن فارس، والصحاح)</cite></dd>
                </div>
                <div>
                    <dt class="inline font-text font-bold">الرَّقْم:</dt>
                    <dd class="inline text-muted">تعجيم الكتاب وتبيينه، ووشيُ الثوب وتزيينه. <cite class="not-italic text-xs">(لسان العرب، وأساس البلاغة)</cite></dd>
                </div>
            </dl>
            <p class="mt-3 text-sm text-muted">فهنا تُكتب الجملة التي بقيت معك، وتُزيَّن بخطٍّ يليق بها.</p>
        </section>

        {{-- Where to go. --}}
        <nav aria-labelledby="footer-explore">
            <h2 id="footer-explore" class="{{ $heading }}">تصفّح</h2>
            <ul class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm sm:grid-cols-1">
                <li><a href="{{ route('home') }}" class="{{ $link }}">الاقتباسات</a></li>
                <li><a href="{{ route('home', ['original' => 1]) }}" class="{{ $link }}">بأقلام القرّاء</a></li>
                <li><a href="{{ route('library.index') }}" class="{{ $link }}">خزانة الأدب</a></li>
                <li><a href="{{ route('diwans.index') }}" class="{{ $link }}">دواوين القرّاء</a></li>
                <li><a href="{{ route('books.index') }}" class="{{ $link }}">رفوف القرّاء</a></li>
                @auth
                    <li><a href="{{ route('profile.show', auth()->user()) }}" class="{{ $link }}">ركني</a></li>
                @endauth
            </ul>
        </nav>

        {{-- Sources and rights. --}}
        <section aria-labelledby="footer-sources" class="sm:col-span-2 lg:col-span-1">
            <h2 id="footer-sources" class="{{ $heading }}">التوثيق والحقوق</h2>
            <ul class="space-y-2.5 text-sm leading-relaxed text-muted">
                <li class="flex gap-2">
                    <x-ornament name="star" class="mt-1.5 size-3 shrink-0 text-gold" />
                    <span>أبيات خزانة الأدب منقولة من <x-external-link href="https://ar.wikisource.org" class="text-ink">ويكي مصدر</x-external-link> (CC BY-SA)، ومراجَعة بتشكيلها، مع رابط القصيدة كاملة لكل بيت.</span>
                </li>
                <li class="flex gap-2">
                    <x-ornament name="star" class="mt-1.5 size-3 shrink-0 text-gold" />
                    <span>من ينقل كلام غيره يذكر قائله ومصدره، وفق المادة ١٥ من نظام حماية حقوق المؤلف.</span>
                </li>
                <li class="flex gap-2">
                    <x-ornament name="star" class="mt-1.5 size-3 shrink-0 text-gold" />
                    <span>دواوين القرّاء لا تُنشر للجميع إلا لشاعرٍ مضى على وفاته أكثر من خمسين سنة (المادة ١٩).</span>
                </li>
                <li class="flex gap-2">
                    <x-ornament name="star" class="mt-1.5 size-3 shrink-0 text-gold" />
                    <span>المواقع الأخرى نُحيل إليها بروابط فقط، ولا ننسخ منها شيئاً.</span>
                </li>
            </ul>
        </section>
    </div>

    <div class="border-t border-line">
        <p class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-5 text-xs text-muted">
            <span>رقيم · منصّة لنشر الاقتباسات وتصميمها</span>
            <span>مشروع معسكر القمّة · مسار البرمجة والتطوير · ٢٠٢٦</span>
        </p>
    </div>
</footer>
