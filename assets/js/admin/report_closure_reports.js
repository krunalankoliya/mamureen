/**
 * report_closure_reports.js
 * Admin closure reports list – DataTable init.
 */
$(document).ready(function () {
    $("#datatable_cr").DataTable({
        dom: "lBfrtip",
        pageLength: 25,
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, "All"],
        ],
        buttons: [
            { extend: "copy",  title: "Closure Reports", exportOptions: { columns: [0, 2, 3, 4, 5, 6] } },
            { extend: "csv",   title: "Closure Reports", filename: "closure_reports_" + Date.now(), exportOptions: { columns: [0, 2, 3, 4, 5, 6] } },
            { extend: "excel", title: "Closure Reports", filename: "closure_reports_" + Date.now(), exportOptions: { columns: [0, 2, 3, 4, 5, 6] } },
        ],
        order: [[4, "asc"]], // sort by Mauze
        columnDefs: [
            { orderable: false, targets: [1, 7] }, // photo, actions
        ],
    });
});
