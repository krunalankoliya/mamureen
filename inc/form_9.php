<?php
require_once(__DIR__ . '/../session.php');
$query = "SELECT * FROM cr_imani_schools WHERE its_id = $user_its";
$fetchDataResult = mysqli_query($mysqli, $query);

// Initialize variables
$a1 = '';


if ($fetchDataResult) {
    if (mysqli_num_rows($fetchDataResult) > 0) {
        // Data found, populate form fields with existing data
        $rowData = mysqli_fetch_assoc($fetchDataResult);
        $a1 = isset($rowData['a1']) ? $rowData['a1'] : '';
    }
    // Free the result set
    mysqli_free_result($fetchDataResult);
}
?>
<div class="card" id="form_9">
    <div class="card-body">
        <h5 class="card-title">Imani Schools</h5>
        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
        <ul>
            <li>All fields marked with <span class="required_star">*</span> are required.</li>
            <li>Mention N/A OR "0" if no Aswer</li>
        </ul>
        <form class="row g-3" method="post" enctype="multipart/form-data">
            <div class="col-12">
                <label for="f9a1" class="form-label">How many children were granted admission in Imani Schools during Shehrullah as a result of BQI program? <span class="required_star">*</span></label>
                <input class="form-control" type="number" id="f8a1" name="a1" value="<?= $a1; ?>" required>
            </div>
            <div class="text-center">
                <a href="?form_id=form_8" class="btn btn-outline-warning">Prev</a>
                <input type="submit" value="Submit" name="submit_form_9" class="btn btn-primary" />
                <input type="reset" value="Reset" name="reset_form_9" class="btn btn-outline-secondary" />
                <a href="?form_id=form_10" class="btn btn-outline-success">Skip</a>
            </div>
        </form>
    </div>
</div>