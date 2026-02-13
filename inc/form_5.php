<?php
require_once(__DIR__ . '/../session.php');
$query = "SELECT * FROM cr_muraqabat WHERE its_id = $user_its";
$fetchDataResult = mysqli_query($mysqli, $query);

// Initialize variables
$a1 = '';
$a2 = '';
if ($fetchDataResult) {
    if (mysqli_num_rows($fetchDataResult) > 0) {
        // Data found, populate form fields with existing data
        $rowData = mysqli_fetch_assoc($fetchDataResult);
        $a1 = isset($rowData['a1']) ? $rowData['a1'] : '';
        $a2 = isset($rowData['a2']) ? $rowData['a2'] : '';
    }
    // Free the result set
    mysqli_free_result($fetchDataResult);
}
?>
<div class="card" id="form_5">
    <div class="card-body">
        <h5 class="card-title">Muraqabat</h5>
        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
        <ul>
            <li>All fields marked with <span class="required_star">*</span> are required.</li>
            <li>Mention N/A if no Aswer</li>
        </ul>
        <form class="row g-3" method="post" enctype="multipart/form-data">
            <div class="col-12">
                <label for="f5a1" class="form-label">What challenges did you face while identifying and tagging 'muraqibeen' to students, and how did you overcome them?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a1" style="height: 120px" id="f5a1" required><?php echo $a1; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f5a2" class="form-label">What aspects were covered in the 'Muraqibeen training' during shehrullah?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a2" style="height: 120px" id="f5a2" required><?php echo $a2; ?></textarea>
            </div>
            <div class="text-center">
                <a href="?form_id=form_4" class="btn btn-outline-warning">Prev</a>
                <input type="submit" value="Submit" name="submit_form_5" class="btn btn-primary" />
                <input type="reset" value="Reset" name="reset_form_5" class="btn btn-outline-secondary" />
                <a href="?form_id=form_6" class="btn btn-outline-success">Skip</a>
            </div>
        </form>
    </div>
</div>