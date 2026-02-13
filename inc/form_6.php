<?php
require_once(__DIR__ . '/../session.php');
$query = "SELECT * FROM cr_accommodation WHERE its_id = $user_its";
$fetchDataResult = mysqli_query($mysqli, $query);

// Initialize variables
$a1 = '';
$a2 = '';
$a3 = '';
$a4 = '';
$a5 = '';
$a6 = '';
$a7 = '';
$a8 = '';
$a9 = '';


if ($fetchDataResult) {
    if (mysqli_num_rows($fetchDataResult) > 0) {
        // Data found, populate form fields with existing data
        $rowData = mysqli_fetch_assoc($fetchDataResult);
        $a1 = isset($rowData['a1']) ? $rowData['a1'] : '';
        $a2 = isset($rowData['a2']) ? $rowData['a2'] : '';
        $a3 = isset($rowData['a3']) ? $rowData['a3'] : '';
        $a4 = isset($rowData['a4']) ? $rowData['a4'] : '';
        $a5 = isset($rowData['a5']) ? $rowData['a5'] : '';
        $a6 = isset($rowData['a6']) ? $rowData['a6'] : '';
        $a7 = isset($rowData['a7']) ? $rowData['a7'] : '';
        $a8 = isset($rowData['a8']) ? $rowData['a8'] : '';
        $a9 = $rowData['a9'];
    }
    // Free the result set
    mysqli_free_result($fetchDataResult);
}
?>
<div class="card" id="form_6">
    <div class="card-body">
        <h5 class="card-title">Accommodation Facility/Hostel</h5>
        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
        <ul>
            <li>All fields marked with <span class="required_star">*</span> are required.</li>
            <li>Mention N/A OR "0" if no Aswer</li>
        </ul>
        <form class="row g-3" method="post" enctype="multipart/form-data">
            <div class="col-12">
                <label class="form-label"><b>What measures are taken to establish a hostel/jamaat provided accommodation for migrated-in students?</b></label>
            </div>
            <div class="col-12">
                <label for="f6a1" class="form-label">Dedicated hostel committee<span class="required_star">*</span></label>
                <textarea class="form-control" name="a1" style="height: 120px" id="f6a1" required><?php echo $a1; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f6a2" class="form-label">Need analysis<span class="required_star">*</span></label>
                <textarea class="form-control" name="a2" style="height: 120px" id="f6a2" required><?php echo $a2; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f6a3" class="form-label">Cost analysis<span class="required_star">*</span></label>
                <textarea class="form-control" name="a3" style="height: 120px" id="f6a3" required><?php echo $a3; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f6a4" class="form-label">Location assessment<span class="required_star">*</span></label>
                <textarea class="form-control" name="a4" style="height: 120px" id="f6a4" required><?php echo $a4; ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label"><b>How would you describe the current living conditions of migrated-in students who live in - </b></label>
            </div>
            <div class="col-12">
                <label for="f6a5" class="form-label">Sharing rental w/ mumin<span class="required_star">*</span></label>
                <textarea class="form-control" name="a5" style="height: 120px" id="f6a5" required><?php echo $a5; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f6a6" class="form-label">Sharing rental w/non-mumin<span class="required_star">*</span></label>
                <textarea class="form-control" name="a6" style="height: 120px" id="f6a6" required><?php echo $a6; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f6a7" class="form-label">Individual rental<span class="required_star">*</span></label>
                <textarea class="form-control" name="a7" style="height: 120px" id="f6a7" required><?php echo $a7; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f6a8" class="form-label">Hostel<span class="required_star">*</span></label>
                <textarea class="form-control" name="a8" style="height: 120px" id="f6a8" required><?php echo $a8; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f6a9" class="form-label">How many students were shifted from non-recommended accommodation during shehrullah?<span class="required_star">*</span></label>
                <input class="form-control" type="number" id="f6a9" name="a9" value="<?php echo $a9; ?>" required>
            </div>
            <div class="text-center">
                <a href="?form_id=form_5" class="btn btn-outline-warning">Prev</a>
                <input type="submit" value="Submit" name="submit_form_6" class="btn btn-primary" />
                <input type="reset" value="Reset" name="reset_form_6" class="btn btn-outline-secondary" />
                <a href="?form_id=form_7" class="btn btn-outline-success">Skip</a>
            </div>
        </form>
    </div>
</div>