<?php
require_once(__DIR__ . '/../session.php');
$query = "SELECT * FROM cr_fmb_thaali WHERE its_id = $user_its";
$fetchDataResult = mysqli_query($mysqli, $query);

// Initialize variables
$challenges = '';
$other = '';
$receive_fmb_thaali = '';
$started_fmb_thaali = '';
$sabeel = 0;
$affordability = 0;
$taste = 0;
$transport = 0;
$timings = 0;

if ($fetchDataResult) {
    if (mysqli_num_rows($fetchDataResult) > 0) {
        // Data found, populate form fields with existing data
        $rowData = mysqli_fetch_assoc($fetchDataResult);
        $challenges = isset($rowData['challenges']) ? $rowData['challenges'] : '';
        $other = isset($rowData['other']) ? $rowData['other'] : '';
        $receive_fmb_thaali = isset($rowData['receive_fmb_thaali']) ? $rowData['receive_fmb_thaali'] : '';
        $started_fmb_thaali = isset($rowData['started_fmb_thaali']) ? $rowData['started_fmb_thaali'] : '';
        $sabeel =  $rowData['sabeel'];
        $affordability =  $rowData['affordability'];
        $taste =  $rowData['taste'];
        $transport =  $rowData['transport'];
        $timings =  $rowData['timings'];
    }
    // Free the result set
    mysqli_free_result($fetchDataResult);
}
?>
<div class="card" id="form_4">
    <div class="card-body">
        <h5 class="card-title">FMB Thaali</h5>
        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
        <ul>
            <li>All fields marked with <span class="required_star">*</span> are required.</li>
            <li>Mention N/A OR "0" if no Aswer</li>
        </ul>
        <form class="row g-3" method="post" enctype="multipart/form-data">
            <div class="col-12">
                <label for="mawze" class="form-label">What are the common reasons of students not receiving thaali in your mawze? <span class="required_star">*</span></label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gridCheck1" name="sabeel" <?php echo ($sabeel == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="gridCheck1">
                        Sabeel/Jamaat nizaam
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gridCheck2" name="affordability" <?php echo ($affordability == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="gridCheck2">
                        Affordability
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gridCheck3" name="taste" <?php echo ($taste == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="gridCheck3">
                        Taste
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gridCheck4" name="transport" <?php echo ($transport == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="gridCheck4">
                        Distance and transport
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gridCheck5" name="timings" <?php echo ($timings == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="gridCheck5">
                        Unsuitable timings
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gridCheck6">
                    <label class="form-check-label" for="gridCheck6">
                        Other
                    </label>
                </div>
            </div>
            <div class="col-12" id="otherReasonField" style="display: none;">
                <input class="form-control" type="text" id="f4a2" name="other" placeholder="Other Reason">
            </div>
            <div class="col-12">
                <label for="f4a1" class="form-label">How did you overcome these challenges? <span class="required_star">*</span></label>
                <textarea class="form-control" name="challenges" style="height: 120px" id="f4a1" required><?= $challenges; ?></textarea>
            </div>
            <div class="col-12">
                <label for="f4a2" class="form-label">How many migrated students started FMB thaali during Shehrullah 1445H? <span class="required_star">*</span></label>
                <input class="form-control" type="number" id="f4a2" name="started_fmb_thaali" value="<?= $started_fmb_thaali; ?>" required>
            </div>
            <div class="col-12">
                <label for="f3a2" class="form-label">What should be done to ensure that 100% migrated students receive FMB Thaali after Shehrullah?<span class="required_star">*</span></label>
                <textarea class="form-control" name="receive_fmb_thaali" style="height: 120px" id="f3a2" required><?= $receive_fmb_thaali; ?></textarea>
            </div>
            <div class="text-center">
                <a href="?form_id=form_3" class="btn btn-outline-warning">Prev</a>
                <input type="submit" value="Submit" name="submit_form_4" class="btn btn-primary" />
                <input type="reset" value="Reset" name="reset_form_4" class="btn btn-outline-secondary" />
                <a href="?form_id=form_5" class="btn btn-outline-success">Skip</a>
            </div>
        </form>
        <script>
            // Get the checkbox and text field elements
            const otherCheckbox = document.getElementById('gridCheck6');
            const otherTextField = document.getElementById('otherReasonField');

            // Add event listener to the "Other" checkbox
            otherCheckbox.addEventListener('change', function() {
                // If the checkbox is checked, show the text field; otherwise, hide it
                if (this.checked) {
                    otherTextField.style.display = 'block';
                } else {
                    otherTextField.style.display = 'none';
                }
            });
        </script>
    </div>
</div>