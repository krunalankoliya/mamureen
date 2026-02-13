<?php
$querySectionDetails = "SELECT * FROM `cr_trends` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<div class="card" id="form_11">
    <div class="card-body">
        <h5 class="card-title">Trends</h5>
        <div class="col-12">
            <p><strong>What are the most preferred cities for students migrating out from your city?</strong></p>
            <p><?=$sectionData['a1']?></p>
        </div>
        <div class="col-12">
            <p><strong>Where do most migrated-in students in your city come from?</strong></p>
            <p><?=$sectionData['a2']?></p>
        </div>
        <div class="col-12">
            <p><strong>What are the most popular courses/careers in your city?</strong></p>
            <p><?=$sectionData['a3']?></p>
        </div>
        <div class="col-12">
            <p><strong>Which courses/career options offer great opportunities in your city?</strong></p>
            <p><?=$sectionData['a4']?></p>
        </div>
    </div>
</div>