document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('memberSearch');
    var cards = document.querySelectorAll('.member-card-wrap');
    var noResults = document.getElementById('noResults');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        var visible = 0;
        cards.forEach(function (wrap) {
            var text = wrap.getAttribute('data-search');
            var show = !q || text.indexOf(q) !== -1;
            wrap.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (noResults) noResults.style.display = visible === 0 ? '' : 'none';
    });
});
