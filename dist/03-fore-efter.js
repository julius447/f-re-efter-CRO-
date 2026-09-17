/* ==========================================================================
   AMPY — FÖRE/EFTER-BLOCKET  ·  FluentSnippets: JS  ·  placering: FOOTER
   --------------------------------------------------------------------------
   Reglaget är en enhancement. Utan den här filen står blocket kvar med båda
   bilderna i DOM och sömmen på 50 % — inget innehåll går förlorat, och
   <noscript> i PHP-filen staplar paret för den som kört utan skript.

   Bindande beteende:
   - Vilolaget 50 %: sömmen mitt i bilden, lika mycket före som efter.
   - Endast horisontell capture. Vi rör ALDRIG touchmove och kallar aldrig
     preventDefault på den — vertikal scroll och nyp-zoom lever (Juxtapose #148).
   - Tryck var som helst i ramen flyttar sömmen dit.
   - Piltangenter ±5 %, PageUp/PageDown ±10 %, Home/End.
   - En engångsvinkning lär ut mekaniken när blocket kommer in i vyn. Den
     respekterar prefers-reduced-motion och avbryts av minsta egen interaktion.
   - Flera instanser på samma sida fungerar oberoende av varandra.

   PEKARKONTRAKTET (kodgranskning 2026-08-17 — tre bevisade defekter rättade):
   Ett drag ägs av EN pekare, identifierad med pointerId. Allt annat ignoreras.
     1  En vilande tumme kapade sömmen. Nu: andra fingret ignoreras helt.
     2  Att lyfta det andra fingret frös draget. Nu: bara ägarens pointerup
        släpper.
     3  pointerleave avbröt draget vid ramkanten om capture inte tog. Nu:
        pointerleave lyssnas inte på alls; lostpointercapture + ett skyddsnät
        på window sköter frisläppningen.
   Dessutom rAF-koalescering: sömmen skrivs högst en gång per bildruta, så en
   120 Hz-skärm inte får fler layoutberäkningar än den hinner rita.

   SCROLLKONTRAKTET (granskning 2026-09-17 — bevisat på iOS och Android):
   Ett finger som landar på fotot är INTE en interaktion förrän det rört sig i
   sidled. Tidigare flyttades sömmen redan vid pointerdown, så en vanlig
   vertikal scroll som råkade börja på bilden ryckte sömmen, släckte ledtråden
   för gott och skickade fore_efter_interact — innan webbläsaren hann ta över
   scrollen. Nu: touch/penna bekräftas först efter > 6 px i sidled och mer
   sidled än höjdled; lyfts fingret utan rörelse var det ett tryck och sömmen
   placeras där. Musen har inget scrollproblem och hoppar direkt som förut.
   ========================================================================== */
(function () {
  "use strict";

  var VILOLAGE = 50;   // sömmen mitt i bilden — ägarbeslut 2026-09-17
  var STEG_PIL = 5;
  var STEG_SIDA = 10;

  function initiera(figur) {
    if (figur.dataset.ampyForeefterIgang === "1") return;
    figur.dataset.ampyForeefterIgang = "1";

    var ram = figur.querySelector(".ampy-foreefter__ram");
    var reglage = figur.querySelector(".ampy-foreefter__reglage");
    if (!ram || !reglage) return;

    var aktivPekare = null;      // pointerId för den pekare som äger draget
    var vidrord = false;
    var vinkningPagar = false;
    var sistAviserat = null;     // senast utskrivna aria-valuetext-värde

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
      if (!isFinite(procent)) procent = VILOLAGE;
      procent = Math.max(0, Math.min(100, procent));
      var avrundat = Math.round(procent);
      figur.style.setProperty("--ampyfe-pos", procent + "%");
      if (Number(reglage.value) !== avrundat) reglage.value = String(avrundat);
      /* Skärmläsaren ska höra vad sömmen betyder, inte ett råtal — men bara
         när talet faktiskt ändrats, annars blir det uppläsningsspam. */
      if (avrundat !== sistAviserat) {
        sistAviserat = avrundat;
        reglage.setAttribute("aria-valuetext", "Efter syns till " + (100 - avrundat) + " procent");
      }
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

    function procentAv(klientX) {
      var r = ram.getBoundingClientRect();
      if (!r.width) return VILOLAGE;
      return ((klientX - r.left) / r.width) * 100;
    }

    /* --- rAF-koalescering ------------------------------------------------
       Pekarhändelser kan komma tätare än skärmen ritar (120 Hz iPad, mus med
       hög pollningsfrekvens). Vi sparar senaste x och skriver EN gång per
       bildruta. Rutan hämtas i callbacken, så ett drag överlever att sidan
       scrollar under fingret. */
    var vantandeX = null;
    var ramad = 0;
    function schemalagg(klientX) {
      vantandeX = klientX;
      if (ramad) return;
      ramad = (window.requestAnimationFrame || function (f) { return setTimeout(f, 16); })(function () {
        ramad = 0;
        if (vantandeX === null) return;
        satt(procentAv(vantandeX), true);
      });
    }

    function slappDraget() {
      if (aktivPekare === null) return;
      aktivPekare = null;
      bekraftat = false;
      vantandeX = null;
    }

    /* Bältet till hängslena i CSS: även om något i ramen skulle vara dragbart
       får webbläsaren aldrig starta sin egen drag-and-drop mitt i en jämförelse. */
    ram.addEventListener("dragstart", function (e) { e.preventDefault(); });

    /* --- Pekare: tryck-för-att-placera + drag ---------------------------- */
    var startX = 0;
    var startY = 0;
    var bekraftat = false;       // har pekaren bevisat att den vill dra i sidled?
    var TROSKEL_PX = 6;
    var sistScrollad = 0;        // iOS: ett tryck som stoppar rullningen är inget tryck

    window.addEventListener("scroll", function () { sistScrollad = Date.now(); }, { passive: true });

    /* Draget är bekräftat: NU tar reglaget fokus och sömmen får följa fingret.
       preventScroll så att sidan inte hoppar när fokus flyttas. Klassen talar
       om för CSS:en att fokus kom från en pekare — ingen tangentbordsring. */
    function bekrafta() {
      if (bekraftat) return;
      bekraftat = true;
      figur.classList.add("fokus-fran-pekare");
      try { reglage.focus({ preventScroll: true }); } catch (fel) { /* äldre motorer */ }
      mjukt(false);                       // under drag ska sömmen sitta i fingret
    }

    ram.addEventListener("pointerdown", function (e) {
      if (aktivPekare !== null) return;                       // ett drag åt gången
      if (e.button !== undefined && e.button !== 0) return;    // bara vänster/primär
      aktivPekare = e.pointerId;
      startX = e.clientX;
      startY = e.clientY;
      bekraftat = false;
      if (ram.setPointerCapture) {
        try { ram.setPointerCapture(e.pointerId); } catch (fel) { /* strunt samma */ }
      }
      if (e.pointerType === "mouse") {
        e.preventDefault();               // ingen textmarkering, ingen bilddragning
        bekrafta();                       // musen kan inte scrolla med knappen nere
        satt(procentAv(e.clientX), true);
      }
      /* Touch/penna: vi rör INGENTING ännu. Går fingret uppåt eller nedåt tar
         webbläsaren över (touch-action: pan-y) och skickar pointercancel —
         sömmen, ledtråden och mätningen förblir orörda. */
    });

    ram.addEventListener("pointermove", function (e) {
      if (e.pointerId !== aktivPekare) return;                // fel finger, ignorera
      // Har musknappen släppts utanför ramen är draget över, oavsett vad vi tror.
      if (e.pointerType === "mouse" && e.buttons === 0) { slappDraget(); return; }
      if (!bekraftat) {
        var dx = e.clientX - startX;
        var dy = e.clientY - startY;
        if (Math.abs(dx) <= TROSKEL_PX) return;                  // för lite för att veta
        if (Math.abs(dx) <= Math.abs(dy) * 1.5) return;          // mer höjdled än sidled
        bekrafta();
      }
      schemalagg(e.clientX);
    });

    ram.addEventListener("pointerup", function (e) {
      if (e.pointerId !== aktivPekare) return;
      if (!bekraftat && Date.now() - sistScrollad > 120) {
        bekrafta();                       // ett rent tryck: placera sömmen där fingret var
        satt(procentAv(e.clientX), true);
      }
      slappDraget();
    });

    ["pointercancel", "lostpointercapture"].forEach(function (typ) {
      ram.addEventListener(typ, function (e) {
        if (e.pointerId === aktivPekare) slappDraget();       // bara släpp — aldrig flytta
      });
    });

    /* Skyddsnät: tappar vi ändå slutet av ett drag — fönstret förlorar fokus,
       ett samtal kommer in, fliken byts — ska sömmen inte fastna i fingret. */
    window.addEventListener("blur", slappDraget);
    window.addEventListener("pointerup", function (e) {
      if (e.pointerId === aktivPekare) slappDraget();
    });
    window.addEventListener("pointercancel", function (e) {
      if (e.pointerId === aktivPekare) slappDraget();
    });

    /* --- Reglaget: mus, pekskärm och tangentbord ------------------------- */
    // Trycker man direkt på reglaget kom fokus också från en pekare.
    reglage.addEventListener("pointerdown", function () {
      figur.classList.add("fokus-fran-pekare");
    });
    // Första tangenttrycket: nu ÄR det en tangentbordsanvändare — visa ringen.
    // Blur: nästa gång fokus kommer in ska webbläsaren få avgöra själv.
    reglage.addEventListener("keydown", function () {
      figur.classList.remove("fokus-fran-pekare");
    }, true);
    reglage.addEventListener("blur", function () {
      figur.classList.remove("fokus-fran-pekare");
    });

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
      if (document.activeElement === reglage) return;         // rör aldrig ett reglage någon redan står på
      var stillsam = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      if (stillsam) return;
      /* Utan @property (Safari före 16.4) går --ampyfe-pos inte att animera:
         vinkningen blir två hopp i stället för en glidning. Då hoppar vi över den. */
      if (!(window.CSS && typeof CSS.registerProperty === "function")) return;
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

    /* Startläget läses ur reglaget, inte ur konstanten. Firefox och Chrome
       återställer formulärvärden vid mjuk omladdning och bakåtnavigering; då
       ska sömmen hamna där tummen står, inte på 50 % med tummen någon annanstans. */
    var start = parseFloat(reglage.value);
    satt(isFinite(start) ? start : VILOLAGE, false);
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

  /* Laddar Bricks in en sektion i efterhand räcker det att kalla på start().
     Redan initierade figurer hoppas över, så den är säker att köra flera gånger. */
  window.ampyForeEfter = { start: start };
})();
