<?php
require_once(__DIR__ . '/../session.php');
$current_page = 'Manage_Resources';
require_once(__DIR__ . '/../inc/header.php');

if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $id = $_POST['id'];

    $query = "UPDATE `my_downloads` SET `title` = '$title' WHERE `id` = '$id'";
    try {
        $result = mysqli_query($mysqli, $query);
        $message['text'] = "Update Record Successfully";
        $message['tag'] = 'success';
    } catch (Exception $e) {
        $message = array('text' => $e->getMessage(), 'tag' => 'danger');
    }
}


// Traditional form submission is kept for backward compatibility
if (isset($_POST['submit']) && isset($_FILES['upload_photo']) && $_FILES['upload_photo']['error'] === 0) {
    /***************For Image Upload Code STRART*******************/

    $tempPath = $_FILES['upload_photo']["tmp_name"];
    $fileName = str_replace(' ', '_', $_FILES['upload_photo']['name']);
    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
    $fileUploadName = $fileName;
    $moved_file = move_uploaded_file($tempPath, __DIR__ . "/../uploads/" . $fileName);

    /***************For Image Upload Code END*******************/
    $title = $_POST['title'];
    $dot_color = $_POST['dot_color'];
    $date = date('Y-m-d');
    $upload_photo = $fileName;

    $query = "INSERT INTO `my_downloads` (`upload_date`, `title`, `file_name`, `dot_color`, `added_its`) VALUES ('$date', '$title', '$upload_photo', '$dot_color', '$user_its')";

    try {
        $result = mysqli_query($mysqli, $query);
        $message['text'] = "File Upload Successfully";
        $message['tag'] = 'success';
    } catch (Exception $e) {
        $message = array('text' => $e->getMessage(), 'tag' => 'danger');
    }
}


if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    // Fetch the file name before deleting
    $fetchQuery = "SELECT file_name FROM my_downloads WHERE id = '$id'";
    $fetchResult = mysqli_query($mysqli, $fetchQuery);
    $fileData = mysqli_fetch_assoc($fetchResult);
    
    if ($fileData) {
        $filePath = __DIR__ . "/../uploads/" . $fileData['file_name'];
        
        // Delete file from the upload folder
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete the database entry
        $deleteQuery = "DELETE FROM my_downloads WHERE id = '$id'";
        try {
            $result = mysqli_query($mysqli, $deleteQuery);
            $message['text'] = "File deleted successfully";
            $message['tag'] = 'success';
        } catch (Exception $e) {
            $message = array('text' => $e->getMessage(), 'tag' => 'danger');
        }
    } else {
        $message = array('text' => "File not fousnd", 'tag' => 'danger');
    }
}


$query = "SELECT * FROM `my_downloads`";
$result = mysqli_query($mysqli, $query);
$row = $result->fetch_all(MYSQLI_ASSOC);

$select = '<select id="inputState" class="form-select" name="dot_color" required>
                <option value="" disabled selected hidden>Select...</option>
                <option value="text-danger">Text Danger</option>
                <option value="text-success">Text Success</option>
                <option value="text-warning">Text Warning</option>
                <option value="text-info">Text Info</option>
            </select>';
?>

<main id="main" class="main">

    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/../inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">

                            <div class="card">
                                <div class="card-body pt-4">
                                    <h5 class="card-title">Upload Download Resource</h5>
                                    <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                                    <ul>
                                        <li>All fields marked with <span class="required_star">*</span> are required.</li>
                                    </ul>
                                    <!-- Horizontal Form -->
                                    <form method="post" id="addUserForm" enctype="multipart/form-data">
                                        <div class="row mb-3">
                                            <label for="inputTitle" class="col-sm-4 col-form-label">Title<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="inputTitle" name='title' required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="uploadFile" class="col-sm-4 col-form-label">Upload file<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="file" class="form-control file" name="upload_photo" id="uploadFile" required>
                                                <!-- Progress bar will be added here via JS -->
                                                <div id="progressContainer" class="mt-2 d-none">
                                                    <div class="progress">
                                                        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                                    </div>
                                                    <div class="mt-1 small text-muted">
                                                        <span id="uploadedSize">0</span> of <span id="totalSize">0</span> MB uploaded
                                                    </div>
                                                    <div id="uploadMessage" class="mt-2 text-center fw-bold text-primary d-none">
                                                        Upload in progress, please wait...
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="inputState" class="col-sm-4 col-form-label">Select Dot Color<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <?= $select ?>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-8"></div>
                                            <div class="col-md-2">
                                                <div class="text-center">
                                                    <div class="d-grid gap-2">
                                                        <input type="reset" value="Reset Form" name="reset" id="resetBtn" class="btn btn-outline-secondary" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="text-center">
                                                    <div class="d-grid gap-2">
                                                        <input type="button" value="Submit" name="submit" id="submitBtn" class="btn btn-primary" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form><!-- End Horizontal Form -->

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">Previously Uploaded Reports</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Upload date</th>
                                        <th scope="col">Title</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($row as $key => $data) {
                                    ?>
                                        <form method="post" enctype="multipart/form-data">
                                            <tr>
                                                <th scope="row"><?= $data['id'] ?></th>
                                                <td><?= $data['upload_date'] ?></td>
                                                <td>
                                                    <input type="text" name="title" class="form-control" placeholder="Expertise" value="<?= $data['title'] ?>" required />
                                                </td>
                                                <td>
                                                    <input type="hidden" name="id" value="<?= $data['id'] ?>" />
                                                    <input type="submit" name="update" value="Update" class="btn btn-outline-primary" />
                                                    <input type="submit" name="delete" value="Delete" class="btn btn-outline-danger" />
                                                </td>
                                            </tr>
                                        </form>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

</main><!-- End #main -->

<?php
require_once(__DIR__ . '/../inc/footer.php');
?>
<style>
.progress        { height: 25px; }
.progress-bar    { line-height: 25px; font-weight: bold; }
#uploadMessage   { font-size: 14px; }
</style>
<script src="../assets/js/manage-download.js"></script>