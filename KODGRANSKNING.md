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
- **Riktiga foton.** Blocket är förberett för dem (se HANDOVER §2–3), men inga har körts igenom.

Det som stänger de tre: öppna live-länken på en iPhone, en Android och i Firefox och dra i båda
reglagen — inklusive med en vilande tumme, och hela vägen ut över kanten.
