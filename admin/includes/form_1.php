<?php
$querySectionDetails = "SELECT * FROM `cr_data_collection` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<div class="card" id="form_1">
    <div class="card-body">
        <h5 class="card-title">Data collection</h5>

        <div class="col-12">
            <p><strong>What challenges did you face in data collection - and how did you overcome them?</strong></p>
            <p><?=$sectionData['a1']?></p>
        </div>
        <div class="col-12">
            <p><strong>How can we achieve 100% data collection of Migrated Out students from your zone before Ashara Mubaraka 1446H?</strong></p>
            <p><?=$sectionData['a2']?></p>
        </div>
        <div class="col-12">
            <p><strong>Mention innovative strategies that you used for acquiring data of elusive students.</strong></p>
            <p><?=$sectionData['a3']?></p>
        </div>
    </div>
</div>