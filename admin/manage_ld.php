<?php
require_once __DIR__ . '/../session.php';
$current_page = 'manage_listan-al-dawat';
require_once __DIR__ . '/../inc/header.php';

// Fetch data from the database
$query = "SELECT * FROM `manage_ld`";
$result = mysqli_query($mysqli, $query);
$data = $result->fetch_all(MYSQLI_ASSOC);
?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Data Entry of Mumineen who cannot speak Lisan al-Dawat</h1>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <!--<h5 class="card-title">Data Entry of Mumineen who cannot speak Lisan al-Dawat</h5>-->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="card-title">List of Entries</h5>
                            <table class="table table-border cell-border" id="datatable_manage_mamureen">
                                <!-- Table Headers -->
                                <thead>
                                    <tr>
                                        <th scope="col">its_id</th>
                                        <th scope="col">Photo</th>
                                        <th scope="col">fullname</th>
                                        <th scope="col">Gender</th>
                                        <th scope="col">Age</th>
                                        <th scope="col">Jamaat</th>
                                        <th scope="col">BQI Mauze</th>
                                         <th scope="col">Preferred Language</th> 
                                        <th scope="col">Important Remarks</th>
                                         <th scope="col">Mamur ITS</th> 
                                    </tr>
                                </thead>
                                <!-- Table Body -->
                                <tbody>
                                    <?php foreach ($data as $admin) : ?>
                                    <?php 
                                                // Calculate age from DOB
                                                $dob = new DateTime($admin['dob']);
                                                $today = new DateTime('today');
                                                $age = $dob->diff($today)->y;
                                            ?>
                                        <tr>
                                            <td><?= $admin['its_id'] ?></td>
                                            <td><img style="max-width:70px" src="https://www.talabulilm.com/mumin_images/<?= $admin['its_id'] ?>.png" alt="Profile" class="rounded-circle"></td>
                                            <td><?= $admin['full_name'] ?></td>
                                            <td><?= $admin['gender'] ?></td>
                                            <td><?= $age ?></td>
                                            <td><?= $admin['jamaat'] ?></td>
                                            <td><?= $admin['bqi_mauze'] ?></td>
                                            <td><?= $admin['preferred_language'] ?></td>
                                            <td><?= $admin['important_remarks'] ?></td>
                                            <td><?= $admin['added_its'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<script>
$(document).ready(function () {
    $('#datatable_manage_mamureen').DataTable( {
      dom: 'Bfrtip',
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, 'All']
      ],
      buttons: [
        {
          extend: 'copy',
          filename: function() {
              return 'entry_list -'  + Date.now();
          },
          exportOptions: {
            columns: ':not(:nth-child(2))'
          }
        },
        {
          extend: 'csv',
          filename: function() {
              return 'entry_list -'  + Date.now();
          },
          exportOptions: {
            columns: ':not(:nth-child(2))'
          }
        },
        {
          extend: 'excel',
          filename: function() {
              return 'entry_list -'  + Date.now();
          },
          exportOptions: {
            columns: ':not(:nth-child(2))'
          }
        }
      ]
    });
});
</script>
<?php
require_once(__DIR__ . '/../inc/footer.php');
require_once(__DIR__ . '/../inc/js-block.php');
?>
</body>
</html>