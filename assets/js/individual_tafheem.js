$(document).ready(function () {

    // ── Delete confirm ─────────────────────────────────────────────────
    $(document).on('click', '.delete-btn', function (e) {
        if (!confirm('Are you sure you want to delete this record?')) {
            e.preventDefault();
        }
    });

    // ── Modal populate ─────────────────────────────────────────────────
    $('.view-btn').on('click', function () {
        $('#edit_id').val($(this).data('id'));
        $('#edit_target_info').text($(this).data('name') + ' (' + $(this).data('its') + ')');
        $('#edit_type').val($(this).data('type'));

        // Pre-select reasons from comma-separated string
        var reasonIds = $(this).data('reason').toString().split(',').map(function (id) { return id.trim(); });
        $('#edit_reason').val(reasonIds);

        $('#edit_details').val($(this).data('details'));
    });

    // ── Excel-style Reason filter ──────────────────────────────────────
    var $typeSelect   = $('#tf_type_select');
    var $filterBtn    = $('#tf_filter_btn');
    var $filterPanel  = $('#tf_filter_panel');
    var $filterLabel  = $('#tf_filter_label');
    var $filterList   = $('#tf_filter_list');
    var $searchInput  = $('#tf_search_input');
    var $hiddenInputs = $('#tf_hidden_inputs');

    // Toggle panel open / close
    $filterBtn.on('click', function (e) {
        e.stopPropagation();
        var opening = !$filterPanel.is(':visible');
        $filterPanel.toggle(opening);
        $filterBtn.toggleClass('open', opening);
        if (opening) { $searchInput.focus(); }
    });

    // Close panel on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#tf_filter_wrap').length) {
            $filterPanel.hide();
            $filterBtn.removeClass('open');
        }
    });

    // Live search inside checkbox list
    $searchInput.on('input', function () {
        var q = $(this).val().trim().toLowerCase();
        $filterList.find('.ts-item').each(function () {
            var text = $(this).find('label').text().toLowerCase();
            $(this).toggleClass('hidden', q !== '' && text.indexOf(q) === -1);
        });
    });

    // Select All
    $('#tf_select_all').on('click', function (e) {
        e.preventDefault();
        $filterList.find('.ts-item:not(.hidden) input[type="checkbox"]').prop('checked', true);
        syncHiddenInputs();
        updateLabel();
    });

    // Clear All
    $('#tf_clear_all').on('click', function (e) {
        e.preventDefault();
        $filterList.find('input[type="checkbox"]').prop('checked', false);
        syncHiddenInputs();
        updateLabel();
    });

    // Load reasons via AJAX when type changes
    $typeSelect.on('change', function () {
        var selectedType = $(this).val();

        // Reset state
        $filterList.html('<div class="ts-placeholder">Loading...</div>');
        $hiddenInputs.empty();
        $filterLabel.removeClass('text-dark').addClass('text-muted').text('Loading reasons...');
        $filterPanel.hide();
        $filterBtn.removeClass('open');
        $searchInput.val('');

        if (!selectedType) {
            $filterList.html('<div class="ts-placeholder">Select a Type first</div>');
            $filterLabel.text('Select a Type first');
            return;
        }

        $.ajax({
            url: 'get_tafheem_reasons.php',
            type: 'GET',
            dataType: 'json',
            data: { type: selectedType },
            success: function (reasons) {
                $filterList.empty();

                if (!reasons || reasons.length === 0) {
                    $filterList.html('<div class="ts-placeholder">No reasons found for this type</div>');
                    $filterLabel.text('No reasons available');
                    return;
                }

                $.each(reasons, function (i, r) {
                    var cbId = 'tf_cb_' + r.id;
                    var $row = $('<div class="ts-item">').append(
                        $('<input type="checkbox">').attr({ id: cbId, value: r.id })
                            .on('change', function () {
                                syncHiddenInputs();
                                updateLabel();
                            }),
                        $('<label>').attr('for', cbId).text(r.reason_name)
                    );
                    $filterList.append($row);
                });

                $filterLabel.removeClass('text-muted').addClass('text-dark').text('Select reasons...');
            },
            error: function (xhr) {
                $filterList.html('<div class="ts-placeholder text-danger">Error ' + xhr.status + ': could not load reasons</div>');
                $filterLabel.text('Error loading reasons');
            }
        });
    });

    // Sync hidden inputs from checked checkboxes
    function syncHiddenInputs() {
        $hiddenInputs.empty();
        $filterList.find('input[type="checkbox"]:checked').each(function () {
            $hiddenInputs.append(
                $('<input>').attr({ type: 'hidden', name: 'reason_ids[]', value: $(this).val() })
            );
        });
    }

    // Update button label with selection summary
    function updateLabel() {
        var $checked = $filterList.find('input[type="checkbox"]:checked');
        var count    = $checked.length;

        if (count === 0) {
            $filterLabel.html('Select reasons...');
        } else if (count === 1) {
            var name = $checked.first().closest('.ts-item').find('label').text();
            $filterLabel.html(
                $('<span>').text(name)[0].outerHTML +
                ' <span class="ts-badge">1</span>'
            );
        } else {
            $filterLabel.html(count + ' reasons selected <span class="ts-badge">' + count + '</span>');
        }
    }

    // ── Progress bar / XHR submit ──────────────────────────────────────
    var form             = document.getElementById('uploadForm');
    if (!form) return; // upload form only shown after ITS verification

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

        // Validate at least one reason selected
        if ($hiddenInputs.find('input').length === 0) {
            alert('Please select at least one reason.');
            $filterBtn.addClass('open');
            $filterPanel.show();
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
        fd.append('submit_tafheem', '1');

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
