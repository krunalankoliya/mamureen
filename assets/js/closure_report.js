(function () {
  // ── DOM Elements ─────────────────────────────────────────────────
  var form = document.querySelector("form");
  var userIts = form ? document.body.dataset.userIts : ""; // We'll add this to body in PHP
  var DRAFT_KEY = "closure_report_draft_" + userIts;

  // ── Navigation Elements ──────────────────────────────────────────
  var sections = document.querySelectorAll(".form-section");
  var currentSection = 0;
  var nextBtns = document.querySelectorAll(".btn-next");
  var prevBtns = document.querySelectorAll(".btn-prev");
  var progressDots = document.querySelectorAll(".progress-dot");

  // ── Slider Elements ──────────────────────────────────────────────
  var sliders = document.querySelectorAll('input[type="range"]');

  // ── Date Elements ────────────────────────────────────────────────
  var arrivalDateInput = document.getElementById("arrival_date");
  var departureDateInput = document.getElementById("departure_date");
  var daysOfServiceInput = document.getElementById("days_of_service");
  var naCheckbox = document.getElementById("na_dates");

  // ═══════════════════════════════════════════════════════════════════
  // ── DATE CALCULATION ──────────────────────────────────────────────
  // ═══════════════════════════════════════════════════════════════════

  function calculateDays() {
    if (!arrivalDateInput || !departureDateInput || !daysOfServiceInput) return;

    if (naCheckbox && naCheckbox.checked) {
      daysOfServiceInput.value = 30;
      return;
    }

    var arrivalStr = arrivalDateInput.value;
    var departureStr = departureDateInput.value;

    if (arrivalStr && departureStr) {
      var arrival = new Date(arrivalStr);
      var departure = new Date(departureStr);

      // Validation: Arrival must not be after Departure
      if (departure < arrival) {
        departureDateInput.setCustomValidity(
          "Departure date cannot be before arrival date.",
        );
        departureDateInput.reportValidity();
        daysOfServiceInput.value = "";
        return;
      } else {
        departureDateInput.setCustomValidity("");
      }

      // Calculate difference in days (inclusive)
      var diffTime = Math.abs(departure - arrival);
      var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
      daysOfServiceInput.value = diffDays;

      // Trigger saveDraft
      saveDraft();
    } else {
      daysOfServiceInput.value = "";
    }
  }

  if (naCheckbox) {
    naCheckbox.addEventListener("change", function () {
      if (this.checked) {
        arrivalDateInput.value = "";
        departureDateInput.value = "";
        arrivalDateInput.disabled = true;
        departureDateInput.disabled = true;
        arrivalDateInput.required = false;
        departureDateInput.required = false;
        daysOfServiceInput.value = 30;
      } else {
        arrivalDateInput.disabled = false;
        departureDateInput.disabled = false;
        arrivalDateInput.required = true;
        departureDateInput.required = true;
        calculateDays();
      }
      saveDraft();
    });
  }

  if (arrivalDateInput && departureDateInput) {
    arrivalDateInput.addEventListener("change", calculateDays);
    departureDateInput.addEventListener("change", calculateDays);
  }

  // ═══════════════════════════════════════════════════════════════════
  // ── NAVIGATION LOGIC ─────────────────────────────────────────────
  // ═══════════════════════════════════════════════════════════════════

  function showSection(index) {
    sections.forEach(function (sec, i) {
      sec.style.display = i === index ? "block" : "none";
    });

    // Update progress dots
    if (progressDots.length > 0) {
      progressDots.forEach(function (dot, i) {
        dot.classList.remove("active", "completed");
        if (i === index) dot.classList.add("active");
        if (i < index) dot.classList.add("completed");
      });
    }

    currentSection = index;
    window.scrollTo(0, 0);
  }

  nextBtns.forEach(function (btn) {
    btn.addEventListener("click", function () {
      // Validate current section before moving
      var inputs = sections[currentSection].querySelectorAll(
        "input[required], textarea[required]",
      );
      var valid = true;
      inputs.forEach(function (input) {
        if (!input.checkValidity()) {
          input.reportValidity();
          valid = false;
        }
      });

      if (valid && currentSection < sections.length - 1) {
        showSection(currentSection + 1);
      }
    });
  });

  prevBtns.forEach(function (btn) {
    btn.addEventListener("click", function () {
      if (currentSection > 0) {
        showSection(currentSection - 1);
      }
    });
  });

  // ═══════════════════════════════════════════════════════════════════
  // ── SLIDER SYNC FUNCTION ─────────────────────────────────────────
  // ═══════════════════════════════════════════════════════════════════

  function syncSlider(slider) {
    var val = slider.value;
    var display = slider.parentElement.querySelector(".rating-value");
    if (display) {
      display.textContent = val;
    }

    // CSS Custom Property for background fill
    var min = slider.min || 0;
    var max = slider.max || 100;
    var pct = ((val - min) / (max - min)) * 100;
    slider.style.setProperty("--value", pct + "%");
  }

  sliders.forEach(function (slider) {
    syncSlider(slider);
    slider.addEventListener("input", function () {
      syncSlider(this);
    });
  });

  // ═══════════════════════════════════════════════════════════════════
  // ── LOCALSTORAGE DRAFT MANAGEMENT ────────────────────────────────
  // ═══════════════════════════════════════════════════════════════════

  function saveDraft() {
    if (!form) return;
    var formData = new FormData(form);
    var data = {};
    formData.forEach(function (value, key) {
      data[key] = value;
    });

    try {
      localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
    } catch (e) {
      console.warn("Draft save failed:", e);
    }
  }

  function restoreDraft() {
    var raw = localStorage.getItem(DRAFT_KEY);
    if (!raw) return;

    var data;
    try {
      data = JSON.parse(raw);
    } catch (e) {
      localStorage.removeItem(DRAFT_KEY);
      return;
    }

    var hasData = false;
    for (var key in data) {
      if (data.hasOwnProperty(key)) {
        var el = form.querySelector('[name="' + key + '"]');
        if (el) {
          if (el.type === "checkbox" || el.type === "radio") {
            // Handle if needed, but here mostly text/sliders
            var radio = form.querySelector(
              '[name="' + key + '"][value="' + data[key] + '"]',
            );
            if (radio) radio.checked = true;
          } else {
            el.value = data[key];
          }

          if (el.type === "range") {
            syncSlider(el);
          }
          hasData = true;
        }
      }
    }

    if (hasData) {
      var banner = document.getElementById("draftBanner");
      if (banner) banner.style.display = "flex";
      // Recalculate days after restoration
      calculateDays();
    }
  }

  function clearDraft() {
    localStorage.removeItem(DRAFT_KEY);
    var banner = document.getElementById("draftBanner");
    if (banner) banner.style.display = "none";
  }

  if (form) {
    var saveTimer;
    form.addEventListener("input", function () {
      clearTimeout(saveTimer);
      saveTimer = setTimeout(saveDraft, 1000);
    });

    form.addEventListener("submit", function () {
      clearDraft();
    });

    restoreDraft();
  }

  var clearDraftBtn = document.getElementById("clearDraftBtn");
  if (clearDraftBtn) {
    clearDraftBtn.addEventListener("click", function (e) {
      e.preventDefault();
      clearDraft();
      form.reset();
      sliders.forEach(syncSlider);
      if (daysOfServiceInput) daysOfServiceInput.value = "";
    });
  }

  // Initialize first section
  showSection(0);
})();
