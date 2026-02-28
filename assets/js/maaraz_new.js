/**
 * Maaraz Form - JavaScript Handler
 * Fixed Q1 Slider: Uses CSS custom properties (--value) for background gradient
 * Improved browser compatibility and smooth transitions
 */

(function () {
  // ── DOM Elements ─────────────────────────────────────────────────
  var slider = document.getElementById("scaleSlider");
  var fillBar = document.getElementById("fillBar");
  var scaleVal = document.getElementById("scaleVal");
  var form = document.getElementById("uploadForm");
  var userIts = form ? form.dataset.userIts : "";

  // ── Constants ────────────────────────────────────────────────────
  var DRAFT_KEY = "maaraz_draft_" + userIts;
  var FIELD_IDS = [
    "prep_hours",
    "attendance_from",
    "attendance_to",
    "duration_per_day",
    "avg_visitor_minutes",
    "tafheem_text",
    "engagement_strategy",
    "key_takeaways",
    "additional_info",
  ];
  var RADIO_NAMES = ["feedback_impact"];

  // ═══════════════════════════════════════════════════════════════════
  // ── SLIDER SYNC FUNCTION (FIXED – CSS GRADIENT) ──────────────────
  // ═══════════════════════════════════════════════════════════════════
  function syncSlider(val) {
    var pct = parseInt(val, 10) || 0;
    var percentStr = pct + "%";

    // Update slider value and CSS custom property for gradient background
    if (slider) {
      slider.value = pct;
      // Set CSS variable for linear-gradient to show filled portion
      slider.style.setProperty("--value", percentStr);
    }

    // Update fill bar width for fallback browsers
    if (fillBar) {
      fillBar.style.width = percentStr;
    }

    // Update display value
    if (scaleVal) {
      scaleVal.textContent = pct;
    }
  }

  // Initialize slider to 0
  if (slider) {
    syncSlider(slider.value);

    // Sync slider on input and change events
    slider.addEventListener("input", function () {
      syncSlider(this.value);
    });

    slider.addEventListener("change", function () {
      syncSlider(this.value);
    });
  }

  // ── Impact Card Visual Toggle ────────────────────────────────────
  document
    .querySelectorAll(".impact-card input[type='radio']")
    .forEach(function (radio) {
      radio.addEventListener("change", function () {
        // Remove checked class from all cards
        document.querySelectorAll(".impact-card").forEach(function (card) {
          card.classList.remove("checked");
        });
        // Add checked class to the parent card of this radio
        if (this.closest(".impact-card")) {
          this.closest(".impact-card").classList.add("checked");
        }
      });
    });

  // ═══════════════════════════════════════════════════════════════════
  // ── LOCALSTORAGE DRAFT MANAGEMENT ────────────────────────────────
  // ═══════════════════════════════════════════════════════════════════

  function saveDraft() {
    var data = {
      scaleSlider: slider ? slider.value : 0,
    };

    // Save text inputs
    FIELD_IDS.forEach(function (id) {
      var el = document.getElementById(id);
      if (el) {
        data[id] = el.value;
      }
    });

    // Save radio selections
    RADIO_NAMES.forEach(function (name) {
      var checked = document.querySelector(
        'input[name="' + name + '"]:checked',
      );
      data[name] = checked ? checked.value : "";
    });

    // Save checkbox state
    var instructionCheckbox = document.getElementById("instruction");
    if (instructionCheckbox) {
      data.instruction = instructionCheckbox.checked ? 1 : 0;
    }

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

    // Restore slider value
    if (data.scaleSlider !== undefined && data.scaleSlider !== "") {
      syncSlider(data.scaleSlider);
      hasData = true;
    }

    // Restore text inputs
    FIELD_IDS.forEach(function (id) {
      if (data[id] !== undefined && data[id] !== "") {
        var el = document.getElementById(id);
        if (el) {
          el.value = data[id];
          hasData = true;
        }
      }
    });

    // Restore radio selections
    RADIO_NAMES.forEach(function (name) {
      if (data[name]) {
        var radio = document.querySelector(
          'input[name="' + name + '"][value="' + data[name] + '"]',
        );
        if (radio) {
          radio.checked = true;
          var card = radio.closest(".impact-card");
          if (card) {
            card.classList.add("checked");
          }
          hasData = true;
        }
      }
    });

    // Restore checkbox state
    if (data.instruction !== undefined && data.instruction) {
      var instructionCheckbox = document.getElementById("instruction");
      if (instructionCheckbox) {
        instructionCheckbox.checked = true;
        hasData = true;
      }
    }

    // Show draft banner if data was restored
    if (hasData) {
      var banner = document.getElementById("draftBanner");
      if (banner) {
        banner.classList.add("show");
      }
    }
  }

  function clearDraft() {
    localStorage.removeItem(DRAFT_KEY);
    var banner = document.getElementById("draftBanner");
    if (banner) {
      banner.classList.remove("show");
    }
  }

  // Auto-save on form changes
  var saveTimer;
  if (form) {
    form.addEventListener("input", function () {
      clearTimeout(saveTimer);
      saveTimer = setTimeout(saveDraft, 800);
    });

    form.addEventListener("change", function () {
      clearTimeout(saveTimer);
      saveTimer = setTimeout(saveDraft, 800);
    });
  }

  // Clear draft button handler
  var clearDraftBtn = document.getElementById("clearDraftBtn");
  if (clearDraftBtn) {
    clearDraftBtn.addEventListener("click", function (e) {
      e.preventDefault();
      clearDraft();
      form.reset();
      syncSlider(0);
    });
  }

  // Reset button handler
  var resetBtn = document.getElementById("resetBtn");
  if (resetBtn) {
    resetBtn.addEventListener("click", function () {
      clearDraft();
      syncSlider(0);
    });
  }

  // Restore draft on page load
  restoreDraft();

  // ═══════════════════════════════════════════════════════════════════
  // ── FORM SUBMISSION WITH FILE UPLOAD PROGRESS ──────────────────────
  // ═══════════════════════════════════════════════════════════════════

  var progressWrap = document.getElementById("progressWrap");
  var progressBar = document.getElementById("progressBar");
  var progressMsg = document.getElementById("progressMsg");
  var submitBtn = document.getElementById("submitBtn");

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      // Validate form
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      // Check attendance date order
      var fromDateEl = document.getElementById("attendance_from");
      var toDateEl = document.getElementById("attendance_to");
      if (
        fromDateEl &&
        toDateEl &&
        fromDateEl.value &&
        toDateEl.value &&
        fromDateEl.value > toDateEl.value
      ) {
        alert("Attendance 'From' date must be on or before the 'To' date.");
        return;
      }

      // Validate file attachments
      var fileInput = document.getElementById("attachments");
      if (fileInput && fileInput.files.length > 0) {
        var allowedTypes = [
          "image/jpeg",
          "image/png",
          "video/mp4",
          "video/quicktime",
          "video/x-msvideo",
          "audio/mpeg",
          "audio/wav",
          "audio/ogg",
          "application/pdf",
        ];
        var maxFileSize = 5 * 1024 * 1024; // 5 MB per file

        for (var i = 0; i < fileInput.files.length; i++) {
          var file = fileInput.files[i];

          // Check file size
          if (file.size > maxFileSize) {
            alert('File "' + file.name + '" exceeds 5 MB.');
            return;
          }

          // Check file type
          if (allowedTypes.indexOf(file.type) === -1) {
            alert(
              'File "' +
                file.name +
                '" type not allowed. Only JPG/PNG images, MP4/MOV/AVI video, MP3/WAV/OGG audio or PDF are accepted.',
            );
            return;
          }
        }
      }

      // Show progress bar
      if (progressWrap) {
        progressWrap.style.display = "block";
      }
      if (progressMsg) {
        progressMsg.textContent = "Submitting…";
      }
      if (submitBtn) {
        submitBtn.disabled = true;
      }

      // Prepare form data
      var formData = new FormData(form);
      formData.append("submit_maaraz", "1");

      // Create XMLHttpRequest
      var xhr = new XMLHttpRequest();
      xhr.open("POST", window.location.href);
      xhr.timeout = 180000; // 3 minutes

      // Track upload progress
      xhr.upload.onprogress = function (event) {
        if (!event.lengthComputable) return;

        var percentComplete = Math.min(
          Math.round((event.loaded / event.total) * 100),
          99,
        );

        if (progressBar) {
          progressBar.style.width = percentComplete + "%";
          progressBar.textContent = percentComplete + "%";
        }

        if (progressMsg) {
          if (percentComplete < 40) {
            progressMsg.textContent = "Uploading, please wait…";
          } else if (percentComplete < 80) {
            progressMsg.textContent = "Almost there…";
          } else {
            progressMsg.textContent = "Finalizing…";
          }
        }
      };

      // Handle successful upload
      xhr.onload = function () {
        if (progressBar) {
          progressBar.style.width = "100%";
          progressBar.textContent = "100%";
          progressBar.style.background = "#16a34a";
        }

        if (progressMsg) {
          progressMsg.style.color = "green";
          progressMsg.textContent = "✓ Submitted! Refreshing…";
        }

        clearDraft();

        setTimeout(function () {
          window.location.reload();
        }, 1200);
      };

      // Handle network errors
      xhr.onerror = function () {
        if (progressMsg) {
          progressMsg.style.color = "red";
          progressMsg.textContent = "✗ Network error. Please try again.";
        }
        if (submitBtn) {
          submitBtn.disabled = false;
        }
      };

      // Handle timeout
      xhr.ontimeout = function () {
        if (progressMsg) {
          progressMsg.style.color = "red";
          progressMsg.textContent = "✗ Request timed out. Please try again.";
        }
        if (submitBtn) {
          submitBtn.disabled = false;
        }
      };

      // Send the request
      xhr.send(formData);
    });
  }
})();
