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

# Riktiga foton från Ampys egna jobb, levererade av ägaren 2026-09-17 (fem
# WhatsApp-zippar, EXIF redan borttaget av WhatsApp — ingen GPS). Bearbetade EXAKT
# som WordPress kommer att göra det: 4:3 (telefonens format, ingen beskärning för
# liggande foton), 1200 px + största tillgängliga upp till 2400 utan uppskalning. Ordningen är kvalitetsordning enligt konsistensregeln (samma
# punkt, samma ljus): a och b är starkast, e svagast (före är mitt i rivningen).
# Alt-texterna beskriver bara vad som syns i bild — inget om jobbet hittas på.
ALLA_PAR = {
    "jobb-a": {
        "omfattning": "Byte av proppskåp till ny elcentral",
        "fore_alt":  "Gammalt proppskåp med skruvsäkringar på blå bakskiva, före byte",
        "efter_alt": "Två nya elcentraler med automatsäkringar på samma bakskiva, utfört av Ampy",
    },
    "jobb-b": {
        "omfattning": "Byte av proppskåp till ny elcentral",
        "fore_alt":  "Gammalt proppskåp med skruvsäkringar på orange vägg, före byte",
        "efter_alt": "Två nya elcentraler på samma vägg, utfört av Ampy",
    },
    "jobb-c": {
        "omfattning": "Byte av proppskåp till ny elcentral",
        "fore_alt":  "Gammalt proppskåp med handskrivna gruppmärkningar, före byte",
        "efter_alt": "Nya elcentraler i samma nisch, utfört av Ampy",
    },
    "jobb-d": {
        "omfattning": "Byte av proppskåp till ny elcentral",
        "fore_alt":  "Gammalt proppskåp med kabelrör, före byte",
        "efter_alt": "Nya elcentraler på samma vägg, utfört av Ampy",
    },
    "jobb-e": {
        "omfattning": "Ny elcentral",
        "fore_alt":  "Lösa ledare i väggen efter att det gamla skåpet tagits ner, före ny central",
        "efter_alt": "Ny elcentral med öppen lucka, utfört av Ampy",
    },
}


def par(nyckel, idnr):
    d = ALLA_PAR[nyckel]
    return {
        "id": "ampy-foreefter-%d" % idnr,
        "omfattning": d["omfattning"],
        "fore":  ("img/jobb/%s-fore" % nyckel,  d["fore_alt"]),
        "efter": ("img/jobb/%s-efter" % nyckel, d["efter_alt"]),
    }


# Standardvisningen: de två starkaste paren.
PAR = [par("jobb-a", 1), par("jobb-b", 2)]

YTTRE = {
    "{{RUBRIK}}": "Så ser det ut när vi har",
    "{{ACCENT}}": ' <span class="ampy-foreefter__accent">bytt en elcentral</span>',
    # Semi-global tagline: samma mönster på alla tjänstesidor, egen text per tjänst.
    "{{TAGLINE}}": '\n\t\t<p class="ampy-foreefter__tagline">Ny elcentral, jordfelsbrytare '
                   'och märkta grupper. Samma jobb oavsett hur det såg ut innan.</p>',
}

NOTIS = """<p class="mockup-note">
  <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.7 2 4 13.2h5.4L8.1 22 17 10.6h-5.6z"/></svg>
  <span><b>F&ouml;rhandsgranskning.</b> Riktiga foton fr&aring;n Ampys egna jobb, levererade av
  &auml;garen 2026-09-17. Inte &auml;nnu signerade i bevismapp &mdash; det &auml;r grinden f&ouml;re publicering.</span>
</p>"""


def version(rel):
    """Cache-buster ur filens mtime. Förhandsgranskningen ska ALDRIG kunna visa
    en cachad äldre version av dist-filerna — då granskar man fel bytes."""
    return str(int(os.path.getmtime(os.path.join(ROT, rel))))


def klipp(php, start):
    trad = re.search(start + r".*?<<<'HTML'\n(.*?)\nHTML;", php, re.S)
    if not trad:
        raise SystemExit("Hittade inte mallen: " + start)
    return trad.group(1)


def bild(bas, alt):
    """Speglar exakt vad wp_get_attachment_image(id, 'ampy-foreefter') ger:
    src = 1200×900, srcset = de varianter som faktiskt finns (WordPress skalar
    aldrig upp, så ett 1600 px-foto får bara 1200-varianten), width/height = 1200×900."""
    import glob as _g
    kandidater = []
    for fil in sorted(_g.glob(os.path.join(ROT, bas + "-*.jpg"))):
        w = int(os.path.basename(fil).rsplit("-", 1)[1][:-4])
        kandidater.append("%s-%d.jpg %dw" % (bas, w, w))
    return (
        '<img class="ampy-foreefter__bild" src="%s-1200.jpg" '
        'srcset="%s" '
        'width="1200" height="900" loading="lazy" decoding="async" draggable="false" '
        'sizes="(max-width: 719px) 94vw, 620px" alt="%s">' % (bas, ", ".join(kandidater), alt)
    )


def nojs_regler(ids):
    v = ", ".join("#" + i for i in ids)
    return (
        v + " .ampy-foreefter__ram{position:static;aspect-ratio:auto;display:grid;gap:4px;"
        "background:rgba(9,11,50,.09);cursor:auto;touch-action:auto}"
        + v + " .ampy-foreefter__ram::after{display:none}"
        + v + " .ampy-foreefter__lager{position:relative;inset:auto}"
        + v + " .ampy-foreefter__lager>img{height:auto;aspect-ratio:4/3}"
        + v + " .ampy-foreefter__lager--fore{clip-path:none;order:-1}"
        + v + " .ampy-foreefter__somlinje," + v + " .ampy-foreefter__handtag,"
        + v + " .ampy-foreefter__ledtrad," + v + " .ampy-foreefter__reglage{display:none}"
    )


def blocket(php, par_lista, rubrik_id):
    mall_par = klipp(php, "AMPY-MALL-PAR-START")
    mall_yttre = klipp(php, "AMPY-MALL-YTTRE-START")

    par_html = ""
    for p in par_lista:
        bit = mall_par
        for nyckel, varde in {
            "{{ID}}": p["id"],
            "{{EFTER_IMG}}": bild(*p["efter"]),
            "{{FORE_IMG}}": bild(*p["fore"]),
            "{{OMFATTNING_ATTR}}": p["omfattning"],
        }.items():
            bit = bit.replace(nyckel, varde)
        par_html += bit + "\n"

    ut = mall_yttre
    for nyckel, varde in YTTRE.items():
        ut = ut.replace(nyckel, varde)
    ut = ut.replace("{{RUBRIK_ID}}", rubrik_id)
    ut = ut.replace("{{NOJS}}", nojs_regler([p["id"] for p in par_lista]))
    ut = ut.replace("{{PAR}}", par_html)
    return ut


def sida(titel, block, med_js, nojs_klass=False):
    if nojs_klass:
        block = block.replace(
            'class="ampy-foreefter"', 'class="ampy-foreefter ampy-foreefter--nojs"', 1
        )
    skript = ('<script src="dist/03-fore-efter.js?v=%s" defer></script>' % version("dist/03-fore-efter.js")) if med_js else ""
    vcss = version("dist/01-fore-efter.css")
    vcss_preview = version("css/preview.css")
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
<link rel="stylesheet" href="css/preview.css?v={vcss_preview}">
<link rel="stylesheet" href="dist/01-fore-efter.css?v={vcss}">
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
    block_ab = blocket(php, [par("jobb-a", 1), par("jobb-b", 2)], "ampy-foreefter-rubrik-1")
    block_cd = blocket(php, [par("jobb-c", 3), par("jobb-d", 4)], "ampy-foreefter-rubrik-2")
    block_e  = blocket(php, [par("jobb-e", 5)],                    "ampy-foreefter-rubrik-3")
    sidor = [
        ("index.html", "Före/efter-blocket — Ampy", block_ab, True, False),
        ("no-js.html", "Före/efter-blocket utan JavaScript — Ampy", block_ab, False, True),
        # Alla fem jobben, som tre block efter varandra — så ägaren ser varje par
        # i verktyget och kan välja vilka som ska stå på vilken sida.
        ("alla.html", "Alla fem jobben — före/efter-blocket", block_ab + "\n" + block_cd + "\n" + block_e, True, False),
    ]
    for namn, titel, block, med_js, nojs in sidor:
        with open(os.path.join(ROT, namn), "w", encoding="utf-8") as f:
            f.write(sida(titel, block, med_js, nojs))
        print("skrev", namn)


if __name__ == "__main__":
    main()
