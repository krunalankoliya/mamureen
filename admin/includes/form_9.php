<?php
$querySectionDetails = "SELECT * FROM `cr_imani_schools` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<div class="card" id="form_9">
    <div class="card-body">
        <h5 class="card-title">Imani Schools</h5>
        <div class="col-12">
            <p><strong>How many children were granted admission in Imani Schools during Shehrullah as a result of BQI program?</strong></p>
            <p><?=$sectionData['a1']?></p>
        </div>
    </div>
</div>