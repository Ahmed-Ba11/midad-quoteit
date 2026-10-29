# مِداد · QuoteIt — Design System

Arabic-first (RTL), literary, calm, "ink and paper". Light + dark. Comfortable for the eyes: generous spacing, thin borders, soft shadows.
No ornaments/patterns on quote cards, no loud gradients, no emoji. The approved visual mockups live in the claude.ai design canvas "QuoteIt — القالب البصري".

## Fonts
**Primary family: thmanyah (خط ثمانية)** — official license allows personal & commercial use in websites and apps,
but **forbids uploading/hosting/redistributing the font files** outside the official source and forbids renaming/modifying them.

| Role | Font | Fallback (OFL) |
|---|---|---|
| UI | thmanyah sans (Regular, Medium, Bold) | IBM Plex Sans Arabic |
| Headings & logo | thmanyah serif display (Bold, Black) | Noto Naskh Arabic 700 |
| Quote default / verses / story | thmanyah serif text (Regular, Medium) | Amiri |
| Quote options (AR) | thmanyah serif display, thmanyah serif text, Amiri, Noto Naskh Arabic, Aref Ruqaa, El Messiri, Reem Kufi | |
| Quote options (EN) | Cormorant Garamond (italic default), EB Garamond, Playfair Display, Libre Baskerville, IM Fell English | |

How to include thmanyah (license-safe):
- Put the original `.woff2` files (unchanged names) in `resources/fonts/thmanyah/` — **not** in `public/`.
- Declare `@font-face` in `resources/css/app.css` with `url('../fonts/thmanyah/…woff2')` so Vite bundles them into the compiled build (hashed).
- Add `resources/fonts/thmanyah/` to `.gitignore`; never push the font files to GitHub, never offer them as downloads,
  and leave them out of the submission ZIP. Add a README note: "Download thmanyah from the official page and place the woff2 files in resources/fonts/thmanyah".
- Every `font-family` stack ends with the OFL fallback above, so the site still looks right without the files.
- thmanyah supports OpenType `swsh` (swash) and `ss01–ss07`; use `font-feature-settings: "swsh" 1` sparingly on the logo only.
- OFL fonts load via Google Fonts `<link>`.

**Kashida (ـ)**: headings and logo only, once per word, between two connecting letters, usually before the last letter:
`مِـــداد` · `المكتبـــة` · `حكايـــة الاقتباس` · `رواق الحـــرف` · `رفّ الدواويـــن` · `رواق الشعـــراء`.
Never after ا د ذ ر ز و, never in quote text or buttons.

## Color tokens (Tailwind 4 `@theme`, dark via `.dark` class on `<html>`)
| Token | Light | Dark |
|---|---|---|
| `--color-paper` (page bg) | #F5EFE4 | #14110F |
| `--color-surface` | #FBF8F3 | #1D1916 |
| `--color-ink` (text) | #2B2520 | #EEE6D8 |
| `--color-muted` | #7A7068 | #A89D8F |
| `--color-line` | #E6DED2 | #342D27 |
| `--color-accent` (oxblood) | #8A3324 | #E3977D |
| `--color-accent-soft` | #F2E6DC | #2F211C |
| `--color-gold` | #A8873F | #C9A45C |

## Quote card palettes (bg / text / highlight)
| key | name | bg | text | highlight |
|---|---|---|---|---|
| mauve | وردي | #8F7378 | #FFFFFF | #F3D9B1 |
| sand | رملي | #E8E1D5 | #1E1A17 | #8A3324 |
| cocoa | بُنّي | #3A2A22 | #E6C88F | #FFFFFF |
| sage | زيتي | #7E8670 | #FAF7F0 | #F2E2B6 |
| navy | كحلي | #1E2533 | #EDE6D6 | #D9B97A |
| ink | حبر | #1C1A18 | #F2ECE2 | #C98B6B |
Users may also pick custom hex colors for each of the three.

**Paper texture** (toggle): an inline SVG `feTurbulence` (fractalNoise, baseFrequency .85, 3 octaves, desaturated) overlay with
`mix-blend-mode: overlay; opacity: .55`, plus a soft radial vignette. Build it once as `<x-paper-texture />`.

**Highlights**: store word indexes (split on whitespace). When rendering, merge consecutive highlighted words into one `<span>`
(with the space inside), and put normal spaces between pieces. Styles: `color` → text = highlight color; `mark` → background =
highlight color, text = card bg, `padding: .05em .3em; border-radius: .25em; box-decoration-break: clone`.

**Ratios**: wide 16:9 (max-w 760px), square 1:1 (max-w 560px), tall 4:5 (max-w 520px). Text centered, padding ~9% 10%,
line-height 1.8; author line below in IBM Plex 300, 15px, opacity .8.

## Components
- Buttons: primary = pill (`rounded-full`), accent bg, light text, h-11/h-12; secondary = 1px ink border; ghost = text only.
- Inputs: radius 12px, 1px line border, white bg (surface in dark), label above, hint below (12px muted).
- Cards: radius 20–24px, surface bg, 1px line or soft shadow.
- Icons: inline stroke SVG, 1.8 stroke, 18–22px. Touch targets ≥ 44px. Visible focus rings.
- Motion: 150–400ms ease; respect `prefers-reduced-motion`.

## Pages
1. **Home**: navbar (logo, الرئيسية/المكتبة/المحفوظات, dark toggle, "شارك اقتباساً", avatar initial) → "اقتبـــاس اليوم" on a manuscript-frame
   background (thin double gold frame + corner diamonds, faint stains) → search + sort → category chips → grid of quote cards
   (category, book link, "له حكاية", sharer, replies, like, save, edit/delete for owner) → pagination → footer.
2. **رواق الحرف (create/edit)**: right = tools panel (~440px) with a 5-icon tab bar: الكلمات · الخط · اللون · الإبراز · الحكاية;
   left = live preview + story preview. Whole page fits one screen. Library text shows locked: "من المكتبة · يُنشر كما هو" with
   "اختر بيتاً آخر" / "اكتب نصك الخاص".
3. **Quote page**: big card, actions (like, save, copy), "حكايـــة الاقتباس", replies (form + list), aside "قد يعجبـــك أيضاً" with reason chips.
4. **Library — "خزانة الأدب"**: dark textured hero + big search →
   - **رفّ الدواوين**: textured wall, wooden shelf (top surface + front edge + brass plaque "دواوين العرب"), bookends, one leaning book,
     spines with side shading, gold bands + diamond, vertical title. Hover lifts 12px; selected lifts 30px + rotate(-3deg), others dim;
     below the shelf an **open book** two-page spread animates open from the spine (rotateY): right = title page, left = "من عيون أبياته" + "تصفّح الديوان كاملاً".
   - **رواق الشعراء**: row of arches (128×232, radius 64 64 6 6, inner hairline arch, gold 8-point star, name, years, region badge for
     peninsula poets) grouped on an era timeline (الجاهلي · المخضرمون · الأموي · العباسي). Selected arch fills with the poet color and
     lifts; a panel shows bio, top verses, "افتح ديوانه" and "كل أبياته". No initial-letter avatars.
   - **لكلّ مقامٍ مقال**: occasion pills → one large textured verse card (صدر above عجز), prev/next, dots, "اختر هذا البيت".
   - **جدار المأثورات**: hanging plaques (nail + string SVG), textured palette colors, slight alternating tilt.
   - Verses always shown as صدر / gold diamond / عجز. Selecting up to 2 items shows a sticky dark bar with "صمّمه في رواق الحرف".
5. **Profile**: avatar initial, name, bio, 3 stats, tabs اقتباساتي / المحفوظات.
6. **Login / Sign up**: form on the right, dark ink panel on the left with one large verse.

All pages responsive down to 390px; no horizontal page scroll (shelf/arcade may scroll inside their own box).
