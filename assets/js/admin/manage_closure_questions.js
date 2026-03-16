/**
 * manage_closure_questions.js
 * Handles DataTable init, edit-modal population, placeholder toggle, and delete confirmation.
 */

$(document).ready(function () {
  // Initialise DataTable without pagination for this page.
  var dt = $("#datatable_closure_q").DataTable({
    dom: "lBfrtip",
    paging: false,
    buttons: ["copy", "csv", "excel"],
    order: [[1, "asc"]],
  });

  // ── Placeholder row visibility ────────────────────────────────────────────
  function togglePlaceholderRow(typeSelect, placeholderRow) {
    if ($(typeSelect).val() === "rating") {
      $(placeholderRow).hide();
    } else {
      $(placeholderRow).show();
    }
  }

  // Edit modal type toggle
  $("#edit_question_type").on("change", function () {
    togglePlaceholderRow(this, "#edit_placeholder_row");
  });

  // ── Populate Edit Modal ───────────────────────────────────────────────────
  $(".edit-btn").on("click", function () {
    var btn = $(this);

    $("#edit_id").val(btn.data("id"));
    $("#modal_qkey_display").text(btn.data("qkey"));
    $("#edit_section").val(btn.data("section"));
    $("#edit_question_type").val(btn.data("type"));
    $("#edit_question_text").val(btn.data("text"));
    $("#edit_placeholder").val(btn.data("placeholder"));

    // Sync placeholder row visibility with current type
    togglePlaceholderRow("#edit_question_type", "#edit_placeholder_row");
  });

  // ── Delete Confirmation ───────────────────────────────────────────────────
  $(".delete-form").on("submit", function (e) {
    if (
      !confirm(
        "Are you sure you want to delete this question?\nThis cannot be undone.",
      )
    ) {
      e.preventDefault();
    }
  });
});
