function updateScale(pct) {
    pct = parseInt(pct, 10);

    // Fresh lookup every call — avoids any null from load-time caching
    var fill = document.getElementById('fillBar');
    var disp = document.getElementById('scaleVal');

    if (fill) fill.style.width = pct + '%';
    if (disp) disp.textContent = pct;

    document.querySelectorAll('input[name="ideal_model_scale"]').forEach(function (r) {
        var step = r.parentElement;
        if (step) step.classList.toggle('filled', parseInt(r.value, 10) <= pct);
    });
}

// ── Approach 1: event delegation on document (cannot be blocked) ──
document.addEventListener('change', function (e) {
    if (e.target && e.target.name === 'ideal_model_scale') {
        updateScale(e.target.value);
    }
});

// ── Approach 2: direct click on each label dot as backup ──────────
document.addEventListener('click', function (e) {
    var label = e.target.closest('.scale-step');
    if (!label) return;
    var radio = label.querySelector('input[type="radio"]');
    if (radio) {
        radio.checked = true;
        updateScale(radio.value);
    }
});

// ── Init on page load ─────────────────────────────────────────────
var checked = document.querySelector('input[name="ideal_model_scale"]:checked');
updateScale(checked ? checked.value : 0);

window._syncScale = updateScale;
