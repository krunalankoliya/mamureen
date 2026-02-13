<?php
$querySectionDetails = "SELECT * FROM `cr_minhat_aalimiyah` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<div class="card" id="form_8">
    <div class="card-body">
        <h5 class="card-title">Minhat Talimiyah</h5>
        <div class="col-12">
            <p><strong>How many students in your mawze will need financial assistance for the upcoming academic year?</strong></p>
            <p><?=$sectionData['a1']?></p>
        </div>
        <div class="col-12">
            <p><strong>How much financial assistance will be required for these students?</strong></p>
            <p><?=$sectionData['a2']?></p>
        </div>
        <div class="col-12">
            <p><strong>currency</strong></p>
            <p><?=$sectionData['currency']?></p>
        </div>
    </div>
</div>