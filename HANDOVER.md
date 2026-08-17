# Handover — före/efter-blocket till WordPress

Till dev (Chris). Tre filer klistras in i FluentSnippets, ingenting hand­konverteras.
Det som ligger i `dist/` är exakt de bytes som ska in — förhandsgranskningen (`index.html`) läser
samma filer, den kopierar dem inte.

---

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

Ligger blocket på en sida där ACF-fälten finns behöver shortcoden inga attribut. Vill du testa med
egna värden går alla fält att skicka in som attribut, t.ex.
`[ampy_fore_efter omrade="Villa i Huddinge"]`.

## 2. ACF-fälten

Fältgruppen kopplas till de sidor blocket ska kunna ligga på.

| Fält | Typ | Krav | Exempel |
|---|---|---|---|
| `fore_bild` | Image | **Ja** | stående 4:5 |
| `efter_bild` | Image | **Ja** | samma aspekt som `fore_bild` |
| `rubrik` | Text | **Ja** | `Så ser det ut när vi har` |
| `rubrik_accent` | Text | **Ja** | `bytt en elcentral` — får understrykningen |
| `omfattning` | Text | **Ja** | `Från proppskåp till ny central med jordfelsbrytare` |
| `omrade` | Text | nej | `Villa i Huddinge` — utelämnas hellre än hittas på |
| `jobbtyp` | Text | nej | `Byte av elcentral` — används i alt-texten |
| `fore_alt` / `efter_alt` | Text | nej | egen alt-text; annars byggs den av fälten ovan |
| `signerad` | True/False | **Ja** | montör eller ägare har intygat par + bildtext |

**Grinden:** saknas någon bild, är `rubrik` eller `omfattning` tom, eller står `signerad` på falskt —
då renderar shortcoden **ingenting alls**. Det är avsiktligt. Ett osignerat par är inte ett bevis,
det är en risk: MFL 10 § lägger bevisbördan på Ampy, och bilder som inte visar det de påstår är
otillbörlig marknadsföring oavsett hur bra de ser ut.

## 3. Bilderna

- Stående **4:5**, samma aspekt på båda. Olika aspekt ger både layouthopp och en trovärdighetsläcka.
- **AVIF 30–80 kB per bild @1200w** (paret 100–200 kB, tak 300 kB). WordPress genererar `srcset`
  själv; blocket sätter `sizes="(max-width: 700px) 100vw, 660px"`.
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
- **Flera instanser på samma sida fungerar.** Laddas en sektion in i efterhand: `window.ampyForeEfter.start()`.
- **Outfit ärvs från temat.** Blocket laddar inget eget typsnitt och ingen extern resurs.
- **Utan JavaScript** staplas paret via `<noscript>` — båda bilderna ligger alltid helt i DOM.
  Se `no-js.html` för hur det ser ut.

## 6. Bygg om förhandsgranskningen

Markupen finns på ett enda ställe: mallen mellan `AMPY-MALL-START` och `AMPY-MALL-SLUT` i
`dist/02-fore-efter.php`. Ändrar du den kör du:

```bash
python3 build.py
```

Då skrivs `index.html` och `no-js.html` om ur PHP-filen. Redigera aldrig dem för hand.
