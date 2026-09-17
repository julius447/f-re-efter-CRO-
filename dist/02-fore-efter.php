<?php
/**
 * AMPY — FÖRE/EFTER-BLOCKET  ·  FluentSnippets: PHP  ·  körs: Frontend & Backend
 * ---------------------------------------------------------------------------
 * Riktning A, "Reglaget". Shortcode: [ampy_fore_efter]
 *
 * Blocket visar TVÅ par sida vid sida på desktop, staplade på mobil.
 * Varje par har sitt eget reglage. Läggs in i ett Bricks SHORTCODE-element
 * (aldrig ett Code-element). Variationen bor i ACF — mallen ändras aldrig.
 *
 * BLOCKFÄLT (på sidan):
 *   rubrik         text  OBLIGATORISK  "Så ser det ut när vi har"
 *   rubrik_accent  text  frivillig     "bytt en elcentral"  (får understrykningen)
 *   tagline        text  frivillig     EN rad för hela blocket, unik per tjänst.
 *                                      Mönster: räkna upp vad jobbet faktiskt
 *                                      innehåller, avsluta med det som binder
 *                                      ihop två olika utgångslägen. Ex:
 *                                      "Ny elcentral, jordfelsbrytare och märkta
 *                                      grupper. Samma jobb oavsett hur det såg
 *                                      ut innan." Ersätter de tidigare
 *                                      bildtexterna per par (ägarbeslut 2026-08-17).
 *
 * REPEATER `foreefter_par` — en rad per jobb, max 2 renderas:
 *   fore_bild   image      OBLIGATORISK  liggande 4:3 (telefonens standard)
 *   efter_bild  image      OBLIGATORISK  samma format som fore_bild
 *   omfattning  text       OBLIGATORISK  "Från proppskåp till ny central med jordfelsbrytare"
 *                                     Syns INTE längre i blocket, men bär alt-texten
 *                                     och reglagets namn för skärmläsare — så den
 *                                     är fortfarande obligatorisk.
 *   jobbtyp     text       frivillig     "Byte av elcentral" — används i alt-texten
 *   fore_alt    text       frivillig     egen alt-text
 *   efter_alt   text       frivillig     egen alt-text
 *   signerad    true/false OBLIGATORISK  montör eller ägare har intygat par + bildtext
 *
 * GRINDEN: en rad som saknar bild, omfattning eller signering renderas INTE.
 * Blir ingen rad kvar returnerar shortcoden tomt. Ett osignerat par får inte nå
 * en besökare — MFL 10 § lägger bevisbördan på Ampy, och ett par utan bevismapp
 * är inte ett bevis utan en risk.
 */

if (!defined('ABSPATH')) { exit; }

/**
 * BILDSTORLEKAR I 4:3 — förutsättningen för att riktiga foton ska funka.
 *
 * Ramen är 4:3, samma som telefonkameran. Ett liggande foto passar då exakt
 * utan att en pixel beskärs — det är hela poängen: ingen beskärning betyder
 * ingen beskärningsskillnad mellan före och efter, och konsistensregeln (samma
 * punkt, samma ljus) håller av sig själv. Laddas ett foto ändå upp i annat
 * format hårdbeskär WordPress det centrerat till 4:3, med samma kod på båda
 * bilderna. WordPress skalar aldrig upp: ett 1600 px-foto får bara 1200-
 * varianten, ett 2048 px-foto får båda.
 *
 * Två storlekar, inte en: WordPress bygger srcset enbart av bilder med SAMMA
 * bildförhållande. Med bara en kvadrat får en retinaskärm ingen skarpare fil.
 *
 * Fotoprotokollet: fota liggande, centralen mitt i bild, med luft runt om.
 */
$ampy_foreefter_bildstorlekar = function () {
	add_image_size('ampy-foreefter', 1200, 900, array('center', 'center'));
	add_image_size('ampy-foreefter-2x', 2400, 1800, array('center', 'center'));
};
// Snippet-laddare kör vid olika tidpunkter. Har init redan passerat registrerar
// vi direkt; annars väntar vi in det. Båda vägarna ger samma resultat.
if (did_action('init')) {
	$ampy_foreefter_bildstorlekar();
} else {
	add_action('init', $ampy_foreefter_bildstorlekar);
}

add_shortcode('ampy_fore_efter', function ($atts) {

	// Layouten är byggd för ett eller två par. Fler renderas inte — och det
	// sägs här i klartext i stället för att tyst kapas.
	$MAX_PAR = 2;

	$a = shortcode_atts(array(
		'rubrik'        => '',
		'rubrik_accent' => '',
		'tagline'       => '',
		// Enstaka par utan repeater (t.ex. för en snabb test i Bricks):
		'fore_bild'     => '',
		'efter_bild'    => '',
		'omfattning'    => '',
		'jobbtyp'       => '',
		'fore_alt'      => '',
		'efter_alt'     => '',
		'signerad'      => '',
	), $atts, 'ampy_fore_efter');

	$falt = function ($nyckel) use ($a) {
		if (isset($a[$nyckel]) && $a[$nyckel] !== '') { return $a[$nyckel]; }
		return function_exists('get_field') ? get_field($nyckel) : '';
	};

	$rubrik  = trim((string) $falt('rubrik'));
	$accent  = trim((string) $falt('rubrik_accent'));
	$tagline = trim((string) $falt('tagline'));
	if ($rubrik === '') { return ''; }

	// Raderna: repeatern först, annars det enstaka paret från attributen.
	$rader = function_exists('get_field') ? get_field('foreefter_par') : null;
	if (!is_array($rader) || !$rader) {
		$rader = array(array(
			'fore_bild'  => $a['fore_bild'],
			'efter_bild' => $a['efter_bild'],
			'omfattning' => $a['omfattning'],
			'jobbtyp'    => $a['jobbtyp'],
			'fore_alt'   => $a['fore_alt'],
			'efter_alt'  => $a['efter_alt'],
			'signerad'   => $a['signerad'],
		));
	}

	/* ACF:s bildfält kan returnera array, ID eller URL beroende på hur fältet
	   är inställt. Alla tre ska funka — annars renderas blocket tomt och ingen
	   förstår varför. */
	$bild_id = function ($varde) {
		if (is_array($varde) && isset($varde['ID']))    { return (int) $varde['ID']; }
		if (is_array($varde) && isset($varde['id']))    { return (int) $varde['id']; }
		if (is_numeric($varde))                          { return (int) $varde; }
		if (is_string($varde) && $varde !== '' && function_exists('attachment_url_to_postid')) {
			return (int) attachment_url_to_postid($varde);
		}
		return 0;
	};
	$sant = function ($varde) {
		return !(!$varde || $varde === 'false' || $varde === '0');
	};

	static $rakning = 0;

	$bild_attr = array(
		'class'      => 'ampy-foreefter__bild',
		'loading'    => 'lazy',   // blocket ligger i beviszonen, aldrig som LCP
		'decoding'   => 'async',
		'draggable'  => 'false',  // annars startar webbläsaren sin egen bilddragning
		'sizes'      => '(max-width: 719px) 94vw, 620px',
	);

	/* AMPY-MALL-PAR-START */
	$mall_par = <<<'HTML'
			<figure class="ampy-foreefter__figur" id="{{ID}}" data-ampy-foreefter>
				<div class="ampy-foreefter__ram">

					<!-- FÖRE står först i DOM så skärmläsaren hör före → efter. Den
					     ritas ändå överst (z-index i CSS) och klipps vid sömmen; chippet
					     klipps med. Chipsen är aria-hidden: reglaget bär redan namnet,
					     annars läses "Före" och "Efter" upp som två lösa ord. -->
					<div class="ampy-foreefter__lager ampy-foreefter__lager--fore">
						{{FORE_IMG}}
						<span class="ampy-foreefter__chip ampy-foreefter__chip--fore" aria-hidden="true">Före</span>
					</div>

					<!-- EFTER är basskiktet och ligger alltid helt i DOM -->
					<div class="ampy-foreefter__lager ampy-foreefter__lager--efter">
						{{EFTER_IMG}}
					</div>

					<!-- EFTER-chippet klipps spegelvänt: det finns bara till höger om
					     sömmen, precis som FÖRE-chippet bara finns till vänster. Drar
					     man hela vägen åt ena hållet försvinner motsvarande sida helt. -->
					<div class="ampy-foreefter__chiplager">
						<span class="ampy-foreefter__chip ampy-foreefter__chip--efter" aria-hidden="true">Efter</span>
					</div>

					<div class="ampy-foreefter__somlinje" aria-hidden="true"></div>
					<div class="ampy-foreefter__handtag" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M9 6l-6 6 6 6M15 6l6 6-6 6"/></svg>
					</div>
					<p class="ampy-foreefter__ledtrad" aria-hidden="true">Dra för att jämföra</p>
				</div>

				<input class="ampy-foreefter__reglage" type="range" min="0" max="100" step="1" value="50"
				       aria-label="Jämför före och efter: {{OMFATTNING_ATTR}}" aria-describedby="{{ID}}-hjalp"
				       aria-valuetext="Efter syns till 50 procent">
				<p class="ampy-foreefter__sr" id="{{ID}}-hjalp">Dra reglaget för att jämföra före och efter. Du kan också trycka var som helst i bilden, eller använda piltangenterna.</p>
			</figure>
HTML;
	/* AMPY-MALL-PAR-SLUT */

	$par_html = '';
	$par_ids = array();
	$schema_bilder = array();

	foreach ($rader as $rad) {
		if (count($par_ids) >= $MAX_PAR) { break; }

		$fore_id  = $bild_id(isset($rad['fore_bild']) ? $rad['fore_bild'] : '');
		$efter_id = $bild_id(isset($rad['efter_bild']) ? $rad['efter_bild'] : '');
		$omfattning = trim((string) (isset($rad['omfattning']) ? $rad['omfattning'] : ''));

		// Tre grindar per rad, alla lika hårda.
		if (!$fore_id || !$efter_id) { continue; }
		if ($omfattning === '') { continue; }
		if (!$sant(isset($rad['signerad']) ? $rad['signerad'] : '')) { continue; }

		$jobbtyp   = trim((string) (isset($rad['jobbtyp']) ? $rad['jobbtyp'] : ''));
		$fore_alt  = trim((string) (isset($rad['fore_alt']) ? $rad['fore_alt'] : ''));
		$efter_alt = trim((string) (isset($rad['efter_alt']) ? $rad['efter_alt'] : ''));

		// Bilderna BÄR hela budskapet. Alt-texten hämtas i tre steg: ACF-fältet
		// på raden, annars den alt-text bilden redan har i mediabiblioteket
		// (importskriptet sätter den från manifest.csv), och först i sista hand
		// en generisk rad byggd på omfattning — det enda obligatoriska textfältet.
		if ($fore_alt === '')  { $fore_alt  = trim((string) get_post_meta($fore_id,  '_wp_attachment_image_alt', true)); }
		if ($efter_alt === '') { $efter_alt = trim((string) get_post_meta($efter_id, '_wp_attachment_image_alt', true)); }
		$sak = $jobbtyp !== '' ? $jobbtyp : $omfattning;
		if ($fore_alt === '')  { $fore_alt  = $sak . ' — före'; }
		if ($efter_alt === '') { $efter_alt = $sak . ' — efter, utfört av Ampy'; }

		/* 'ampy-foreefter' är 4:3-varianten (1200×900). Finns den inte ännu
		   (bilder uppladdade före snippeten) faller WordPress tillbaka på
		   fullstorlek och webbläsaren beskär i stället — sämre, men aldrig
		   trasigt. Kör Regenerate Thumbnails en gång så är det borta. */
		$fore_img = wp_get_attachment_image($fore_id, 'ampy-foreefter', false,
			array_merge($bild_attr, array('alt' => $fore_alt)));
		$efter_img = wp_get_attachment_image($efter_id, 'ampy-foreefter', false,
			array_merge($bild_attr, array('alt' => $efter_alt)));
		if (!$fore_img || !$efter_img) { continue; }

		$rakning++;
		$id = 'ampy-foreefter-' . $rakning;
		$par_ids[] = $id;

		// Strukturerad data för bildsök: ett ImageObject per bild, med samma
		// alt-text som bilden bär. Fullstorleksfilen är contentUrl.
		foreach (array(array($fore_id, $fore_alt), array($efter_id, $efter_alt)) as $b) {
			$src = wp_get_attachment_image_src($b[0], 'full');
			if (!$src) { continue; }
			$schema_bilder[] = array(
				'@type'       => 'ImageObject',
				'contentUrl'  => $src[0],
				'width'       => (int) $src[1],
				'height'      => (int) $src[2],
				'name'        => $b[1],
				'description' => $omfattning,
			);
		}

		$par_html .= strtr($mall_par, array(
			'{{ID}}'               => esc_attr($id),
			'{{EFTER_IMG}}'        => $efter_img,
			'{{FORE_IMG}}'         => $fore_img,
			'{{OMFATTNING_ATTR}}'  => esc_attr($omfattning),
		)) . "\n";
	}

	if ($par_html === '') { return ''; }

	// <noscript> måste peka på varje figur som faktiskt renderades. Varje
	// selektor byggs PER figur: "#a .x, #b .x" — inte "#a, #b .x", som betyder
	// "hela #a" och gömde första paret helt (granskning 2026-09-17).
	$sel = function ($klass) use ($par_ids) {
		$ut = array();
		foreach ($par_ids as $pid) { $ut[] = '#' . $pid . ' ' . $klass; }
		return implode(',', $ut);
	};
	$nojs = $sel('.ampy-foreefter__ram') . '{position:static;aspect-ratio:auto;display:grid;gap:4px;background:rgba(9,11,50,.09);cursor:auto;touch-action:auto}'
		. $sel('.ampy-foreefter__ram::after') . '{display:none}'
		. $sel('.ampy-foreefter__lager') . '{position:relative;inset:auto}'
		. $sel('.ampy-foreefter__lager>img') . '{height:auto;aspect-ratio:4/3}'
		. $sel('.ampy-foreefter__lager--fore') . '{clip-path:none;grid-area:1/1}'
		. $sel('.ampy-foreefter__lager--efter') . '{grid-area:2/1}'
		. $sel('.ampy-foreefter__chiplager') . '{grid-area:2/1;position:relative;inset:auto;clip-path:none}'
		. $sel('.ampy-foreefter__somlinje') . ',' . $sel('.ampy-foreefter__handtag') . ','
		. $sel('.ampy-foreefter__ledtrad') . ',' . $sel('.ampy-foreefter__reglage') . '{display:none}';

	$accent_del = $accent !== ''
		? ' <span class="ampy-foreefter__accent">' . esc_html($accent) . '</span>'
		: '';

	// Taglinen står EFTER paren, aldrig före. Den är en avslutande rad, inte en
	// ingress — H2:an ska förbli blockets enda öppning (kanon §4.2).
	$tagline_rad = $tagline !== ''
		? "\n\t\t<p class=\"ampy-foreefter__tagline\">" . esc_html($tagline) . "</p>"
		: '';

	$rubrik_id = 'ampy-foreefter-rubrik-' . count($par_ids) . '-' . $rakning;

	$schema_html = '';
	if ($schema_bilder) {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'ImageGallery',
			'name'     => trim($rubrik . ' ' . $accent),
			'image'    => $schema_bilder,
		);
		$schema_html = "\n\t<script type=\"application/ld+json\">"
			. wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
			. '</script>';
	}

	/* AMPY-MALL-YTTRE-START — index.html byggs ur exakt den här strängen
	   (build.py), så det som godkänns i förhandsgranskningen är samma element
	   som WordPress skickar ut. */
	$mall_yttre = <<<'HTML'
<section class="ampy-foreefter" aria-labelledby="{{RUBRIK_ID}}">
	<noscript><style>{{NOJS}}</style></noscript>

	<div class="ampy-foreefter__inner">

		<h2 class="ampy-foreefter__rubrik" id="{{RUBRIK_ID}}">{{RUBRIK}}{{ACCENT}}</h2>

		<div class="ampy-foreefter__par">
{{PAR}}		</div>{{TAGLINE}}

	</div>{{SCHEMA}}
</section>
HTML;
	/* AMPY-MALL-YTTRE-SLUT */

	return strtr($mall_yttre, array(
		'{{RUBRIK_ID}}' => esc_attr($rubrik_id),
		'{{NOJS}}'      => $nojs,
		'{{RUBRIK}}'    => esc_html($rubrik),
		'{{ACCENT}}'    => $accent_del,
		'{{PAR}}'       => $par_html,
		'{{TAGLINE}}'   => $tagline_rad,
		'{{SCHEMA}}'    => $schema_html,
	));
});
