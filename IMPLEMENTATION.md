# Before/after block — implementation guide

**For:** the developer implementing this on ampy.se (WordPress + Bricks).
**Assumes:** no prior context. Everything you need is in this file and in `dist/`.
**Live reference:** https://julius447.github.io/f-re-efter-CRO-/ — this is exactly what the block
must look and behave like on the site. Open it on your phone too.

---

## 1. What this is

A proof block for the service pages. Two before/after photo pairs side by side (stacked on mobile),
each with a draggable seam resting in the centre: the visitor drags to reveal the old panel or the
new one. One H2 above. **No buttons, no links, no CTA — by design.** The page's ask budget is spent
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
| `images/elcentral-byte-0N-{fore,efter}.jpg` | the four upload masters: two jobs, SEO filenames, highest available quality | upload to Media (§6) |
| `images/manifest.csv` | filename → alt text, title, caption, dimensions, source | reference for the media library fields |
| `images/wp-media-import.sh` | one WP-CLI command per image with alt/title/caption pre-filled | run once (§4 step 6) |
| `FOTOPROTOKOLL.md` | seven rules for the electricians who shoot future pairs (Swedish) | hand to the field team |
| `KODGRANSKNING.md` | the two code reviews and what they fixed (Swedish) | background only |

The live reference (`index.html`, `no-js.html`) is generated from the PHP template by a build script
in the GitHub repo (`julius447/f-re-efter-CRO-`), not shipped in this package. It links to `dist/` —
it contains no copy of it. What you paste is what the preview shows.

**Paste the files as-is.** Do not reformat, do not "clean up", do not move rules to a global
stylesheet. The block is deliberately self-contained (§9).

---

## 4. Install, step by step — verify each step before the next

### Step 1 — CSS snippet
FluentSnippets → New → type **CSS** → paste `dist/01-fore-efter.css` → placement **Head** → activate.

Verify: view-source of any page contains `@property --ampyfe-pos` (the first rule in the file).

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

### Step 6 — Import the images
With WP-CLI available, run from the WordPress root (where `wp-config.php` is). The `images/` folder
can be anywhere — pass its path:

```bash
bash /path/to/handover/images/wp-media-import.sh /path/to/handover/images
```

It imports the four masters with alt text, title and caption already set (from `manifest.csv`) and
prints one attachment ID per file. Note the IDs — they go into the repeater in step 8.

No WP-CLI? Upload the files from `images/` through Media → Add New, then paste alt/title/caption
from `manifest.csv` by hand. Run Regenerate Thumbnails afterwards if you uploaded before step 5.

Verify: Media library shows four images named `elcentral-byte-…`, each with alt text filled.

### Step 7 — Place the block
In Bricks, add a **Shortcode** element (not a Code element) where the block should sit — in the
proof zone, after the content block, before Testimonials. Content: `[ampy_fore_efter]`.

Verify: nothing renders yet. That is correct — no pair is signed yet (§5).

### Step 8 — Fill the fields
On the page: Rubrik `Så ser det ut när vi har`, Rubrik – understruken del `bytt en elcentral`,
leave Tagline empty, then add two rows in the repeater: before = `elcentral-byte-01-fore`, after =
`elcentral-byte-01-efter`, Omfattning `Byte av proppskåp till ny elcentral`; then the same for 02.
Leave `fore_alt`/`efter_alt` empty — the block picks up the media-library alt text you imported.

**Signerad is Julius's box, not yours.** It is the owner's attestation that the evidence folder for
that pair exists (§13). Until he ticks it the pair renders nothing — that is the gate working. To see
the block on a staging page before he has, tick it there and untick before the page goes public.

Verify: with both rows signed, the block renders both pairs. Drag both sliders. Compare against the
live reference.

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
| `signerad` | True/False | no (defaults off) | the gate below skips the row while it is off — only the owner ticks it |

### The gate — this is intentional, do not "fix" it

A row is skipped if it lacks either image, has an empty `omfattning`, or has `signerad` off.
If no row survives, the shortcode returns an empty string and **nothing renders**.

Reason: Swedish marketing law (MFL 10 §) puts the burden of proof on Ampy. A before/after pair that
cannot be backed by original files with EXIF, an order reference and the customer's consent is not
proof — it is a liability. `signerad` is the editorial confirmation that this evidence folder exists
for that pair. Never tick it as a shortcut; on a public page only Julius ticks it.

If exactly one row survives it renders centred at up to 760 px instead of half-width and alone.
More than two rows: only the first two render (`$MAX_PAR = 2` in the PHP — a stated limit, not a
silent truncation).

---

## 6. Images

### The masters in `images/`

| File | Job | Source | Pixels |
|---|---|---|---|
| `elcentral-byte-01-fore.jpg` / `-efter.jpg` | blue backing board, wooden ceiling | the owner's photos of the job, polished (Ampy logo retouched out); encoded once from the lossless master, JPEG q95 4:4:4 | 1448 × 1086 |
| `elcentral-byte-02-fore.jpg` / `-efter.jpg` | orange wall | same | 1448 × 1086 |

These four are the only images the block ships with — owner decision 2026-09-17. "Highest possible
quality" here means the pixels were touched exactly once: one encode from the lossless master, no
resampling. No EXIF in any file — nothing to leak.

Filenames are SEO-ready: lowercase, hyphenated, the service term first, pair number, side. No
location in the name because none is verified — never add one that isn't.

`manifest.csv` carries, per file: alt text, title and caption for the media library. The alt text
describes only what is visible in the photo. The import script in §4 step 6 applies all of it.

### Format: landscape 4:3

That is what a phone camera produces. A 4:3 photo fits the frame with zero cropping. Zero cropping
means the before and after image can never be cropped differently — and identical framing is the
entire trust mechanic of a before/after pair.

The PHP registers two sizes, both centre-cropped to 4:3 for anything uploaded in another format:

| Size name | Dimensions | Role |
|---|---|---|
| `ampy-foreefter` | 1200 × 900 | the `src` the block requests |
| `ampy-foreefter-2x` | 2400 × 1800 | retina candidate in `srcset` |

WordPress never upscales. The masters are 1448 px wide, so they get the 1200 size only — the 2x size
is not generated for them and the browser uses the 1200 file on retina. That is the source ceiling,
not a bug. Two sizes are registered anyway because future photos shot on a phone at full resolution
(3000–4000 px) will populate both.

### File weight

Masters are 460–570 kB; the 1200 × 900 derivative WordPress serves will land at roughly 100–180 kB
as JPEG. The budget is 30–80 kB per image. WordPress does not produce AVIF/WebP by itself. Two ways
to close the gap, both site-wide and therefore **your call, not part of this block**:

- a `image_editor_output_format` filter mapping `image/jpeg` → `image/webp` (WordPress 6.1+), or
- an optimisation plugin / CDN that serves WebP/AVIF on the fly.

### Loading and structured data

`loading="lazy"`, `decoding="async"`, `sizes="(max-width: 719px) 94vw, 620px"`. The block sits in
the proof zone and must never be the page's LCP.

Each rendered block emits a JSON-LD `ImageGallery` with one `ImageObject` per image (`contentUrl` =
full-size file, `name` = the alt text, `description` = Omfattning). It targets Google Images; expect
long-tail, not a traffic line. Check Search Console → Performance → Search type: Image after launch.

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
| Rest position | seam centred at 50 %: half before, half after |
| Drag | mouse: press anywhere → seam jumps there, drag follows. Touch/pen: the seam waits until the finger has moved > 6 px sideways (and more sideways than up/down); then it follows. A tap without movement places the seam where the finger was. Continues past the frame edge |
| Multi-touch | the first finger owns the drag; a resting thumb neither moves nor ends it |
| Vertical scroll | a vertical swipe **starting on the frame** scrolls the page; the seam, the hint and the interact event are untouched; pinch-zoom still works |
| Native image drag | never starts (images are `pointer-events: none` + `draggable="false"`) |
| Slider under the frame | native `<input type="range">`, mirrors the seam; dragging it moves the seam |
| Keyboard (slider focused) | ← → ±5 %, PageUp/Down ±10 %, Home/End. Motion 260 ms |
| Focus ring | shown only for keyboard focus, in midnight (≥3:1). Pressing in the image never shows it |
| Chips | FÖRE lives left of the seam, EFTER right of it. Dragging fully left hides FÖRE; fully right hides EFTER |
| Hint pill "Dra för att jämföra" | fades after the first interaction; never clipped at 0 % or 100 % |
| Nudge | once, when ≥55 % of the frame is visible: seam moves 50→63→50 %. Off under `prefers-reduced-motion`, off if the slider already has focus, off in Safari < 16.4 (no `@property` → it cannot animate) |
| Screen reader | reading order före → efter; slider announces "Efter syns till N procent"; images have alt text; chips, hint and handle are hidden from AT |
| Layout | 2 columns when the block's own content box is >680 px (iPad portrait = 2 columns); 1 column below |
| No JavaScript | `<noscript>` stacks the pair (before above after), both images whole with their chips, slider chrome hidden |
| Print | pairs stacked, slider chrome hidden |
| Forced colours | seam, handle and chips get system colours |
| Multiple blocks per page | each pair is independent; `window.ampyForeEfter.start()` re-initialises after AJAX loads |
| Analytics | see §9 |

### Device checklist

Run on a real iPhone (Safari), a real Android (Chrome), an iPad (both orientations) and desktop
Chrome + Firefox + Safari. On each: drag both sliders; drag with a thumb resting on the screen;
drag past the frame edge; **swipe vertically starting on a frame** (the page must scroll and the seam must not move); tap once in a frame; Tab to a slider and use arrows.

Everything above was verified in Blink (Chrome/Android engine) with touch emulation and synthetic
pointer events, including the vertical-scroll case. **WebKit on a real iPhone and Gecko were not
available in the build environment** — those two are on you.

---

## 9. Analytics

Two `dataLayer` events, only pushed if `window.dataLayer` exists:

| Event | When |
|---|---|
| `fore_efter_view` | a pair has been ≥55 % visible once — one per pair, so up to two per block; `block_id` tells them apart |
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
- **Only Ampy's own jobs**, signed. No stock, no other firm's work.
- **The JSON-LD stays inside the shortcode output.** It is built from the same IDs and alt texts as
  the images, so it can never describe a photo that isn't on the page.

### If the markup must change
The markup lives in one place: the two heredoc templates in `dist/02-fore-efter.php` between the
`AMPY-MALL-*-START/SLUT` markers. Edit there. The live reference is regenerated from the same
template in the GitHub repo — never hand-edit a preview.

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
| No `-2x` file in srcset | the source is narrower than 2400 px; WordPress never upscales. Expected for jobs 01–02 |
| Import script prints an error about `wp` | WP-CLI missing from PATH; upload manually and copy fields from `manifest.csv` |
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
The two jobs in `images/` are the owner's own photos, supplied 2026-09-17. Ticking `signerad` on a
row is the owner's attestation that the folder above exists for that pair — which is why it is his
box to tick, not the developer's.
