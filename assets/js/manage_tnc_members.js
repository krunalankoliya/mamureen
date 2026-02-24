$(document).ready(function () {

    // Populate edit modal from data-attributes
    $('.edit-btn').on('click', function () {
        var btn = $(this);
        $('#edit_id').val(btn.data('id'));
        $('#edit_its').val(btn.data('its'));
        $('#edit_name').val(btn.data('name'));
        $('#edit_post').val(btn.data('post'));
        $('#edit_mobile').val(btn.data('mobile'));
        $('#edit_email').val(btn.data('email'));
        $('#edit_jamaat').val(btn.data('jamaat'));
        $('#edit_jamiaat').val(btn.data('jamiaat'));
    });

    // Delete confirmation
    $('.delete-form').on('submit', function (e) {
        if (!confirm('Are you sure you want to delete this member? This cannot be undone.')) {
            e.preventDefault();
        }
    });

});
