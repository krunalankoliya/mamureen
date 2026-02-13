$(document).ready(function () {
  $("#datatable, #datatable1, #datatable2").each(function () {
    $(this).DataTable({
      dom: "lBfrtip",
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"],
      ], // Define the length menu options
      buttons: ["copy", "csv", "excel"],
    });
  });

  $("#datatable_manage_mamureen").each(function () {
    $(this).DataTable({
      dom: "lBfrtip",
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"],
      ], // Define the length menu options
      buttons: [
        {
          extend: "copy",
          filename: function () {
            return "mamureen_list -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:nth-child(2))",
          },
        },
        {
          extend: "csv",
          filename: function () {
            return "mamureen_list -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:nth-child(2))",
          },
        },
        {
          extend: "excel",
          filename: function () {
            return "mamureen_list -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:nth-child(2))",
          },
        },
      ],
    });
  });

  $("#datatable_migrated_in").each(function () {
    $(this).DataTable({
      dom: "lBfrtip",
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"],
      ], // Define the length menu options
      columnDefs: [
        {
          targets: [4, 11, 10],
          visible: false,
        },
      ],
      buttons: [
        {
          extend: "copy",
          filename: function () {
            return "migrated_in_student - <?= $city ?> -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:last-child):not(:nth-last-child(2))",
          },
        },
        {
          extend: "csv",
          filename: function () {
            return "migrated_in_student - <?= $city ?> -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:last-child):not(:nth-last-child(2))",
          },
        },
        {
          extend: "excel",
          filename: function () {
            return "migrated_in_student - <?= $city ?> -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:last-child):not(:nth-last-child(2))",
          },
        },
      ],
    });
  });

  $("#datatable_migrated_out").each(function () {
    $(this).DataTable({
      dom: "lBfrtip",
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"],
      ], // Define the length menu options
      columnDefs: [
        {
          targets: [4, 10, 9],
          visible: false,
        },
      ],
      buttons: [
        {
          extend: "copy",
          filename: function () {
            return "migrated_out_student - <?= $city ?> -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:last-child)",
          },
        },
        {
          extend: "csv",
          filename: function () {
            return "migrated_out_student - <?= $city ?> -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:last-child)",
          },
        },
        {
          extend: "excel",
          filename: function () {
            return "migrated_out_student - <?= $city ?> -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:last-child)",
          },
        },
      ],
    });
  });

  $("#datatable_muraqib_tagging").each(function () {
    $(this).DataTable({
      dom: "lBfrtip",
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"],
      ], // Define the length menu options
      columnDefs: [
        {
          targets: [5],
          visible: false,
        },
      ],
      buttons: [
        {
          extend: "copy",
          filename: function () {
            return "muraqib_tagging - <?= $city ?> -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:last-child)",
          },
        },
        {
          extend: "csv",
          filename: function () {
            return "muraqib_tagging - <?= $city ?> -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:last-child)",
          },
        },
        {
          extend: "excel",
          filename: function () {
            return "muraqib_tagging - <?= $city ?> -" + Date.now();
          },
          exportOptions: {
            columns: ":not(:last-child)",
          },
        },
      ],
    });
  });
});
