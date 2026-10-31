# رقيم — ملف التسليم (للمتابعة من أي أداة)

آخر تحديث: 31 أكتوبر 2026. المستودع: https://github.com/Ahmed-Ba11/midad-quoteit (فرع `main`).

## الحالة الآن
- **المشروع:** منصة اقتباس عربية (RTL)، مشروع تخرّج معسكر طويق. Laravel 13 + Blade + Tailwind 4 + Pest + SQLite محلياً.
- **الاختبارات:** 285 اختباراً تنجح (`php artisan test --compact`).
- **الموقع المنشور:** https://raqeem-production-mt79ly.laravel.cloud (Laravel Cloud، فرانكفورت، MySQL 8.4، بخط ثمانية).
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
- FR-07: الحذف **فوري بلا تأكيد** كما في SRS v1.2 (قرار المالك، 31 أكتوبر).

## ما بقي
1. **نتائج المراجعات: طُبّقت كلها (31 أكتوبر)** ولكل منها اختبار:
   - الحذف فوري بلا تأكيد (FR-07)، ومن صفحة الاقتباس يعود للرئيسية حتى مع query (`destroy` يقارن المسار فقط).
   - سطر تلميح لكل أداة في المرسم، و«اللون» في المحرّر.
   - البيت من المكتبة فيه «من يرى الاقتباس» و«أضفه إلى» (`quotes/partials/publish-options.blade.php`، يستعمله `text-origin` أيضاً).
   - «نص / بطاقة» زرّ يرسل النموذج: `QuoteController@draft` (جديد أو من المكتبة) و`@redraft` (محفوظ، `PUT /quote/{quote}/draft`) يحفظان الاختيارات غير المحفوظة في الجلسة؛ ما لا حقل له في الصفحة الأخرى يبقى من المسودة السابقة (`from_draft`). النموذج الفارغ يبدّل بلا خطأ.
   - «صمّم مثله» ينسخ `design` و`layout`، ويظهر مرة واحدة في صفحة الاقتباس (`x-quote-menu :with-template="false"`).
   - الحجم اليدوي يُحفظ: `design.auto_size` (في `CardDesign` و`config/card.php`).
   - الجوال: الشرائح والكلمات وأزرار المقامات 44px، ودوائر الألوان لها حلقة لمس خفية 44px؛ تبويبات ركني تفتح والتبويب النشط في الوسط (`data-center-current` في `app.js`).
   - حُذفت البقايا (`text_fonts`، `.quote-art-line`، `.brass-plaque`، `withPresentation`)، وحُدّث ERD وPhase 6 في PROJECT.md حسب قاعدة البيانات الفعلية.
   - **لم يُعالج:** مؤثرات الورق المكررة في المكتبة على الجوالات الضعيفة (تحتاج قياساً على جهاز حقيقي).
   - **محلياً:** صفحات أكبر من 126KB تتوقف أحياناً ~19 ثانية (خادم PHP على Windows أو فحص Avast لـ localhost)، ليس من الكود.
2. **النشر على Laravel Cloud: تمّ (31 أكتوبر).** ما أُنشئ فعلاً:
   - المؤسسة `ahmd-khald`، التطبيق `raqeem` (من `Ahmed-Ba11/midad-quoteit` فرع `main`، النشر عند كل push مفعّل)، البيئة `production` في EU Central (Frankfurt)، PHP 8.4، Node 24، حوسبة Flex 512MB مع النوم عند الخمول.
   - قاعدة البيانات: Laravel MySQL 8.4، الإعداد Dev (حتى 8.30$ شهرياً)، مرتبطة بالبيئة.
   - **خط ثمانية:** المستودع عام فلا تُرفع ملفاته. الملفات في مخزن عام `raqeem-fonts` (Org › Resources › Object storage)، رفعها المالك من «File explorer» (ملف واحد في كل مرة). المخزن **غير مرتبط** بالبيئة عمداً: الربط يشترط مكتبة `league/flysystem-aws-s3-v3`، والموقع لا يحتاجها. عنوان الموقع مضاف في «Allowed origins» للمخزن (ضروري للخطوط). `THMANYAH_FONTS_URL` يشير إلى عنوانه العام، و`components/thmanyah-faces.blade.php` يطبع `@font-face` منه. **إن تغيّر نطاق الموقع** (نطاق خاص مثلاً): أضفه إلى Allowed origins وإلا لن يظهر الخط.
   - المتغيرات المضافة: `APP_KEY` (ولّده Cloud)، `APP_NAME=Raqeem`، `APP_ENV=production`، `APP_DEBUG=false`، `APP_LOCALE=ar`، `APP_URL`، `SESSION_DRIVER=database`، `SESSION_SECURE_COOKIE=true`، `CACHE_STORE=database`، `QUEUE_CONNECTION=sync`، `LOG_LEVEL=warning`، `THMANYAH_FONTS_URL`.
   - البناء: `composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader` ثم `npm ci --audit false` ثم `npm run build` ثم `php artisan optimize`. النشر: `php artisan migrate --force` ثم `php artisan db:seed --force`.
   - **ما ظهر على MySQL فقط:** رابط مصدر أحد المأثورات طوله 332 حرفاً، فرفضه MySQL (SQLite لا يتحقق من الطول). أصلحته migration `widen_library_source_urls` (روابط المكتبة حتى 1000 حرف) مع اختبار. بقية الـ migrations نجحت على MySQL.
   - **تحقّقت منه بعد النشر:** `/up` يرجع 200؛ الرئيسية والمكتبة تعملان على الجوال والكمبيوتر بخط ثمانية (الملفات السبعة تأتي من المخزن)؛ `X-Frame-Options` و`Strict-Transport-Security` و`nosniff` موجودة؛ ملفات تعريف الارتباط `secure`؛ `/_boost` يرجع 404؛ في القاعدة 0 مستخدمين و0 اقتباسات و138 بيتاً و29 قولاً، `debug=false`، `env=production`.
   - **بقي على المالك:** إنشاء حساب حقيقي ونشر اقتباس عام وآخر خاص، ثم التحقق أن الخاص لا يظهر للزوار.
   - أداة سطر الأوامر `laravel/cloud-cli` مثبّتة عالمياً على جهاز المالك لكنها لا تعمل في هذه البيئة (تخرج بصمت)؛ كل الإعداد تمّ من لوحة Cloud.

   **الخطوات الأصلية (مرجع):**
   - **على المالك وحده (لا تفعله الأداة):** إنشاء حساب Laravel Cloud، وإدخال الدفع (الشهر الأول ورصيد 5$ مجاناً، ثم 5$ شهرياً + الاستهلاك)، وربط GitHub بالمستودع `Ahmed-Ba11/midad-quoteit` فرع `main`.
   - **تم التحقق منه:** البناء ينجح بدون خطوط ثمانية؛ `LibrarySeeder` آمن للتكرار (updateOrCreate)؛ في الإنتاج تُزرع المكتبة فقط؛ `trustProxies` وترويسات الأمان مضبوطة.
   - **الإعدادات:**
     1. تطبيق جديد من المستودع، PHP 8.4، أقرب منطقة (مثلاً Frankfurt)، حوسبة Flex.
     2. قاعدة بيانات Laravel MySQL أو Serverless Postgres مرتبطة بالبيئة (لا SQLite: الملفات تُمسح عند كل نشر). Cloud يضيف متغيرات `DB_*` تلقائياً.
     3. المتغيرات: `APP_NAME=Raqeem`، `APP_ENV=production`، `APP_DEBUG=false`، `APP_URL=<رابط laravel.cloud>`، `APP_LOCALE=ar`، `SESSION_DRIVER=database`، `SESSION_SECURE_COOKIE=true`، `CACHE_STORE=database`، `QUEUE_CONNECTION=sync`، `LOG_LEVEL=warning`. و`APP_KEY` يولّده Cloud (أو `php artisan key:generate --show` محلياً).
     4. أمر البناء: `composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan optimize`
     5. أمر النشر: `php artisan migrate --force && php artisan db:seed --force`
     6. انشر؛ وبعد أي تغيير في المتغيرات أو الموارد أعد النشر.
   - **عبر سطر الأوامر (اختياري، بموافقة المالك على إضافة الحزمة):** `composer require --dev laravel/cloud-cli` ثم `./vendor/bin/cloud auth -n` (يسجّل المالك دخوله في المتصفح) ثم `cloud ship -h` و`cloud ship -n …` لأول نشر، وبعدها `cloud deploy {app} {env} -n` ثم دائماً `cloud deploy:monitor -n`. التوثيق: https://cloud.laravel.com/docs/llms.txt
   - **التحقق بعد النشر:** `/up` يرجع 200؛ الرئيسية تعرض «بيتُ اليوم» ولا أرقام وهمية؛ المكتبة تعمل؛ إنشاء حساب حقيقي ونشر اقتباس؛ الاقتباس الخاص لا يظهر للزوار؛ الترويسات فيها `X-Frame-Options` و`Strict-Transport-Security`؛ لا مسارات `_boost`. ثم يُضاف الرابط إلى SRS v1.3 والتسليم.
3. **ملفات التسليم:** SRS v1.3 (الميزات الجديدة والجداول)، ERD PDF (الجداول الجديدة: books, book_user, anthologies, anthology_quote, poet_user, diwans, diwan_verses, saying_user، وأعمدة quotes الجديدة: layout, design, presentation, mark_style…)، فيديو دقيقة من النسخة المحلية، ملف ZIP بـ `git archive` (يستثني الخطوط و`.env` وقاعدة البيانات)، والتسليم على Pathy.

## بعد كل تعديل
```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
```
