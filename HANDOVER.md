# Handover — före/efter-blocket till WordPress

Till dev (Chris). Tre filer klistras in i FluentSnippets, ingenting hand­konverteras.
Det som ligger i `dist/` är exakt de bytes som ska in — förhandsgranskningen (`index.html`) läser
samma filer, den kopierar dem inte.

---

## 0. Ordningen

1. Klistra in de tre snippetsen (§1).
2. Importera ACF-fältgruppen (§2).
3. Kör Regenerate Thumbnails en gång (§3).
4. Lägg shortcoden i ett Bricks Shortcode-element.
5. Fyll fälten och kryssa **Signerad**. Först då syns blocket.

## 1. Klistra in

| Fil | FluentSnippets-typ | Placering |
|---|---|---|
| `dist/01-fore-efter.css` | CSS | **Head** |
| `dist/02-fore-efter.php` | PHP | **Frontend & Backend** |
| `dist/03-fore-efter.js` | JS | **Footer** |

Blocket läggs sedan in i Bricks som ett **Shortcode-element** (aldrig ett Code-element):

```
[ampy_fore_efter]
```

Ligger blocket på en sida där ACF-fälten finns behöver shortcoden inga attribut. För en snabb test
utan repeater går ett enstaka par att skicka in som attribut, t.ex.
`[ampy_fore_efter rubrik="Så ser det ut när vi har" rubrik_accent="bytt en elcentral" fore_bild="123" efter_bild="124" omfattning="Från proppskåp till ny central" signerad="1"]`.

## 2. ACF-fälten

**Importera i stället för att bygga för hand:** `acf/ampy-foreefter-falt.json`
→ ACF → Verktyg → Importera fältgrupper. Den innehåller alla fält nedan, med instruktionstexter
för den som fyller i, och är kopplad till sidor. Vill du ha den på fler posttyper ändrar du
platsregeln efter importen.

Blocket visar **två par sida vid sida** på desktop och staplade på mobil. Varje par har sitt eget
reglage.

**På sidan (tre fält):**

| Fält | Typ | Krav | Exempel |
|---|---|---|---|
| `rubrik` | Text | **Ja** | `Så ser det ut när vi har` |
| `rubrik_accent` | Text | nej | `bytt en elcentral` — får understrykningen |
| `tagline` | Text | nej | en rad för hela blocket, unik per tjänst — se nedan |

### Taglinen

Den ersätter bildtexterna per par. **En rad för hela blocket**, placerad under paren, aldrig över:
H2:an är blockets enda öppning, taglinen dess avslutande mening.

Mönstret är semi-globalt — samma form på alla tjänstesidor, egen text per tjänst:

> **Räkna upp vad jobbet faktiskt innehåller. Avsluta med det som binder ihop två olika utgångslägen.**

| Tjänst | Tagline |
|---|---|
| Elcentral | Ny elcentral, jordfelsbrytare och märkta grupper. Samma jobb oavsett hur det såg ut innan. |
| Laddbox | Laddbox på egen grupp, med rätt skydd och en kabeldragning som håller. Samma jobb oavsett hur det såg ut innan. |
| Belysning | Ny armatur, rätt ljus och en installation som går att serva. Samma jobb oavsett hur det såg ut innan. |

Varför den formen: de två paren visar med flit **olika** utgångslägen. Utan en rad som binder ihop
dem ser besökaren två slumpvisa jobb. Med den ser hen ett löfte som gäller oavsett hur illa det ser
ut hemma hos just hen — vilket är den enda fråga en villaägare faktiskt har.

Resten av de 22 tjänsterna skriver ni själva. Håll er till mönstret, och skriv bara sådant som är
sant för varje jobb i den tjänsten — inte för det bästa.

**Repeater `foreefter_par` — en rad per jobb. Max två rader renderas.**

| Underfält | Typ | Krav | Exempel |
|---|---|---|---|
| `fore_bild` | Image | **Ja** | kvadratisk 1:1 |
| `efter_bild` | Image | **Ja** | samma aspekt som `fore_bild` |
| `omfattning` | Text | **Ja** | `Från proppskåp till ny central med jordfelsbrytare` — syns inte längre i blocket, men bär alt-texten och reglagets namn för skärmläsare |
| `jobbtyp` | Text | nej | `Byte av elcentral` — används i alt-texten |
| `fore_alt` / `efter_alt` | Text | nej | egen alt-text; annars byggs den av fälten ovan |
| `signerad` | True/False | **Ja** | montör eller ägare har intygat par + bildtext |

**Grinden per rad:** saknas någon bild, är `omfattning` tom, eller står `signerad` på falskt — då
hoppas raden över. Blir ingen rad kvar renderar shortcoden **ingenting alls**. Det är avsiktligt.
Ett osignerat par är inte ett bevis, det är en risk: MFL 10 § lägger bevisbördan på Ampy, och bilder
som inte visar det de påstår är otillbörlig marknadsföring oavsett hur bra de ser ut.

Finns bara **ett** signerat par renderas det centrerat i stället för halvbrett och ensamt.
Läggs fler än två rader in renderas de två första — layouten är byggd för ett eller två par, och
det står i koden (`$MAX_PAR`) i stället för att tyst kapas.

## 3. Bilderna — så här blir riktiga foton kvadratiska

Snippeten registrerar två hårdbeskurna kvadratiska bildstorlekar:

| Storlek | Mått | Roll |
|---|---|---|
| `ampy-foreefter` | 800 × 800, centrerad hårdbeskärning | den som blocket begär |
| `ampy-foreefter-2x` | 1600 × 1600, centrerad hårdbeskärning | retinaledet i `srcset` |

WordPress beskär alltså åt oss, med **samma kod på båda bilderna** — det är själva poängen. Låter
man webbläsaren beskära i stället kan ett liggande före-foto och ett stående efter-foto få olika
beskärning, och då faller konsistensregeln som hela tilliten vilar på.

**Två storlekar, inte en:** WordPress bygger `srcset` enbart av bilder med samma bildförhållande.
Med bara en kvadrat får en retinaskärm ingen skarpare fil.

**Kör Regenerate Thumbnails en gång** efter att snippeten lagts in. Bilder som redan låg i
mediabiblioteket saknar annars de nya storlekarna, och blocket faller tillbaka på fullstorlek —
det fungerar, men bilden blir onödigt tung och beskärs av webbläsaren i stället.

- Fotoprotokollet måste uppdateras: montören ska fota **med marginal runt om**, så hela centralen
  ryms även efter en centrerad kvadratbeskärning.
- **AVIF 30–80 kB per bild @1200w** (paret 100–200 kB, tak 300 kB). WordPress genererar `srcset`
  själv; blocket sätter `sizes="(max-width: 780px) 100vw, 620px"`.
- `loading="lazy"` är avsiktligt: blocket ligger i beviszonen och ska aldrig vara sidans LCP.
- **Före- och efterbilden måste vara tagna från samma punkt, i samma ljus, med samma beskärning.**
  Det enda som får skilja är jobbet. En lite rå efterbild från samma punkt slår en polerad från en
  annan vinkel — den polerade läses som manipulation även när ingen skett.

## 4. Mätning

Blocket skickar två `dataLayer`-händelser, och bara om sidan har en `dataLayer`:

| Händelse | När |
|---|---|
| `fore_efter_view` | blocket har varit minst 55 % synligt |
| `fore_efter_interact` | besökaren har rört reglaget första gången |

Har du en samtyckesflagga sätter du `window.ampyConsent = { analytics: false }` för den som tackat
nej, så skickas ingenting. Blocket rör aldrig samtycket själv.

De två händelserna finns av ett skäl: hela valet av riktning A vilar på en hypotes om att folk
faktiskt drar i reglaget. Forskningen säger ~1 %. Utan mätning kan vi aldrig avgöra om det stämde.

## 5. Att veta om koden

- **Inga globala regler.** Ingen `html{}`, ingen `body{}`, ingen `*{}`. Allt är scopat till
  `.ampy-foreefter`, och tokens sitter på wrappern, aldrig på `:root`.
- **px, inte rem.** Blocket ärver aldrig temats rotstorlek.
- **`@container`, inte `@media`.** Blocket mäter sin egen bredd, så det beter sig rätt även i en
  smal Bricks-kolumn.
- **Två reglage per block, helt oberoende av varandra.** Flera block på samma sida fungerar också. Laddas en sektion in i efterhand: `window.ampyForeEfter.start()`.
- **Outfit ärvs från temat.** Blocket laddar inget eget typsnitt och ingen extern resurs.
- **Utan JavaScript** staplas paren via `<noscript>` — båda bilderna ligger alltid helt i DOM.
  Se `no-js.html` för hur det ser ut.
- **Container queries med fallback.** Blocket mäter sin egen bredd. Saknar webbläsaren `@container`
  (Safari 15 och äldre) faller det tillbaka på en vanlig viewport-fråga, så en gammal iPhone aldrig
  får två 180 px breda ramar bredvid varandra.
- **Bilderna är `pointer-events: none` och `draggable="false"`.** Utan det startar webbläsaren sin
  egen bilddragning så fort man drar i bilden, och reglaget tappar pekaren mitt i rörelsen.

## 6. Bygg om förhandsgranskningen

Markupen finns på ett enda ställe: mallarna mellan `AMPY-MALL-YTTRE-START`/`-SLUT` och
`AMPY-MALL-PAR-START`/`-SLUT` i `dist/02-fore-efter.php`. Ändrar du den kör du:

```bash
python3 build.py
```

Då skrivs `index.html` och `no-js.html` om ur PHP-filen. Redigera aldrig dem för hand.
