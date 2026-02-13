<?php
$querySectionDetails = "SELECT * FROM `cr_ut_committee` WHERE its_id = '$mamur_its'";
$resultSectionDetails = mysqli_query($mysqli, $querySectionDetails);
$sectionData = mysqli_fetch_assoc($resultSectionDetails);
?>
<div class="card" id="form_7">
    <div class="card-body">
        <h5 class="card-title">Umoor Taalimiyah Committee</h5>
        <div class="col-12">
            <p><strong>What was the role of UT Coordinator/ Higher Education Team lead in BQI activities?</strong></p>
            <p><?=$sectionData['a1']?></p>
        </div>
        <div class="col-12">
            <p><strong>Mention the names of different local committees and their contribution towards BQI activities.</strong></p>
            <p><?=$sectionData['a2']?></p>
        </div>
        <div class="col-12">
            <p><strong>What are your observations regarding the overall working of umoor taalimiyah committee in your mawze?</strong></p>
            <p><?=$sectionData['a3']?></p>
        </div>
        <div class="col-12">
            <p><strong>What preparations were done by Umoor Taalimiyah and Umaal Kiraam for BQI tasks - before shehrullah?</strong></p>
            <p><?=$sectionData['a4']?></p>
        </div>
        <div class="col-12">
            <p><strong>Did you notice any substantial growth/development in the working of Umoor Taalimiyah committee as a result of BQI project?</strong></p>
            <p><?=$sectionData['a5']?></p>
        </div>
    </div>
</div>