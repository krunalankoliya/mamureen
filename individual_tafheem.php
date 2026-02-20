<?php
require_once(__DIR__ . '/session.php');
$current_page = 'individual_tafheem';

require_once(__DIR__ . '/inc/header.php');

$program_type_options = [
    'Zakereen Farzando Training',
    'Social Media Awareness',
    'Shaadi Tafheem',
];

// Initialize variables
$verified_user = null;

// Function to fetch user data from API (server-side)
function fetchUserDataByITS_tafheem($its_id)
{
    if (empty($its_id)) {
        return null;
    }

    $user_its = $_COOKIE['user_its'] ?? '';
    $ver      = $_COOKIE['ver'] ?? '';

    if (empty($user_its) || empty($ver)) {
        return null;
    }

    $api_url = "https://www.talabulilm.com/api2022/core/user/getUserDetailsByItsID/" . urlencode($its_id);
    $auth    = base64_encode("$user_its:$ver");
    $headers = ["Authorization: Basic $auth"];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RESOLVE, ['www.talabulilm.com:443:66.85.132.227']);
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error || empty($response)) {
        return null;
    }

    $data = json_decode($response, true);
    if (empty($data) || !isset($data['its_id'])) {
        return null;
    }

    return $data;
}

// Handle Verify ITS (server-side)
if (isset($_POST['verify_its'])) {
    $verify_its_id = (int) $_POST['verify_its_id'];

    if ($verify_its_id <= 0 || strlen((string)$verify_its_id) < 8) {
        $message = ['text' => 'Please enter a valid 8-digit ITS ID.', 'tag' => 'danger'];
    } else {
        $verified_user = fetchUserDataByITS_tafheem($verify_its_id);
        if (!$verified_user) {
            $message = ['text' => "ITS ID $verify_its_id not found. Please check and try again.", 'tag' => 'danger'];
        }
    }
}

// Handle Submit
if (isset($_POST['submit_tafheem'])) {
    $target_its_id       = (int)$_POST['target_its_id'];
    $target_name         = mysqli_real_escape_string($mysqli, $_POST['target_name']);
    $type                = mysqli_real_escape_string($mysqli, $_POST['type'] ?? '');
    $selected_reason_ids = $_POST['reason_ids'] ?? [];
    $reason_ids_str      = implode(',', array_map('intval', $selected_reason_ids));
    $report_details      = mysqli_real_escape_string($mysqli, $_POST['report_details']);

    if ($target_its_id <= 0 || empty($target_name) || empty($type) || empty($selected_reason_ids) || empty($report_details)) {
        $message = ['text' => 'All required fields must be filled.', 'tag' => 'danger'];
    } else {
        $query = "INSERT INTO `bqi_individual_tafheem` (`target_its_id`, `target_name`, `type`, `reason_id`, `report_details`, `user_its`, `jamaat`, `added_its`)
                  VALUES ('$target_its_id', '$target_name', '$type', '$reason_ids_str', '$report_details', '$user_its', '$mauze', '$user_its')";
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
    $edit_id             = (int)$_POST['edit_id'];
    $type                = mysqli_real_escape_string($mysqli, $_POST['type'] ?? '');
    $selected_reason_ids = $_POST['reason_ids'] ?? [];
    $reason_ids_str      = implode(',', array_map('intval', $selected_reason_ids));
    $report_details      = mysqli_real_escape_string($mysqli, $_POST['report_details']);

    $query = "UPDATE `bqi_individual_tafheem` SET `type` = '$type', `reason_id` = '$reason_ids_str', `report_details` = '$report_details',
              `update_its` = '$user_its', `update_ts` = NOW() WHERE `id` = '$edit_id' AND `added_its` = '$user_its'";
    try {
        mysqli_query($mysqli, $query);
        $message = ['text' => 'Report updated successfully.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

// Fetch all active Tafheem Reasons (for edit modal multi-select)
$query   = "SELECT * FROM `bqi_tafheem_reasons` WHERE `is_active` = 1 ORDER BY `sort_order`, `reason_name`";
$result  = mysqli_query($mysqli, $query);
$reasons = $result->fetch_all(MYSQLI_ASSOC);

// Build reasons map for display (id => reason_name)
$reasons_map = array_column($reasons, 'reason_name', 'id');

// Fetch user's submitted records
$query = "SELECT * FROM `bqi_individual_tafheem` WHERE `added_its` = '$user_its' ORDER BY `added_ts` DESC";
$result = mysqli_query($mysqli, $query);
$tafheem_list = $result->fetch_all(MYSQLI_ASSOC);
?>

<link rel="stylesheet" href="assets/css/training_sessions.css">
<main id="main" class="main bqi-1447">
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

                                    <?php if ($verified_user): ?>
                                        <!-- ITS Verified - Show details and report form -->
                                        <div class="alert alert-success py-2">
                                            <strong>ITS Verified Successfully</strong>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3 text-center">
                                                <img src="https://www.talabulilm.com/mumin_images/<?php echo htmlspecialchars($verified_user['its_id']) ?>.png"
                                                     class="rounded-circle" width="80" height="80"
                                                     alt="<?php echo htmlspecialchars($verified_user['full_name_en']) ?>">
                                            </div>
                                            <div class="col-md-9">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tr><td><strong>ITS ID:</strong></td><td><?php echo htmlspecialchars($verified_user['its_id']) ?></td></tr>
                                                    <tr><td><strong>Name:</strong></td><td><?php echo htmlspecialchars($verified_user['full_name_en']) ?></td></tr>
                                                    <tr><td><strong>Gender:</strong></td><td><?php echo htmlspecialchars($verified_user['gender'] ?? '') ?></td></tr>
                                                    <tr><td><strong>Jamaat:</strong></td><td><?php echo htmlspecialchars($verified_user['jamaat'] ?? '') ?></td></tr>
                                                </table>
                                            </div>
                                        </div>

                                        <form method="post" enctype="multipart/form-data" id="uploadForm">
                                            <input type="hidden" name="target_its_id" value="<?php echo htmlspecialchars($verified_user['its_id']) ?>">
                                            <input type="hidden" name="target_name" value="<?php echo htmlspecialchars($verified_user['full_name_en']) ?>">

                                            <!-- Type Dropdown -->
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label">Type<span class="required_star">*</span></label>
                                                <div class="col-sm-8">
                                                    <select name="type" id="tf_type_select" class="form-select" required>
                                                        <option value="" disabled selected>Select Type...</option>
                                                        <?php foreach ($program_type_options as $opt): ?>
                                                            <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Reason Multiselect (Excel-style) -->
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label">Reason(s)<span class="required_star">*</span></label>
                                                <div class="col-sm-8">
                                                    <div class="ts-filter-wrap" id="tf_filter_wrap">
                                                        <div id="tf_hidden_inputs"></div>
                                                        <button type="button" class="ts-filter-btn" id="tf_filter_btn">
                                                            <span id="tf_filter_label" class="text-muted">Select a Type first</span>
                                                            <i class="bi bi-caret-down-fill ts-caret"></i>
                                                        </button>
                                                        <div class="ts-filter-panel" id="tf_filter_panel">
                                                            <div class="ts-filter-search">
                                                                <input type="text" id="tf_search_input" placeholder="Search reasons..." autocomplete="off">
                                                            </div>
                                                            <div class="ts-filter-actions">
                                                                <a href="#" id="tf_select_all">Select All</a>
                                                                <span class="ts-sep">|</span>
                                                                <a href="#" id="tf_clear_all">Clear</a>
                                                            </div>
                                                            <div class="ts-filter-list" id="tf_filter_list">
                                                                <div class="ts-placeholder">Select a Type first</div>
                                                            </div>
                                                        </div>
                                                    </div>
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
                                                    <div id="progressContainer" class="mt-2 d-none">
                                                        <div class="progress">
                                                            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                        </div>
                                                        <div id="uploadMessage" class="mt-2 text-center fw-bold d-none"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary">Cancel</a>
                                                <button type="submit" name="submit_tafheem" id="submitBtn" class="btn btn-primary">Submit Report</button>
                                            </div>
                                        </form>

                                    <?php else: ?>
                                        <!-- Step 1: Enter ITS ID and Verify -->
                                        <form method="post">
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label">Target ITS ID<span class="required_star">*</span></label>
                                                <div class="col-sm-5">
                                                    <input type="number" name="verify_its_id" class="form-control" placeholder="Enter ITS ID" required>
                                                </div>
                                                <div class="col-sm-3">
                                                    <button type="submit" name="verify_its" class="btn btn-info btn-sm">Verify</button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php endif; ?>

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
                                        <th>Type</th>
                                        <th>Reason(s)</th>
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
                                            <td><?= htmlspecialchars($t['type'] ?? '') ?></td>
                                            <td>
                                                <?php
                                                    $ids   = array_filter(array_map('intval', explode(',', $t['reason_id'])));
                                                    $names = [];
                                                    foreach ($ids as $rid) {
                                                        $names[] = htmlspecialchars($reasons_map[$rid] ?? '#' . $rid);
                                                    }
                                                    echo $names ? implode(', ', $names) : 'N/A';
                                                ?>
                                            </td>
                                            <td><?= date('d-M-Y', strtotime($t['added_ts'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info view-btn"
                                                    data-id="<?= $t['id'] ?>"
                                                    data-its="<?= $t['target_its_id'] ?>"
                                                    data-name="<?= htmlspecialchars($t['target_name']) ?>"
                                                    data-type="<?= htmlspecialchars($t['type'] ?? '') ?>"
                                                    data-reason="<?= htmlspecialchars($t['reason_id']) ?>"
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
                        <label class="form-label">Type</label>
                        <select name="type" id="edit_type" class="form-select" required>
                            <option value="" disabled>Select Type...</option>
                            <?php foreach ($program_type_options as $opt): ?>
                                <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason(s)</label>
                        <select name="reason_ids[]" id="edit_reason" class="form-select" multiple required style="height: 160px;">
                            <?php foreach ($reasons as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['reason_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl / Cmd to select multiple.</small>
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
<style>
.progress     { height: 25px; }
.progress-bar { line-height: 25px; font-weight: bold; }
#uploadMessage { font-size: 14px; }
</style>
<script src="assets/js/individual_tafheem.js"></script>
