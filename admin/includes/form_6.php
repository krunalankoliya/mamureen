<?php
$querySectionDetails = "SELECT * FROM `cr_accommodation` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<div class="card" id="form_6">
    <div class="card-body">
        <h5 class="card-title">Accommodation Facility/Hostel</h5>
        <div class="col-12">
            <p><strong>What measures are taken to establish a hostel/jamaat provided accommodation for migrated-in students?</strong></p>
        </div>
        <div class="col-12">
            <p><strong>Dedicated hostel committee</strong></p>
            <p><?=$sectionData['a1']?></p>
        </div>
        <div class="col-12">
            <p><strong>Need analysis</strong></p>
            <p><?=$sectionData['a2']?></p>
        </div>
        <div class="col-12">
            <p><strong>Cost analysis</strong></p>
            <p><?=$sectionData['a3']?></p>
        </div>
        <div class="col-12">
            <p><strong>Location assessment</strong></p>
            <p><?=$sectionData['a4']?></p>
        </div>
        <hr>
        <div class="col-12">
            <h3>How would you describe the current living conditions of migrated-in students who live in – <span class="required_star"></h2>
        </div>
        <div class="col-12">
            <p><strong>Sharing rental w/ mumin</strong></p>
            <p><?=$sectionData['a5']?></p>
        </div>
        <div class="col-12">
            <p><strong>Sharing rental w/non-mumin</strong></p>
            <p><?=$sectionData['a6']?></p>
        </div>
        <div class="col-12">
            <p><strong>Individual rental</strong></p>
            <p><?=$sectionData['a7']?></p>
        </div>
        <div class="col-12">
            <p><strong>Hostel</strong></p>
            <p><?=$sectionData['a8']?></p>
        </div>
        <div class="col-12">
            <p><strong>How many students were shifted from non-recommended accommodation during shehrullah?</strong></p>
            <p><?=$sectionData['a9']?></p>
        </div>
    </div>
</div>