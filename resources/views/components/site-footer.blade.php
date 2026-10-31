{{--
    The foot of every page: what رقيم means (with its dictionary sources), where to go, and how the
    texts are sourced and licensed — the documentation a reader may want before quoting anything.
--}}
@php
    $heading = 'mb-3 text-xs font-bold tracking-wide text-gold';
    $link = 'transition hover:text-accent';
@endphp

<footer class="foot mt-20 pt-6">
    {{-- A sheet of paper with a hand-cut top edge, the mark engraved faintly in its corner. --}}
    <svg class="edge" viewBox="0 0 1200 26" preserveAspectRatio="none" aria-hidden="true"><path fill="currentColor" d="M0 0H1200V8C1170 14 1150 6 1120 11S1070 16 1040 9 990 13 960 10 905 17 870 11 820 7 790 13 735 16 700 10 650 6 620 12 565 17 530 11 480 7 450 12 395 16 360 10 310 6 280 13 225 17 190 11 140 7 110 12 55 16 0 10Z" /></svg>
    <svg class="wm" aria-hidden="true"><use href="#mark" /></svg>
    <div class="foot-glow" aria-hidden="true"></div>

    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1.4fr]">
        {{-- The name and its meaning. --}}
        <section aria-labelledby="footer-about">
            <h2 id="footer-about" class="sr-only">عن رقيم</h2>
            <x-logo tagline class="text-3xl" />
            <dl class="mt-6 space-y-3 text-sm leading-relaxed">
                <div>
                    <dt class="inline font-text font-bold">الرَقيم:</dt>
                    <dd class="inline text-muted">الكتاب. <cite class="not-italic text-xs">(مقاييس اللغة)</cite>
                        وقيل في أصحاب الكهف: لوحٌ كُتب فيه خبرهم. <cite class="not-italic text-xs">(لسان العرب)</cite></dd>
                </div>
                <div>
                    <dt class="inline font-text font-bold">الرَقْم:</dt>
                    <dd class="inline text-muted">الخطّ والكتابة؛ ورَقَمَ الكتابَ: أعجمه وبيّنه. <cite class="not-italic text-xs">(لسان العرب)</cite>
                        وكلُّ ثوبٍ وُشِيَ فهو رَقْم. <cite class="not-italic text-xs">(مقاييس اللغة)</cite></dd>
                </div>
            </dl>
            <p class="mt-3 text-sm text-muted">فهنا تُكتب الجملة التي بقيت معك، وتُوشّى بخطٍّ يليق بها.</p>
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
                    <span>أبيات خزانة الأدب منقولة من <x-external-link href="https://ar.wikisource.org" class="text-ink">ويكي مصدر</x-external-link> (CC BY-SA)، ومراجَعة بتشكيلها؛ وتُكمَل قراءة القصائد في الديوان.</span>
                </li>
                <li class="flex gap-2">
                    <x-ornament name="star" class="mt-1.5 size-3 shrink-0 text-gold" />
                    <span>الأمثال من «مجمع الأمثال» للميداني وغيره، ولكلّ مثلٍ رابط صفحته في مصدره.</span>
                </li>
                <li class="flex gap-2">
                    <x-ornament name="star" class="mt-1.5 size-3 shrink-0 text-gold" />
                    <span>أصوات من الجزيرة: بيت أو بيتان للاستشهاد، طوبق كل منهما حرفياً في مصدرين مستقلّين، والحقوق محفوظة لأصحابها.</span>
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

    {{-- قيد الفراغ: as old manuscripts close with the copyist's name, رقيم closes with its maker's. --}}
    <section aria-labelledby="footer-maker" class="colophon mx-auto max-w-6xl px-4 pb-10">
        <div class="flex flex-col items-center gap-3 text-center">
            <x-ornament name="star" class="size-3.5 text-gold" />
            <h2 id="footer-maker" class="font-display text-xl leading-relaxed">
                <span class="text-muted">رَقَمَه</span> أحمد
            </h2>
            <p class="max-w-md text-sm leading-relaxed text-muted">
                طالبٌ جامعيّ في تخصّص هندسة البرمجيات، يكتب الشيفرة كما يُكتب البيت: سطراً يُوزن قبل أن يُرسَل.
            </p>
            <ul class="flex items-center gap-2" aria-label="حسابات أحمد">
                <li>
                    <a href="https://github.com/Ahmed-Ba11" target="_blank" rel="me noopener noreferrer"
                        class="inline-flex h-11 items-center gap-2 rounded-full border border-line px-4 text-sm transition hover:border-accent hover:text-accent">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-[18px]" fill="currentColor"><path d="M12 .5a11.5 11.5 0 0 0-3.64 22.41c.58.1.79-.25.79-.56v-2c-3.2.7-3.88-1.37-3.88-1.37-.53-1.33-1.28-1.69-1.28-1.69-1.05-.71.08-.7.08-.7 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.24-1.28-5.24-5.69 0-1.26.45-2.29 1.19-3.1-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.17 1.18a11 11 0 0 1 5.77 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.76.11 3.05.74.81 1.19 1.84 1.19 3.1 0 4.42-2.7 5.4-5.26 5.68.41.36.78 1.06.78 2.14v3.17c0 .31.21.67.8.56A11.5 11.5 0 0 0 12 .5Z" /></svg>
                        GitHub<span class="sr-only"> (يفتح في نافذة جديدة)</span>
                    </a>
                </li>
                <li>
                    <a href="https://www.linkedin.com/in/ahmed-basharaf-9633b6435" target="_blank" rel="me noopener noreferrer"
                        class="inline-flex h-11 items-center gap-2 rounded-full border border-line px-4 text-sm transition hover:border-accent hover:text-accent">
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-[18px]" fill="currentColor"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.34V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13ZM7.12 20.45H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z" /></svg>
                        LinkedIn<span class="sr-only"> (يفتح في نافذة جديدة)</span>
                    </a>
                </li>
            </ul>
        </div>
    </section>

    <div class="border-t border-line">
        <p class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-5 text-xs text-muted">
            <span>رقيم · منصّة لنشر الاقتباسات وتصميمها</span>
            <span>مشروع معسكر القمّة · مسار البرمجة والتطوير · ٢٠٢٦</span>
        </p>
    </div>
</footer>
