# -*- coding: utf-8 -*-
"""
Packar förhandsgranskningen ur produktionsfilerna.

Poängen är EN sanning: blockets markup finns bara på ett ställe —
`dist/02-fore-efter.php`. Det här skriptet klipper ut mallen mellan
AMPY-MALL-START och AMPY-MALL-SLUT, fyller den med demovärden och skriver
`index.html` + `no-js.html`. CSS och JS länkas in ur `dist/`, aldrig kopieras.

Alltså: det som godkänns i förhandsgranskningen är exakt de element och exakt
de bytes Chris klistrar in. Ingen handkonvertering, ingen drift.

Kör:  python3 build.py
"""
import os
import re

ROT = os.path.dirname(os.path.abspath(__file__))
PHP = os.path.join(ROT, "dist", "02-fore-efter.php")

DEMO = {
    "{{ID}}": "ampy-foreefter-1",
    "{{RUBRIK}}": "Så ser det ut när vi har",
    "{{ACCENT}}": ' <span class="ampy-foreefter__accent">bytt en elcentral</span>',
    "{{OMFATTNING}}": "Från proppskåp till ny central med jordfelsbrytare",
    "{{PLATS}}": '<span class="ampy-foreefter__plats">Villa i [Ort]</span>',
    "{{FORE_IMG}}": (
        '<img class="ampy-foreefter__bild" src="img/dominant-fore.svg" '
        'width="800" height="1000" loading="lazy" decoding="async" '
        'sizes="(max-width: 700px) 100vw, 660px" '
        'alt="Byte av elcentral — före, villa i [Ort]">'
    ),
    "{{EFTER_IMG}}": (
        '<img class="ampy-foreefter__bild" src="img/dominant-efter.svg" '
        'width="800" height="1000" loading="lazy" decoding="async" '
        'sizes="(max-width: 700px) 100vw, 660px" '
        'alt="Byte av elcentral — efter, utfört av Ampy, villa i [Ort]">'
    ),
}

NOTIS = """<p class="mockup-note">
  <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.7 2 4 13.2h5.4L8.1 22 17 10.6h-5.6z"/></svg>
  <span><b>Förhandsgranskning.</b> Bilderna är illustrationer, inte foton fr&aring;n riktiga jobb.
  De ers&auml;tts av original ur bevismappen f&ouml;re publicering.</span>
</p>"""


def mall():
    php = open(PHP, encoding="utf-8").read()
    trad = re.search(r"AMPY-MALL-START.*?<<<'HTML'\n(.*?)\nHTML;", php, re.S)
    if not trad:
        raise SystemExit("Hittade inte mallen mellan markörerna i 02-fore-efter.php")
    ut = trad.group(1)
    for nyckel, varde in DEMO.items():
        ut = ut.replace(nyckel, varde)
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
    block = mall()
    filer = [
        ("index.html", "Före/efter-blocket — Ampy", True, False),
        ("no-js.html", "Före/efter-blocket utan JavaScript — Ampy", False, True),
    ]
    for namn, titel, med_js, nojs in filer:
        with open(os.path.join(ROT, namn), "w", encoding="utf-8") as f:
            f.write(sida(titel, block, med_js, nojs))
        print("skrev", namn)


if __name__ == "__main__":
    main()
