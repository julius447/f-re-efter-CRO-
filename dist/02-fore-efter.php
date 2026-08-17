<?php
/**
 * AMPY — FÖRE/EFTER-BLOCKET  ·  FluentSnippets: PHP  ·  körs: Frontend & Backend
 * ---------------------------------------------------------------------------
 * Riktning A, "Reglaget". Shortcode: [ampy_fore_efter]
 *
 * Läggs in i en Bricks SHORTCODE-element (aldrig ett Code-element).
 * Variationen bor i ACF — mallen ändras aldrig per sida.
 *
 * ACF-fält som läses (alla kan överstyras som shortcode-attribut):
 *   fore_bild      image   OBLIGATORISK   stående 4:5
 *   efter_bild     image   OBLIGATORISK   samma aspekt som fore_bild
 *   rubrik         text    OBLIGATORISK   "Så ser det ut när vi har"
 *   rubrik_accent  text    OBLIGATORISK   "bytt en elcentral"  (får understrykningen)
 *   omfattning     text    OBLIGATORISK   "Från proppskåp till ny central med jordfelsbrytare"
 *   omrade         text    frivillig      "Villa i Huddinge" — utelämnas hellre än hittas på
 *   jobbtyp        text    frivillig      "Byte av elcentral" — används i alt-fallback
 *   fore_alt       text    frivillig      egen alt-text
 *   efter_alt      text    frivillig      egen alt-text
 *   signerad       true/false OBLIGATORISK montör/ägare har intygat par + bildtext
 *
 * GRINDEN: är signerad falsk renderas INGENTING. Ett osignerat par får inte nå
 * en besökare — MFL 10 § lägger bevisbördan på Ampy, och ett par utan bevismapp
 * är inte ett bevis utan en risk.
 */

if (!defined('ABSPATH')) { exit; }

add_shortcode('ampy_fore_efter', function ($atts) {

	$a = shortcode_atts(array(
		'fore_bild'     => '',
		'efter_bild'    => '',
		'rubrik'        => '',
		'rubrik_accent' => '',
		'omfattning'    => '',
		'omrade'        => '',
		'jobbtyp'       => '',
		'fore_alt'      => '',
		'efter_alt'     => '',
		'signerad'      => '',
	), $atts, 'ampy_fore_efter');

	// Attribut vinner; annars ACF-fältet på den aktuella posten.
	$f = function ($nyckel) use ($a) {
		if ($a[$nyckel] !== '') { return $a[$nyckel]; }
		return function_exists('get_field') ? get_field($nyckel) : '';
	};

	$fore_bild  = $f('fore_bild');
	$efter_bild = $f('efter_bild');
	$signerad   = $f('signerad');

	// ACF-bildfält kan komma som array, som ID eller som URL.
	$bild_id = function ($varde) {
		if (is_array($varde) && isset($varde['ID'])) { return (int) $varde['ID']; }
		if (is_numeric($varde)) { return (int) $varde; }
		return 0;
	};
	$fore_id  = $bild_id($fore_bild);
	$efter_id = $bild_id($efter_bild);

	// Tre grindar, alla lika hårda: paret måste finnas HELT, och vara signerat.
	if (!$fore_id || !$efter_id) { return ''; }
	if (!$signerad || $signerad === 'false' || $signerad === '0') { return ''; }

	$rubrik     = trim((string) $f('rubrik'));
	$accent     = trim((string) $f('rubrik_accent'));
	$omfattning = trim((string) $f('omfattning'));
	$omrade     = trim((string) $f('omrade'));
	$jobbtyp    = trim((string) $f('jobbtyp'));

	if ($rubrik === '' || $omfattning === '') { return ''; }

	// Alt-text: eget fält först, annars ett mönster byggt på riktiga fältvärden.
	$fore_alt  = trim((string) $f('fore_alt'));
	$efter_alt = trim((string) $f('efter_alt'));
	// Bilderna BÄR hela budskapet. En skärmläsaranvändare som får "före" och
	// inget mer får ingenting alls, så fallbacken lutar sig mot omfattning —
	// det enda textfältet som är obligatoriskt.
	$sak = $jobbtyp !== '' ? $jobbtyp : $omfattning;
	$plats = $omrade !== '' ? ', ' . $omrade : '';
	if ($fore_alt === '') {
		$fore_alt = $sak . ' — före' . $plats;
	}
	if ($efter_alt === '') {
		$efter_alt = $sak . ' — efter, utfört av Ampy' . $plats;
	}

	// Flera instanser på samma sida måste kunna leva sida vid sida.
	static $rakning = 0;
	$rakning++;
	$id = 'ampy-foreefter-' . $rakning;

	$bild_attr = array(
		'class'    => 'ampy-foreefter__bild',
		'loading'  => 'lazy',   // blocket ligger i beviszonen, aldrig som LCP
		'decoding' => 'async',
		'sizes'    => '(max-width: 700px) 100vw, 660px',
	);

	$fore_img  = wp_get_attachment_image($fore_id, 'large', false,
		array_merge($bild_attr, array('alt' => $fore_alt)));
	$efter_img = wp_get_attachment_image($efter_id, 'large', false,
		array_merge($bild_attr, array('alt' => $efter_alt)));

	if (!$fore_img || !$efter_img) { return ''; }

	$plats_rad = $omrade !== ''
		? '<span class="ampy-foreefter__plats">' . esc_html($omrade) . '</span>'
		: '';

	$accent_del = $accent !== ''
		? ' <span class="ampy-foreefter__accent">' . esc_html($accent) . '</span>'
		: '';

	/* AMPY-MALL-START — allt mellan markörerna är blockets markup.
	   preview/index.html byggs ur exakt den här strängen (build.py), så det som
	   godkänns i förhandsgranskningen är samma element som WordPress skickar ut. */
	$mall = <<<'HTML'
<section class="ampy-foreefter" aria-labelledby="{{ID}}-rubrik">
	<noscript>
		<style>#{{ID}} .ampy-foreefter__ram{position:static;aspect-ratio:auto;display:grid;gap:4px;background:rgba(9,11,50,.09);cursor:auto;touch-action:auto}#{{ID}} .ampy-foreefter__ram::before,#{{ID}} .ampy-foreefter__ram::after{display:none}#{{ID}} .ampy-foreefter__lager{position:relative;inset:auto}#{{ID}} .ampy-foreefter__lager>img{height:auto;aspect-ratio:4/5}#{{ID}} .ampy-foreefter__lager--fore{clip-path:none;order:-1}#{{ID}} .ampy-foreefter__somlinje,#{{ID}} .ampy-foreefter__handtag,#{{ID}} .ampy-foreefter__ledtrad,#{{ID}} .ampy-foreefter__reglage{display:none}</style>
	</noscript>

	<div class="ampy-foreefter__inner">

		<h2 class="ampy-foreefter__rubrik" id="{{ID}}-rubrik">{{RUBRIK}}{{ACCENT}}</h2>

		<figure class="ampy-foreefter__figur" id="{{ID}}" data-ampy-foreefter>
			<div class="ampy-foreefter__ram">

				<!-- EFTER är basskiktet och ligger alltid helt i DOM -->
				<div class="ampy-foreefter__lager ampy-foreefter__lager--efter">
					{{EFTER_IMG}}
					<span class="ampy-foreefter__chip ampy-foreefter__chip--efter">
						<svg class="ampy-foreefter__blixt" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12.7 2 4 13.2h5.4L8.1 22 17 10.6h-5.6z"/></svg>
						Efter
					</span>
				</div>

				<!-- FÖRE ligger ovanpå och klipps vid sömmen. Chippet klipps med. -->
				<div class="ampy-foreefter__lager ampy-foreefter__lager--fore">
					{{FORE_IMG}}
					<span class="ampy-foreefter__chip ampy-foreefter__chip--fore">Före</span>
				</div>

				<div class="ampy-foreefter__somlinje" aria-hidden="true"></div>
				<div class="ampy-foreefter__handtag" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M9 6l-6 6 6 6M15 6l6 6-6 6"/></svg>
				</div>
				<p class="ampy-foreefter__ledtrad" aria-hidden="true">Dra för att jämföra</p>
			</div>

			<input class="ampy-foreefter__reglage" type="range" min="0" max="100" step="1" value="35"
			       aria-label="Jämför före och efter" aria-describedby="{{ID}}-hjalp"
			       aria-valuetext="Efter syns till 65 procent">
			<p class="ampy-foreefter__sr" id="{{ID}}-hjalp">Dra reglaget för att jämföra före och efter. Du kan också trycka var som helst i bilden, eller använda piltangenterna.</p>

			<figcaption class="ampy-foreefter__bildtext">
				<span class="ampy-foreefter__omfattning">{{OMFATTNING}}</span>
				{{PLATS}}
			</figcaption>
		</figure>

	</div>
</section>
HTML;
	/* AMPY-MALL-SLUT */

	return strtr($mall, array(
		'{{ID}}'         => esc_attr($id),
		'{{RUBRIK}}'     => esc_html($rubrik),
		'{{ACCENT}}'     => $accent_del,
		'{{EFTER_IMG}}'  => $efter_img,
		'{{FORE_IMG}}'   => $fore_img,
		'{{OMFATTNING}}' => esc_html($omfattning),
		'{{PLATS}}'      => $plats_rad,
	));
});
