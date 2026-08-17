# Före/efter-blocket — tre riktningar (mockup)

Ampy uppdrag 06. Ett rent bevisblock där riktiga före/efter-foton från Ampys egna jobb bär hela
budskapet: en H2, noll asks, designen talar. Blocket ska fungera på 22 tjänstesidor, dussintals
programmatiska sidor och produktsidor — variationen bor i ACF, aldrig i mallen.

**Live:** https://julius447.github.io/f-re-efter-CRO-/

---

## Om bilderna — läs detta först

Varje bild i repot är en **illustration**, märkt som sådan i sitt eget hörn. Ingen är ett foto från ett
riktigt Ampy-jobb, och ingen är AI-genererad. Fotobiblioteket finns ännu inte: live-kollen 2026-08-16
gav noll före/efter-material på ampy.se.

Illustrationerna finns för att visa **formen** och **konsistensregeln** (samma ram, samma ljus, bara
jobbet skiljer). De ersätts av original ur bevismappen före publicering. Det är inte en formalitet:
MFL 10 § lägger bevisbördan på Ampy, och en bild som inte visar det den påstår är otillbörlig
marknadsföring oavsett hur bra den ser ut.

## De tre riktningarna

| Fil | Riktning | Kärnidé |
|---|---|---|
| `a-reglaget.html` | **A — Reglaget** | Ett par i samma ram; besökaren drar sömmen. Vilolaget 65/35 med EFTER dominant. |
| `a-reglaget-nojs.html` | **A utan JS** | Samma markup utan skriptet: staplat par. Beviset på att reglaget är enhancement, inte grunden. |
| `b-bevisparet.html` | **B — Bevisparet** | Jämsides, noll JavaScript. 100 % av informationen i vilolaget för 100 % av besökarna. |
| `c-journalen.html` | **C — Journalen** | Fallet som bevis: dominant par + faktakolumn med riskrad + tre stödpar. Stödparen sveps på mobil. |
| `c-journalen-stapel.html` | **C, variant** | Samma, men stödparen staplas på mobil (GAP-C1). |
| `index.html` | Översikt | Alla fem i levande ramar, mobil 390 + desktop 1280, plus grindtabellen. |

`wireframes/` innehåller fas 1 (wireframe-fasen) oförändrad, som referens.

## Kanon som inte får brytas

- **0 asks.** Ingen knapp, ingen länk i blocket. Sidbasens ask-tak (5) är fullt.
- **EN H2.** Ingen ingress, ingen eyebrow-paragraf.
- **Renderingskontraktet.** Båda bilderna som riktiga `<img>` med width/height, synliga utan JS.
  Ingen enterView, ingen JS-injicerad src.
- **Stående 4:5**, samma aspekt på båda bilderna (CLS + tillit).
- **Variationen bor i ACF** (`fore_bild`, `efter_bild`, `jobbtyp`, `omfattning`, `omrade`, `riskrad`,
  `signerad`), aldrig i mallen.
- **Endast riktiga Ampy-jobb.** Inga AI-bilder, ingen stock, ingen iscensättning, ingen retusch som
  rör arbetets kvalitet.

## Teknik

Ren HTML och CSS. Ett enda skript (`js/reglage.js`, 60 rader) och bara för riktning A. Inga beroenden,
inget byggsteg.

- `css/tokens.css` — produktionstokens hämtade ur live-CSS:en på ampy.se (teal `#00a991`, midnatt
  `#090b32`, sky-mist `#f5f9ff`, Outfit, `ap*`-skalan på `html{font-size:62.5%}`, 1rem=10px).
  `--shadow-primary` definieras explicit; i produktion refereras den fem gånger utan att definieras.
  `--apspace-4xs` används inte alls (malformad clamp i produktion).
- `css/block.css` — blocket. Brytpunkter 992 / 768 / 480. 380px är ingen brytpunkt.
- `css/shell.css` — endast förhandsgranskningens skal, följer inte med till Bricks.

Kör lokalt:

```bash
python3 -m http.server 8763
```

## Öppna grindar

| Grind | Fråga | Läge |
|---|---|---|
| GRIND 0 | Fotobiblioteket finns inte | Blockerar SHIP på alla sidor. Fotoprotokollet måste ut till montörerna. |
| GAP-A1 | Desktopaspekt för reglaget | 4:3 testades och föll: den klipper bort centralens topp och botten. Mockupen kör 4:5 överallt. Väntar godkännande. |
| GAP-B1 | Lightbox vid tap i B | AV. Inget tap-beteende ritat. |
| GAP-C1 | Stödparens mobilform | Båda ritade. Rekommendation: svep (stapel kostar 578 px extra skroll utan mer information). |
| GAP-8.1 | Medgivanderad i arbetsordern | Blockerar SHIP. |
| GAP-11.1 | ”Riktiga, oretuscherade bilder” kräver ägarintyg | Raden ritad, skeppas inte osignerad. |
| GAP-11.2 | Position 4 vs 5 i sidsekvensen | Blocket ritat fristående. |
| Chipshörnen | Kanon §4.5 säger ”samma hörn varje gång”, §6/B säger FÖRE vänster / EFTER höger | B och C följer kanon. A tvingas spegla: chipsen kan inte dela hörn i samma ram. |
| Copyn | `[Ort]`, `[X]`, `[årtal]` är ACF-fält, inte text | Låses av ampy-rost + riktiga jobbdata. Inget hittas på. |
