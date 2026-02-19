$(document).ready(function () {

    var form             = document.getElementById('addUserForm');
    var fileInput        = document.getElementById('uploadFile');
    var submitBtn        = document.getElementById('submitBtn');
    var resetBtn         = document.getElementById('resetBtn');
    var progressContainer = document.getElementById('progressContainer');
    var progressBar      = document.getElementById('progressBar');
    var uploadedSizeEl   = document.getElementById('uploadedSize');
    var totalSizeEl      = document.getElementById('totalSize');
    var uploadMessage    = document.getElementById('uploadMessage');

    // ── Delete confirmation (event delegation — replaces inline onclick) ──
    $(document).on('click', '[name="delete"]', function (e) {
        if (!confirm('Are you sure you want to delete this file?')) {
            e.preventDefault();
        }
    });

    // ── Reset button ──────────────────────────────────────────────────────
    resetBtn.addEventListener('click', function () {
        resetUploadUI();
        submitBtn.disabled = false;
        submitBtn.value    = 'Submit';
    });

    // ── Submit / chunked upload ───────────────────────────────────────────
    submitBtn.addEventListener('click', function () {

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var file = fileInput.files[0];
        if (!file) {
            alert('Please select a file to upload.');
            return;
        }

        var maxSize = 200 * 1024 * 1024; // 200 MB
        if (file.size > maxSize) {
            alert('Uploaded file size must be less than 200 MB.');
            return;
        }

        // Show progress UI
        progressContainer.classList.remove('d-none');
        uploadMessage.classList.remove('d-none');
        uploadMessage.style.color = '#0d6efd';
        uploadMessage.textContent = 'Preparing upload…';
        submitBtn.disabled = true;
        resetBtn.disabled  = true;
        submitBtn.value    = 'Uploading…';

        var chunkSize    = 5 * 1024 * 1024; // 5 MB per chunk
        var totalChunks  = Math.ceil(file.size / chunkSize);
        var fileName     = file.name.replace(/\s+/g, '_');
        var uploadId     = Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        var currentChunk = 0;
        var uploadedSize = 0;
        var retryCount   = 0;

        totalSizeEl.textContent = (file.size / (1024 * 1024)).toFixed(2);

        // Collect extra form fields once
        var extraData = new FormData();
        extraData.append('title',     document.getElementById('inputTitle').value);
        extraData.append('dot_color', document.getElementById('inputState').value);

        sendChunk();

        function sendChunk() {
            var start = currentChunk * chunkSize;
            var end   = Math.min(start + chunkSize, file.size);
            var chunk = file.slice(start, end);

            var fd = new FormData();
            fd.append('chunk',       chunk);
            fd.append('uploadId',    uploadId);
            fd.append('chunkIndex',  currentChunk);
            fd.append('totalChunks', totalChunks);
            fd.append('fileName',    fileName);
            fd.append('fileSize',    file.size);

            var isFinal = (currentChunk === totalChunks - 1);
            if (isFinal) {
                for (var pair of extraData.entries()) {
                    fd.append(pair[0], pair[1]);
                }
                fd.append('finalChunk', '1');
            }

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload_chunk.php', true);
            xhr.timeout = 180000; // 3-min per chunk

            xhr.upload.onprogress = function (e) {
                if (!e.lengthComputable) return;
                var done = uploadedSize + e.loaded;
                var pct  = Math.min(Math.round((done / file.size) * 100), 99);
                setProgress(pct, done);
            };

            xhr.onload = function () {
                var resp;
                try   { resp = JSON.parse(xhr.responseText); }
                catch (ex) {
                    onChunkError('Server returned non-JSON response. Check server logs.');
                    console.error('Raw response:', xhr.responseText);
                    return;
                }

                if (xhr.status !== 200 || !resp.success) {
                    onChunkError(resp ? resp.message : 'HTTP ' + xhr.status);
                    return;
                }

                // Chunk succeeded
                retryCount    = 0;
                uploadedSize += (end - start);
                currentChunk++;

                if (currentChunk < totalChunks) {
                    setProgress(Math.round((uploadedSize / file.size) * 100), uploadedSize);
                    sendChunk();
                } else {
                    // All done
                    if (resp.data && resp.data.allComplete) {
                        setProgress(100, file.size);
                        uploadMessage.textContent  = '✔ Upload complete! Refreshing…';
                        uploadMessage.style.color  = 'green';
                        setTimeout(function () { window.location.reload(); }, 1200);
                    } else {
                        onChunkError('Server error finalizing: ' + resp.message);
                    }
                }
            };

            xhr.onerror   = function () { onChunkError('Network error — check your connection.'); };
            xhr.ontimeout = function () { onChunkError('Chunk timed out. Try a faster connection.'); };

            xhr.send(fd);
        }

        function onChunkError(msg) {
            retryCount++;
            if (retryCount <= 3) {
                uploadMessage.textContent = 'Retrying chunk ' + currentChunk + ' (attempt ' + retryCount + '/3)…';
                setTimeout(sendChunk, 1500);
            } else {
                uploadMessage.textContent  = '✖ ' + msg;
                uploadMessage.style.color  = 'red';
                setTimeout(function () {
                    resetUploadUI();
                    submitBtn.disabled = false;
                    resetBtn.disabled  = false;
                    submitBtn.value    = 'Submit';
                }, 4000);
            }
        }

        function setProgress(pct, loadedBytes) {
            progressBar.style.width = pct + '%';
            progressBar.textContent = pct + '%';
            progressBar.setAttribute('aria-valuenow', pct);
            uploadedSizeEl.textContent = (loadedBytes / (1024 * 1024)).toFixed(2);
            if      (pct < 40) uploadMessage.textContent = 'Upload in progress, please wait…';
            else if (pct < 80) uploadMessage.textContent = 'Still uploading, almost there…';
            else if (pct < 100) uploadMessage.textContent = "Finalizing — don't close this page…";
        }
    }); // end submitBtn click

    function resetUploadUI() {
        progressContainer.classList.add('d-none');
        uploadMessage.classList.add('d-none');
        uploadMessage.style.color = '#0d6efd';
        progressBar.style.width   = '0%';
        progressBar.textContent   = '0%';
        progressBar.setAttribute('aria-valuenow', 0);
        uploadedSizeEl.textContent = '0';
        totalSizeEl.textContent    = '0';
    }

});
