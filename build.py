# -*- coding: utf-8 -*-
"""
Packar förhandsgranskningen ur produktionsfilerna.

Poängen är EN sanning: blockets markup finns bara på ett ställe —
`dist/02-fore-efter.php`. Det här skriptet klipper ut de två mallarna
(yttre skalet och parmallen), fyller dem med demovärden och skriver
`index.html` + `no-js.html`. CSS och JS länkas in ur `dist/`, aldrig kopieras.

Alltså: det som godkänns i förhandsgranskningen är exakt de element och exakt
de bytes Chris klistrar in. Ingen handkonvertering, ingen drift.

Kör:  python3 build.py
"""
import os
import re

ROT = os.path.dirname(os.path.abspath(__file__))
PHP = os.path.join(ROT, "dist", "02-fore-efter.php")

PAR = [
    {
        "id": "ampy-foreefter-1",
        "omfattning": "Från proppskåp till ny central med jordfelsbrytare",
        "fore": ("img/par1-fore.svg", "Byte av elcentral — före"),
        "efter": ("img/par1-efter.svg", "Byte av elcentral — efter, utfört av Ampy"),
    },
    {
        "id": "ampy-foreefter-2",
        "omfattning": "Femton grupper samlade i en central med jordfelsbrytare",
        "fore": ("img/par2-fore.svg", "Byte av elcentral — före"),
        "efter": ("img/par2-efter.svg", "Byte av elcentral — efter, utfört av Ampy"),
    },
]

YTTRE = {
    "{{RUBRIK}}": "Så ser det ut när vi har",
    "{{ACCENT}}": ' <span class="ampy-foreefter__accent">bytt en elcentral</span>',
    "{{RUBRIK_ID}}": "ampy-foreefter-rubrik-2-2",
}

NOTIS = """<p class="mockup-note">
  <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.7 2 4 13.2h5.4L8.1 22 17 10.6h-5.6z"/></svg>
  <span><b>F&ouml;rhandsgranskning.</b> Bilderna &auml;r illustrationer, inte foton fr&aring;n riktiga jobb.
  De ers&auml;tts av original ur bevismappen f&ouml;re publicering.</span>
</p>"""


def klipp(php, start):
    trad = re.search(start + r".*?<<<'HTML'\n(.*?)\nHTML;", php, re.S)
    if not trad:
        raise SystemExit("Hittade inte mallen: " + start)
    return trad.group(1)


def bild(src, alt):
    return (
        '<img class="ampy-foreefter__bild" src="%s" width="1000" height="1000" '
        'loading="lazy" decoding="async" draggable="false" '
        'sizes="(max-width: 780px) 100vw, 620px" alt="%s">' % (src, alt)
    )


def nojs_regler(ids):
    v = ", ".join("#" + i for i in ids)
    return (
        v + " .ampy-foreefter__ram{position:static;aspect-ratio:auto;display:grid;gap:4px;"
        "background:rgba(9,11,50,.09);cursor:auto;touch-action:auto}"
        + v + " .ampy-foreefter__ram::after{display:none}"
        + v + " .ampy-foreefter__lager{position:relative;inset:auto}"
        + v + " .ampy-foreefter__lager>img{height:auto;aspect-ratio:1/1}"
        + v + " .ampy-foreefter__lager--fore{clip-path:none;order:-1}"
        + v + " .ampy-foreefter__somlinje," + v + " .ampy-foreefter__handtag,"
        + v + " .ampy-foreefter__ledtrad," + v + " .ampy-foreefter__reglage{display:none}"
    )


def blocket(php):
    mall_par = klipp(php, "AMPY-MALL-PAR-START")
    mall_yttre = klipp(php, "AMPY-MALL-YTTRE-START")

    par_html = ""
    for p in PAR:
        bit = mall_par
        for nyckel, varde in {
            "{{ID}}": p["id"],
            "{{EFTER_IMG}}": bild(*p["efter"]),
            "{{FORE_IMG}}": bild(*p["fore"]),
            "{{OMFATTNING}}": p["omfattning"],
            "{{OMFATTNING_ATTR}}": p["omfattning"],
        }.items():
            bit = bit.replace(nyckel, varde)
        par_html += bit + "\n"

    ut = mall_yttre
    for nyckel, varde in YTTRE.items():
        ut = ut.replace(nyckel, varde)
    ut = ut.replace("{{NOJS}}", nojs_regler([p["id"] for p in PAR]))
    ut = ut.replace("{{PAR}}", par_html)
    return ut


def sida(titel, block, med_js, nojs_klass=False):
    if nojs_klass:
        block = block.replace(
            'class="ampy-foreefter"', 'class="ampy-foreefter ampy-foreefter--nojs"', 1
        )
    skript = '<script src="dist/03-fore-efter.js" defer></script>' if med_js else ""
    return f"""<!doctype html>
<html lang="sv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{titel}</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/preview.css">
<link rel="stylesheet" href="dist/01-fore-efter.css">
</head>
<body>

{NOTIS}

<!-- Genererad av build.py ur dist/02-fore-efter.php. Redigera INTE här. -->
{block}

{skript}
</body>
</html>
"""


def main():
    php = open(PHP, encoding="utf-8").read()
    block = blocket(php)
    for namn, titel, med_js, nojs in [
        ("index.html", "Före/efter-blocket — Ampy", True, False),
        ("no-js.html", "Före/efter-blocket utan JavaScript — Ampy", False, True),
    ]:
        with open(os.path.join(ROT, namn), "w", encoding="utf-8") as f:
            f.write(sida(titel, block, med_js, nojs))
        print("skrev", namn)


if __name__ == "__main__":
    main()
