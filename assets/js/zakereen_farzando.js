$(document).ready(function () {

    // ── Success popup ──────────────────────────────────────────────────
    var pageState = document.getElementById('page-state');
    if (pageState && pageState.getAttribute('data-show-popup') === '1') {
        alert('Farzand added successfully!');
    }

    // ── Delete confirm ─────────────────────────────────────────────────
    $(document).on('click', '.delete-btn', function (e) {
        if (!confirm('Are you sure you want to delete this record?')) {
            e.preventDefault();
        }
    });

    // ── Edit modal populate ────────────────────────────────────────────
    $(document).on('click', '.edit-farzand-btn', function () {
        $('#edit_farzand_id').val($(this).data('id'));
        $('#edit_farzand_name').text($(this).data('name'));
        $('#edit_farzand_party').val($(this).data('party-id'));
    });

});
