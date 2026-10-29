# مِداد · QuoteIt

منصّة عربية لنشر الاقتباسات وتصميمها — مشروع معسكر القمّة (مسار البرمجة والتطوير).

Laravel 13 · PHP 8.4 · Blade · Tailwind 4 · Vite · SQLite

## التشغيل

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
composer run dev
```

الاختبارات: `php artisan test`

## خط ثمانية

ترخيص خط ثمانية لا يسمح بإعادة توزيع ملفاته، لذلك ليست ضمن المستودع.
حمّل الخط من صفحته الرسمية وضع ملفات `woff2` كما هي (بأسمائها الأصلية) في `resources/fonts/thmanyah/`.
بدونها يعمل الموقع بخطوط بديلة مفتوحة الترخيص (IBM Plex Sans Arabic، Amiri، Noto Naskh Arabic).
