<?php
$querySectionDetails = "SELECT * FROM `cr_migrated_out` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<div class="card" id="form_3">
    <div class="card-body">
        <h5 class="card-title">Parent's tafheem (Migrated out)</h5>

        <div class="col-12">
            <p><strong>What challenges did you face in this task - and how did you overcome them?</strong></p>
            <p><?=$sectionData['a1']?></p>
        </div>
        <div class="col-12">
            <p><strong>Why do you think that parents fail to stop their children from migrating?</strong></p>
            <p><?=$sectionData['a2']?></p>
        </div>
    </div>
</div>