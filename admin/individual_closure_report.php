<?php
require_once(__DIR__ . '/../session.php');
$current_page = 'individual_closure_report';
require_once(__DIR__ . '/../inc/header.php');

// Get the ITS ID of the mamur. If not submitted then redirect to list page.
$mamur_its = isset($_GET['its']) ? (int) $_GET['its'] : '';
if(!$mamur_its) {
    header('Location: https://www.talabulilm.com/mamureen/admin/list_closure_reports.php');
    exit();
}
// Get the completion status of the report.
$queryCompletedSections = "SELECT * FROM `closure_report_submitted` WHERE `its_id` = '$mamur_its'";
$resultCompletedSections = mysqli_query($mysqli, $queryCompletedSections);
$completedData = mysqli_fetch_assoc($resultCompletedSections);

$completedSections = array();
if ($completedData) {
    foreach ($completedData as $key => $value) {
        if (preg_match('/^form_(1[0-1]|[1-9])$/', $key) && $value === "1") {
            $completedSections[] = $key;
        }
    }
}

// For each completed section, get the report fields.
// Display each section question and submitted report.

?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Closure Report for Mamur ITS: <?=$mamur_its?></h1>
    </div><!-- End Page Title -->
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">Completed Sections by Mamur: <?= count($completedSections)?></h5>
                    <?php
                    foreach($completedSections as $section){
                        include_once __DIR__ . "/includes/$section.php";
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->
<?php require_once(__DIR__ . '/../inc/footer.php'); ?>
