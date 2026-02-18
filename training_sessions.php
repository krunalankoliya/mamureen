<?php
require_once(__DIR__ . '/session.php');
$current_page = 'training_sessions';

require_once(__DIR__ . '/inc/header.php');

if (isset($_POST['submit'])) {
    $errors = array();
    $uploaded_file_count = 0;
    $uploadFileName1 = $uploadFileName2 = $uploadFileName3 = '';

    function prossesFile($fileTag, $user_its)
    {
        $maxsize    = 4194304;
        $acceptable = array('jpeg', 'jpg', 'png');
        $is_error = false;

        $fileName = $_FILES[$fileTag]['name'];
        $tempPath = $_FILES[$fileTag]["tmp_name"];
        $file_size = $_FILES[$fileTag]['size'];
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

        if (($file_size >= $maxsize)) {
            $is_error = true;
        }

        if ((!in_array(strtolower($fileType), $acceptable))) {
            $is_error = true;
        }

        if (!$is_error) {
            $fileUploadName = $user_its . $fileTag . date('YmdHis') . '.' . $fileType;
            $moved_file = move_uploaded_file($tempPath, __DIR__ . "/user_uploads/" . $fileUploadName);
            if (!$moved_file) {
                return false;
            }
            return $fileUploadName;
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

    if (empty($errors)) {
        $category = mysqli_real_escape_string($mysqli, $_POST['category']);
        $program_title = mysqli_real_escape_string($mysqli, $_POST['program_title']);
        $attendee_count = (int)$_POST['attendee_count'];

        $upload_photo_1 = $uploadFileName1;
        $upload_photo_2 = $uploadFileName2;
        $upload_photo_3 = $uploadFileName3;

        $query = "INSERT INTO `meeting_and_photo_reports` (`user_its`, `category`, `jamaat`, `upload_photo_1`, `upload_photo_2`, `upload_photo_3`, `uploaded_file_count`)
        VALUES ('$user_its', '$category - $program_title (Attendees: $attendee_count)', '$mauze', '$upload_photo_1', '$upload_photo_2', '$upload_photo_3', '$uploaded_file_count')";

        try {
            $result = mysqli_query($mysqli, $query);
            $message = ['text' => 'Training session report submitted successfully.', 'tag' => 'success'];
            $show_popup = true;
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    } else {
        $message = ['text' => implode('<br>', $errors), 'tag' => 'danger'];
    }
}

// Fetch existing training session reports
$training_categories = "'Zakereen Farzando Training%','Social Media Awareness%','Shaadi Tafheem%'";
$query = "SELECT * FROM `meeting_and_photo_reports` WHERE `jamaat` = '$mauze'
          AND (`category` LIKE 'Zakereen Farzando Training%' OR `category` LIKE 'Social Media Awareness%' OR `category` LIKE 'Shaadi Tafheem%')
          ORDER BY `id` DESC";
$result = mysqli_query($mysqli, $query);
$reports = $result->fetch_all(MYSQLI_ASSOC);
?>

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
                    </ul>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Submit New Report</h5>
                                    <form method="post" enctype="multipart/form-data">

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Program Type<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="category" class="form-select" required>
                                                    <option value="" disabled selected>Select Program Type...</option>
                                                    <option value="Zakereen Farzando Training">Zakereen Farzando Training</option>
                                                    <option value="Social Media Awareness">Social Media Awareness</option>
                                                    <option value="Shaadi Tafheem">Shaadi Tafheem</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Program Title<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="program_title" class="form-control" placeholder="Enter Program Title" required>
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

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-grid gap-2">
                                                    <input type="reset" value="Reset Form" class="btn btn-outline-secondary">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-grid gap-2">
                                                    <input type="submit" value="Submit" name="submit" class="btn btn-primary">
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
                                        <th>Program Details</th>
                                        <th>Uploads</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $key => $data) : ?>
                                        <tr>
                                            <td><?= $key + 1 ?></td>
                                            <td><?= htmlspecialchars($data['category']) ?></td>
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
<script>
<?php if (!empty($show_popup)) : ?>
    alert('Training session report submitted successfully!');
<?php endif; ?>

function validateFileSize() {
    var fileInputs = document.querySelectorAll('input[type="file"]');
    var maxSize = 4 * 1024 * 1024;
    for (var i = 0; i < fileInputs.length; i++) {
        if (fileInputs[i].files.length > 0 && fileInputs[i].files[0].size > maxSize) {
            alert('File "' + fileInputs[i].getAttribute('name') + '" must be less than 4 MB.');
            return false;
        }
    }
    return true;
}

document.querySelector('form').addEventListener('submit', function(event) {
    if (!validateFileSize()) {
        event.preventDefault();
    }
});

$(document).ready(function () {
    $('#datatable').DataTable({
        dom: 'Bfrtip',
        pageLength: 10,
        buttons: [
            { extend: 'copy', filename: function(){ return 'training_sessions-' + Date.now(); } },
            { extend: 'csv', filename: function(){ return 'training_sessions-' + Date.now(); } },
            { extend: 'excel', filename: function(){ return 'training_sessions-' + Date.now(); } }
        ]
    });
});
</script>
