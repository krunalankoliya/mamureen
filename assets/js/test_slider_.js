var slider  = document.getElementById('scaleSlider');
var fillBar = document.getElementById('fillBar');
var valDisp = document.getElementById('scaleVal');

function sync(v) {
    var pct = parseInt(v, 10);
    if (fillBar) fillBar.style.width = pct + '%';
    if (valDisp) valDisp.textContent = pct;
    if (slider)  slider.style.background =
        'linear-gradient(90deg, #0d6efd ' + pct + '%, #e9ecef ' + pct + '%)';
}

if (slider) {
    slider.addEventListener('input', function () { sync(this.value); });
    sync(slider.value);
}

// Expose for maaraz.js draft restore
window._syncScale = sync;
