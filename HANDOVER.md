# رقيم — ملف التسليم (للمتابعة من أي أداة)

آخر تحديث: 31 أكتوبر 2026. المستودع: https://github.com/Ahmed-Ba11/midad-quoteit (فرع `main`).

## الحالة الآن
- **المشروع:** منصة اقتباس عربية (RTL)، مشروع تخرّج معسكر طويق. Laravel 13 + Blade + Tailwind 4 + Pest + SQLite محلياً.
- **الاختبارات:** 268 اختباراً تنجح (`php artisan test --compact`).
- **الشعار النصي:** «حيثُ يُرقَمُ الأثر». الهوية: «رأس القلم» (حرف ر بشكل سنّ قلم)، اللون القاني `#8A3324`.

## التشغيل محلياً
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed      # محلياً: المكتبة + بيانات تجريبية (DemoSeeder)
composer run dev                # افتح http://127.0.0.1:8000 (لا localhost: يتأخر بسبب IPv6)
```
- الحسابات التجريبية (محلياً فقط): `sara@raqeem.test` وغيرها، كلمة المرور `Raqeem-Demo-2026`.
- خط ثمانية في `resources/fonts/thmanyah` غير مرفوع (رخصته). بدونه تُستخدم خطوط OFL بديلة تلقائياً.
- إن ظهرت الصفحات بلا تنسيق: احذف `public/hot`.
- خادم PHP المحلي على Windows يعالج طلباً واحداً في كل مرة؛ التعليق المتقطع محلياً ليس من الكود.

## أهم أجزاء الكود
| الجزء | الملفات |
|---|---|
| رحلة النشر: «شارك اقتباساً» ← مسودة ← محرّر/مرسم ← نشر | `components/share-sheet.blade.php`، `QuoteController@draft/create/store/edit/update`، `QuoteRequest` |
| محرّر النص | `quotes/editor.blade.php`، `resources/js/editor.js` |
| مرسم الحروف (بسيط: لون، ملمس، تعتيم، فاصل، صندوق، خط، إبراز) | `quotes/studio.blade.php`، `resources/js/studio.js`، `components/card-art.blade.php`، `resources/css/card.css`، `config/card.php`، `app/Support/CardDesign.php` (تحقق من `quotes.design`) |
| تنسيق الشعر/النثر (تلقائي/شعر/نثر) | `app/Support/QuoteLayout.php` + `resources/js/quote-layout.js` (نفس الخوارزمية، يختبرهما `tests/fixtures/quote-layout.json`)، `components/quote-words.blade.php` + `resources/js/quote-words.js` |
| الخطوط (23 خط OFL) | `config/quotes.php` (fonts)، `components/font-picker.blade.php`، `resources/js/font-picker.js`، `app/Support/PageFonts.php` |
| المكتبة | `LibraryController`، `library/index.blade.php`، `library/partials/collections.blade.php` (مخزّنة مؤقتاً)، `resources/js/library.js` |
| الديوان (aldiwan.net) | `app/Support/Aldiwan.php` |
| الخلفيات | `resources/css/backdrops.css`، `components/page-wall.blade.php`، `components/brand-mark-defs.blade.php` |
| الرجوع | `components/back.blade.php` + `data-back` في `resources/js/app.js`، `Controller::previousOnSite()` |
| الأمان | `app/Http/Middleware/SecurityHeaders.php`، `bootstrap/app.php` (trustProxies)، `LoginController` (حدّ لكل بريد ولكل IP)، `routes/web.php` (throttle) |
| البيانات | `DatabaseSeeder`: في الإنتاج المكتبة فقط؛ `DemoSeeder`: محلياً والاختبارات فقط |

## قرارات المالك (لا تُعَد مناقشتها)
- مرسم الحروف **بسيط**: حُذفت القوالب و«فاجئني» والإطارات والإهداء والختم ورفع الصورة والعلامة المائية **والحفظ كصورة** (لم يُضمن تطابق الخط في Safari).
- النص من 3 إلى 155 حرفاً (FR-05 الرسمي).
- خط ثمانية يبقى (المالك تحقق من رخصته)، ولا يُرفع للمستودع.
- لا حسابات تجريبية في النسخة المنشورة؛ «بيت اليوم» من المكتبة حتى يصل عدد الاقتباسات العامة إلى 10، والأرقام تظهر من 10.
- لا تستخدم أبداً `&text=` في Google Fonts لمعاينة الخطوط: يسبب «تشقلب» الحروف.

## ما بقي
1. **مراجعتان لم تُطبَّق نتائجهما بعد:** الجوال والأداء، والنطاق والمنطق. افحص يدوياً: تداخل العناصر على 375px، أزرار أقل من 44px، الحقول بخط أصغر من 16px (تكبير iOS)، أن كل حقل له تلميح (متطلب المعسكر)، بقايا الميزات المحذوفة (`text_fonts` في config، `x-paper-texture`، `quote-art-line`).
2. **النشر على Laravel Cloud:**
   - أنشئ التطبيق من GitHub، وأضف قاعدة بيانات MySQL أو Postgres (لا SQLite: الملفات تُمسح عند كل نشر).
   - المتغيرات: `APP_ENV=production`، `APP_DEBUG=false`، `APP_KEY` جديد، `APP_URL` = رابط الموقع، `SESSION_SECURE_COOKIE=true`، `SESSION_DRIVER=database`، `CACHE_STORE=database`.
   - أمر البناء: `composer install --no-dev --optimize-autoloader && npm ci && npm run build`.
   - أمر النشر: `php artisan migrate --force && php artisan db:seed --force && php artisan optimize`.
   - تأكد أن الهجرات تعمل على MySQL/Postgres (كُتبت وجُرّبت على SQLite).
3. **ملفات التسليم:** SRS v1.3 (الميزات الجديدة والجداول)، ERD PDF (الجداول الجديدة: books, book_user, anthologies, anthology_quote, poet_user, diwans, diwan_verses, saying_user، وأعمدة quotes الجديدة: layout, design, presentation, mark_style…)، فيديو دقيقة من النسخة المحلية، ملف ZIP بـ `git archive` (يستثني الخطوط و`.env` وقاعدة البيانات)، والتسليم على Pathy.

## بعد كل تعديل
```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
```
