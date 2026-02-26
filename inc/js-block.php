<!-- Vendor JS Files -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= MODULE_PATH ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= MODULE_PATH ?>assets/vendor/quill/quill.min.js"></script>
<script src="<?= MODULE_PATH ?>assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="<?= MODULE_PATH ?>assets/vendor/tinymce/tinymce.min.js"></script>
<script src="<?= MODULE_PATH ?>assets/vendor/php-email-form/validate.js"></script>

<!-- Datatables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>

<!-- Template Main JS File -->
<script src="<?= MODULE_PATH ?>assets/js/main.js"></script>

<script>
$(document).ready(function() {
    // Initialize standard datatables
    if ($('.datatable').length) {
        $('.datatable').DataTable({
            "pageLength": 25,
            "order": [],
            "dom": 'Bfrtip',
            "buttons": [
                'copy', 'csv', 'excel'
            ],
            "responsive": true
        });
    }

    // Sidebar Toggle Logic
    $('.toggle-sidebar-btn').on('click', function() {
        $('body').toggleClass('toggle-sidebar');
    });
});
</script>
