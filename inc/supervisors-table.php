<?php
require_once(__DIR__ . '/../session.php');

// $cousellorQuery = "SELECT * FROM `counsellors_master` WHERE `is_super_counsellor` = 1 AND `gender` = 'M'";
$mauze = $rowdata['miqaat_mauze'];
$cousellorQuery = "SELECT *  FROM `counsellors_master` WHERE `jamaat` LIKE '%$mauze%'";
$counsellorResult = mysqli_query($mysqli, $cousellorQuery);
$counsellorData = $counsellorResult->fetch_all(MYSQLI_ASSOC);
?>


<h5 class="card-title">Supervisors</h5>

<table class="table table-bordered  cell-border" id="datatable2">
    <thead>
        <tr>
            <th scope="col">Photo</th>
            <th scope="col">ITS ID</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Mobile</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($counsellorData as $data) {
        ?>
            <tr>
                <td><img style="max-width:70px" src="https://www.talabulilm.com/mumin_images/<?= $data['its_id'] ?>.png" alt="Profile" class="rounded-circle"></td>
                <td><?= $data['its_id'] ?></td>
                <td><?= $data['full_name'] ?></td>
                <td><?= $data['email'] ?></td>
                <td><?= $data['mobile'] ?></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>
