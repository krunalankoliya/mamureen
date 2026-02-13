<?php
require_once(__DIR__ . '/../session.php');
$current_page = 'individual_download_report';
require_once(__DIR__ . '/../inc/header.php');

// Get the ITS ID of the mamur. If not submitted then redirect to list page.
$mamur_its = isset($_GET['its']) ? (int) $_GET['its'] : '';
if (!$mamur_its) {
    header('Location: https://www.talabulilm.com/mamureen/admin/list_download_reports.php');
    exit();
}

// Get mamur information
$queryMamurInfo = "SELECT `fullname`, `miqaat_mauze`, `zone` FROM `users_mamureen` WHERE `its_id` = '$mamur_its'";
$resultMamurInfo = mysqli_query($mysqli, $queryMamurInfo);
$mamurInfo = mysqli_fetch_assoc($resultMamurInfo);

// Get detailed download history
$queryDownloadHistory = "SELECT dl.id, dl.download_id, MAX(dl.download_date) AS `download_date`, md.title, md.file_name
                         FROM `download_log` dl
                         JOIN `my_downloads` md ON dl.download_id = md.id
                         WHERE dl.its_id = '$mamur_its' GROUP BY dl.download_id
                         ORDER BY dl.download_date DESC";
$resultDownloadHistory = mysqli_query($mysqli, $queryDownloadHistory);
$downloadHistory = mysqli_fetch_all($resultDownloadHistory, MYSQLI_ASSOC);

$downloadCount = count($downloadHistory);
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Download Report for Mamur ITS: <?= $mamur_its ?></h1>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <!-- Mamur Information Card -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Mamur Information</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>ITS ID:</strong> <?= $mamur_its ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Name:</strong> <?= $mamurInfo['fullname'] ?? 'N/A' ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Mauze:</strong> <?= $mamurInfo['miqaat_mauze'] ?? 'N/A' ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Zone:</strong> <?= $mamurInfo['zone'] ?? 'N/A' ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Total Downloads:</strong> <?= $downloadCount ?? '0' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Download History -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="overflow-y: auto;">
                        <h5 class="card-title">Download History</h5>
                        <?php if (empty($downloadHistory)): ?>
                            <div class="alert alert-info">No download history available for this mamur.</div>
                        <?php else: ?>
                            <table class="table table-bordered cell-border display" id="downloadTable">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Resource Title</th>
                                        <th scope="col">File Name</th>
                                        <th scope="col">Download Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($downloadHistory as $download): ?>
                                        <tr>
                                            <td><?= $counter++ ?></td>
                                            <td><?= $download['title'] ?></td>
                                            <td><?= $download['file_name'] ?></td>
                                            <td><?= $download['download_date'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<?php require_once(__DIR__ . '/../inc/footer.php'); ?>

<script>
    $(document).ready(function() {
        $("#downloadTable").DataTable({
            dom: "lBfrtip",
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"],
            ],
            buttons: [{
                    extend: 'copy',
                    filename: function() {
                        return 'download_report_<?= $mamur_its ?>_' + Date.now();
                    }
                },
                {
                    extend: 'csv',
                    filename: function() {
                        return 'download_report_<?= $mamur_its ?>_' + Date.now();
                    }
                },
                {
                    extend: 'excel',
                    filename: function() {
                        return 'download_report_<?= $mamur_its ?>_' + Date.now();
                    }
                }
            ]
        });
    });
</script>