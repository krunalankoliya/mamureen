$(document).ready(function () {

    // ── Modal populate ─────────────────────────────────────────────────
    $('.edit-btn').on('click', function () {
        $('#edit_id').val($(this).data('id'));
        $('#edit_category').val($(this).data('category'));
        $('#edit_challenge').val($(this).data('challenge'));
        $('#edit_solution').val($(this).data('solution'));
    });

    // ── Progress bar / XHR submit ──────────────────────────────────────
    var form             = document.getElementById('uploadForm');
    var progressContainer = document.getElementById('progressContainer');
    var progressBar      = document.getElementById('progressBar');
    var uploadMessage    = document.getElementById('uploadMessage');
    var submitBtn        = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // File size validation (4 MB per file)
        var fileInput = form.querySelector('[name="attachments[]"]');
        if (fileInput && fileInput.files.length > 0) {
            var maxSize = 4 * 1024 * 1024;
            for (var i = 0; i < fileInput.files.length; i++) {
                if (fileInput.files[i].size > maxSize) {
                    alert('File "' + fileInput.files[i].name + '" exceeds the 4 MB limit.');
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
        fd.append('submit_challenge', '1');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href);
        xhr.timeout = 120000;

        xhr.upload.onprogress = function (e) {
            if (!e.lengthComputable) return;
            var pct = Math.min(Math.round((e.loaded / e.total) * 100), 99);
            progressBar.style.width = pct + '%';
            progressBar.textContent = pct + '%';
            progressBar.setAttribute('aria-valuenow', pct);
            if      (pct < 50) uploadMessage.textContent = 'Uploading\u2026';
            else if (pct < 90) uploadMessage.textContent = 'Almost there\u2026';
            else               uploadMessage.textContent = 'Finalizing\u2026';
        };

        xhr.onload = function () {
            progressBar.style.width = '100%';
            progressBar.textContent = '100%';
            uploadMessage.textContent = '\u2714 Submitted successfully! Refreshing\u2026';
            uploadMessage.style.color = 'green';
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
