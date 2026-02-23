<?php
require_once(__DIR__ . '/session.php');
$current_page = 'noteworthy_experiences';

require_once(__DIR__ . '/inc/header.php');

// Handle Submit
if (isset($_POST['submit_experience'])) {
    $category_id = (int)$_POST['category_id'];
    $experience_text = mysqli_real_escape_string($mysqli, $_POST['experience_text']);

    if ($category_id <= 0 || empty($experience_text)) {
        $message = ['text' => 'All required fields must be filled.', 'tag' => 'danger'];
    } else {
        $query = "INSERT INTO `bqi_noteworthy_experiences` (`category_id`, `experience_text`, `user_its`, `jamaat`, `added_its`)
                  VALUES ('$category_id', '$experience_text', '$user_its', '$mauze', '$user_its')";
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
                        $acceptable = ['jpeg', 'jpg', 'png', 'pdf', 'mp4', 'mov', 'avi', 'mp3', 'wav', 'ogg'];

                        if ($fileSize > 5242880) continue; // 5MB limit
                        if (!in_array(strtolower($fileType), $acceptable)) continue;

                        $uploadName = $user_its . '_experience_' . date('YmdHis') . '_' . $i . '.' . $fileType;
                        $moved = move_uploaded_file($tempPath, __DIR__ . "/user_uploads/" . $uploadName);

                        if ($moved) {
                            $esc_name = mysqli_real_escape_string($mysqli, $fileName);
                            $esc_type = mysqli_real_escape_string($mysqli, $fileType);
                            $attachQuery = "INSERT INTO `bqi_file_attachments` (`module`, `record_id`, `file_name`, `file_path`, `file_type`, `file_size`, `added_its`)
                                            VALUES ('experience', '$record_id', '$esc_name', '$uploadName', '$esc_type', '$fileSize', '$user_its')";
                            mysqli_query($mysqli, $attachQuery);
                        }
                    }
                }
            }

            $message = ['text' => 'Noteworthy experience submitted successfully.', 'tag' => 'success'];
            $show_popup = true;
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    }
}

// Handle Edit
if (isset($_POST['update_experience'])) {
    $edit_id = (int)$_POST['edit_id'];
    $category_id = (int)$_POST['category_id'];
    $experience_text = mysqli_real_escape_string($mysqli, $_POST['experience_text']);

    $query = "UPDATE `bqi_noteworthy_experiences` SET `category_id` = '$category_id', `experience_text` = '$experience_text',
              `update_its` = '$user_its', `update_ts` = NOW() WHERE `id` = '$edit_id' AND `added_its` = '$user_its'";
    try {
        mysqli_query($mysqli, $query);
        $message = ['text' => 'Experience updated successfully.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

// Fetch categories
$query = "SELECT * FROM `bqi_categories` WHERE `category_type` = 'experience' AND `is_active` = 1 ORDER BY `sort_order`, `category_name`";
$result = mysqli_query($mysqli, $query);
$categories = $result->fetch_all(MYSQLI_ASSOC);

// Fetch user's submitted records
$query = "SELECT ne.*, c.category_name FROM `bqi_noteworthy_experiences` ne
          LEFT JOIN `bqi_categories` c ON ne.category_id = c.id
          WHERE ne.added_its = '$user_its'
          ORDER BY ne.added_ts DESC";
$result = mysqli_query($mysqli, $query);
$records = $result->fetch_all(MYSQLI_ASSOC);
?>

<main id="main" class="main bqi-1447">
    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Noteworthy Experiences on Khidmat</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Submit New Experience</h5>
                                    <form method="post" enctype="multipart/form-data" id="uploadForm">

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Category<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="category_id" class="form-select" required>
                                                    <option value="" disabled selected>Select Category...</option>
                                                    <?php foreach ($categories as $c) : ?>
                                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Experience Details<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <textarea name="experience_text" class="form-control" style="height: 150px;" dir="auto" placeholder="Describe the noteworthy experience..." required></textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Attachments</label>
                                            <div class="col-sm-8">
                                                <input type="file" name="attachments[]" class="form-control" accept="image/*,video/*,audio/*,.pdf" multiple>
                                                <div class="alert alert-info mt-2 py-1 px-2 mb-0">
                                                    <small><strong>Allowed:</strong> Images (JPG, PNG), Videos (MP4, MOV, AVI), Audio (MP3, WAV, OGG), PDF<br>
                                                    <strong>Max file size: 5 MB per file</strong></small>
                                                </div>
                                                <div id="progressContainer" class="mt-2 d-none">
                                                    <div class="progress">
                                                        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                    </div>
                                                    <div id="uploadMessage" class="mt-2 text-center fw-bold d-none"></div>
                                                </div>
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
                                                    <button type="submit" name="submit_experience" id="submitBtn" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" style="overflow-x: auto;">
                            <h5 class="card-title">Your Submitted Experiences</h5>
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Category</th>
                                        <th>Experience</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $key => $r) : ?>
                                        <tr>
                                            <td><?= $key + 1 ?></td>
                                            <td><?= htmlspecialchars($r['category_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars(mb_substr($r['experience_text'], 0, 80)) ?>...</td>
                                            <td><?= date('d-M-Y', strtotime($r['added_ts'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info edit-btn"
                                                    data-id="<?= $r['id'] ?>"
                                                    data-category="<?= $r['category_id'] ?>"
                                                    data-experience="<?= htmlspecialchars($r['experience_text']) ?>"
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
                    <h5 class="modal-title">Edit Experience</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="edit_category" class="form-select" required>
                            <?php foreach ($categories as $c) : ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Experience Details</label>
                        <textarea name="experience_text" id="edit_experience" class="form-control" style="height: 180px;" dir="auto" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_experience" class="btn btn-primary">Update</button>
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
<script src="assets/js/noteworthy_experiences.js"></script>
