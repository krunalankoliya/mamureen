<?php
$querySectionDetails = "SELECT * FROM `cr_fmb_thaali` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<div class="card" id="form_4">
    <div class="card-body">
        <h5 class="card-title">FMB Thaali</h5>

        <div class="col-12">
            <p><strong>What are the common reasons of students not receiving thaali in your mawze?</strong></p>
            <p>
                <?php
                if ($sectionData['sabeel']) echo "Sabeel/Jamaat nizaam<br>";
                if ($sectionData['affordability']) echo "Affordability<br>";
                if ($sectionData['taste']) echo "Taste<br>";
                if ($sectionData['transport']) echo "Distance and transport<br>";
                if ($sectionData['timings']) echo "Unsuitable timings<br>";
                if ($sectionData['other']) echo "Other: $sectionData[other]<br>";
                ?>
            </p>
        </div>
        <div class="col-12">
            <p><strong>How did you overcome these challenges?</strong></p>
            <p><?=$sectionData['challenges']?></p>
        </div>
        <div class="col-12">
            <p><strong>How many migrated students started FMB thaali during Shehrullah 1445H?</strong></p>
            <p><?=$sectionData['started_fmb_thaali']?></p>
        </div>
        <div class="col-12">
            <p><strong>What should be done to ensure that 100% migrated students receive FMB Thaali after Shehrullah?</strong></p>
            <p><?=$sectionData['receive_fmb_thaali']?></p>
        </div>
    </div>
</div>