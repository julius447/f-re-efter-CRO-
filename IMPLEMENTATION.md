# Before/after block — implementation guide

**For:** the developer implementing this on ampy.se (WordPress + Bricks).
**Assumes:** no prior context. Everything you need is in this file and in `dist/`.
**Live reference:** https://julius447.github.io/f-re-efter-CRO-/ — this is exactly what the block
must look and behave like on the site. Open it on your phone too.

---

## 1. What this is

A proof block for the service pages. Two before/after photo pairs side by side (stacked on mobile),
each with a draggable seam: the visitor drags to reveal the old panel or the new one. One H2 above,
one tagline below. **No buttons, no links, no CTA — by design.** The page's ask budget is spent
elsewhere; this block only proves.

The variation lives in ACF. The template never changes per page.

**Done means:** the shortcode renders on a service page with two signed photo pairs, both sliders
drag on iPhone, Android and desktop, and the block looks identical to the live reference.

---

## 2. Prerequisites

| Requirement | Why |
|---|---|
| WordPress 6.x | `wp_get_attachment_image` with `sizes`/`srcset`, `add_image_size` with crop position |
| Bricks | the block is placed via a **Shortcode element** |
| **ACF Pro** | the pairs are a **Repeater** field — that is a Pro feature |
| FluentSnippets | the three files are pasted as snippets; no plugin is written |
| Regenerate Thumbnails (plugin or WP-CLI) | existing media lacks the new 4:3 sizes until regenerated |
| Outfit loaded by the theme | the block inherits the site font; it loads nothing itself |

---

## 3. Files

| File | What it is | Where it goes |
|---|---|---|
| `dist/01-fore-efter.css` | block styles, fully scoped to `.ampy-foreefter` | FluentSnippets → **CSS** → **Head** |
| `dist/02-fore-efter.php` | image sizes + shortcode `[ampy_fore_efter]` + the markup template | FluentSnippets → **PHP** → **Frontend & Backend** |
| `dist/03-fore-efter.js` | the slider | FluentSnippets → **JS** → **Footer** |
| `acf/ampy-foreefter-falt.json` | the ACF field group | ACF → Tools → **Import** |
| `img/jobb/*.jpg` | five real jobs, already processed to 4:3 at 1200 + source width | upload to Media (see §6) |

`index.html`, `alla.html`, `no-js.html` are previews generated from the PHP template by `build.py`.
They reference `dist/` by link — they contain no copy of it. What you paste is what the preview shows.

**Paste the files as-is.** Do not reformat, do not "clean up", do not move rules to a global
stylesheet. The block is deliberately self-contained (§9).

---

## 4. Install, step by step — verify each step before the next

### Step 1 — CSS snippet
FluentSnippets → New → type **CSS** → paste `dist/01-fore-efter.css` → placement **Head** → activate.

Verify: view-source of any page contains `.ampy-foreefter{`.

### Step 2 — PHP snippet
FluentSnippets → New → type **PHP** → paste `dist/02-fore-efter.php` **without** the opening
`<?php` line if FluentSnippets adds it for you (check its editor — it usually does) → run on
**Frontend & Backend** → activate.

Verify: the site still loads (no fatal), and in WP-CLI or a test snippet:
`shortcode_exists('ampy_fore_efter')` → `true`.

### Step 3 — JS snippet
FluentSnippets → New → type **JS** → paste `dist/03-fore-efter.js` → placement **Footer** → activate.

Verify: in the browser console on any page: `typeof window.ampyForeEfter` → `"object"`.

### Step 4 — ACF field group
ACF → Tools → Import Field Groups → choose `acf/ampy-foreefter-falt.json` → import.

The group is attached to **Pages**. If the service pages are a custom post type, edit the group's
Location rule after import.

Verify: open a page in the editor; a box "Före/efter-blocket" appears with Rubrik, Rubrik – understruken
del, Tagline, and a repeater "Före/efter-par".

### Step 5 — Regenerate thumbnails
Run **Regenerate Thumbnails** (plugin) or `wp media regenerate --yes`.

Why: the PHP snippet registers two image sizes (`ampy-foreefter` 1200×900 and `ampy-foreefter-2x`
2400×1800). Media uploaded **before** the snippet existed has no such derivatives. Without them the
block falls back to full-size images: it still works, but the images are heavier and cropped by the
browser instead of by WordPress.

Verify: pick an uploaded image → `wp_get_attachment_image_src($id, 'ampy-foreefter')` returns a
`…-1200x900.jpg` URL.

### Step 6 — Place the block
In Bricks, add a **Shortcode** element (not a Code element) where the block should sit — in the
proof zone, after the content block, before Testimonials. Content: `[ampy_fore_efter]`.

Verify: nothing renders yet. That is correct — no pair is signed (§5).

### Step 7 — Fill the fields
On the page: Rubrik `Så ser det ut när vi har`, Rubrik – understruken del `bytt en elcentral`,
Tagline (see §5), then add up to two rows in the repeater with before image, after image, Omfattning,
and tick **Signerad**.

Verify: the block renders with both pairs. Drag both sliders. Compare against the live reference.

---

## 5. ACF fields

### Page-level

| Field | Type | Required | Example |
|---|---|---|---|
| `rubrik` | Text | **yes** | `Så ser det ut när vi har` |
| `rubrik_accent` | Text | no | `bytt en elcentral` — gets the black underline |
| `tagline` | Text | no | `Ny elcentral, jordfelsbrytare och märkta grupper. Samma jobb oavsett hur det såg ut innan.` |

The tagline is ONE line for the whole block, written per service. Pattern: list what the job actually
includes, end with the sentence that ties two different starting points together. It sits **below**
the pairs, never above — the H2 must stay the block's only opening.

### Repeater `foreefter_par` — one row per job, max 2 rendered

| Sub-field | Type | Required | Notes |
|---|---|---|---|
| `fore_bild` | Image | **yes** | landscape 4:3, min 1200×900 |
| `efter_bild` | Image | **yes** | same format as `fore_bild` |
| `omfattning` | Text | **yes** | not displayed — it is the images' alt text and the slider's accessible name |
| `jobbtyp` | Text | no | used in alt text, e.g. `Byte av elcentral` |
| `fore_alt` / `efter_alt` | Text | no | leave empty and they are built from `jobbtyp`/`omfattning` |
| `signerad` | True/False | **yes** | see the gate below |

### The gate — this is intentional, do not "fix" it

A row is skipped if it lacks either image, has an empty `omfattning`, or has `signerad` off.
If no row survives, the shortcode returns an empty string and **nothing renders**.

Reason: Swedish marketing law (MFL 10 §) puts the burden of proof on Ampy. A before/after pair that
cannot be backed by original files with EXIF, an order reference and the customer's consent is not
proof — it is a liability. `signerad` is the editorial confirmation that this evidence folder exists
for that pair. Never tick it as a shortcut.

If exactly one row survives it renders centred at up to 760 px instead of half-width and alone.
More than two rows: only the first two render (`$MAX_PAR = 2` in the PHP — a stated limit, not a
silent truncation).

---

## 6. Images

**Format: landscape 4:3.** That is what a phone camera produces. A 4:3 photo fits the frame with
zero cropping. Zero cropping means the before and after image can never be cropped differently —
and identical framing is the entire trust mechanic of a before/after pair.

The PHP registers two sizes, both centre-cropped to 4:3 for anything uploaded in another format:

| Size name | Dimensions | Role |
|---|---|---|
| `ampy-foreefter` | 1200 × 900 | the `src` the block requests |
| `ampy-foreefter-2x` | 2400 × 1800 | retina candidate in `srcset` |

WordPress never upscales: a 1600 px upload gets only the 1200 variant; a 2048 px upload gets both.
Two sizes exist because WordPress builds `srcset` only from derivatives with the **same aspect ratio**.

**The five real jobs in `img/jobb/`** are already processed exactly this way (4:3, no upscaling,
JPEG q82, no EXIF). Upload the `-1200.jpg` files if you want WordPress to do its own processing, or
upload the largest variant of each and let Regenerate Thumbnails produce the sizes. Pairs `a` and `b`
are the strongest; `e` is the weakest (the before photo is mid-demolition).

**File weight.** Budget is 30–80 kB per image. The processed JPEGs sit at 56–156 kB per 1200 px
image — over budget. WordPress does not produce AVIF/WebP by itself; add an optimisation step (plugin
or CDN). This is an ops item, not a code item.

**Loading.** `loading="lazy"`, `decoding="async"`, `sizes="(max-width: 719px) 94vw, 620px"`. The
block sits in the proof zone and must never be the page's LCP.

---

## 7. Shortcode reference

Normally: `[ampy_fore_efter]` — reads everything from ACF on the current page.

For a quick test without ACF, every field can be passed as an attribute (single pair only):

```
[ampy_fore_efter
  rubrik="Så ser det ut när vi har"
  rubrik_accent="bytt en elcentral"
  tagline="Ny elcentral, jordfelsbrytare och märkta grupper. Samma jobb oavsett hur det såg ut innan."
  fore_bild="123" efter_bild="124"
  omfattning="Byte av proppskåp till ny elcentral"
  signerad="1"]
```

`fore_bild`/`efter_bild` accept an attachment ID, an ACF image array, or an attachment URL.

---

## 8. Behaviour specification — what QA tests against

| Aspect | Expected |
|---|---|
| Rest position | seam at 35 % from the left: AFTER dominant. A visitor who never drags still sees the result |
| Drag | press anywhere in the frame → seam jumps there; drag follows the finger/pointer; continues past the frame edge |
| Multi-touch | the first finger owns the drag; a resting thumb neither moves nor ends it |
| Vertical scroll | a vertical swipe over the frame scrolls the page; pinch-zoom still works |
| Native image drag | never starts (images are `pointer-events: none` + `draggable="false"`) |
| Slider under the frame | native `<input type="range">`, mirrors the seam; dragging it moves the seam |
| Keyboard (slider focused) | ← → ±5 %, PageUp/Down ±10 %, Home/End. Motion 260 ms |
| Focus ring | shown only for keyboard focus. Pressing in the image never shows it |
| Chips | FÖRE lives left of the seam, EFTER right of it. Dragging fully left hides FÖRE; fully right hides EFTER |
| Hint pill "Dra för att jämföra" | fades after the first interaction; never clipped at 0 % or 100 % |
| Nudge | once, when ≥55 % of the frame is visible: seam moves 35→48→35 %. Off under `prefers-reduced-motion` |
| Screen reader | slider announces "Efter syns till N procent"; images have alt text; hint and handle are hidden from AT |
| Layout | 2 columns when the block's own content box is >680 px (iPad portrait = 2 columns); 1 column below |
| No JavaScript | `<noscript>` stacks the pair (before above after), both images whole, slider chrome hidden |
| Print | pairs stacked, slider chrome hidden |
| Forced colours | seam, handle and chips get system colours |
| Multiple blocks per page | each pair is independent; `window.ampyForeEfter.start()` re-initialises after AJAX loads |
| Analytics | see §9 |

### Device checklist

Run on a real iPhone (Safari), a real Android (Chrome), an iPad (both orientations) and desktop
Chrome + Firefox + Safari. On each: drag both sliders; drag with a thumb resting on the screen;
drag past the frame edge; swipe vertically over a frame; Tab to a slider and use arrows.

Everything above was verified in Blink (Chrome/Android engine) with touch emulation and synthetic
pointer events. **WebKit on a real iPhone and Gecko were not available in the build environment** —
those two are on you.

---

## 9. Analytics

Two `dataLayer` events, only pushed if `window.dataLayer` exists:

| Event | When |
|---|---|
| `fore_efter_view` | the block has been ≥55 % visible once |
| `fore_efter_interact` | the visitor moved a slider for the first time |

Payload: `{event, block: "fore_efter", riktning: "reglaget", block_id}`.

Consent: if `window.ampyConsent.analytics === false` nothing is pushed. The block never sets or
reads consent itself — wire that flag from the site's consent tool.

Why these two: the interactive direction was chosen over a static pair on a hypothesis that people
drag (research says ~1 %). These events are the only way to learn whether that was right.

---

## 10. Do not change

The block is built to a contract. Breaking any of these will be caught in review.

- **No global CSS.** No `html{}`, `body{}`, `*{}` — everything is scoped under `.ampy-foreefter`,
  tokens live on the wrapper, not on `:root`.
- **px, not rem.** The block must not inherit the theme's root font size.
- **`@container`, not viewport `@media`** for layout. The block measures its own width, so it behaves
  correctly inside a narrow Bricks column. A `@supports not (container-type)` fallback covers Safari 15.
- **No buttons, no links** inside the block. **One H2.** No intro text above the pairs.
- **Both images are real `<img>` with width/height**, rendered server-side, never injected by JS.
  The slider is an enhancement; the content must survive without it.
- **The H2 is the site's H2:** `--aptext-xl` (24→32 px), weight 400, line-height 1.2, Outfit, no
  letter-spacing. Measured on ampy.se, not taken from the theme's default rule.
- **Illustrations, stock photos, AI images, or another firm's work are never allowed** in this block.
  Only Ampy's own jobs, signed.

### If the markup must change
The markup lives in one place: the two heredoc templates in `dist/02-fore-efter.php` between the
`AMPY-MALL-*-START/SLUT` markers. Edit there, then run `python3 build.py` to regenerate the previews.
Never edit `index.html` by hand.

---

## 11. Troubleshooting

| Symptom | Check |
|---|---|
| Block renders nothing | `rubrik` filled? At least one repeater row with both images, `omfattning`, **and `signerad` on**? Is ACF Pro active (repeater)? |
| Images look cropped / zoomed | Regenerate Thumbnails not run → block is using full-size uploads. Or the photo is portrait/panorama (centre-cropped to 4:3 by design) |
| Sliders do not drag | JS snippet not in **Footer**, or not active. Console: `window.ampyForeEfter` must exist |
| A big green ring appears around the slider when dragging | old CSS cached. The current CSS gates the focus ring on keyboard origin (`fokus-fran-pekare`) |
| Dragging starts a browser image drag | old CSS cached — images must be `pointer-events: none` |
| Two tiny frames side by side on an old iPhone | expected on iOS ≤15 only if the `@supports` fallback is missing — it is in the shipped CSS |
| Wrong font | Outfit is not loaded by the theme on that page |
| Block appears twice / IDs collide | each block instance increments a counter; if you render the shortcode inside a loop, IDs stay unique |
| Loaded via AJAX and dead | call `window.ampyForeEfter.start()` after injecting |

---

## 12. Rollback

Deactivate or delete the three snippets. The block disappears. No database residue except the ACF
field group and its values, which are harmless and can stay.

---

## 13. Legal gate before publishing

Before ticking `signerad` on any pair, the evidence folder for that pair must contain:

1. the original files with EXIF (date, device), untouched
2. the order/job reference
3. the customer's written consent to use the photos in marketing

Swedish law (MFL 10 §) places the burden of proof on the advertiser. A pair without this folder is
not published — the gate in the PHP enforces it, and the person ticking the box is the one attesting.
The five jobs currently in `img/jobb/` were supplied by the owner on 2026-09-17 and are **not yet
signed**.
