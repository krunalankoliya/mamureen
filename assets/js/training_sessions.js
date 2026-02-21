$(document).ready(function () {

    // ── Delete confirm ─────────────────────────────────────────
    $(document).on('click', '.delete-btn', function (e) {
        if (!confirm('Are you sure you want to delete this record?')) {
            e.preventDefault();
        }
    });

    // ── Edit modal populate ────────────────────────────────────
    $(document).on('click', '.edit-session-btn', function () {
        $('#edit_session_id').val($(this).data('id'));
        $('#edit_session_desc').val($(this).data('desc'));
        $('#edit_session_date').val($(this).data('date'));
        $('#edit_session_duration').val($(this).data('duration'));
        $('#edit_session_attendees').val($(this).data('attendees'));
    });

    // ── Success popup ──────────────────────────────────────────
    var form = document.querySelector('form[data-show-popup]');
    if (form && form.getAttribute('data-show-popup') === '1') {
        alert('Training session report submitted successfully!');
    }

    // ── Element references ─────────────────────────────────────
    var $typeSelect   = $('#program_type_select');
    var $filterBtn    = $('#ts_filter_btn');
    var $filterPanel  = $('#ts_filter_panel');
    var $filterLabel  = $('#ts_filter_label');
    var $filterList   = $('#ts_filter_list');
    var $searchInput  = $('#ts_search_input');
    var $hiddenInputs = $('#ts_hidden_inputs');
    var $noMsg        = $('#no_titles_msg');

    // ── Toggle panel open / close ──────────────────────────────
    $filterBtn.on('click', function (e) {
        e.stopPropagation();
        var opening = !$filterPanel.is(':visible');
        $filterPanel.toggle(opening);
        $filterBtn.toggleClass('open', opening);
        if (opening) { $searchInput.focus(); }
    });

    // Close panel on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#ts_filter_wrap').length) {
            $filterPanel.hide();
            $filterBtn.removeClass('open');
        }
    });

    // ── Live search inside checkbox list ──────────────────────
    $searchInput.on('input', function () {
        var q = $(this).val().trim().toLowerCase();
        $filterList.find('.ts-item').each(function () {
            var text = $(this).find('label').text().toLowerCase();
            $(this).toggleClass('hidden', q !== '' && text.indexOf(q) === -1);
        });
    });

    // ── Select All ────────────────────────────────────────────
    $('#ts_select_all').on('click', function (e) {
        e.preventDefault();
        $filterList.find('.ts-item:not(.hidden) input[type="checkbox"]').prop('checked', true);
        syncHiddenInputs();
        updateLabel();
    });

    // ── Clear All ─────────────────────────────────────────────
    $('#ts_clear_all').on('click', function (e) {
        e.preventDefault();
        $filterList.find('input[type="checkbox"]').prop('checked', false);
        syncHiddenInputs();
        updateLabel();
    });

    // ── Load titles via AJAX when program type changes ────────
    $typeSelect.on('change', function () {
        var selectedType = $(this).val();

        // Reset state
        $filterList.html('<div class="ts-placeholder">Loading...</div>');
        $hiddenInputs.empty();
        $noMsg.hide();
        $filterLabel.removeClass('text-dark').addClass('text-muted').text('Loading titles...');
        $filterPanel.hide();
        $filterBtn.removeClass('open');
        $searchInput.val('');

        if (!selectedType) {
            $filterList.html('<div class="ts-placeholder">Select a Program Type first</div>');
            $filterLabel.text('Select a Program Type first');
            return;
        }

        $.ajax({
            url: 'get_program_titles.php',
            type: 'GET',
            dataType: 'json',
            data: { program_type: selectedType },
            success: function (titles) {
                $filterList.empty();

                if (!titles || titles.length === 0) {
                    $filterList.html('<div class="ts-placeholder">No titles found</div>');
                    $noMsg.show();
                    $filterLabel.text('No titles available');
                    return;
                }

                $.each(titles, function (i, t) {
                    var cbId = 'ts_cb_' + t.id;
                    var $row = $('<div class="ts-item">').append(
                        $('<input type="checkbox">').attr({ id: cbId, value: t.id })
                            .on('change', function () {
                                syncHiddenInputs();
                                updateLabel();
                            }),
                        $('<label>').attr('for', cbId).text(t.title_name)
                    );
                    $filterList.append($row);
                });

                $filterLabel.removeClass('text-muted').addClass('text-dark').text('Select titles...');
            },
            error: function (xhr) {
                $filterList.html('<div class="ts-placeholder text-danger">Error ' + xhr.status + ': could not load titles</div>');
                $filterLabel.text('Error loading titles');
            }
        });
    });

    // ── Sync hidden inputs from checked checkboxes ────────────
    function syncHiddenInputs() {
        $hiddenInputs.empty();
        $filterList.find('input[type="checkbox"]:checked').each(function () {
            $hiddenInputs.append(
                $('<input>').attr({ type: 'hidden', name: 'program_title_ids[]', value: $(this).val() })
            );
        });
    }

    // ── Update button label with selection summary ────────────
    function updateLabel() {
        var $checked = $filterList.find('input[type="checkbox"]:checked');
        var count    = $checked.length;

        if (count === 0) {
            $filterLabel.html('Select titles...');
        } else if (count === 1) {
            var name = $checked.first().closest('.ts-item').find('label').text();
            $filterLabel.html(
                $('<span>').text(name)[0].outerHTML +
                ' <span class="ts-badge">1</span>'
            );
        } else {
            $filterLabel.html(count + ' titles selected <span class="ts-badge">' + count + '</span>');
        }
    }

    // ── Progress bar / XHR submit ──────────────────────────────
    var uploadForm        = document.getElementById('uploadForm');
    var progressContainer = document.getElementById('progressContainer');
    var progressBar       = document.getElementById('progressBar');
    var uploadMessage     = document.getElementById('uploadMessage');
    var submitBtn         = document.getElementById('submitBtn');

    if (uploadForm) {
        uploadForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate program titles
            if ($hiddenInputs.find('input').length === 0) {
                alert('Please select at least one program title.');
                $filterBtn.addClass('open');
                $filterPanel.show();
                return;
            }

            // Validate file sizes (4 MB each)
            var fileInputs = uploadForm.querySelectorAll('input[type="file"]');
            var maxSize    = 4 * 1024 * 1024;
            for (var i = 0; i < fileInputs.length; i++) {
                if (fileInputs[i].files.length > 0 && fileInputs[i].files[0].size > maxSize) {
                    alert('File "' + fileInputs[i].getAttribute('name') + '" must be less than 4 MB.');
                    return;
                }
            }

            // Show progress UI
            progressContainer.classList.remove('d-none');
            uploadMessage.classList.remove('d-none');
            uploadMessage.style.color = '#0d6efd';
            uploadMessage.textContent = 'Submitting\u2026';
            submitBtn.disabled = true;
            submitBtn.value    = 'Submitting\u2026';

            var fd = new FormData(uploadForm);
            fd.append('submit', '1');

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
                submitBtn.value    = 'Submit';
            };

            xhr.ontimeout = function () {
                uploadMessage.textContent = '\u2716 Request timed out. Please try again.';
                uploadMessage.style.color = 'red';
                submitBtn.disabled = false;
                submitBtn.value    = 'Submit';
            };

            xhr.send(fd);
        });
    }

    // NOTE: #datatable is initialised globally by script.js — do NOT init here.

});
