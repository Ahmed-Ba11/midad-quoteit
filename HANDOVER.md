# رقيم — ملف التسليم (للمتابعة من أي أداة)

آخر تحديث: 31 أكتوبر 2026. المستودع: https://github.com/Ahmed-Ba11/midad-quoteit (فرع `main`).

## الحالة الآن
- **المشروع:** منصة اقتباس عربية (RTL)، مشروع تخرّج معسكر طويق. Laravel 13 + Blade + Tailwind 4 + Pest + SQLite محلياً.
- **الاختبارات:** 269 اختباراً تنجح (`php artisan test --compact`).
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
1. **بقية نتائج المراجعات (طُبّق الأهم: الأمان، زر التعديل، الحفظ، الجوال، التباين):**
   - **متطلب المعسكر FR-07:** حذف الاقتباس يطلب تأكيداً (`components/quote-menu.blade.php` `data-confirm` + `resources/js/app.js`)، ووثيقة SRS تقول «فوراً». إما يُزال التأكيد أو يُوثَّق في SRS v1.3 بموافقة المالك.
   - **«كل حقل له تلميح» (متطلب المعسكر):** أغلب أدوات المرسم (المقاس، الملمس، الفاصل، الحجم، المحاذاة، لون النص، التنسيق، الإبراز، خيارات الصندوق) و«اللون» في المحرّر بلا سطر تلميح قصير. أضف `<p class="text-xs text-muted">` لكل منها.
   - **بيت من المكتبة:** لا يظهر فيه «من يرى الاقتباس» ولا «أضفه إلى» (`quotes/partials/details.blade.php` يتخطى `text-origin` للمكتبة).
   - **التبديل نص ↔ بطاقة** لاقتباس محفوظ أو بيت من المكتبة رابط GET فيفقد الاختيارات غير المحفوظة؛ وعلى نموذج فارغ يظهر «نص الاقتباس مطلوب».
   - **«صمّم مثله»** (`QuoteController::withTemplate`) لا ينسخ `design` و`layout`، ويظهر مرتين في صفحة الاقتباس.
   - **الحذف من صفحة الاقتباس** قد يعيد إلى 404 إذا كان في الرابط query (`destroy` يقارن الرابط كاملاً).
   - **المرسم:** الحجم اليدوي يعود «تلقائي» عند إعادة فتح البطاقة (`studio.js` يبدأ بـ `autoSize = true`).
   - **الجوال:** أهداف لمس أصغر من 44px (شرائح التصنيف، دوائر الألوان، كلمات الإبراز، نقاط «لكلّ مقامٍ مقال»)؛ تبويبات ركني تحتاج تمريراً للتبويب النشط؛ مؤثرات الورق مكررة في المكتبة قد تثقل الجوالات الضعيفة.
   - **بقايا:** `text_fonts` في `config/quotes.php`، CSS `.quote-art-line` و`.brass-plaque`، خيارات `withPresentation` في `text-origin.blade.php`، و ERD/Phase 6 في PROJECT.md غير محدّثة.
   - **محلياً:** صفحات أكبر من 126KB تتوقف أحياناً ~19 ثانية (خادم PHP على Windows أو فحص Avast لـ localhost)، ليس من الكود.
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
