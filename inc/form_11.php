<?php
require_once(__DIR__ . '/../session.php');
$query = "SELECT * FROM cr_trends WHERE its_id = $user_its";
$fetchDataResult = mysqli_query($mysqli, $query);

// Initialize variables
$a1 = '';
$a2 = '';
$a3 = '';
$a4 = '';


if ($fetchDataResult) {
    if (mysqli_num_rows($fetchDataResult) > 0) {
        // Data found, populate form fields with existing data
        $rowData = mysqli_fetch_assoc($fetchDataResult);
        $a1 = isset($rowData['a1']) ? $rowData['a1'] : '';
        $a2 = isset($rowData['a2']) ? $rowData['a2'] : '';
        $a3 = isset($rowData['a3']) ? $rowData['a3'] : '';
        $a4 = isset($rowData['a4']) ? $rowData['a4'] : '';
    }
    // Free the result set
    mysqli_free_result($fetchDataResult);
}
?>
<div class="card" id="form_11">
    <div class="card-body">
        <h5 class="card-title">Trends</h5>
        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
        <ul>
            <li>All fields marked with <span class="required_star">*</span> are required.</li>
            <li>Mention N/A if no Aswer</li>
        </ul>
        <form class="row g-3" method="post" enctype="multipart/form-data">
            <div class="col-12">
                <label for="f11a1" class="form-label">What are the most preferred cities for students migrating out from your city?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a1" style="height: 120px" id="f11a1" required><?php echo $a1; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f11a2" class="form-label">Where do most migrated-in students in your city come from?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a2" style="height: 120px" id="f11a2" required><?php echo $a2; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f11a3" class="form-label">What are the most popular courses/careers in your city?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a3" style="height: 120px" id="f11a3" required><?php echo $a3; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f11a4" class="form-label">Which courses/career options offer great opportunities in your city?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a4" style="height: 120px" id="f11a4" required><?php echo $a4; ?></textarea>
            </div>
            <div class="text-center">
                <a href="?form_id=form_10" class="btn btn-outline-warning">Prev</a>
                <input type="submit" value="Submit" name="submit_form_11" class="btn btn-primary" />
                <input type="reset" value="Reset" name="reset_form_11" class="btn btn-outline-secondary" />
            </div>
        </form>
    </div>
</div>