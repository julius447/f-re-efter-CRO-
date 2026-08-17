/* ==========================================================================
   AMPY — FÖRE/EFTER-BLOCKET  ·  FluentSnippets: JS  ·  placering: FOOTER
   --------------------------------------------------------------------------
   Reglaget är en enhancement. Utan den här filen står blocket kvar med båda
   bilderna i DOM och sömmen på 35 % — inget innehåll går förlorat, och
   <noscript> i PHP-filen staplar paret för den som kört utan skript.

   Bindande beteende:
   - Vilolaget 35 % (EFTER dominant). Den som aldrig drar ser ändå utfallet.
   - Endast horisontell capture. Vi rör ALDRIG touchmove och kallar aldrig
     preventDefault på den — vertikal scroll och nyp-zoom lever (Juxtapose #148).
   - Tryck var som helst i ramen flyttar sömmen dit.
   - Piltangenter ±5 %, PageUp/PageDown ±10 %, Home/End.
   - En engångsvinkning lär ut mekaniken när blocket kommer in i vyn. Den
     respekterar prefers-reduced-motion och avbryts av minsta egen interaktion.
   - Flera instanser på samma sida fungerar oberoende av varandra.
   ========================================================================== */
(function () {
  "use strict";

  var VILOLAGE = 35;
  var STEG_PIL = 5;
  var STEG_SIDA = 10;

  function initiera(figur) {
    if (figur.dataset.ampyForeefterIgang === "1") return;
    figur.dataset.ampyForeefterIgang = "1";

    var ram = figur.querySelector(".ampy-foreefter__ram");
    var reglage = figur.querySelector(".ampy-foreefter__reglage");
    if (!ram || !reglage) return;

    var drar = false;
    var vidrord = false;
    var vinkningPagar = false;

    /* Mätning. Hela skälet att A valdes framför B är en hypotes om
       interaktion (~1 % enligt forskningen). Utan de här två händelserna
       kan vi aldrig avgöra om reglaget bar sin JS eller inte.
       Tyst om sidan saknar dataLayer, och rör aldrig samtycket själv —
       den som inte har sagt ja till mätning får ingen händelse. */
    function mat(handelse) {
      if (!window.dataLayer || typeof window.dataLayer.push !== "function") return;
      if (window.ampyConsent && window.ampyConsent.analytics === false) return;
      window.dataLayer.push({
        event: handelse,
        block: "fore_efter",
        riktning: "reglaget",
        block_id: figur.id || null
      });
    }

    function satt(procent, egenInteraktion) {
      procent = Math.max(0, Math.min(100, procent));
      var avrundat = Math.round(procent);
      figur.style.setProperty("--ampyfe-pos", procent + "%");
      if (Number(reglage.value) !== avrundat) reglage.value = String(avrundat);
      // Skärmläsaren ska höra vad sömmen betyder, inte ett råtal.
      reglage.setAttribute("aria-valuetext", "Efter syns till " + (100 - avrundat) + " procent");
      if (egenInteraktion && !vidrord) {
        vidrord = true;
        figur.classList.add("ar-vidrord");
        avbrytVinkning();
        mat("fore_efter_interact");
      }
    }

    function mjukt(pa) {
      figur.classList.toggle("ar-mjuk", !!pa);
    }

    function positionFran(handelse) {
      var r = ram.getBoundingClientRect();
      if (!r.width) return VILOLAGE;
      return ((handelse.clientX - r.left) / r.width) * 100;
    }

    /* Bältet till hängslena i CSS: även om något i ramen skulle vara dragbart
       får webbläsaren aldrig starta sin egen drag-and-drop mitt i en jämförelse. */
    ram.addEventListener("dragstart", function (e) { e.preventDefault(); });

    /* --- Pekare: tryck-för-att-placera + drag ---------------------------- */
    ram.addEventListener("pointerdown", function (e) {
      if (e.button !== undefined && e.button !== 0) return;   // bara vänster/primär
      e.preventDefault();                                     // ingen textmarkering, ingen bilddragning
      drar = true;
      if (ram.setPointerCapture) {
        try { ram.setPointerCapture(e.pointerId); } catch (fel) { /* strunt samma */ }
      }
      mjukt(false);                       // under drag ska sömmen sitta i fingret
      satt(positionFran(e), true);
    });

    ram.addEventListener("pointermove", function (e) {
      if (!drar) return;
      // Har knappen släppts utanför ramen är draget över, oavsett vad vi tror.
      if (e.pointerType === "mouse" && e.buttons === 0) { drar = false; return; }
      satt(positionFran(e), true);
    });

    ["pointerup", "pointercancel", "pointerleave"].forEach(function (typ) {
      ram.addEventListener(typ, function () { drar = false; });
    });

    /* --- Reglaget: mus, pekskärm och tangentbord ------------------------- */
    reglage.addEventListener("input", function () {
      mjukt(false);
      satt(Number(reglage.value), true);
    });

    reglage.addEventListener("keydown", function (e) {
      var v = Number(reglage.value);
      var traff = true;
      switch (e.key) {
        case "ArrowLeft":
        case "ArrowDown":  v -= STEG_PIL; break;
        case "ArrowRight":
        case "ArrowUp":    v += STEG_PIL; break;
        case "PageDown":   v -= STEG_SIDA; break;
        case "PageUp":     v += STEG_SIDA; break;
        case "Home":       v = 0; break;
        case "End":        v = 100; break;
        default: traff = false;
      }
      if (!traff) return;
      e.preventDefault();
      mjukt(true);                        // tangentbord får glida, inte hoppa
      satt(v, true);
    });

    /* --- Engångsvinkningen ---------------------------------------------- */
    var vinkningsTimers = [];
    function avbrytVinkning() {
      vinkningsTimers.forEach(clearTimeout);
      vinkningsTimers = [];
      if (vinkningPagar) {
        vinkningPagar = false;
        mjukt(false);
      }
    }

    function vinka() {
      if (vidrord || vinkningPagar) return;
      var stillsam = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      if (stillsam) return;
      vinkningPagar = true;
      mjukt(true);
      satt(VILOLAGE + 13, false);
      vinkningsTimers.push(setTimeout(function () { satt(VILOLAGE, false); }, 460));
      vinkningsTimers.push(setTimeout(function () {
        vinkningPagar = false;
        mjukt(false);
      }, 760));
    }

    if ("IntersectionObserver" in window) {
      var blick = new IntersectionObserver(function (poster) {
        poster.forEach(function (post) {
          if (post.isIntersecting && post.intersectionRatio >= 0.55) {
            blick.disconnect();
            mat("fore_efter_view");
            setTimeout(vinka, 420);
          }
        });
      }, { threshold: [0.55] });
      blick.observe(ram);
    }

    satt(VILOLAGE, false);
  }

  function start() {
    var figurer = document.querySelectorAll("[data-ampy-foreefter]");
    for (var i = 0; i < figurer.length; i++) initiera(figurer[i]);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", start);
  } else {
    start();
  }

  // Laddar Bricks in en sektion i efterhand räcker det att kalla på start().
  window.ampyForeEfter = { start: start };
})();
