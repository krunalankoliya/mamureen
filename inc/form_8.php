<?php
require_once(__DIR__ . '/../session.php');
$query = "SELECT * FROM cr_minhat_aalimiyah WHERE its_id = $user_its";
$fetchDataResult = mysqli_query($mysqli, $query);

// Initialize variables
$a1 = '';
$a2 = '';
$currency = '';


if ($fetchDataResult) {
    if (mysqli_num_rows($fetchDataResult) > 0) {
        // Data found, populate form fields with existing data
        $rowData = mysqli_fetch_assoc($fetchDataResult);
        $a1 = isset($rowData['a1']) ? $rowData['a1'] : '';
        $a2 = isset($rowData['a2']) ? $rowData['a2'] : '';
        $currency = isset($rowData['currency']) ? $rowData['currency'] : '';
    }
    // Free the result set
    mysqli_free_result($fetchDataResult);
}


$currenciesQuery = "SELECT *  FROM `currencies`";
$currenciesResult = mysqli_query($mysqli, $currenciesQuery);
$currenciesData = $currenciesResult->fetch_all(MYSQLI_ASSOC);

$select = '<select id="currency" class="form-select" name="currency" required>
            <option value="" disabled hidden>Select...</option>';

foreach ($currenciesData as $currencyOption) {
    $selected = ($currencyOption['currency_iso'] === $currency) ? 'selected' : '';
    $select .= '<option value="' . $currencyOption['currency_iso'] . '" ' . $selected . '>' . $currencyOption['currency_name'] . '</option>';
}

// Check if $rowData['currency'] is empty or not set
if ($currency === '') {
    // Set the "Select..." option as selected
    $select = str_replace('hidden', 'selected', $select);
}

$select .= '</select>';

?>
<div class="card" id="form_8">
    <div class="card-body">
        <h5 class="card-title">Minhat Talimiyah</h5>
        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
        <ul>
            <li>All fields marked with <span class="required_star">*</span> are required.</li>
            <li>Mention N/A OR "0" if no Aswer</li>
        </ul>
        <form class="row g-3" method="post" enctype="multipart/form-data">
            <div class="col-12">
                <label for="f8a1" class="form-label">How many students in your mawze will need financial assistance for the upcoming academic year? <span class="required_star">*</span></label>
                <input class="form-control" type="number" id="f8a1" name="a1" value="<?php echo $a1; ?>" required>
            </div>
            <div class="col-12">
                <label for="f8a2" class="form-label">How much financial assistance will be required for these students?<span class="required_star">*</span></label>
                <input class="form-control" type="number" id="f8a2" name="a2" value="<?php echo $a2; ?>" required>
            </div>
            <div class="col-12">
                <label for="inputEmail3" class="col-sm-4 col-form-label">currency<span class="required_star">*</span></label>
                <?= $select ?>
            </div>
            <div class="text-center">
                <a href="?form_id=form_7" class="btn btn-outline-warning">Prev</a>
                <input type="submit" value="Submit" name="submit_form_8" class="btn btn-primary" />
                <input type="reset" value="Reset" name="reset_form_8" class="btn btn-outline-secondary" />
                <a href="?form_id=form_9" class="btn btn-outline-success">Skip</a>
            </div>
        </form>
    </div>
</div>