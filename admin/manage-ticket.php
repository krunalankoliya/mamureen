<?php
require_once(__DIR__ . '/../session.php');

$current_page = 'manage-ticket';
require_once(__DIR__ . '/../inc/header.php');


$query = "SELECT *FROM `manage_tickets`";
$result = mysqli_query($mysqli, $query);
$data = $result->fetch_all(MYSQLI_ASSOC);

?>
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Manage Tickets</h1>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">List of Student</h5>
                    <table class="table table-border cell-border" id="datatable">
                        <thead>
                            <tr>
                                <th scope="col">Sr. NO</th>
                                <th scope="col">ITS ID</th>
                                <th scope="col">User ITS</th>
                                <th scope="col">Purpose</th>
                                <th scope="col">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $i = 1;
                            foreach ($data as $key => $student) {
                        ?>
                                <tr>
                                    <th scope="row" class="hide-on-mobile"><?=$i++?></th>
                                    <td><?=$student['its_id']?></td>
                                    <td><?=$student['user_its']?></td>
                                    <td><?=$student['purpose']?></td>
                                    <td><?=$student['details']?></td>
                                </tr>
                            <?php
                                }
                            ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

</main><!-- End #main -->

<?php
require_once(__DIR__ . '/../inc/footer.php');
?>
