<?php
require_once(__DIR__ . '/../session.php');
$query = "SELECT * FROM cr_data_collection WHERE its_id = $user_its";
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

<div class="card" id="form_1">
    <div class="card-body">
        <h5 class="card-title">Data collection</h5>
        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
        <ul>
            <li>All fields marked with <span class="required_star">*</span> are required.</li>
            <li>Mention N/A if no Aswer</li>
        </ul>
        <form class="row g-3" method="post" enctype="multipart/form-data">
            <div class="col-12">
                <label for="f1a1" class="form-label">What challenges did you face in data collection - and how did you overcome them?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a1" style="height: 120px" id="f1a1" required><?php echo $a1; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f1a2" class="form-label">How can we achieve 100% data collection of Migrated Out students from your zone before Ashara Mubaraka 1446H?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a2" style="height: 120px" id="f1a2" required><?php echo $a2; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f1a3" class="form-label">Mention innovative strategies that you used for acquiring data of elusive students.<span class="required_star">*</span></label>
                <textarea class="form-control" name="a3" style="height: 120px" id="f1a3" required><?php echo $a3; ?></textarea>
            </div>
            <div class="text-center">
                <a href="<?= MODULE_PATH ?>closure-report.php" class="btn btn-outline-warning">Prev</a>
                <input type="submit" value="Submit" name="submit_form_1" class="btn btn-primary" />
                <input type="reset" value="Reset" name="reset_form_1" class="btn btn-outline-outline-secondary" />
                <a href="?form_id=form_2" class="btn btn-success">Skip</a>
            </div>
        </form>
    </div>
</div>