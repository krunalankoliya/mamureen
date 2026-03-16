document.addEventListener('DOMContentLoaded', function () {
    var printBtn = document.getElementById('printBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function () { window.print(); });
    }
    var closeBtn = document.getElementById('closeBtn');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () { window.close(); });
    }
});
