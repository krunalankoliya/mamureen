<?php

$current_page = 'closure-report';
require_once(__DIR__ . '/inc/header.php');
?>
<main id="main" class="main">
  <section class="section dashboard">
    <div class="row">
        <?php require_once(__DIR__ . '/inc/messages.php'); ?>
        <div class="col-md-12">
            <div class="card">
            <div class="card-body">
                <div style="text-align:center;margin-top:20px;">
                <a href="<?=MODULE_PATH?>counselling-report.php"><span class="step">1</span></a>
                <a href="<?=MODULE_PATH?>migrated-students-report.php"><span class="step">2</span></a>
                <a href="<?=MODULE_PATH?>asabaq-report.php"><span class="step">3</span></a>
                <a href="<?=MODULE_PATH?>minhat-talimiyah-report.php"><span class="step">4</span></a>
                <a href="<?=MODULE_PATH?>tehfeez-report.php"><span class="step">5</span></a>
                <a href="<?=MODULE_PATH?>talim-al-quran-report.php"><span class="step">6</span></a>
                <a href="<?=MODULE_PATH?>deeni-talim-report.php"><span class="step">7</span></a>
                <a href="<?=MODULE_PATH?>umoor-talimiyah-report.php"><span class="step">8</span></a>
                <a href="<?=MODULE_PATH?>mauze-overview.php"><span class="step">9</span></a>
                </div>
                <h5 class="card-title mt-5 mb-5" style="text-align:center">Your report has been recorded successfully. Thank you.</h5>
            </div>
            </div>
        </div>
    </div>
  </section>
</main><!-- End #main -->

<?php
require_once(__DIR__ . '/inc/footer.php');
?>

<?php
require_once(__DIR__ . '/inc/js-block.php');
?>