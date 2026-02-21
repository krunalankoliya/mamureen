$(document).ready(function () {
    $(document).on('click', '.remove-btn', function (e) {
        if (!confirm('Are you sure you want to remove this Sub Admin?')) {
            e.preventDefault();
        }
    });
});
