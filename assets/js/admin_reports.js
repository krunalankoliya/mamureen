$(document).ready(function () {

    var BASE_URL    = $('#app-config').attr('data-base-url') || '';
    var imageExts   = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    var videoExts   = ['mp4', 'mov', 'avi'];
    var audioExts   = ['mp3', 'wav', 'ogg'];

    $(document).on('click', '.view-btn', function () {
        var fields = [], files = [];
        try { fields = JSON.parse($(this).attr('data-fields') || '[]'); } catch (e) {}
        try { files  = JSON.parse($(this).attr('data-files')  || '[]'); } catch (e) {}

        $('#viewModalLabel').text($(this).attr('data-title') || 'Record Details');

        // ── Fields table ───────────────────────────────────────────────
        var html = '<table class="table table-sm table-bordered mb-0">';
        $.each(fields, function (i, f) {
            html += '<tr>' +
                '<th class="table-light" style="width:35%;white-space:nowrap;vertical-align:top">' +
                    $('<span>').text(f.label).html() +
                '</th>' +
                '<td style="white-space:pre-wrap">' +
                    $('<span>').text(f.value).html() +
                '</td></tr>';
        });
        html += '</table>';
        $('#modal-fields').html(html);

        // ── Attachments ────────────────────────────────────────────────
        var filesHtml = '';
        if (files.length > 0) {
            filesHtml = '<hr><h6 class="mb-2"><i class="bi bi-paperclip"></i> Attachments (' + files.length + ')</h6>' +
                        '<div class="d-flex flex-wrap gap-2">';
            $.each(files, function (i, f) {
                var url  = BASE_URL + 'user_uploads/' + f.path;
                var ext  = (f.type || '').toLowerCase();
                var name = $('<span>').text(f.name || f.path).html();

                if (imageExts.indexOf(ext) !== -1) {
                    filesHtml += '<a href="' + url + '" target="_blank">' +
                        '<img src="' + url + '" style="height:120px;width:120px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;" title="' + name + '">' +
                        '</a>';
                } else if (ext === 'pdf') {
                    filesHtml += '<a href="' + url + '" target="_blank" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-pdf-fill me-1"></i>' + name + '</a>';
                } else if (videoExts.indexOf(ext) !== -1) {
                    filesHtml += '<a href="' + url + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-camera-video-fill me-1"></i>' + name + '</a>';
                } else if (audioExts.indexOf(ext) !== -1) {
                    filesHtml += '<a href="' + url + '" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-music-note-beamed me-1"></i>' + name + '</a>';
                } else {
                    filesHtml += '<a href="' + url + '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark me-1"></i>' + name + '</a>';
                }
            });
            filesHtml += '</div>';
        }
        $('#modal-files').html(filesHtml);

        // ── Show modal ─────────────────────────────────────────────────
        bootstrap.Modal.getOrCreateInstance(document.getElementById('viewModal')).show();
    });

});
