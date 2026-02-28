document.getElementById('viewModal').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    var d = btn.dataset;
    var basePath = document.getElementById('viewModal').dataset.basePath;

    document.getElementById('m-location').textContent    = d.location;
    document.getElementById('m-submittedby').textContent = d.submittedby;
    document.getElementById('m-date').textContent        = d.date;
    document.getElementById('m-impact').textContent      = d.impact;
    document.getElementById('m-dates').textContent       = d.from + ' \u2013 ' + d.to;
    document.getElementById('m-duration').textContent    = d.duration + ' min';
    document.getElementById('m-scale').textContent       = d.scale + '%';
    document.getElementById('m-prep-avg').textContent    = d.prep + ' hrs / ' + d.avg + ' min';
    document.getElementById('m-tafheem').textContent     = d.tafheem;
    document.getElementById('m-engagement').textContent  = d.engagement;
    document.getElementById('m-takeaways').textContent   = d.takeaways;

    var addWrap = document.getElementById('m-additional-wrap');
    if (d.additional && d.additional.trim()) {
        document.getElementById('m-additional').textContent = d.additional;
        addWrap.style.display = '';
    } else {
        addWrap.style.display = 'none';
    }

    var filesList = document.getElementById('m-files-list');
    filesList.innerHTML = '';
    var filesWrap = document.getElementById('m-files-wrap');
    try {
        var files = JSON.parse(d.files);
        if (files && files.length) {
            filesWrap.style.display = '';
            files.forEach(function (f) {
                var a = document.createElement('a');
                a.href = basePath + 'user_uploads/' + f.path;
                a.target = '_blank';
                a.className = 'badge bg-light text-primary border text-decoration-none';
                a.innerHTML = '<i class="bi bi-file-earmark me-1"></i>' + f.type.toUpperCase();
                filesList.appendChild(a);
            });
        } else {
            filesWrap.style.display = 'none';
        }
    } catch (ex) {
        filesWrap.style.display = 'none';
    }
});
