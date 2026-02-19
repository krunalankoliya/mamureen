<?php
require_once(__DIR__ . '/session.php');
$current_page = 'training_sessions';
require_once(__DIR__ . '/inc/header.php');

if (isset($_POST['submit'])) {
    $errors = [];
    $uploaded_file_count = 0;
    $uploadFileName1 = $uploadFileName2 = $uploadFileName3 = '';

    function prossesFile($fileTag, $user_its)
    {
        $maxsize    = 4194304;
        $acceptable = ['jpeg', 'jpg', 'png'];
        $is_error   = false;

        $fileName  = $_FILES[$fileTag]['name'];
        $tempPath  = $_FILES[$fileTag]['tmp_name'];
        $file_size = $_FILES[$fileTag]['size'];
        $fileType  = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($file_size >= $maxsize || !in_array(strtolower($fileType), $acceptable)) {
            $is_error = true;
        }

        if (!$is_error) {
            $fileUploadName = $user_its . $fileTag . date('YmdHis') . '.' . $fileType;
            $moved_file = move_uploaded_file($tempPath, __DIR__ . '/user_uploads/' . $fileUploadName);
            return $moved_file ? $fileUploadName : false;
        }
        return false;
    }

    if (isset($_FILES['upload_photo_1']) && $_FILES['upload_photo_1']['tmp_name'] !== '') {
        $uploadFileName1 = prossesFile('upload_photo_1', $user_its);
        if ($uploadFileName1) $uploaded_file_count++;
    }
    if (isset($_FILES['upload_photo_2']) && $_FILES['upload_photo_2']['tmp_name'] !== '') {
        $uploadFileName2 = prossesFile('upload_photo_2', $user_its);
        if ($uploadFileName2) $uploaded_file_count++;
    }
    if (isset($_FILES['upload_photo_3']) && $_FILES['upload_photo_3']['tmp_name'] !== '') {
        $uploadFileName3 = prossesFile('upload_photo_3', $user_its);
        if ($uploadFileName3) $uploaded_file_count++;
    }

    $selected_title_ids = $_POST['program_title_ids'] ?? [];
    if (empty($selected_title_ids)) {
        $errors[] = 'Please select at least one program title.';
    }
    if (empty($_POST['session_date'])) {
        $errors[] = 'Session date is required.';
    }
    if (empty($_POST['duration_minutes']) || (int)$_POST['duration_minutes'] < 1) {
        $errors[] = 'Duration (in minutes) is required and must be at least 1.';
    }
    if (empty($_POST['attendee_count']) || (int)$_POST['attendee_count'] < 1) {
        $errors[] = 'Attendee count is required.';
    }

    if (empty($errors)) {
        $program_type      = mysqli_real_escape_string($mysqli, $_POST['program_type']);
        $attendee_count    = (int)$_POST['attendee_count'];
        $session_date      = mysqli_real_escape_string($mysqli, $_POST['session_date']);
        $duration_minutes  = (int)$_POST['duration_minutes'];
        $program_title_ids = implode(',', array_map('intval', $selected_title_ids));
        $upload_photo_1    = mysqli_real_escape_string($mysqli, $uploadFileName1);
        $upload_photo_2    = mysqli_real_escape_string($mysqli, $uploadFileName2);
        $upload_photo_3    = mysqli_real_escape_string($mysqli, $uploadFileName3);

        $query = "INSERT INTO `bqi_training_sessions`
                    (`user_its`, `jamaat`, `program_type`, `program_title_ids`,
                     `session_date`, `duration_minutes`, `attendee_count`,
                     `upload_photo_1`, `upload_photo_2`, `upload_photo_3`, `uploaded_file_count`)
                  VALUES
                    ('$user_its', '$mauze', '$program_type', '$program_title_ids',
                     '$session_date', '$duration_minutes', '$attendee_count',
                     '$upload_photo_1', '$upload_photo_2', '$upload_photo_3', '$uploaded_file_count')";

        try {
            mysqli_query($mysqli, $query);
            $message    = ['text' => 'Training session report submitted successfully.', 'tag' => 'success'];
            $show_popup = true;
        } catch (\Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    } else {
        $message = ['text' => implode('<br>', $errors), 'tag' => 'danger'];
    }
}

// Fetch distinct active program types for the dropdown
$types_result  = mysqli_query($mysqli, "SELECT DISTINCT `program_type` FROM `bqi_program_titles` WHERE `is_active` = 1 AND `program_type` != '' ORDER BY `program_type`");
$program_types = $types_result->fetch_all(MYSQLI_ASSOC);

// Lookup map: id => title_name for display in reports table
$titles_map        = [];
$all_titles_result = mysqli_query($mysqli, "SELECT `id`, `title_name` FROM `bqi_program_titles` ORDER BY `id`");
while ($row = $all_titles_result->fetch_assoc()) {
    $titles_map[$row['id']] = $row['title_name'];
}

// Fetch this jamaat's training sessions
$reports = [];
$result  = mysqli_query($mysqli, "SELECT * FROM `bqi_training_sessions` WHERE `jamaat` = '$mauze' ORDER BY `id` DESC");
if ($result) {
    $reports = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<link rel="stylesheet" href="assets/css/training_sessions.css">
<main id="main" class="main bqi-1447">
    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Training Sessions / Seminars</h5>
                    <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                    <ul>
                        <li>Photos with extension .jpg, .jpeg or .png can only be uploaded</li>
                        <li>Maximum allowed photo size is 4 MB</li>
                        <li>All fields marked with <span class="required_star">*</span> are required.</li>
                        <li>You can select multiple program titles by holding <kbd>Ctrl</kbd> (Windows) or <kbd>Cmd</kbd> (Mac).</li>
                    </ul>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Submit New Report</h5>
                                    <form method="post" enctype="multipart/form-data" id="uploadForm" data-show-popup="<?php echo !empty($show_popup) ? '1' : '0' ?>">

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Program Type<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="program_type" id="program_type_select" class="form-select" required>
                                                    <option value="" disabled selected>Select Program Type...</option>
                                                    <?php foreach ($program_types as $pt): ?>
                                                        <option value="<?php echo htmlspecialchars($pt['program_type']) ?>"><?php echo htmlspecialchars($pt['program_type']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Program Title(s)<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <!-- Excel-style filter -->
                                                <div class="ts-filter-wrap" id="ts_filter_wrap">
                                                    <!-- Hidden inputs injected here by JS on checkbox change -->
                                                    <div id="ts_hidden_inputs"></div>
                                                    <!-- Trigger button -->
                                                    <button type="button" class="ts-filter-btn" id="ts_filter_btn">
                                                        <span id="ts_filter_label" class="text-muted">Select a Program Type first</span>
                                                        <i class="bi bi-caret-down-fill ts-caret"></i>
                                                    </button>
                                                    <!-- Dropdown panel -->
                                                    <div class="ts-filter-panel" id="ts_filter_panel">
                                                        <div class="ts-filter-search">
                                                            <input type="text" id="ts_search_input" placeholder="Search titles..." autocomplete="off">
                                                        </div>
                                                        <div class="ts-filter-actions">
                                                            <a href="#" id="ts_select_all">Select All</a>
                                                            <span class="ts-sep">|</span>
                                                            <a href="#" id="ts_clear_all">Clear</a>
                                                        </div>
                                                        <div class="ts-filter-list" id="ts_filter_list">
                                                            <div class="ts-placeholder">Select a Program Type first</div>
                                                        </div>
                                                    </div>
                                                    <div id="no_titles_msg" class="text-warning mt-1 small" style="display:none;">No titles found for this program type.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Session Date<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="date" name="session_date" class="form-control" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Duration (minutes)<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="number" name="duration_minutes" class="form-control" placeholder="e.g. 60" min="1" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Attendee Count<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="number" name="attendee_count" class="form-control" placeholder="Number of Attendees" min="1" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Upload Photo 1<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="file" class="form-control" accept="image/*" name="upload_photo_1" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Upload Photo 2</label>
                                            <div class="col-sm-8">
                                                <input type="file" class="form-control" accept="image/*" name="upload_photo_2">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Upload Photo 3</label>
                                            <div class="col-sm-8">
                                                <input type="file" class="form-control" accept="image/*" name="upload_photo_3">
                                            </div>
                                        </div>

                                        <div id="progressContainer" class="d-none mb-3">
                                            <div class="progress">
                                                <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                            </div>
                                            <div id="uploadMessage" class="mt-2 text-center fw-bold d-none"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-grid gap-2">
                                                    <input type="reset" value="Reset Form" class="btn btn-outline-secondary">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-grid gap-2">
                                                    <input type="submit" value="Submit" name="submit" id="submitBtn" class="btn btn-primary">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" style="overflow-x: auto;">
                            <h5 class="card-title">Previously Submitted Reports</h5>
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Program Type</th>
                                        <th>Program Title(s)</th>
                                        <th>Session Date</th>
                                        <th>Duration (min)</th>
                                        <th>Attendees</th>
                                        <th>Uploads</th>
                                        <th>Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $key => $data): ?>
                                        <tr>
                                            <td><?= $key + 1 ?></td>
                                            <td><?= htmlspecialchars($data['program_type']) ?></td>
                                            <td>
                                                <?php
                                                if (!empty($data['program_title_ids'])) {
                                                    $ids   = explode(',', $data['program_title_ids']);
                                                    $names = [];
                                                    foreach ($ids as $id) {
                                                        $names[] = htmlspecialchars($titles_map[(int)$id] ?? '#' . $id);
                                                    }
                                                    echo implode(', ', $names);
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td><?= date('d-M-Y', strtotime($data['session_date'])) ?></td>
                                            <td><?= $data['duration_minutes'] ?></td>
                                            <td><?= $data['attendee_count'] ?></td>
                                            <td><?= $data['uploaded_file_count'] ?></td>
                                            <td><?= date('d-M-Y', strtotime($data['added_ts'])) ?></td>
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

<?php require_once(__DIR__ . '/inc/footer.php'); ?>
<style>
.progress     { height: 25px; }
.progress-bar { line-height: 25px; font-weight: bold; }
#uploadMessage { font-size: 14px; }
</style>
<script src="assets/js/training_sessions.js"></script>
