$(document).ready(function () {

    var form = document.getElementById('uploadForm');
    if (!form) return;

    var progressContainer = document.getElementById('progressContainer');
    var progressBar       = document.getElementById('progressBar');
    var uploadMessage     = document.getElementById('uploadMessage');
    var submitBtn         = document.getElementById('submitBtn');

    // ── Slider live display ────────────────────────────────────────────
    var complianceSlider  = document.getElementById('compliance_pct');
    var complianceDisplay = document.getElementById('complianceDisplay');

    complianceSlider.addEventListener('input', function () {
        complianceDisplay.textContent = this.value + '%';
    });

    // ── LocalStorage Draft ─────────────────────────────────────────────
    var DRAFT_KEY    = 'maraz_draft_' + form.dataset.userIts;
    var TEXT_FIELDS  = [
        'prep_time_hours', 'maraz_from_date', 'maraz_to_date',
        'duration_per_day_min', 'time_spent_avg_min',
        'tafheem_details', 'engagement_strategy', 'key_takeaways', 'additional_info'
    ];
    var RANGE_FIELDS = ['compliance_pct'];
    var RADIO_FIELDS = ['impact_rating'];

    function getDraftData() {
        var data = {};

        RANGE_FIELDS.concat(TEXT_FIELDS).forEach(function (name) {
            var el = form.querySelector('[name="' + name + '"]');
            data[name] = el ? el.value : '';
        });

        RADIO_FIELDS.forEach(function (name) {
            var radio = form.querySelector('[name="' + name + '"]:checked');
            data[name] = radio ? radio.value : '';
        });

        return data;
    }

    function countFilled(data) {
        return Object.values(data).filter(function (v) {
            return v !== '' && v !== null && v !== undefined;
        }).length;
    }

    function saveDraft() {
        var data = getDraftData();
        if (countFilled(data) >= 3) {
            try {
                localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
            } catch (e) { /* storage quota exceeded — silently skip */ }
        }
    }

    function restoreDraft() {
        var raw = localStorage.getItem(DRAFT_KEY);
        if (!raw) return;

        var data;
        try { data = JSON.parse(raw); } catch (e) { return; }

        var restored = false;

        RANGE_FIELDS.concat(TEXT_FIELDS).forEach(function (name) {
            if (!data[name]) return;
            var el = form.querySelector('[name="' + name + '"]');
            if (!el) return;
            el.value = data[name];
            if (el.type === 'range') {
                complianceDisplay.textContent = data[name] + '%';
            }
            restored = true;
        });

        RADIO_FIELDS.forEach(function (name) {
            if (!data[name]) return;
            var radio = form.querySelector('[name="' + name + '"][value="' + data[name] + '"]');
            if (radio) {
                radio.checked = true;
                restored = true;
            }
        });

        if (restored) {
            document.getElementById('draftBanner').classList.remove('d-none');
        }
    }

    function clearDraft() {
        localStorage.removeItem(DRAFT_KEY);
    }

    // Restore on page load
    restoreDraft();

    // Save on any input / change (debounced 600 ms)
    var saveTimer;
    form.addEventListener('input', function () {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraft, 600);
    });
    form.addEventListener('change', function () {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveDraft, 600);
    });

    // Clear draft button
    var clearBtn = document.getElementById('clearDraftBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            clearDraft();
            document.getElementById('draftBanner').classList.add('d-none');
        });
    }

    // ── XHR Form Submit ────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Validate impact rating (radio — not covered by HTML required in all browsers)
        if (!form.querySelector('[name="impact_rating"]:checked')) {
            alert('Please select an impact rating.');
            form.querySelector('.impact-option').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // File validation — images only, max 5 MB each
        var fileInput = form.querySelector('[name="photos[]"]');
        if (fileInput && fileInput.files.length > 0) {
            var maxSize = 5 * 1024 * 1024;
            for (var i = 0; i < fileInput.files.length; i++) {
                var file = fileInput.files[i];
                if (file.size > maxSize) {
                    alert('File "' + file.name + '" exceeds the 5 MB limit.');
                    return;
                }
            }
        }

        // Show progress UI
        progressContainer.classList.remove('d-none');
        uploadMessage.classList.remove('d-none');
        uploadMessage.style.color = '#0d6efd';
        uploadMessage.textContent = 'Submitting\u2026';
        submitBtn.disabled = true;

        var fd = new FormData(form);
        fd.append('submit_maraz', '1');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href);
        xhr.timeout = 120000;

        xhr.upload.onprogress = function (e) {
            if (!e.lengthComputable) return;
            var pct = Math.min(Math.round((e.loaded / e.total) * 100), 99);
            progressBar.style.width        = pct + '%';
            progressBar.textContent        = pct + '%';
            progressBar.setAttribute('aria-valuenow', pct);
            if      (pct < 50) uploadMessage.textContent = 'Uploading\u2026';
            else if (pct < 90) uploadMessage.textContent = 'Almost there\u2026';
            else               uploadMessage.textContent = 'Finalizing\u2026';
        };

        xhr.onload = function () {
            progressBar.style.width = '100%';
            progressBar.textContent = '100%';
            uploadMessage.textContent = '\u2714 Submit successfully! Refreshing\u2026';
            uploadMessage.style.color = 'green';
            clearDraft();
            setTimeout(function () { window.location.reload(); }, 1200);
        };

        xhr.onerror = function () {
            uploadMessage.textContent = '\u2716 Network error. Please try again.';
            uploadMessage.style.color = 'red';
            submitBtn.disabled = false;
        };

        xhr.ontimeout = function () {
            uploadMessage.textContent = '\u2716 Request timed out. Please try again.';
            uploadMessage.style.color = 'red';
            submitBtn.disabled = false;
        };

        xhr.send(fd);
    });

});
