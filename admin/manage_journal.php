<?php
require_once(__DIR__ . '/../session.php');
$current_page = 'manage_journal';
require_once(__DIR__ . '/../inc/header.php');

// Fetch data from the database
$query = "SELECT
    `j`.`its_id`,
    `j`.`a1`,
    `j`.`a2`,
    `j`.`a3`,
    `j`.`a4`,
    `j`.`a5`,
    `j`.`a6`,
    `j`.`a7`,
    `um`.`fullname` AS `kg_name`,
    `um`.`miqaat_mauze`
FROM `journal` `j`
LEFT JOIN `users_mamureen` `um` ON `j`.`its_id` = `um`.`its_id`";
$result = mysqli_query($mysqli, $query);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Mamureen Journal</h1>
    </div><!-- End Page Title -->
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">List of Journal</h5>
                    <table class="table table-bordered cell-border display" id="datatable0">
                        <thead>
                            <tr>
                                <th scope="col">KG ITS</th>
                                <th scope="col">KG Name</th>
                                <th scope="col">Miqaat Mauze</th>
                                <th scope="col">A1</th>
                                <th scope="col">A2</th>
                                <th scope="col">A3</th>
                                <th scope="col">A4</th>
                                <th scope="col">A5</th>
                                <th scope="col">A6</th>
                                <th scope="col">A7</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $student) : ?>
                                <tr>
                                    <td><?= $student['its_id'] ?></td>
                                    <td><?= $student['kg_name'] ?></td>
                                    <td><?= $student['miqaat_mauze'] ?></td>
                                    <td><?= $student['a1'] ?></td>
                                    <td><?= $student['a2'] ?></td>
                                    <td><?= $student['a3'] ?></td>
                                    <td><?= $student['a4'] ?></td>
                                    <td><?= $student['a5'] ?></td>
                                    <td><?= $student['a6'] ?></td>
                                    <td><?= $student['a7'] ?></td>
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
                // columnDefs: [{
                //     targets: [1, 3],
                //     visible: false
                // }],
                buttons: [{
                        extend: 'copy',
                        filename: function() {
                            return 'manage_journal' + Date.now();
                        }
                    },
                    {
                        extend: 'csv',
                        filename: function() {
                            return 'manage_journal' + Date.now();
                        }
                    },
                    {
                        extend: 'excel',
                        filename: function() {
                            return 'manage_journal' + Date.now();
                        }
                    }
                ]
            });
        });
    });
</script>