# Kodgranskning — före/efter-blocket

Genomförd 2026-08-17 på hela blocket (CSS, PHP, JS) inför att riktiga bilder läggs in.
Metoden: räkna upp var koden KAN gå sönder, reproducera varje misstanke i webbläsaren,
rätta, och köra samma reproduktion igen. Inget nedan är en gissning — varje defekt är
framkallad och verifierad.

---

## 1. Felytan

Blocket kan bara gå sönder på fem ställen. Granskningen följer dem i tur och ordning.

| Yta | Vad som kan gå fel |
|---|---|
| Pekarhantering | flera fingrar, pekare som lämnar ramen, drag som aldrig avslutas, rörelse tätare än skärmen ritar |
| Formulärtillstånd | webbläsaren återställer reglaget vid omladdning och bakåtnavigering |
| Layout | container smalare eller bredare än väntat, surfplatta i porträtt, texter som spiller |
| Bilddata | fel bildförhållande, saknad bild, ACF som returnerar annat format än väntat |
| Renderingsmotor | funktioner som saknas i äldre Safari eller Android-webbvyer |

---

## 2. Defekter som hittades och rättades

### D1 — En vilande tumme kapade sömmen  ·  ALLVARLIG  ·  rättad

**Reproducerad:** finger 1 ner vid 30 %, tumme vilar vid 80 % → sömmen hoppade till 80 %.

Tillståndet var en boolean (`drar`), inte en identitet. Varje `pointerdown` tog över, oavsett
vilken pekare det var. På en telefon eller iPad räcker det att tummen vilar mot skärmkanten.

**Rättat:** draget ägs av EN pekare, identifierad med `pointerId`. Alla andra pekare ignoreras
helt tills ägaren släpper.

### D2 — Att lyfta det andra fingret frös draget  ·  ALLVARLIG  ·  rättad

**Reproducerad:** tummen lyfts → `pointerup` nollställde flaggan → finger 1 fortsatte dra till
55 % men sömmen stod kvar på 40 %.

**Rättat:** bara ägarens `pointerup` släpper draget.

### D3 — Draget dog vid ramkanten  ·  ALLVARLIG  ·  rättad

**Reproducerad:** drag från 50 % förbi ramens högerkant → sömmen frös på 70 % i stället för att
följa med till 90 %.

`pointerleave` avbröt draget. Normalt döljs det av `setPointerCapture`, men om capture inte tar
— äldre WebKit, ovanliga inmatningsenheter — biter det direkt.

**Rättat:** `pointerleave` lyssnas inte på alls. `lostpointercapture` plus ett skyddsnät på
`window` (`pointerup`, `pointercancel`, `blur`) sköter frisläppningen.

### D4 — Sömmen skrevs oftare än skärmen ritade  ·  PRESTANDA  ·  rättad

Varje `pointermove` skrev en CSS-variabel som driver `clip-path` på ett lager plus `left` på två
element. En 120 Hz-iPad eller en mus med hög pollningsfrekvens skickar fler händelser än det
finns bildrutor — allt arbete däremellan kastas bort och kan ge hack på svagare Android.

**Rättat:** rAF-koalescering. Senaste x sparas, sömmen skrivs en gång per bildruta. Ramens
position hämtas i callbacken, så ett drag överlever att sidan scrollar under fingret.

### D5 — Skärmläsaren spammades  ·  TILLGÄNGLIGHET  ·  rättad

`aria-valuetext` skrevs om vid varje rörelse, även när det avrundade talet var oförändrat.

**Rättat:** attributet skrivs bara när talet faktiskt ändras.

### D6 — Sömmen och tummen kunde hamna i otakt  ·  MEDEL  ·  rättad

Firefox och Chrome återställer formulärvärden vid mjuk omladdning och bakåtnavigering. Koden
tvingade alltid 35 % vid start, medan reglagets tumme kunde stå någon annanstans.

**Rättat:** startläget läses ur reglaget, inte ur konstanten.

### D7 — Tangentbordet tappade tråden efter ett drag  ·  MINDRE  ·  rättad

`preventDefault` på `pointerdown` blockerar fokus. Den som drog med musen och sedan tryckte på
piltangenterna fick ingenting.

**Rättat:** reglaget får fokus vid `pointerdown`, med `preventScroll` så sidan inte hoppar.
Fokusringen syns fortfarande bara för tangentbord (`:focus-visible`).

### D8 — iPad i porträtt staplade i onödan  ·  MEDEL  ·  rättad

Brytpunkten låg på 780 px innehållsruta. En iPad i porträtt ger 736 px och hamnade under —
alltså en kolumn, dubbelt så lång sida, halva ytan outnyttjad.

**Rättat:** brytpunkten är 680 px. Mätt efteråt: iPad porträtt ger nu två kolumner, 357×357 px
per ram och 633 px blockhöjd i stället för drygt 1 100.

### D9 — ACF kunde returnera ett format koden inte förstod  ·  MEDEL  ·  rättad

Bildfältet läste array och ID, men inte URL. Är fältet inställt på "Image URL" hoppades raden
över — blocket hade renderat tomt utan att någon förstått varför.

**Rättat:** array, ID och URL accepteras alla.

---

## 3. Kontrollerat och funnet i sin ordning

| Kontroll | Resultat |
|---|---|
| Två reglage oberoende av varandra | passerar — drag i det ena rör inte det andra |
| Vertikal scroll under fingret | `touch-action: pan-y pinch-zoom`, `touchmove` rörs aldrig |
| Nyp-zoom bevarad | ja — `pinch-zoom` kvar i `touch-action` |
| Webbläsarens egen bilddragning | avstängd tre gånger om: `pointer-events: none`, `draggable="false"`, `dragstart` avbryts |
| Tangentbord | piltangenter ±5, PageUp/PageDown ±10, Home/End |
| Skärmläsare | `aria-label` med jobbets omfattning, `aria-describedby`, `aria-valuetext` på svenska |
| Träffytor | handtag 52 px desktop / 46 px smal ram, reglagerad 44 px |
| Utan JavaScript | fyra bilder renderar hela och kvadratiska, allt reglagechrome dolt |
| Horisontell överspillning | ingen vid 390, 412, 768, 1024, 1440 |
| Ledtrådens klamring | håller sig inom ramen vid 0 %, 35 % och 100 % |
| Rubriken mot sajtens H2 | identisk: Outfit, aptext-xl, vikt 400, line-height 1,2 |
| Globala CSS-regler | noll — ingen `html{}`, `body{}` eller `*{}` utanför wrappern |
| rem i dist-CSS | noll — allt i px, blocket ärver aldrig temats rotstorlek |
| Utskrift | paren staplas, reglaget döljs |
| Reducerad rörelse | vinkningen stängs av, övergångar tas bort |
| Forced colors | söm, handtag och chips får systemfärger |

---

## 4. Motorstöd och nedgradering

| Funktion | Krav | Utan stöd |
|---|---|---|
| `@property` (mjuk sömrörelse) | Safari 16.4, Chrome 85, Firefox 128 | sömmen hoppar i stället för att glida — allt annat intakt |
| Container queries | Safari 16, Chrome 105, Firefox 110 | `@supports`-gren med vanlig viewport-fråga tar över |
| `:has()` (fokusring på handtaget) | Safari 15.4, Chrome 105, Firefox 121 | reglaget har kvar sin egen fokusring |
| `aspect-ratio` | Safari 15, Chrome 88 | äldre än så saknar stöd — se begränsningen nedan |
| `text-wrap: balance` | Chrome 114, Safari 17.5 | rubriken bryter normalt |
| Pointer Events | överallt sedan iOS 13 | reglaget under bilden fungerar ändå |

---

## 5. Vad som INTE gick att verifiera här

Sägs rakt ut i stället för att antas.

- **iOS/WebKit på riktig enhet.** Simulatorn kräver full Xcode, som saknas på maskinen. Testat i
  Blink med touch-emulering och syntetiska pekarhändelser av typen `touch`. Blink är samma motor
  som Android och Chrome kör, så Android är täckt — WebKit är det inte.
- **Firefox/Gecko.** Ingen Firefox tillgänglig i den här miljön.
- **Riktiga foton.** Stängd 2026-09-17 — ägarens fyra bilder ligger i `images/` och renderas i live-länken.

Det som stänger de tre: öppna live-länken på en iPhone, en Android och i Firefox och dra i båda
reglagen — inklusive med en vilande tumme, och hela vägen ut över kanten.


---

# Granskning 2 — 2026-09-17, inför överlämning

Sex granskningslinser (iOS, Android, desktop, tillgänglighet, WordPress, dokumentation) körda parallellt
på hela blocket; varje fynd prövat av tre oberoende skeptiker som fick i uppdrag att vederlägga det.
30 fynd, 0 vederlagda — sex av dem var samma noscript-bugg och två samma scroll-bugg, så 20 unika.
Alla 20 rättade och verifierade i Blink. Dessutom ett 21:a fynd som hittades under verifieringen.

## Rättat i JS (`dist/03-fore-efter.js`)

| # | Defekt | Bevis | Rättning |
|---|---|---|---|
| 1 | **En vertikal scroll som började på fotot flyttade sömmen, släckte ledtråden för gott, fokuserade reglaget och skickade `fore_efter_interact`** — innan webbläsaren hann ta över scrollen. iOS och Android. | CDP-touch i Blink: pos 50 → 30 %, dataLayer fick interact, sidan scrollade 205 px | Touch/penna bekräftar draget först efter > 6 px i sidled och mer sidled än höjdled. Ett lyft utan rörelse är ett tryck och placerar sömmen. Musen hoppar direkt som förut. `pointercancel`/`lostpointercapture` släpper bara, flyttar aldrig. Verifierat: vertikal scroll → pos 50, ingen klass, inget fokus, tom dataLayer. |
| 2 | Vinkningen i Safari < 16.4 blev två hopp (ingen `@property` → ingen övergång) | motorstöd | Vinkningen hoppas över där `CSS.registerProperty` saknas |
| 3 | Vinkningen flyttade ett reglage som redan hade tangentbordsfokus | a11y | Ingen vinkning om `document.activeElement` är reglaget |
| 4 | iOS: ett tryck som stoppar rullningsmomentum tolkades som tryck-för-att-placera | iOS-lins | Tryck inom 120 ms efter senaste scroll-händelse placerar inte |

## Rättat i CSS (`dist/01-fore-efter.css`)

| # | Defekt | Rättning |
|---|---|---|
| 5 | Safari < 16.4: `--ampyfe-pos` saknade värde tills JS kört — FÖRE över hela ramen | `--ampyfe-pos: 50%` som vanlig deklaration på figuren, `@property` kvar för animeringen |
| 6 | **Ensamt par renderades i vänstra halvan** | `grid-column: 1 / -1` |
| 7 | …och fick bredd 0 (hittat under verifieringen): storlekscontainer + `margin-inline: auto` = shrink-to-fit av ett element som inte får mäta sitt innehåll | `width: min(100%, 760px)` + `justify-self: center` i stället för max-width + auto-marginaler |
| 8 | iOS 15.0–15.3: `:focus{outline:none}` tog bort ringen även där `:focus-visible` saknas — ingen ring alls | Två regler: `.fokus-fran-pekare … :focus` och `:focus:not(:focus-visible)`; en motor utan `:focus-visible` kastar den andra och behåller sin egen ring |
| 9 | Fokusringen i teal gav 2,8:1 mot bakgrunden — under WCAG 1.4.11:s 3:1 | Midnatt (15:1), vit mellanring på handtaget |
| 10 | Print och no-JS: EFTER-chippet lossnade och hamnade i sektionens övre högra hörn (ramen slutar vara positionerad) | Chiplagret läggs i samma rutnätscell som efter-bilden, `position: relative` |
| 11 | Windows High Contrast: spårets gradient togs bort, bara tummen syntes | Kant i `CanvasText` på spåret |

## Rättat i PHP (`dist/02-fore-efter.php`) och `build.py`

| # | Defekt | Rättning |
|---|---|---|
| 12 | **`<noscript>`-selektorn `#a, #b .klass` betydde "hela #a" — första paret försvann helt utan JS** | Ett prefix per figur: `#a .klass,#b .klass`. Speglat i `build.py`. Verifierat: båda figurerna synliga med PHP:ns egna regler injicerade |
| 13 | Mediabibliotekets alt-text användes aldrig — produktionen fick generiska, identiska alt-texter | Kedjan ACF-fält → `_wp_attachment_image_alt` → generisk |
| 14 | Chipsen FÖRE/EFTER lästes upp som lösa ord | `aria-hidden="true"` — reglaget bär namnet |
| 15 | Läsordningen var efter → före | FÖRE först i DOM, ritas överst via z-index |
| 16 | `JSON_UNESCAPED_SLASHES`: ett `</script>` i ett fält bröt ut ur script-taggen | `JSON_HEX_TAG | JSON_HEX_AMP`; `build.py` escapar `</` |

## Rättat i dokumentationen

| # | Defekt | Rättning |
|---|---|---|
| 17 | Steg 8 beordrade utvecklaren att kryssa i Signerad — mot grinden i §5/§13 | Signerad är ägarens ruta; staging-undantaget står utskrivet |
| 18 | §5 sa `signerad` Required: yes, ACF-JSON säger required 0 (JSON är rätt) | Tabellen rättad |
| 19 | Steg 1-verifieringen sökte `.ampy-foreefter{` som inte finns | Söker `@property --ampyfe-pos` |
| 20 | Importkommandot förutsatte `images/` i WordPress-roten | Sökvägen är ett argument, exempel med full sökväg |
| 21 | Guiden pekade på `build.py`/`index.html`/`img/` som inte följer med i zippen | Pekar på repot |
| 22 | `fore_efter_view` skickas per par, §9 sa per block | §9 säger per par, `block_id` skiljer dem |
| 23 | README motsade koden på fyra punkter (35/65 %, kvadrat, aptext-2-5xl/500, HANDOVER.md) | README rättad |
| 24 | `paketera.sh` pekade på PNG-filer med ChatGPT-namn som "mästare" | Borttaget — JPEG-mästarna i `images/` är mästarna; källan är ägarens polerade foton |

## Kvar att verifiera på riktig enhet

Samma som §5 ovan: WebKit på en riktig iPhone och Firefox. Nytt testfall som ska köras där:
**scrolla sidan med ett finger som börjar på fotot** — sidan ska scrolla, sömmen stå kvar på 50 %,
ledtråden synas och ingen `fore_efter_interact` skickas.
