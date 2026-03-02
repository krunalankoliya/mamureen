<?php
require_once(__DIR__ . '/../session.php');
$current_page = 'list_download_reports';
require_once(__DIR__ . '/../inc/header.php');

// Corrected query to count downloads grouped by its_id
$queryDownloadReports = "SELECT `u`.`its_id`, `u`.`fullname`, `u`.`miqaat_mauze`, `u`.`zone`,
                         COUNT(DISTINCT(`dl`.`download_id`)) as download_count
                         FROM `users_mamureen` `u`
                         LEFT JOIN `download_log` `dl` ON `u`.`its_id` = `dl`.`its_id`
                         GROUP BY `u`.`its_id`, `u`.`fullname`, `u`.`miqaat_mauze`, `u`.`zone`
                         ORDER BY download_count DESC";

$resultDownloadReports = mysqli_query($mysqli, $queryDownloadReports);
$downloadData = mysqli_fetch_all($resultDownloadReports, MYSQLI_ASSOC);

?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Download Reports</h1>
    </div><!-- End Page Title -->
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">List of Mamureen</h5>
                    <table class="table table-bordered cell-border display" id="datatable0">
                        <thead>
                            <tr>
                                <th scope="col">Mamur ITS</th>
                                <th scope="col">Name</th>
                                <th scope="col">Mauze</th>
                                <th scope="col">Zone</th>
                                <th scope="col">Download Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($downloadData as $row) : ?>
                                <tr>
                                    <td><a href="https://www.talabulilm.com/mamureen/admin/individual_download_report.php?its=<?= $row['its_id'] ?>"><?= $row['its_id'] ?></a></td>
                                    <td><?= $row['fullname'] ?></td>
                                    <td><?= $row['miqaat_mauze'] ?></td>
                                    <td><?= $row['zone'] ?></td>
                                    <td><?= $row['download_count'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->
<?php require_once(__DIR__ . '/../inc/footer.php'); ?>

<script>
    $(document).ready(function() {
        $("#datatable0").each(function() {
            $(this).DataTable({
                dom: "lBfrtip",
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"],
                ], // Define the length menu options
                buttons: [{
                        extend: 'copy',
                        filename: function() {
                            return 'list_download_reports' + Date.now();
                        }
                    },
                    {
                        extend: 'csv',
                        filename: function() {
                            return 'list_download_reports' + Date.now();
                        }
                    },
                    {
                        extend: 'excel',
                        filename: function() {
                            return 'list_download_reports' + Date.now();
                        }
                    }
                ]
            });
        });
    });
</script>