<?php
$querySectionDetails = "SELECT * FROM `cr_migrated_in` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<div class="card" id="form_2">
    <div class="card-body">
        <h5 class="card-title">House visits (Migrated In)</h5>

        <div class="col-12">
            <p><strong>What challenges did you face while conducting house visits - and how did you overcome them?</strong></p>
            <p><?= $sectionData['a1'] ?></p>
        </div>
        <div class="col-12">
            <p><strong>How can we complete house visits of all remaining migrated students before Ashara Mubaraka 1446H?</strong></p>
            <p><?= $sectionData['a2'] ?></p>
        </div>
        <div class="col-12">
            <p><strong>What Silat/Hadiya were presented to students by local jamaat?</strong></p>
            <p><?= $sectionData['a3'] ?></p>
        </div>
    </div>
</div>