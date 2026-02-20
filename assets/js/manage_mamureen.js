$(document).ready(function () {

    // NOTE: #datatable_manage_mamureen is initialised globally by script.js — do NOT init here.

    // ── Toggle upload form ─────────────────────────────────────────────────
    $('#addUserBtn').on('click', function () {
        $('#addUserForm').toggle();
        $('#uploadProgress').addClass('d-none');
        resetProgress();
    });

    // ── CSV upload with batch processing ───────────────────────────────────
    $('#addUserForm').on('submit', function (e) {
        e.preventDefault();

        var fileInput = document.getElementById('csv_file');

        if (!fileInput || !fileInput.files.length) {
            alert('Please select a CSV file.');
            return;
        }

        if (fileInput.files[0].size > 10 * 1024 * 1024) {
            alert('File size must be less than 10 MB.');
            return;
        }

        // Show progress UI
        $('#uploadProgress').removeClass('d-none');
        $('#uploadBtn').prop('disabled', true).text('Uploading…');
        setProgress(0, 'Uploading file…', '');

        // Step 1: Upload file, validate, truncate table
        var fd = new FormData();
        fd.append('csv_file', fileInput.files[0]);
        fd.append('action', 'upload');

        $.ajax({
            url: 'import_mamureen_batch.php',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    showError(res.message);
                    return;
                }
                setProgress(0, 'Processing rows 1–10 of ' + res.total + '…', '');
                processBatch(res.key, 0, res.total, 0, []);
            },
            error: function () {
                showError('Upload failed. Please try again.');
            }
        });
    });

    // ── Batch loop ─────────────────────────────────────────────────────────
    function processBatch(key, offset, total, totalInserted, allErrors) {
        var from = offset + 1;
        var to   = Math.min(offset + 10, total);
        var pct  = total > 0 ? Math.min(Math.round((offset / total) * 100), 98) : 0;

        setProgress(pct, 'Processing rows ' + from + '–' + to + ' of ' + total + '…', '');

        $.ajax({
            url: 'import_mamureen_batch.php',
            type: 'POST',
            data: { action: 'batch', key: key, offset: offset },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    showError(res.message);
                    return;
                }

                totalInserted += res.inserted;
                allErrors      = allErrors.concat(res.errors);

                if (res.done) {
                    // Build summary
                    var summary = totalInserted + ' records imported successfully.';
                    if (allErrors.length > 0) {
                        summary += ' ' + allErrors.length + ' row(s) skipped.';
                    }
                    setProgress(100, '\u2714 Done! ' + summary, 'green');
                    $('#uploadBtn').prop('disabled', false).text('Upload');
                    setTimeout(function () { window.location.reload(); }, 2000);
                } else {
                    processBatch(key, res.offset, total, totalInserted, allErrors);
                }
            },
            error: function () {
                showError('Batch processing failed at offset ' + offset + '. Please try again.');
            }
        });
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    function setProgress(pct, msg, color) {
        $('#progressBar').css('width', pct + '%').attr('aria-valuenow', pct).text(pct + '%');
        $('#progressMsg').text(msg).css('color', color || '#0d6efd');
    }

    function resetProgress() {
        setProgress(0, '', '');
        $('#uploadBtn').prop('disabled', false).text('Upload');
    }

    function showError(msg) {
        $('#progressMsg').text('Error: ' + msg).css('color', 'red');
        $('#uploadBtn').prop('disabled', false).text('Upload');
    }

});
