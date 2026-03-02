var scaleSlider  = document.getElementById('scaleSlider');
var scaleFillBar = document.getElementById('scaleFillBar');
var scaleDisplay = document.getElementById('scaleDisplay');

function syncScale(v) {
    var pct = parseInt(v, 10);
    if (scaleFillBar) scaleFillBar.style.width = pct + '%';
    if (scaleDisplay) scaleDisplay.textContent = pct;
    if (scaleSlider)  scaleSlider.style.background =
        'linear-gradient(90deg, #0d6efd ' + pct + '%, #e9ecef ' + pct + '%)';
}

if (scaleSlider) {
    scaleSlider.addEventListener('input', function () { syncScale(this.value); });
    syncScale(scaleSlider.value);
}

window._syncScale = syncScale;
