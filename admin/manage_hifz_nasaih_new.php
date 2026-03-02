<?php
require_once __DIR__ . '/../session.php';
$current_page = 'manage_hifz_nasaih';
require_once __DIR__ . '/../inc/header.php';

// Fetch data from the database
$query = "SELECT `hn`.*,`um`.`fullname` FROM `hifz_nasaih` `hn`
LEFT JOIN `users_mamureen` `um` ON `hn`.`added_its` = `um`.`its_id` LIMIT 3";
$result = mysqli_query($mysqli, $query);
$data = $result->fetch_all(MYSQLI_ASSOC);
?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Hifz al-Nasa’ih</h1>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <!--<h5 class="card-title">Data Entry of Mumineen who cannot speak Lisan al-Dawat</h5>-->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">List of hifz_nasaih</h5>
                                <button id="bulk-print-btn" class="btn btn-primary">Print All Certificates</button>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <table class="table table-border cell-border" id="datatable_manage_hifz">
                                <!-- Table Headers -->
                                <thead>
                                    <tr>
                                        <th scope="col">its_id</th>
                                        <th scope="col">Photo</th>
                                        <th scope="col">Full Name</th>
                                        <th scope="col">Gender</th>
                                        <th scope="col">Jamaat</th>
                                        <th scope="col">Mobile</th>
                                        <th scope="col">Email</th>
                                         <th scope="col">Mamur ITS</th> 
                                         <th scope="col">Mamur Name</th> 
                                         <th scope="col">Certificate</th> 
                                    </tr>
                                </thead>
                                <!-- Table Body -->
                                <tbody>
                                    <?php foreach ($data as $admin) : ?>
                                        <tr>
                                            <td><?= $admin['its_id'] ?></td>
                                            <td><img style="max-width:70px" src="https://www.talabulilm.com/mumin_images/<?= $admin['its_id'] ?>.png" alt="Profile" class="rounded-circle"></td>
                                            <td><?= $admin['full_name'] ?></td>
                                            <td><?= $admin['gender'] ?></td>
                                            <td><?= $admin['jamaat'] ?></td>
                                            <td><?= $admin['mobile'] ?></td>
                                            <td><?= $admin['email'] ?></td>
                                            <td><?= $admin['added_its'] ?></td>
                                            <td><?= $admin['fullname'] ?></td>
                                            <td>
                                                <a href="../certificates.php?its_id=<?= $admin['its_id'] ?>&gender=<?= $admin['gender'] ?>&name=<?= urlencode($admin['full_name_ar']) ?>" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="bi bi-file-earmark-text"></i> View
                                                </a>
                                            </td>
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
<?php
require_once(__DIR__ . '/../inc/footer.php');
?>
<script>
$(document).ready(function() {
    // Add event listeners for bulk buttons
    $('#bulk-print-btn').on('click', function() {
        window.open('bulk_certificates.php?action=print', '_blank');
    });
    
    $('#bulk-download-btn').on('click', function() {
        window.open('bulk_certificates.php?action=download', '_blank');
    });
});
$(document).ready(function () {
    $('#datatable_manage_hifz').DataTable( {
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
              return 'hifz_nasaih -'  + Date.now();
          },
          exportOptions: {
            columns: ':not(:nth-child(2))'
          }
        },
        {
          extend: 'csv',
          filename: function() {
              return 'hifz_nasaih -'  + Date.now();
          },
          exportOptions: {
            columns: ':not(:nth-child(2))'
          }
        },
        {
          extend: 'excel',
          filename: function() {
              return 'hifz_nasaih -'  + Date.now();
          },
          exportOptions: {
            columns: ':not(:nth-child(2))'
          }
        }
      ]
    });
});
</script>
