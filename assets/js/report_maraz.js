$(document).ready(function () {
  var BASE_URL = $("#app-config").attr("data-base-url") || "";
  var imageExts = ["jpg", "jpeg", "png", "gif", "webp"];
  var videoExts = ["mp4", "mov", "avi"];
  var audioExts = ["mp3", "wav", "ogg"];

  // Override admin_reports.js with stacked Q / A layout (question on its own row, answer below)
  $(document)
    .off("click", ".view-btn")
    .on("click", ".view-btn", function () {
      var fields = [],
        files = [];
      var recordId = $(this).attr("data-record-id") || 0;
      var module = $(this).attr("data-module") || "maaraz";

      try {
        fields = JSON.parse($(this).attr("data-fields") || "[]");
      } catch (e) {}
      try {
        files = JSON.parse($(this).attr("data-files") || "[]");
      } catch (e) {}

      $("#viewModalLabel").text($(this).attr("data-title") || "Record Details");

      // ── Stacked Q / A ────────────────────────────────────────────────
      var html = "<div>";
      $.each(fields, function (i, f) {
        var isLast = i === fields.length - 1;
        html +=
          "<div" +
          (isLast ? "" : ' class="mb-3 pb-3 border-bottom"') +
          ">" +
          '<div class="small fw-semibold text-muted mb-1" style="white-space:normal;line-height:1.5;">' +
          $("<span>").text(f.label).html() +
          "</div>" +
          '<div style="white-space:pre-wrap;font-size:.9rem;color:#212529;line-height:1.6;">' +
          ($("<span>")
            .text(f.value || "")
            .html() || '<span class="text-muted fst-italic">—</span>') +
          "</div>" +
          "</div>";
      });
      html += "</div>";
      $("#modal-fields").html(html);

      // ── Attachments with download buttons ──────────────────────────
      var filesHtml = "";
      if (files.length > 0) {
        filesHtml =
          '<hr><h6 class="mb-3"><i class="bi bi-paperclip"></i> Attachments (' +
          files.length +
          ")</h6>";

        // Add "Download All" button if multiple files
        if (files.length > 1) {
          filesHtml +=
            '<div class="mb-3">' +
            '<a href="' +
            BASE_URL +
            "admin/download_attachments.php?record_id=" +
            recordId +
            "&module=" +
            module +
            '" ' +
            'class="btn btn-sm btn-success"><i class="bi bi-download me-1"></i>Download All (' +
            files.length +
            ")</a>" +
            "</div>";
        }

        // Determine layout based on attachment count
        var isSingleFile = files.length === 1;
        var containerClass = isSingleFile
          ? "d-flex gap-2 align-items-center"
          : "d-flex flex-wrap gap-3";

        filesHtml += '<div class="' + containerClass + '">';
        $.each(files, function (i, f) {
          var url = BASE_URL + "user_uploads/" + f.path;
          var ext = (f.type || "").toLowerCase();
          var name = $("<span>")
            .text(f.name || f.path)
            .html();

          if (imageExts.indexOf(ext) !== -1) {
            filesHtml +=
              '<div class="text-center">' +
              '<div class="mb-2">' +
              '<a href="' +
              url +
              '" target="_blank">' +
              '<img src="' +
              url +
              '" style="height:120px;width:120px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;display:block;" title="' +
              name +
              '">' +
              "</a></div>" +
              '<a href="' +
              url +
              '" download class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>Download</a>' +
              "</div>";
          } else if (ext === "pdf") {
            filesHtml +=
              '<div class="d-flex flex-column align-items-start">' +
              '<a href="' +
              url +
              '" target="_blank" class="btn btn-sm btn-outline-danger mb-2"><i class="bi bi-file-pdf-fill me-1"></i>View PDF</a>' +
              '<a href="' +
              url +
              '" download class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>Download</a>' +
              "</div>";
          } else if (videoExts.indexOf(ext) !== -1) {
            filesHtml +=
              '<div class="d-flex flex-column align-items-start">' +
              '<a href="' +
              url +
              '" target="_blank" class="btn btn-sm btn-outline-primary mb-2"><i class="bi bi-camera-video-fill me-1"></i>View Video</a>' +
              '<a href="' +
              url +
              '" download class="btn btn-sm btn-outline-success"><i class="bi bi-download me-1"></i>Download</a>' +
              "</div>";
          } else if (audioExts.indexOf(ext) !== -1) {
            filesHtml +=
              '<div class="d-flex flex-column align-items-start">' +
              '<a href="' +
              url +
              '" target="_blank" class="btn btn-sm btn-outline-success mb-2"><i class="bi bi-music-note-beamed me-1"></i>Play Audio</a>' +
              '<a href="' +
              url +
              '" download class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>Download</a>' +
              "</div>";
          } else {
            filesHtml +=
              '<div class="d-flex flex-column align-items-start">' +
              '<a href="' +
              url +
              '" target="_blank" class="btn btn-sm btn-outline-secondary mb-2"><i class="bi bi-file-earmark me-1"></i>View File</a>' +
              '<a href="' +
              url +
              '" download class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>Download</a>' +
              "</div>";
          }
        });
        filesHtml += "</div>";
      }
      $("#modal-files").html(filesHtml);

      bootstrap.Modal.getOrCreateInstance(
        document.getElementById("viewModal"),
      ).show();
    });
});
