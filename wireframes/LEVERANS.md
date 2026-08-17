# Före/efter-blocket — wireframe-fasen, leverans

Datum: 2026-08-16. Uppdrag: `06-FORE-EFTER-BILDER.md` §10 (Definition of Done för wireframe-fasen).
Ingenting här är ritat i Figma eller renderat som bild — allt är körbar HTML på riktiga tokens.

Öppna: `index.html` (visar alla sju artefakter i mobil 390 och desktop 1280 samtidigt).
Lokalt: `python3 -m http.server 8752 -d "06-LEVERANS-FORE-EFTER"` → http://localhost:8752

---

## 1. Vad som byggdes

| Fil | Vad |
|---|---|
| `a-reglaget.html` | Riktning A, reglaget (JS-enhancement ovanpå staplat par) |
| `a-reglaget-nojs.html` | Riktning A i no-JS-läget — identisk markup, utan `.js`-klassen och skriptet |
| `b-bevisparet.html` | Riktning B, jämsides utan JavaScript |
| `c-journalen.html` | Riktning C, stödparen sveps på mobil (GAP-C1 variant 1) |
| `c-journalen-stapel.html` | Riktning C, stödparen staplade på mobil (GAP-C1 variant 2) |
| `index.html` | Översikt: alla ovan i levande ramar, mobil 390 + desktop 1280, plus grindtabell |
| `css/tokens.css` | Produktionstokens, hämtade ur live-CSS:en på ampy.se |
| `css/block.css` | Blockets CSS, gemensam för alla tre riktningarna |
| `js/reglage.js` | Enbart riktning A. 60 rader, ingen beroendekedja |
| `img/*.svg` | 8 platshållare i 4:5 (ett dominant par + tre detaljpar) |

Alla filer är responsiva — samma fil renderar mobil- och desktopläget, brytpunkt 768 respektive 992.

## 2. Definition of Done, punkt för punkt

| Krav (§10) | Läge |
|---|---|
| Tre wireframes (A, B, C) i mobil 390 OCH desktop 1280 | KLART |
| A dessutom i no-JS-läge (fyra artefakter för A) | KLART |
| Klickbar/skrollbar HTML på riktiga tokens, inte statiska bilder | KLART |
| FÖRE/EFTER-chips, bildtextrad och "oretuscherade"-raden i alla tre | KLART |
| Platshållare i 4:5 med realistisk motivbeskrivning, inte AI-foton | KLART — grå schematiska skisser märkta PLATSHÅLLARE |
| Noll knappar/länkar i blocken | KLART — mätt i webbläsaren: `document.querySelectorAll('a,button').length === 0` i alla fem blockfiler |
| En rad per riktning om vilket ägarbeslut som låser den | KLART — står i `index.html` under varje riktning |

## 3. Mätt i webbläsaren (inte uppskattat)

Kört i Chromium på 1280×900 och 390×844, live-server.

| Läge | Blockhöjd | Kommentar |
|---|---|---|
| A, desktop 1280 | 1070 px | ram 880×660 = exakt 4:3, centrerad |
| A, mobil 390 | 773 px | ryms på EN skärm — minst yta av alla tre |
| A utan JS, desktop | 2538 px | två 880×1100-bilder staplade; det är fallbackens ärliga kostnad |
| B, desktop 1280 | 1106 px | två bilder 601×751 jämsides |
| B, mobil 390 | 1129 px = 1,34 skärmar | uppdragets mål var "~1,5 skärm" |
| C, desktop 1280 | 1192 px | dominant kort 864 px + faktakolumn 320 px; stödpar 389 px breda |
| C, mobil svep | 1679 px = 1,99 skärmar | |
| C, mobil stapel | 2259 px = 2,68 skärmar | 580 px mer skroll, samma information |

Funktionstest på reglaget (A), kört i webbläsaren: viloläge 35 % ✓ · piltangent = ±5 % ✓ ·
End → 100 % ✓ · Home → 0 % ✓ · `touch-action: pan-y` på ramen ✓ (vertikal scroll kan aldrig
kapas) · instruktionsetiketten bleknar efter första interaktionen ✓ · no-JS-läget döljer allt
reglagechrome och lämnar båda bilderna hela i DOM ✓.

## 4. Beslut jag tvingades ta under bygget (dina att ompröva)

1. **Chipsens hörn — en konflikt inne i uppdraget.** §4.5 säger "etiketten sitter alltid inne i
   'sin' bild, samma hörn varje gång". §6/B säger FÖRE uppe till vänster, EFTER uppe till höger.
   De går inte ihop. Jag följde kanon (§4) i B och C: **båda chipsen uppe till vänster**. I A är
   det fysiskt omöjligt — bilderna delar ram — så där speglas de. **Din grind.**
2. **Teal H2-accent blev en understrykning, inte teal text.** #00a991 mot vitt ger 2,97:1 och
   klarar inte 3:1 ens för stor text. Accentordet får i stället en tealtonad understrykning.
3. **`--shadow-primary` definieras explicit** i `tokens.css`. I produktion refereras den fem gånger
   men definieras aldrig (§11.4), så komponenter faller tillbaka på rå `#bebebe`. Jag ärvde inte
   den buggen. `--apspace-4xs` (malformad clamp) används inte alls.
4. **Riskraden i C har ingen färgad sidokant.** Första utkastet hade en 3 px teal vänsterkant;
   det är den mest igenkännbara AI-UI-markören, och den serieuppfattas som varningsruta. Nu är
   det en hårfin överlinje och en versal etikett i stället.

## 5. Defekter jag hittade och rättade under verifieringen

- **A låg vänsterjusterad på desktop.** `<figure style="margin:0">` slog ut `margin-inline: auto`.
  All inline-CSS är borttagen ur blockfilerna; marginalerna bor i `block.css`.
- **Instruktionsetiketten klipptes vid sömläge 0 % och 100 %.** Klamrad med
  `clamp(10.5rem, var(--fe-pos), calc(100% - 10.5rem))`.
- **Svep-raden i C snappade bort sitt vänsterindrag vid inladdning** (scrollLeft hoppade till 17 px).
  Löst med `scroll-padding-inline-start`.

## 6. Vad som INTE är gjort (och varför)

- **Inget är byggt för Bricks.** Det här är wireframe-fasen; paste-JSON och FluentSnippets-paketet
  hör till byggfasen efter ditt riktningsval.
- **Copyn är platshållare.** H2:n, bildtexten och faktaraderna är ritade med uppdragets
  platshållartext och `[Ort]`/`[X]`/`[årtal]`-luckor. Orden låses av `ampy-rost` i byggfasen.
- **Fotobiblioteket finns fortfarande inte.** Grind 0 står kvar exakt som i uppdraget: noll
  före/efter-material live på ampy.se, fotoprotokollet inte utskickat. Blocket kan inte skeppas på
  någon sida förrän ett signerat par finns i bevismappen — det gäller alla tre riktningarna lika.
- **Lightbox (GAP-B1) är inte ritad**, per fallbacken AV.

## 7. Rekommendationen står kvar

B som mallens bas nu → C som flaggskeppsuppgradering när biblioteket vuxit → A:s reglage som mätbart
experiment ovanpå B. Bygget bekräftade komponerbarheten i praktiken: B:s kort ÄR C:s dominanta par
(samma `.fe-card` + `.fe-pair`), och A:s reglage ligger ovanpå samma två `<img>` utan ombyggnad.
Det som talar starkast för B efter att ha sett det köra: A:s viloläge visar 0 % av före-bilden i sin
helhet, och på mobil är A:s sömmar så smala (119 px av 340) att före-bilden bara blir en remsa.
