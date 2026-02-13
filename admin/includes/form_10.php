<?php
$querySectionDetails = "SELECT * FROM `cr_asbaaq` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<style>
    /* CSS to set the max-height of the image */
    img {
        max-height: 300px;
        width: auto; /* Ensure the image maintains its aspect ratio */
        height: auto; /* Allow the image to scale proportionally */
    }
</style>
<div class="card" id="form_10">
    <div class="card-body">
        <h5 class="card-title">Asbaaq/Educational seminars</h5>
        <div class="col-12">
            <p><strong>What different media were used to convey the paighaam of Maula TUS regarding higher education and migration?</strong></p>
            <p><?=$sectionData['a1']?></p>
        </div>
        <div class="col-md-12">
            <p><strong>You can upload Majhoodaat or Presentations of your seminars here.</strong></p>
            <p><?=$sectionData['file']?></p>
            <img src=<?="https://www.talabulilm.com/mamureen/user_uploads/$sectionData[file]"?> alt="Sabaq Image" />
        </div>
    </div>
</div>