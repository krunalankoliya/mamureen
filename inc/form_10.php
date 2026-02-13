<?php
require_once(__DIR__ . '/../session.php');

// Check if form is submitted
if (isset($_POST['submit_form_10'])) {
    // Handle image upload if a file is selected
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $tempPath = $_FILES['file']['tmp_name'];
        $fileName = str_replace(' ', '_', $_FILES['file']['name']);
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileUploadName = $user_its . 'cr_asbaaq' . date('YmdHis') . '.' . $fileType;
        $uploadDir = __DIR__ . "/../user_uploads/";

        // Move uploaded file to user uploads directory
        $moved_file = move_uploaded_file($tempPath, $uploadDir . $fileUploadName);

        if (!$moved_file) {
            $errors[] = "Failed to move uploaded file.";
        }
    }

    // Process form data if no errors occurred during upload
    if (empty($errors)) {
        $a1 = isset($_POST['a1']) ? mysqli_real_escape_string($mysqli, $_POST['a1']) : '';

        // Check if a row already exists for the user ID
        $checkQuery = "SELECT * FROM `cr_asbaaq` WHERE `its_id` = '$user_its'";
        $checkResult = mysqli_query($mysqli, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            // Update existing row
            $updateQuery = "UPDATE `cr_asbaaq` SET `a1` = '$a1'";
            if (!empty($fileUploadName)) {
                $updateQuery .= ", `file` = '$fileUploadName'";
            }
            $updateQuery .= " WHERE `its_id` = '$user_its'";

            try {
                $result = mysqli_query($mysqli, $updateQuery);
                $message['text'] = "Asbaaq/Educational seminars updated successfully";
                $message['tag'] = 'success';
            } catch (Exception $e) {
                $message = array('text' => $e->getMessage(), 'tag' => 'danger');
            }
        } else {
            // Insert new row
            $insertQuery = "INSERT INTO `cr_asbaaq` (`its_id`, `a1`, `file`) VALUES ('$user_its', '$a1', '$fileUploadName')";

            try {
                $result = mysqli_query($mysqli, $insertQuery);
                $message['text'] = "Asbaaq/Educational seminars added successfully";
                $message['tag'] = 'success';
            } catch (Exception $e) {
                $message = array('text' => $e->getMessage(), 'tag' => 'danger');
            }
        }
    }
}

// Retrieve existing data for form pre-population
$query = "SELECT * FROM `cr_asbaaq` WHERE `its_id` = '$user_its'";
$fetchDataResult = mysqli_query($mysqli, $query);

// Initialize variables for form data
$a1 = '';
$file = '';

if ($fetchDataResult && mysqli_num_rows($fetchDataResult) > 0) {
    $rowData = mysqli_fetch_assoc($fetchDataResult);
    $a1 = isset($rowData['a1']) ? $rowData['a1'] : '';
    $file = isset($rowData['file']) ? $rowData['file'] : '';
}

// Handle file removal if requested
if (isset($_POST['remove_image']) && !empty($file)) {
    $filePath = __DIR__ . "/../user_uploads/$file";
    if (file_exists($filePath)) {
        unlink($filePath); // Delete the file
    }
    // Update database to clear the 'file' field
    $clearFileQuery = "UPDATE `cr_asbaaq` SET `file` = '' WHERE `its_id` = '$user_its'";
    mysqli_query($mysqli, $clearFileQuery);

    $file = ''; // Clear the file variable
}
?>
<style>
    /* CSS to set the max-height of the image */
    img {
        max-height: 300px;
        width: auto;
        height: auto;
    }
</style>
<div class="card" id="form_10">
    <div class="card-body">
        <h5 class="card-title">Asbaaq/Educational seminars</h5>
        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
        <ul>
            <li>All fields marked with <span class="required_star">*</span> are required.</li>
            <li>Each file should be less than 10 MB</li>
            <li>Mention N/A if no Answer</li>
        </ul>
        <form class="row g-3" method="post" enctype="multipart/form-data">
            <div class="col-12">
                <label for="f10a1" class="form-label">What different media were used to convey the paighaam of Maula TUS regarding higher education and migration?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a1" style="height: 120px" id="f10a1" required><?= htmlspecialchars($a1); ?></textarea>
            </div>
            <?php if (!empty($file)) : ?>
                <!-- Display uploaded image if $file is set -->
                <div class="col-md-12">
                    <img src="<?= MODULE_PATH . "user_uploads/" . htmlspecialchars($file); ?>" alt="Sabaq Image" />
                    <br />
                    <button type="submit" name="remove_image" class="btn btn-danger mt-2">Remove Image</button>
                </div>
            <?php else : ?>
                <!-- Display file upload field if $file is not set -->
                <div class="col-md-12">
                    <label for="file" class="form-label">You can upload Majhoodaat or Presentations of your seminars here.</label>
                    <input type="file" class="form-control file" accept="image/jpeg,image/jpg,image/png" name="file" id="file">
                </div>
            <?php endif; ?>
            <div class="text-center">
                <a href="?form_id=form_9" class="btn btn-outline-warning">Prev</a>
                <input type="submit" value="Submit" name="submit_form_10" class="btn btn-primary" />
                <input type="reset" value="Reset" name="reset_form_10" class="btn btn-outline-secondary" />
                <a href="?form_id=form_11" class="btn btn-outline-success">Skip</a>
            </div>
        </form>
    </div>
</div>