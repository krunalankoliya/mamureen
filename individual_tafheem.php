<?php
require_once(__DIR__ . '/session.php');
$current_page = 'individual_tafheem';

require_once(__DIR__ . '/inc/header.php');

// Handle Submit
if (isset($_POST['submit_tafheem'])) {
    $target_its_id = (int)$_POST['target_its_id'];
    $target_name = mysqli_real_escape_string($mysqli, $_POST['target_name']);
    $reason_id = (int)$_POST['reason_id'];
    $report_details = mysqli_real_escape_string($mysqli, $_POST['report_details']);

    if ($target_its_id <= 0 || empty($target_name) || $reason_id <= 0 || empty($report_details)) {
        $message = ['text' => 'All required fields must be filled.', 'tag' => 'danger'];
    } else {
        $query = "INSERT INTO `bqi_individual_tafheem` (`target_its_id`, `target_name`, `reason_id`, `report_details`, `user_its`, `jamaat`, `added_its`)
                  VALUES ('$target_its_id', '$target_name', '$reason_id', '$report_details', '$user_its', '$mauze', '$user_its')";
        try {
            mysqli_query($mysqli, $query);
            $record_id = mysqli_insert_id($mysqli);

            // Handle file uploads
            if (isset($_FILES['attachments'])) {
                $file_count = count($_FILES['attachments']['name']);
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileName = $_FILES['attachments']['name'][$i];
                        $tempPath = $_FILES['attachments']['tmp_name'][$i];
                        $fileSize = $_FILES['attachments']['size'][$i];
                        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                        $acceptable = ['jpeg', 'jpg', 'png'];

                        if ($fileSize > 4194304) continue; // Skip files > 4MB
                        if (!in_array(strtolower($fileType), $acceptable)) continue;

                        $uploadName = $user_its . '_tafheem_' . date('YmdHis') . '_' . $i . '.' . $fileType;
                        $moved = move_uploaded_file($tempPath, __DIR__ . "/user_uploads/" . $uploadName);

                        if ($moved) {
                            $esc_name = mysqli_real_escape_string($mysqli, $fileName);
                            $esc_type = mysqli_real_escape_string($mysqli, $fileType);
                            $attachQuery = "INSERT INTO `bqi_file_attachments` (`module`, `record_id`, `file_name`, `file_path`, `file_type`, `file_size`, `added_its`)
                                            VALUES ('tafheem', '$record_id', '$esc_name', '$uploadName', '$esc_type', '$fileSize', '$user_its')";
                            mysqli_query($mysqli, $attachQuery);
                        }
                    }
                }
            }

            $message = ['text' => 'Tafheem report submitted successfully.', 'tag' => 'success'];
            $show_popup = true;
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    }
}

// Handle Edit
if (isset($_POST['update_tafheem'])) {
    $edit_id = (int)$_POST['edit_id'];
    $reason_id = (int)$_POST['reason_id'];
    $report_details = mysqli_real_escape_string($mysqli, $_POST['report_details']);

    $query = "UPDATE `bqi_individual_tafheem` SET `reason_id` = '$reason_id', `report_details` = '$report_details',
              `update_its` = '$user_its', `update_ts` = NOW() WHERE `id` = '$edit_id' AND `added_its` = '$user_its'";
    try {
        mysqli_query($mysqli, $query);
        $message = ['text' => 'Report updated successfully.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

// Fetch Tafheem Reasons
$query = "SELECT *, CASE WHEN reason_name_ld IS NOT NULL AND reason_name_ld != '' THEN CONCAT(reason_name, ' / ', reason_name_ld) ELSE reason_name END AS display_name FROM `bqi_tafheem_reasons` WHERE `is_active` = 1 ORDER BY `sort_order`, `reason_name`";
$result = mysqli_query($mysqli, $query);
$reasons = $result->fetch_all(MYSQLI_ASSOC);

// Fetch user's submitted records
$query = "SELECT t.*, r.reason_name FROM `bqi_individual_tafheem` t
          LEFT JOIN `bqi_tafheem_reasons` r ON t.reason_id = r.id
          WHERE t.added_its = '$user_its'
          ORDER BY t.added_ts DESC";
$result = mysqli_query($mysqli, $query);
$tafheem_list = $result->fetch_all(MYSQLI_ASSOC);
?>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Individual Tafheem Report</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Submit New Report</h5>
                                    <form method="post" enctype="multipart/form-data" id="tafheemForm">

                                        <!-- ITS Verification -->
                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Target ITS ID<span class="required_star">*</span></label>
                                            <div class="col-sm-5">
                                                <input type="number" id="its_id_input" class="form-control" placeholder="Enter ITS ID" required>
                                            </div>
                                            <div class="col-sm-3">
                                                <button type="button" id="verifyBtn" class="btn btn-info btn-sm">Verify</button>
                                            </div>
                                        </div>

                                        <!-- Verified Info -->
                                        <div id="verifiedInfo" class="d-none mb-3">
                                            <div class="row">
                                                <div class="col-md-3 text-center">
                                                    <img id="itsPhoto" src="" class="rounded-circle" width="60" height="60" alt="Photo">
                                                </div>
                                                <div class="col-md-9">
                                                    <strong id="itsName"></strong><br>
                                                    <small id="itsDetails" class="text-muted"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="target_its_id" id="hid_target_its">
                                        <input type="hidden" name="target_name" id="hid_target_name">

                                        <!-- Reason Dropdown -->
                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Reason<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="reason_id" class="form-select" required>
                                                    <option value="" disabled selected>Select Reason...</option>
                                                    <?php foreach ($reasons as $r) : ?>
                                                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['display_name'] ?? $r['reason_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Report Details -->
                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Report Details<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <textarea name="report_details" class="form-control" style="height: 120px;" dir="auto" required></textarea>
                                            </div>
                                        </div>

                                        <!-- File Attachments -->
                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Attachments</label>
                                            <div class="col-sm-8">
                                                <input type="file" name="attachments[]" class="form-control" accept="image/*" multiple>
                                                <small class="text-muted">Optional. JPG/PNG, max 4MB each.</small>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-grid gap-2">
                                                    <input type="reset" value="Reset" class="btn btn-outline-secondary">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-grid gap-2">
                                                    <button type="submit" name="submit_tafheem" id="submitBtn" class="btn btn-primary" disabled>Submit Report</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Submitted Reports -->
                        <div class="col-md-6" style="overflow-x: auto;">
                            <h5 class="card-title">Your Submitted Reports</h5>
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Target ITS</th>
                                        <th>Target Name</th>
                                        <th>Reason</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tafheem_list as $key => $t) : ?>
                                        <tr>
                                            <td><?= $key + 1 ?></td>
                                            <td><?= $t['target_its_id'] ?></td>
                                            <td><?= htmlspecialchars($t['target_name']) ?></td>
                                            <td><?= htmlspecialchars($t['reason_name'] ?? 'N/A') ?></td>
                                            <td><?= date('d-M-Y', strtotime($t['added_ts'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info view-btn"
                                                    data-id="<?= $t['id'] ?>"
                                                    data-its="<?= $t['target_its_id'] ?>"
                                                    data-name="<?= htmlspecialchars($t['target_name']) ?>"
                                                    data-reason="<?= $t['reason_id'] ?>"
                                                    data-details="<?= htmlspecialchars($t['report_details']) ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editModal">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">View / Edit Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label"><strong>Target:</strong> <span id="edit_target_info"></span></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <select name="reason_id" id="edit_reason" class="form-select" required>
                            <?php foreach ($reasons as $r) : ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['display_name'] ?? $r['reason_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Report Details</label>
                        <textarea name="report_details" id="edit_details" class="form-control" style="height: 150px;" dir="auto" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_tafheem" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/inc/footer.php'); ?>
<script>
<?php if (!empty($show_popup)) : ?>
    alert('Tafheem report submitted successfully!');
<?php endif; ?>

// ITS Verification
$('#verifyBtn').click(function() {
    var its_id = $('#its_id_input').val();
    if (!its_id || its_id.length < 8) {
        alert('Please enter a valid ITS ID');
        return;
    }

    $(this).prop('disabled', true).text('Verifying...');

    $.ajax({
        url: '<?= MODULE_PATH ?>ajax_its_verify.php',
        type: 'POST',
        data: { its_id: its_id },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#itsPhoto').attr('src', res.photo_url);
                $('#itsName').text(res.full_name);
                $('#itsDetails').text(res.jamaat + ' | ' + res.gender);
                $('#verifiedInfo').removeClass('d-none');

                $('#hid_target_its').val(res.its_id);
                $('#hid_target_name').val(res.full_name);

                $('#submitBtn').prop('disabled', false);
            } else {
                alert(res.message || 'ITS ID not found');
                $('#verifiedInfo').addClass('d-none');
                $('#submitBtn').prop('disabled', true);
            }
        },
        error: function() {
            alert('Error verifying ITS ID. Please try again.');
        },
        complete: function() {
            $('#verifyBtn').prop('disabled', false).text('Verify');
        }
    });
});

// Edit Modal populate
$('.view-btn').click(function() {
    $('#edit_id').val($(this).data('id'));
    $('#edit_target_info').text($(this).data('name') + ' (' + $(this).data('its') + ')');
    $('#edit_reason').val($(this).data('reason'));
    $('#edit_details').val($(this).data('details'));
});

$(document).ready(function () {
    $('#datatable').DataTable({
        dom: 'Bfrtip',
        pageLength: 10,
        buttons: [
            { extend: 'copy', filename: function(){ return 'individual_tafheem-' + Date.now(); } },
            { extend: 'csv', filename: function(){ return 'individual_tafheem-' + Date.now(); } },
            { extend: 'excel', filename: function(){ return 'individual_tafheem-' + Date.now(); } }
        ]
    });
});
</script>
