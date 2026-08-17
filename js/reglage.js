/* ==========================================================================
   RIKTNING A — reglaget. Enhancement, aldrig grunden.
   Utan denna fil renderas blocket som ett staplat par (se html:not(.js) i
   block.css) — inget innehåll går förlorat.

   Bindande regler ur uppdraget §6/A:
   - Vilolaget 65/35 med EFTER dominant (sömmen står på 35 %).
   - Endast horisontell capture: ramen har touch-action: pan-y och vi kallar
     ALDRIG preventDefault på touchmove (Juxtapose #148 / twentytwenty-defekten).
   - Tap-to-position på hela ramen.
   - Piltangenter ±5 %, Home/End — via reglaget (tangentbord + skärmläsare).
   - Instruktionsetiketten bleknar efter första interaktionen.
   ========================================================================== */
(function () {
  "use strict";

  var slider = document.querySelector(".fe-slider");
  if (!slider) return;

  var frame = slider.querySelector(".fe-frame");
  var range = slider.querySelector(".fe-range");
  var dragging = false;

  function setPos(pct, touched) {
    pct = Math.max(0, Math.min(100, pct));
    slider.style.setProperty("--fe-pos", pct + "%");
    if (range && Number(range.value) !== Math.round(pct)) range.value = Math.round(pct);
    if (touched) slider.classList.add("is-touched");
  }

  function posFromEvent(e) {
    var r = frame.getBoundingClientRect();
    return ((e.clientX - r.left) / r.width) * 100;
  }

  // Pekare: tap-to-position + drag. Ingen preventDefault → vertikal scroll lever.
  frame.addEventListener("pointerdown", function (e) {
    dragging = true;
    if (frame.setPointerCapture) frame.setPointerCapture(e.pointerId);
    setPos(posFromEvent(e), true);
  });
  frame.addEventListener("pointermove", function (e) {
    if (!dragging) return;
    setPos(posFromEvent(e), true);
  });
  ["pointerup", "pointercancel", "pointerleave"].forEach(function (t) {
    frame.addEventListener(t, function () { dragging = false; });
  });

  if (range) {
    range.addEventListener("input", function () { setPos(Number(range.value), true); });
    // Piltangenter ±5 % (native step är 1) + Home/End.
    range.addEventListener("keydown", function (e) {
      var v = Number(range.value), hit = true;
      switch (e.key) {
        case "ArrowLeft":  case "ArrowDown": v -= 5; break;
        case "ArrowRight": case "ArrowUp":   v += 5; break;
        case "Home": v = 0;   break;
        case "End":  v = 100; break;
        default: hit = false;
      }
      if (!hit) return;
      e.preventDefault();
      setPos(v, true);
    });
  }

  setPos(35, false); // vilolaget: EFTER dominant
})();
