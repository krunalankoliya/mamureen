<?php
require_once(__DIR__ . '/../session.php');
$query = "SELECT * FROM cr_asbaaq WHERE its_id = $user_its";
$fetchDataResult = mysqli_query($mysqli, $query);

// Initialize variables
$a1 = '';
$file = '';

if ($fetchDataResult) {
    if (mysqli_num_rows($fetchDataResult) > 0) {
        // Data found, populate form fields with existing data
        $rowData = mysqli_fetch_assoc($fetchDataResult);
        $a1 = isset($rowData['a1']) ? $rowData['a1'] : '';
        $file = isset($rowData['file']) ? $rowData['file'] : '';
    }
    // Free the result set
    mysqli_free_result($fetchDataResult);
}

// Handle file removal if requested
if (isset($_POST['remove_image']) && !empty($file)) {
    // Remove the file from the server
    $filePath = __DIR__ . "/../user_uploads/$file";
    if (file_exists($filePath)) {
        unlink($filePath); // Delete the file
    }
    $file = ''; // Clear the file variable
}
?>
<style>
    /* CSS to set the max-height of the image */
    img {
        max-height: 300px;
        width: auto;
        /* Ensure the image maintains its aspect ratio */
        height: auto;
        /* Allow the image to scale proportionally */
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
                <textarea class="form-control" name="a1" style="height: 120px" id="f10a1" required><?= $a1; ?></textarea>
            </div>
            <?php if (!empty($file)) : ?>
                <!-- Display uploaded image if $file is set -->
                <div class="col-md-12">
                    <img src="<?= MODULE_PATH . "user_uploads/" . $file; ?>" alt="Sabaq Image" />
                    <br />
                    <button type="submit" name="remove_image" class="btn btn-danger mt-2">Remove Image</button>
                </div>
            <?php else : ?>
                <!-- Display file upload field if $file is not set -->
                <div class="col-md-12">
                    <label for="file" class="form-label"> You can upload Majhoodaat or Presentations of your seminars here. <span class="required_star">*</span></label>
                    <input type="file" class="form-control file" accept="image/jpeg,image/jpg,image/png" name="file" id="file" required>
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