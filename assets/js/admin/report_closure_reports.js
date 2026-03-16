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
            { extend: "copy",  title: "Closure Reports" },
            { extend: "csv",   title: "Closure Reports", filename: "closure_reports_" + Date.now() },
            { extend: "excel", title: "Closure Reports", filename: "closure_reports_" + Date.now() },
        ],
        order: [[4, "asc"]], // sort by Mauze
        columnDefs: [
            { orderable: false, targets: [1, 7] }, // photo, actions
        ],
    });
});
