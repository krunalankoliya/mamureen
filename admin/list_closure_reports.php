<?php
require_once(__DIR__ . '/../session.php');
$current_page = 'list_closure_reports';
require_once(__DIR__ . '/../inc/header.php');

$queryClosureReports = "SELECT `its_id`, `fullname`, `miqaat_mauze`, `upload_report` FROM `closure_report` `c`
RIGHT JOIN `users_mamureen` `u` USING (`its_id`);";
$resultClosureReports = mysqli_query($mysqli, $queryClosureReports);
$closureData = mysqli_fetch_all($resultClosureReports, MYSQLI_ASSOC);

?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Closure Reports</h1>
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
                                <th scope="col">Report</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($closureData as $row) : ?>
                                <tr>
                                    <td><?= $row['its_id'] ?></td>
                                    <td><?= $row['fullname'] ?></td>
                                    <td><?= $row['miqaat_mauze'] ?></td>
                                    <td>
                                        <?php if($row['upload_report']){ ?>
                                        <!--<a href="../user_report_uploads/<?php echo $row['upload_report']; ?>" class="btn btn-primary" target="_blank">-->
                                        <!--    <i class="bi bi-file-earmark-pdf"></i> -->
                                        <!--</a>-->
                                        <a href="../user_report_uploads/<?php echo $row['upload_report']; ?>" class="btn btn-success" download>
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <?php }else{ ?>
                                            Not Uploaded
                                        <?php } ?>
                                    </td>
                                    
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
                            return 'list_closure_reports' + Date.now();
                        }
                    },
                    {
                        extend: 'csv',
                        filename: function() {
                            return 'list_closure_reports' + Date.now();
                        }
                    },
                    {
                        extend: 'excel',
                        filename: function() {
                            return 'list_closure_reports' + Date.now();
                        }
                    }
                ]
            });
        });
    });
</script>