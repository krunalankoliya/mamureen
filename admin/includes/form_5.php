<?php
$querySectionDetails = "SELECT * FROM `cr_muraqabat` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<div class="card" id="form_5">
    <div class="card-body">
        <h5 class="card-title">Muraqabat</h5>
        <div class="col-12">
            <p><strong>What challenges did you face while identifying and tagging 'muraqibeen' to students, and how did you overcome them?</strong></p>
            <p><?=$sectionData['a1']?></p>
        </div>
        <div class="col-12">
            <p><strong>What aspects were covered in the 'Muraqibeen training' during shehrullah?</strong></p>
            <p><?=$sectionData['a2']?></p>
        </div>
    </div>
</div>