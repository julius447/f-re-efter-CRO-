# Före/efter-blocket — riktning A, "Reglaget"

Ampy uppdrag 06. Ett rent bevisblock där riktiga före/efter-foton från Ampys egna jobb bär hela
budskapet: en H2, noll asks, designen talar. **Två jobb sida vid sida**, vart och ett i en liggande
4:3-ram med sitt eget reglage — besökaren drar sömmen och ser förvandlingen ske i exakt den punkt hen
väljer. På mobil staplas de.

**Live:** https://julius447.github.io/f-re-efter-CRO-/
· [utan JavaScript](https://julius447.github.io/f-re-efter-CRO-/no-js.html)

Riktning A valdes av ägaren 2026-08-17. B ("Bevisparet") och C ("Journalen") är avförda; de ligger
kvar i `wireframes/` som historik och byggs inte vidare.

---

## Om bilderna — läs detta först

Blocket har **fyra bilder, två jobb** — ägarbeslut 2026-09-17: bara dessa, inga testbilder. Det är
ägarens egna foton från Ampys jobb, polerade (logotypen bortretuscherad). Mästarna ligger i
`images/` med SEO-filnamn, kodade en gång från förlustfri källa (JPEG q95 4:4:4, 1448 × 1086, ingen
EXIF). `img/jobb/` är förhandsgranskningens derivat av exakt dem, byggda som WordPress bygger sina.

**De är inte signerade i bevismapp ännu.** Det är grinden före publicering: originalfiler med EXIF,
order-referens och kundmedgivande per par (MFL 10 §, omvänd bevisbörda). Blocket vägrar rendera ett
par som inte är signerat — i förhandsgranskningen är den grinden förbi­kopplad med flit, för att
ägaren ska kunna se hur det ser ut.

| Jobb | Mästare | Före | Efter |
|---|---|---|---|
| a | `elcentral-byte-01` | proppskåp på blå bakskiva, träpanel i taket | två Hager-centraler, samma skiva |
| b | `elcentral-byte-02` | proppskåp på orange vägg | två Hager-centraler, samma vägg |

Förhandsgranskningsremsan är borttagen (ägarbeslut 2026-09-17): sidan visar blocket exakt som det
kommer att se ut på sajten.

## Vad som ligger var

| Fil | Roll |
|---|---|
| `dist/01-fore-efter.css` | Blockets CSS → FluentSnippets, head |
| `dist/02-fore-efter.php` | Shortcode `[ampy_fore_efter]` + markupmallen → FluentSnippets, frontend & backend |
| `dist/03-fore-efter.js` | Reglaget → FluentSnippets, footer |
| `index.html` | Förhandsgranskningen. Genereras av `build.py` ur PHP-mallen, läser `dist/` |
| `images/` | **Uppladdningsmästarna**: fyra bilder med SEO-filnamn, `manifest.csv` (alt/titel/bildtext) och `wp-media-import.sh` |
| `img/` | Förhandsgranskningens derivat — laddas aldrig upp |
| `paketera.sh` | Bygger `~/Desktop/Ampy-fore-efter-handover.zip` ur repot |
| `no-js.html` | Samma block utan skript: staplade par |
| `build.py` | Packar förhandsgranskningen ur PHP-mallen (en sanning, ingen drift) |
| `acf/ampy-foreefter-falt.json` | ACF-fältgruppen, importeras rakt av — inget byggs för hand |
| `IMPLEMENTATION.md` | **Implementationsguiden till Chris** (engelska): steg för steg med kontroll efter varje steg, fältreferens, beteendespec, felsökning, juridisk grind |
| `FOTOPROTOKOLL.md` | Sju punkter till montörerna, en laminerad rad i servicebilen |
| `KODGRANSKNING.md` | Två kodgranskningar: nio + tjugo rättade defekter, vad som kontrollerats, vad som inte gick att verifiera |
| `KODGRANSKNING.md` §5 + `IMPLEMENTATION.md` §8 | vad som INTE verifierats: WebKit på riktig iPhone, Firefox |
| `wireframes/` | Fas 1, oförändrad |

## Kanon som inte får brytas

- **0 asks.** Ingen knapp, ingen länk i blocket. Sidbasens ask-tak (5) är fullt, och blockets styrka
  är just att det får ligga var som helst utan att röra budgeten.
- **EN H2.** Ingen ingress, ingen eyebrow.
- **Renderingskontraktet.** Båda bilderna som riktiga `<img>` med width/height, synliga utan JS.
  Ingen enterView, ingen JS-injicerad src. Reglaget är en enhancement, aldrig grunden.
- **Liggande 4:3**, telefonens format, samma på båda bilderna. Inget beskärs.
- **Variationen bor i ACF**, aldrig i mallen. Samma block på 22 tjänstesidor.
- **Endast riktiga Ampy-jobb.** Inga AI-bilder, ingen stock, ingen iscensättning, ingen retusch som
  rör arbetets kvalitet. Osignerat par → blocket renderar ingenting.

## Reglagets beteende

| | |
|---|---|
| Antal | Två par, ett reglage vardera, helt oberoende av varandra |
| Viloläge | 50 % — sömmen mitt i bilden |
| Peka | mus: tryck var som helst i ramen flyttar sömmen dit, drag följer. Finger: sömmen väntar tills fingret rört sig > 6 px i sidled (mer sidled än höjdled) — ett tryck utan rörelse placerar den där fingret var |
| Tangentbord | piltangenter ±5 %, PageUp/PageDown ±10 %, Home/End |
| Skärmläsare | reglaget säger "Efter syns till 65 procent", inte "35" |
| Touch | `pan-y pinch-zoom` — en vertikal scroll som börjar på fotot scrollar sidan utan att sömmen, ledtråden eller mätningen rörs; nyp-zoom lever; vi rör aldrig touchmove |
| Rörelse | 260 ms, under husets 300 ms-tak; engångsvinkning som lär ut mekaniken, avstängd vid `prefers-reduced-motion`, om reglaget redan har fokus, och i Safari < 16.4 (kan inte animera `@property`) |
| Dragning | bilderna är `pointer-events: none` + `draggable="false"`, annars startar webbläsaren sin egen bilddragning |
| Flera fingrar | draget ägs av en `pointerId`; en vilande tumme kan varken kapa eller frysa det |
| Utanför ramen | draget följer med förbi kanten och släpps av `lostpointercapture` eller skyddsnätet på `window` |
| Prestanda | sömmen skrivs högst en gång per bildruta (rAF-koalescering) |
| Utan JS | staplat par via `<noscript>` (före över efter, chipsen kvar på sina bilder), inget innehåll försvinner |
| Skärmläsare, ordning | före → efter, som DOM:en — FÖRE ritas överst via z-index, inte via ordning |
| Fokusring | midnatt, aldrig teal (teal når inte 3:1 mot bakgrunden) |
| Chipsen | båda klipps av sömmen: dra helt åt vänster och FÖRE försvinner, helt åt höger och EFTER försvinner |
| Utskrift | paren staplas — ett halvt foto är meningslöst på papper |

## Ägarbeslut som sitter i koden

| Beslut | Datum | Var |
|---|---|---|
| Riktning A vinner, B och C avförs | 2026-08-17 | hela repot |
| Två par sida vid sida i stället för ett | 2026-08-17 | `.ampy-foreefter__par` |
| Bilderna är kvadratiska, inte stående | 2026-08-17 | ersatt av 4:3 2026-09-17 |
| Ramen är 4:3 — kvadraten beskar bort 25 % av varje foto och såg inzoomad ut | 2026-09-17 | `aspect-ratio: 4 / 3`, bildstorlekar 1200×900 + 2400×1800 |
| H2:an är exakt den H2 sajten faktiskt renderar: aptext-xl / 400 / line-height 1,2 (temats default 2-5xl/500 skrivs över av varje block) | 2026-08-17, mätt om 2026-09-17 | `.ampy-foreefter__rubrik` |
| Mörka skärmen bakom chipsen borttagen | 2026-08-17 | `__ram::before` struken |
| EFTER-chippet: en plan färg, ingen gradient, ingen blixt | 2026-08-17 | `.ampy-foreefter__chip--efter` |
| "Villa i [Ort]" borttagen ur bildtexten | 2026-08-17 | `__plats` struken |
| ILLUSTRATION-taggen borttagen ur bilderna | 2026-08-17 | ersatt av riktiga foton 2026-09-17 |
| Förhandsgranskningsremsan borttagen | 2026-09-17 | `build.py` |
| Fokusringen visas bara för tangentbordsfokus | 2026-09-17 | `fokus-fran-pekare` i JS + CSS |
| Riktiga foton ersätter illustrationerna; bara ägarens fyra bilder (två jobb), inga testbilder | 2026-09-17 | `images/`, `img/jobb/`, `build.py` |
| Sömmen vilar på 50 %, mitt i bilden | 2026-09-17 | `VILOLAGE`, `@property` |
| Bildtexterna per par ersatta av EN semi-global tagline, unik per tjänst — och taglinen tom tills vidare | 2026-08-17, tom 2026-09-17 | `.ampy-foreefter__tagline`, ACF-fält `tagline` |
| EFTER-chippet klipps spegelvänt mot sömmen, så en dragning helt åt höger döljer det precis som en dragning helt åt vänster döljer FÖRE | 2026-08-17 | `.ampy-foreefter__chiplager` |
| Understrykningen under rubrikaccenten är svart, inte grön | 2026-08-17 | `.ampy-foreefter__accent::after` |
| Raden "Riktiga, oretuscherade bilder från våra jobb" tas bort | 2026-08-17 | borttagen; stänger samtidigt GAP-11.1 |

## Öppna grindar

| Grind | Fråga | Läge |
|---|---|---|
| **GRIND 0** | Fotobiblioteket | Två par, ägarens egna. **Signering i bevismapp saknas** (original med EXIF, order-ref, kundmedgivande) — det blockerar lansering. |
| GAP-8.1 | Medgivanderad i arbetsordern | Blockerar lansering. |
| GAP-11.2 | Position 4 vs 5 i sidsekvensen | Blocket är byggt fristående. |
| ACF | Fältgruppen + repeatern finns inte i WordPress ännu | `acf/ampy-foreefter-falt.json` importeras, se `IMPLEMENTATION.md` §4 steg 4. |
| Fotoprotokollet | Fota liggande, centralen mitt i bild, luft runt om | Protokollet till montörerna. |
| Bildoptimering | WordPress gör inte AVIF själv | Utan optimeringssteg spränger paret filbudgeten. |
| iOS på riktig enhet | Testat i Blink (samma motor som Android/Chrome) + touch-emulering, **inte** i Safari/WebKit på enhet | Öppna live-länken på en iPhone och dra i båda reglagen. |
