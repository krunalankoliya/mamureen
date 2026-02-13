<?php
require_once(__DIR__ . '/../session.php');
$query = "SELECT * FROM cr_migrated_in WHERE its_id = $user_its";
$fetchDataResult = mysqli_query($mysqli, $query);

// Initialize variables
$a1 = '';
$a2 = '';
$a3 = '';

if ($fetchDataResult) {
    if (mysqli_num_rows($fetchDataResult) > 0) {
        // Data found, populate form fields with existing data
        $rowData = mysqli_fetch_assoc($fetchDataResult);
        $a1 = isset($rowData['a1']) ? $rowData['a1'] : '';
        $a2 = isset($rowData['a2']) ? $rowData['a2'] : '';
        $a3 = isset($rowData['a3']) ? $rowData['a3'] : '';
    }
    // Free the result set
    mysqli_free_result($fetchDataResult);
}
?>
<div class="card" id="form_2">
    <div class="card-body">
        <h5 class="card-title">House visits (Migrated In)</h5>
        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
        <ul>
            <li>All fields marked with <span class="required_star">*</span> are required.</li>
            <li>Mention N/A if no Aswer</li>
        </ul>
        <form class="row g-3" method="post" enctype="multipart/form-data">
            <div class="col-12">
                <label for="f2a1" class="form-label">What challenges did you face while conducting house visits - and how did you overcome them?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a1" style="height: 120px" id="f2a1" required><?php echo $a1; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f2a2" class="form-label">How can we complete house visits of all remaining migrated students before Ashara Mubaraka 1446H?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a2" style="height: 120px" id="f2a2" required><?php echo $a2; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f2a3" class="form-label">What Silat/Hadiya were presented to students by local jamaat?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a3" style="height: 120px" id="f2a3" required><?php echo $a3; ?></textarea>
            </div>
            <div class="text-center">
                <a href="?form_id=form_1" class="btn btn-outline-warning">Prev</a>
                <input type="submit" value="Submit" name="submit_form_2" class="btn btn-primary" />
                <input type="reset" value="Reset" name="reset_form_2" class="btn btn-outline-secondary" />
                <a href="?form_id=form_3" class="btn btn-outline-success">Skip</a>
            </div>
        </form>
    </div>
</div>