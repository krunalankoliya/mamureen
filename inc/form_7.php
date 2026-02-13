<?php
require_once(__DIR__ . '/../session.php');
$query = "SELECT * FROM cr_ut_committee WHERE its_id = $user_its";
$fetchDataResult = mysqli_query($mysqli, $query);

// Initialize variables
$a1 = '';
$a2 = '';
$a3 = '';
$a4 = '';
$a5 = '';


if ($fetchDataResult) {
    if (mysqli_num_rows($fetchDataResult) > 0) {
        // Data found, populate form fields with existing data
        $rowData = mysqli_fetch_assoc($fetchDataResult);
        $a1 = isset($rowData['a1']) ? $rowData['a1'] : '';
        $a2 = isset($rowData['a2']) ? $rowData['a2'] : '';
        $a3 = isset($rowData['a3']) ? $rowData['a3'] : '';
        $a4 = isset($rowData['a4']) ? $rowData['a4'] : '';
        $a5 = isset($rowData['a5']) ? $rowData['a5'] : '';
    }
    // Free the result set
    mysqli_free_result($fetchDataResult);
}
?>
<div class="card" id="form_7">
    <div class="card-body">
        <h5 class="card-title">Umoor Taalimiyah Committee</h5>
        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
        <ul>
            <li>All fields marked with <span class="required_star">*</span> are required.</li>
        </ul>
        <form class="row g-3" method="post" enctype="multipart/form-data">
            <div class="col-12">
                <label for="f7a1" class="form-label">What was the role of UT Coordinator/ Higher Education Team lead in BQI activities?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a1" style="height: 120px" id="f7a1" required><?php echo $a1; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f7a2" class="form-label">Mention the names of different local committees and their contribution towards BQI activities.<span class="required_star">*</span></label>
                <textarea class="form-control" name="a2" style="height: 120px" id="f7a2" required><?php echo $a2; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f7a3" class="form-label">What are your observations regarding the overall working of umoor taalimiyah committee in your mawze?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a3" style="height: 120px" id="f7a3" required><?php echo $a3; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f7a4" class="form-label">What preparations were done by Umoor Taalimiyah and Umaal Kiraam for BQI tasks - before shehrullah?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a4" style="height: 120px" id="f7a4" required><?php echo $a4; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f7a5" class="form-label">Did you notice any substantial growth/development in the working of Umoor Taalimiyah committee as a result of BQI project?<span class="required_star">*</span></label>
                <textarea class="form-control" name="a5" style="height: 120px" id="f7a5" required><?php echo $a5; ?></textarea>
            </div>
            <div class="text-center">
                <a href="?form_id=form_6" class="btn btn-outline-warning">Prev</a>
                <input type="submit" value="Submit" name="submit_form_7" class="btn btn-primary" />
                <input type="reset" value="Reset" name="reset_form_7" class="btn btn-outline-secondary" />
                <a href="?form_id=form_8" class="btn btn-outline-success">Skip</a>
            </div>
        </form>
    </div>
</div>