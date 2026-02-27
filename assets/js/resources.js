(function () {
    var activeFilter = 'all';
    var searchTerm   = '';

    function applyFilters() {
        var items     = document.querySelectorAll('#dlList .dl-item');
        var noResults = document.getElementById('dlNoResults');
        var visible   = 0;

        items.forEach(function (item) {
            var matchFilter = activeFilter === 'all' || item.dataset.color === activeFilter;
            var matchSearch = !searchTerm || (item.dataset.title || '').indexOf(searchTerm) !== -1;
            if (matchFilter && matchSearch) {
                item.style.display = '';
                visible++;
            } else {
                item.style.display = 'none';
            }
        });

        if (noResults) {
            noResults.classList.toggle('d-none', visible > 0);
        }
    }

    // Tab filter
    document.querySelectorAll('.dl-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.dl-tab').forEach(function (t) {
                t.classList.remove('active');
            });
            this.classList.add('active');
            activeFilter = this.dataset.filter;
            applyFilters();
        });
    });

    // Search filter
    var searchInput = document.getElementById('dlSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            searchTerm = this.value.toLowerCase().trim();
            applyFilters();
        });
    }
})();
