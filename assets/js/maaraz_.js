$(document).ready(function () {

    // ── localStorage Draft ────────────────────────────────────────────
    var form        = document.getElementById('uploadForm');
    var userIts     = form ? form.dataset.userIts : '';
    var DRAFT_KEY   = 'maaraz_draft_' + userIts;

    // Fields to save/restore (excludes file inputs)
    var FIELD_IDS = [
        'scaleSlider',
        'prep_hours',
        'attendance_from', 'attendance_to',
        'duration_per_day', 'avg_visitor_minutes',
        'tafheem_text', 'engagement_strategy',
        'key_takeaways', 'additional_info'
    ];
    var RADIO_NAMES = ['feedback_impact'];

    // ── Slider sync (shared by live input + draft restore + reset) ────
    function syncSlider(val) {
        var pct    = parseInt(val, 10) || 0;
        var slider = document.getElementById('scaleSlider');
        var disp   = document.getElementById('scaleVal');
        if (slider) {
            slider.value = pct;
            slider.style.background =
                'linear-gradient(90deg,#0d6efd ' + pct + '%,#e9ecef ' + pct + '%)';
        }
        if (disp) disp.textContent = pct;
    }

    // Expose so restoreDraft (line below) and external callers can use it
    window._syncScale = syncSlider;

    // Init slider on page load (value="0" by default)
    syncSlider(0);

    function saveDraft() {
        var data = {};
        FIELD_IDS.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) data[id] = el.value;
        });
        RADIO_NAMES.forEach(function (name) {
            var checked = document.querySelector('input[name="' + name + '"]:checked');
            data[name] = checked ? checked.value : '';
        });
        localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
    }

    function restoreDraft() {
        var raw = localStorage.getItem(DRAFT_KEY);
        if (!raw) return;
        var data;
        try { data = JSON.parse(raw); } catch (e) { return; }

        var hasData = false;

        FIELD_IDS.forEach(function (id) {
            if (data[id] !== undefined && data[id] !== '') {
                var el = document.getElementById(id);
                if (el) { el.value = data[id]; hasData = true; }
                if (id === 'scaleSlider') syncSlider(data[id]);
            }
        });

        RADIO_NAMES.forEach(function (name) {
            if (data[name] !== undefined && data[name] !== '') {
                var radio = document.querySelector('input[name="' + name + '"][value="' + data[name] + '"]');
                if (radio) {
                    radio.checked = true;
                    hasData = true;
                }
            }
        });

        if (hasData) {
            document.getElementById('draftBanner').classList.remove('d-none');
        }
    }

    function clearDraft() {
        localStorage.removeItem(DRAFT_KEY);
        document.getElementById('draftBanner').classList.add('d-none');
    }

    // Restore on page load
    restoreDraft();

    // Auto-save on any input change (debounced 800 ms)
    var saveTimer;
    if (form) {
        form.addEventListener('input', function (e) {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveDraft, 800);
            // Sync slider display live — piggybacks on the confirmed-working form listener
            if (e.target && e.target.id === 'scaleSlider') {
                syncSlider(e.target.value);
            }
        });
        form.addEventListener('change', function () {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveDraft, 800);
        });
    }

    // Clear draft button
    var clearBtn = document.getElementById('clearDraftBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            clearDraft();
            form.reset();
            syncSlider(0);
        });
    }

    // Clear draft on reset button
    var resetBtn = form ? form.querySelector('[type="reset"]') : null;
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            clearDraft();
            syncSlider(0);
        });
    }

    // ── XHR Submit with Progress Bar ─────────────────────────────────
    var progressContainer = document.getElementById('progressContainer');
    var progressBar       = document.getElementById('progressBar');
    var uploadMessage     = document.getElementById('uploadMessage');
    var submitBtn         = document.getElementById('submitBtn');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // File validation: images only, max 5 MB each
        var fileInput = document.getElementById('attachments');
        if (fileInput && fileInput.files.length > 0) {
            var maxSize      = 5 * 1024 * 1024;
            var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            for (var i = 0; i < fileInput.files.length; i++) {
                var file = fileInput.files[i];
                if (file.size > maxSize) {
                    alert('File "' + file.name + '" exceeds the 5 MB limit.');
                    return;
                }
                if (allowedTypes.indexOf(file.type) === -1) {
                    alert('File "' + file.name + '" is not allowed.\nOnly JPEG, PNG, and WebP images are accepted.');
                    return;
                }
            }
        }

        // Show progress UI
        progressContainer.classList.remove('d-none');
        uploadMessage.classList.remove('d-none');
        uploadMessage.style.color   = '#0d6efd';
        uploadMessage.textContent   = 'Submitting\u2026';
        submitBtn.disabled          = true;

        var fd = new FormData(form);
        fd.append('submit_maaraz', '1');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href);
        xhr.timeout = 180000;

        xhr.upload.onprogress = function (e) {
            if (!e.lengthComputable) return;
            var pct = Math.min(Math.round((e.loaded / e.total) * 100), 99);
            progressBar.style.width = pct + '%';
            progressBar.textContent = pct + '%';
            progressBar.setAttribute('aria-valuenow', pct);
            if      (pct < 40) uploadMessage.textContent = 'Uploading, please wait\u2026';
            else if (pct < 80) uploadMessage.textContent = 'Still uploading, almost there\u2026';
            else               uploadMessage.textContent = 'Finalizing \u2014 don\u2019t close this page\u2026';
        };

        xhr.onload = function () {
            progressBar.style.width = '100%';
            progressBar.textContent = '100%';
            uploadMessage.style.color = 'green';
            uploadMessage.textContent = '\u2714 Submitted successfully! Refreshing\u2026';
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
