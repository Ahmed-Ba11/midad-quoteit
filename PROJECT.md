# مِداد · QuoteIt — Project Brief for Claude Code

> Read this file, `DESIGN.md`, and `CLAUDE.md` before any work. Follow the phases in order.
> One phase per session/commit. Run `php artisan test` after each phase.
> **Deadline: Thursday 29 Oct 2026, 11:59 PM (Riyadh).** Prefer working features over polish.

## 1. What we are building
An Arabic-first (RTL) web platform for publishing and **designing** quotes, with a calm literary identity, light/dark mode,
and a built-in poetry library (classical Arab and Arabian-Peninsula poets) users can quote from.

- Stack: Laravel 13, PHP 8.4, Blade, Tailwind 4, Vite, vanilla JavaScript (no new frameworks without approval), SQLite.
- **Hard rule from the bootcamp:** every view uses Blade template inheritance (`@extends` / `@yield` / `@section`) **and** Blade components (`<x-...>`).
- Base code: start from `quet_refrence` (core CRUD/auth already works). Do **not** ship `.env`, `vendor/`, `node_modules/`, `database/database.sqlite`.

## 2. Core requirements (SRS v1.2 — must all pass)
| ID | Requirement |
|---|---|
| FR-01 | Anyone (incl. guests) sees all quotes with text, sharer name, date |
| FR-02 | Sign up: name 2–50 **letters only** (Arabic or English, spaces allowed), valid unique email, password ≥ 8 |
| FR-03 | Log in with email + password; wrong credentials → error, stay on page |
| FR-04 | Log out (POST + CSRF) |
| FR-05 | Logged-in user shares a quote of **3–155** characters |
| FR-06 | User edits only own quotes (Policy) |
| FR-07 | User deletes only own quotes — **immediate, no confirmation** (SRS constraint) |
| NFR | Hashed passwords; only owner can edit/delete; every field shows a short hint (e.g. "من ٣ إلى ١٥٥ حرفاً"); quotes page < 3 s |

### Known gaps in `quet_refrence` to fix (Phase 1)
- Quote max length is 255 → must be **155** (validation + `string('content', 155)`).
- `quotes.user_id` has no FK → `foreignId('user_id')->constrained()->cascadeOnDelete()`.
- Logout is GET → make it POST with `@csrf`.
- No `auth`/`guest` middleware on routes; use a `QuotePolicy` instead of manual `abort_if`.
- Name has no letters-only rule → regex `/^[\p{Arabic}A-Za-z\s]{2,50}$/u`.
- Index shows only 20 quotes → show all with pagination (12 per page).

## 3. Decided new features
1. **Categories** (حكمة، شعر، أدب، فلسفة، تأمل) — filter chips on home.
2. **Quote source fields**: author (القائل) + book/diwan (الكتاب).
3. **Quote story** (حكاية الاقتباس) — optional ≤ 500 chars, shown on the quote page.
4. **Card design — "مرسم الحروف" page** (kept simple on the owner's request): 23 OFL fonts in groups (+ local ثمانية), 12 heritage palettes **or a custom colour**, texture (paper / linen / kraft), dim, a divider under the words, an inner box (colour, padding, shadow, outline), alignment, ratio (wide 16:9 / square / tall 4:5 / story 9:16) with automatic text sizing, **word highlighting** (color or shading; adjacent highlighted words render as ONE continuous highlight). Stored as `quotes.design` (JSON, whitelisted by `App\Support\CardDesign`).
4b. **Text quotes — "محرّر النص"**: details typed once in «شارك اقتباساً», then format (تلقائي / شعر / نثر via `App\Support\QuoteLayout`), font list, emphasis in 12 colours, quote marks previewed live.
5. **Likes** (M:N) and **Bookmarks / المحفوظات** (M:N).
6. **Replies** on quotes (≤ 300 chars). *(Moves "comments" into scope — document in SRS v1.3.)*
7. **Search** (text, author, book, poet) + **sort** (latest / most liked / most discussed).
8. **Quote of the day** on home (deterministic per day).
9. **Quote page**: big card, story, replies, "قد يعجبك أيضاً" (same author → same category).
10. **Profile page**: user's quotes, saved quotes, counts.
11. **Library (خزانة الأدب)** — built-in, read-only:
    - Poets, works (diwans), verses (صدر/عجز), occasions, and Arabic sayings (مأثورات).
    - Sections: diwan shelf with open-book animation, poets arcade by era, "لكل مقامٍ مقال" occasion slider, sayings wall, search.
    - User selects 1–2 verses or a saying → opens رواق الحرف **pre-filled and locked** (library text can't be edited) → designs → publishes, attributed automatically.
12. Light/dark mode toggle (persist in `localStorage`), copy-quote button.

### Explicitly NOT doing (out of scope)
Image upload to the server / avatar upload, delete confirmation, saved "my colors" list, following users, DMs, external APIs at runtime.

**Tried and withdrawn (31 Oct 2026):** saving a card as a PNG and a photo from the device were built client-side, then removed at the owner's request: the saved image could not be guaranteed to match the card on every phone (fonts in Safari). Back to: no image saving, no photo.

## 4. Database (target ERD)
```
users        id, name(50), email UQ, password, bio(160)?, timestamps
categories   id, name(30), slug UQ, timestamps
quotes       id, user_id FK→users (cascade), category_id FK→categories (nullOnDelete)?,
             verse_id FK→verses (nullOnDelete)?, saying_id FK→sayings (nullOnDelete)?,
             content(155), author(100)?, book(150)?, story(500)?,
             font(20) default 'amiri', bg_color(7), text_color(7), highlight_color(7),
             highlight_style enum(color,mark) default 'mark', highlights json?  (array of word indexes),
             texture bool default true, ratio enum(wide,square,tall) default 'wide',
             font_size tinyint default 34, timestamps
likes        id, user_id FK (cascade), quote_id FK (cascade), UQ(user_id,quote_id), timestamps
bookmarks    id, user_id FK (cascade), quote_id FK (cascade), UQ(user_id,quote_id), timestamps
replies      id, user_id FK (cascade), quote_id FK (cascade), body(300), timestamps
poets        id, name, slug UQ, era, years, region, bio, is_peninsula bool, color(7), sort_order
works        id, poet_id FK (cascade), title, slug UQ, type enum(diwan,muallaqa,collection), color(7), ink(7)
verses       id, work_id FK (cascade), sadr, ajuz, occasion(20), timestamps
sayings      id, text, source, timestamps
```
Relations: User 1:N Quote, User 1:N Reply, User M:N Quote via likes & bookmarks, Category 1:N Quote,
Quote 1:N Reply, Poet 1:N Work, Work 1:N Verse, Verse 1:N Quote (optional), Saying 1:N Quote (optional).

Occasions keys: `himma, najah, sabr, hikma, ilm, hubb, hanin, wafa`.

## 5. Phases (each ends with tests passing + a commit)

### Phase 0 — Setup (15 min) — Claude Code does all of this
The working folder initially contains: `PROJECT.md`, `DESIGN.md`, `quet_refrence.zip` (or an already-extracted `quet_refrence/` folder),
and `Thmanyah-Font-Family.zip` (or its extracted folder). Possibly also `quet-main.zip` (starter, reference only).
1. Extract `quet_refrence` and move **its contents** into the current folder root (so `artisan` sits next to PROJECT.md).
   Skip/delete: `.env`, `.git/`, `vendor/`, `node_modules/`, `database/database.sqlite`, `public/hot`, `public/build/`,
   `bootstrap/cache/*.php`, `storage/framework/views/*.php`, `__MACOSX/`, `.DS_Store`, and the stray file named `laravel`.
2. From the Thmanyah package copy only the `.woff2` files (keep original names) into `resources/fonts/thmanyah/`.
   Do not modify or rename them. Add `resources/fonts/thmanyah/` to `.gitignore`. Delete the extracted font folder and zips afterwards
   (keep the license PDFs out of the repo too).
3. `git init` + first commit (fonts ignored). Then: `composer install`, `npm install`, `cp .env.example .env`, `php artisan key:generate`,
   `touch database/database.sqlite`, `php artisan migrate`, `php artisan test`.
4. Tell the user to run `composer run dev` and open the URL shown, and report the result before Phase 1.

### Phase 1 — Core fixes to match SRS (45 min)
All items in "Known gaps". Form Requests with Arabic messages and field hints. Feature tests for FR-01…FR-07.

### Phase 2 — Identity & layout (1.5 h)
Fonts, Tailwind tokens, dark mode, RTL `<html dir="rtl" lang="ar">`, layout `layouts/app.blade.php`, components:
`x-navbar, x-button, x-input, x-textarea, x-quote-card, x-chip, x-icon, x-empty-state`. Restyle home, login, sign-up per DESIGN.md.

### Phase 3 — Quote model + رواق الحرف (2 h)
Migrations for categories + new quote columns. `create`/`edit` pages (رواق الحرف) with 5 tool tabs and live preview (vanilla JS).
`x-quote-card` renders styles + continuous highlights from `highlights` JSON. Validate colors as hex, font/ratio as enum.

### Phase 4 — Social: likes, bookmarks, replies, quote page (1.5 h)
Toggle routes (POST), counts with `withCount`, quote show page with story, replies form/list, related quotes.

### Phase 5 — Discovery: search, sort, filter, quote of the day, profile (1 h)

### Phase 6 — Library (2 h)
Migrations poets/works/verses/sayings. `database/data/library.json` (≈ 100–150 verified verses from the 11 poets + 8–12 sayings)
→ `LibrarySeeder`. Verify verse text against ar.wikisource.org where possible; mark doubtful ones for human review.
Library page with shelf/arcade/occasions/sayings (DESIGN.md). Selecting verses → `/quotes/create?verse=ID` or `?saying=ID` → locked text.
Footer attribution: "النصوص من ويكي مصدر وويكي الاقتباس (CC BY-SA)".

### Phase 7 — Demo data, polish, mobile, tests (45 min)
`DatabaseSeeder`: 5 users, categories, ~20 styled quotes, likes, replies. Check every page at 390 px width. Run Pint.

### Phase 8 — After code (by the student)
Update SRS to v1.3 + ERD PDF, record demo video, zip code + SRS.

## 6. Poets in the library
Peninsula: امرؤ القيس، طرفة بن العبد، عنترة بن شداد، زهير بن أبي سلمى، الخنساء، قيس بن الملوّح، جرير، الإمام الشافعي.
Masters: أبو تمام، المتنبي، أبو العلاء المعري.
Modern poets still under copyright (e.g. غازي القصيبي، محمد الثبيتي) are **not** included with verses.
