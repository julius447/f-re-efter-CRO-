# Före/efter-blocket — riktning A, "Reglaget"

Ampy uppdrag 06. Ett rent bevisblock där riktiga före/efter-foton från Ampys egna jobb bär hela
budskapet: en H2, noll asks, designen talar. **Två jobb sida vid sida**, vart och ett i en kvadratisk
ram med sitt eget reglage — besökaren drar sömmen och ser förvandlingen ske i exakt den punkt hen
väljer. På mobil staplas de.

**Live:** https://julius447.github.io/f-re-efter-CRO-/
· [utan JavaScript](https://julius447.github.io/f-re-efter-CRO-/no-js.html)

Riktning A valdes av ägaren 2026-08-17. B ("Bevisparet") och C ("Journalen") är avförda; de ligger
kvar i `wireframes/` som historik och byggs inte vidare.

---

## Om bilderna — läs detta först

Bilderna i repot är **illustrationer**, märkta som sådana i sitt eget hörn. Ingen är ett foto från
ett riktigt Ampy-jobb, och ingen är AI-genererad. Fotobiblioteket finns ännu inte: live-kollen
2026-08-16 gav noll före/efter-material på ampy.se.

De finns för att visa **formen** och **konsistensregeln** (samma ram, samma ljus, bara jobbet
skiljer). De ersätts av original ur bevismappen före publicering, och blocket vägrar rendera ett par
som inte är signerat.

## Vad som ligger var

| Fil | Roll |
|---|---|
| `dist/01-fore-efter.css` | Blockets CSS → FluentSnippets, head |
| `dist/02-fore-efter.php` | Shortcode `[ampy_fore_efter]` + markupmallen → FluentSnippets, frontend & backend |
| `dist/03-fore-efter.js` | Reglaget → FluentSnippets, footer |
| `index.html` | Förhandsgranskning. Genereras av `build.py` ur PHP-mallen, läser `dist/` |
| `no-js.html` | Samma block utan skript: staplade par |
| `build.py` | Packar förhandsgranskningen ur PHP-mallen (en sanning, ingen drift) |
| `HANDOVER.md` | Vad Chris klistrar in var, ACF-fälten, bildkraven, mätningen |
| `wireframes/` | Fas 1, oförändrad |

## Kanon som inte får brytas

- **0 asks.** Ingen knapp, ingen länk i blocket. Sidbasens ask-tak (5) är fullt, och blockets styrka
  är just att det får ligga var som helst utan att röra budgeten.
- **EN H2.** Ingen ingress, ingen eyebrow.
- **Renderingskontraktet.** Båda bilderna som riktiga `<img>` med width/height, synliga utan JS.
  Ingen enterView, ingen JS-injicerad src. Reglaget är en enhancement, aldrig grunden.
- **Kvadratiska 1:1**, samma aspekt på båda bilderna.
- **Variationen bor i ACF**, aldrig i mallen. Samma block på 22 tjänstesidor.
- **Endast riktiga Ampy-jobb.** Inga AI-bilder, ingen stock, ingen iscensättning, ingen retusch som
  rör arbetets kvalitet. Osignerat par → blocket renderar ingenting.

## Reglagets beteende

| | |
|---|---|
| Antal | Två par, ett reglage vardera, helt oberoende av varandra |
| Viloläge | 35 % — EFTER dominant, så den som aldrig drar ändå ser utfallet |
| Peka | tryck var som helst i ramen flyttar sömmen dit; drag följer fingret |
| Tangentbord | piltangenter ±5 %, PageUp/PageDown ±10 %, Home/End |
| Skärmläsare | reglaget säger "Efter syns till 65 procent", inte "35" |
| Touch | `pan-y pinch-zoom` — vertikal scroll och nyp-zoom lever, vi rör aldrig touchmove |
| Rörelse | 260 ms, under husets 300 ms-tak; engångsvinkning som lär ut mekaniken, avstängd vid `prefers-reduced-motion` |
| Dragning | bilderna är `pointer-events: none` + `draggable="false"`, annars startar webbläsaren sin egen bilddragning |
| Utan JS | staplat par via `<noscript>`, inget innehåll försvinner |
| Chipsen | båda klipps av sömmen: dra helt åt vänster och FÖRE försvinner, helt åt höger och EFTER försvinner |
| Utskrift | paren staplas — ett halvt foto är meningslöst på papper |

## Ägarbeslut som sitter i koden

| Beslut | Datum | Var |
|---|---|---|
| Riktning A vinner, B och C avförs | 2026-08-17 | hela repot |
| Två par sida vid sida i stället för ett | 2026-08-17 | `.ampy-foreefter__par` |
| Bilderna är kvadratiska, inte stående | 2026-08-17 | `aspect-ratio: 1 / 1` |
| H2:an är exakt sajtens globala H2 (aptext-2-5xl / 500, ingen egen line-height eller letter-spacing) | 2026-08-17 | `.ampy-foreefter__rubrik` |
| Mörka skärmen bakom chipsen borttagen | 2026-08-17 | `__ram::before` struken |
| EFTER-chippet: en plan färg, ingen gradient, ingen blixt | 2026-08-17 | `.ampy-foreefter__chip--efter` |
| "Villa i [Ort]" borttagen ur bildtexten | 2026-08-17 | `__plats` struken |
| Bildtexterna per par ersatta av EN semi-global tagline, unik per tjänst | 2026-08-17 | `.ampy-foreefter__tagline`, ACF-fält `tagline` |
| EFTER-chippet klipps spegelvänt mot sömmen, så en dragning helt åt höger döljer det precis som en dragning helt åt vänster döljer FÖRE | 2026-08-17 | `.ampy-foreefter__chiplager` |
| Understrykningen under rubrikaccenten är svart, inte grön | 2026-08-17 | `.ampy-foreefter__accent::after` |
| Raden "Riktiga, oretuscherade bilder från våra jobb" tas bort | 2026-08-17 | borttagen; stänger samtidigt GAP-11.1 |

## Öppna grindar

| Grind | Fråga | Läge |
|---|---|---|
| **GRIND 0** | Fotobiblioteket finns inte | **Blockerar lansering.** Fotoprotokollet måste ut till montörerna. |
| GAP-8.1 | Medgivanderad i arbetsordern | Blockerar lansering. |
| GAP-11.2 | Position 4 vs 5 i sidsekvensen | Blocket är byggt fristående. |
| ACF | Fältgruppen + repeatern finns inte i WordPress ännu | Se `HANDOVER.md` §2. |
| Fotoprotokollet | Kvadratisk beskärning kräver att montören fotar med mer marginal | Protokollet måste uppdateras innan fotograferingen börjar. |
| iOS på riktig enhet | Testat i Blink (samma motor som Android/Chrome) + touch-emulering, **inte** i Safari/WebKit på enhet | Öppna live-länken på en iPhone och dra i båda reglagen. |
