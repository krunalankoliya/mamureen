<?php

$current_page = 'dashboard';
require_once(__DIR__ . '/inc/header.php');

$query = "SELECT `its_id` FROM `users_admin`";
$result = mysqli_query($mysqli, $query);
$admin_list = $result->fetch_all(MYSQLI_ASSOC);
$admin = array_column($admin_list, 'its_id');

if (in_array($user_its, $admin)) { ?>
  <script>
    // window.location = '/test_mamureen/admin_dashboard.php';
  </script>
<?php
}

function getCounts($query, $mysqli)
{

  $result = mysqli_query($mysqli, $query);
  $res = $result->fetch_assoc();

  return ($res && $res['count']) ? (int) $res['count'] : 0;
}



$ld_count = getCounts("SELECT COUNT(*) `count` FROM `manage_ld` WHERE `jamaat` = '$mauze' ", $mysqli);
$hifz_nasaih_count = getCounts("SELECT COUNT(*) `count` FROM `hifz_nasaih` WHERE `added_its` = '$user_its' ", $mysqli);
$bqi_17_session_attendees_count = getCounts("SELECT COUNT(*) `count` FROM `bqi_17_session_attendees` WHERE `added_its` = '$user_its' ", $mysqli);

?>
<div style="display:none"><?= $city ?></div>
<main id="main" class="main">

  <div class="pagetitle">
    <h1>Dashboard</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section dashboard">
    <div class="row">

      <!-- Left side columns -->
      <div class="col-lg-8">
        <div class="card info-card sales-card pt-4 pb-0">
          <div class="card-body">
            <div class="row">
              <div class="col-xxl-6 col-md-6">
                <a href="<?= MODULE_PATH ?>manage_ld.php">
                  <div class="card info-card sales-card pb-0">
                    <div class="card-body">
                      <h5 class="card-title">Lisan al Dawat</h5>
                      <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                          <i class="bx bx-chat"></i>
                        </div>
                        <div class="ps-3">
                           <h6><?= $ld_count ?></h6>
                        </div>
                      </div>
                    </div>
                    
                  </div>
                </a>
              </div>

              <div class="col-xxl-6 col-md-6">
                <a href="<?= MODULE_PATH ?>hifz_nasaih.php">
                  <div class="card info-card sales-card pb-0">
                    <div class="card-body">
                      <h5 class="card-title">Hifz al Nasaeh</h5>
                      <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                          <i class="bx bxs-book"></i>
                        </div>
                        <div class="ps-3">
                          <h6><?= $hifz_nasaih_count ?></h6>
                        </div>
                      </div>
                    </div>
                   
                  </div>
                </a>
              </div>
              
              <div class="col-xxl-6 col-md-6">
                <a href="<?= MODULE_PATH ?>bqi_17_session_attendees.php">
                  <div class="card info-card sales-card pb-0">
                    <div class="card-body">
                      <h5 class="card-title">BQI 17 Session Attendees</h5>
                      <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                          <i class="bi bi-person-check-fill"></i>
                        </div>
                        <div class="ps-3">
                          <h6><?= $bqi_17_session_attendees_count ?></h6>
                        </div>
                      </div>
                    </div>
                   
                  </div>
                </a>
              </div>

              <div class="col-xxl-6 col-md-6">
                <a href="#">
                  <div class="card info-card revenue-card  pb-0">
                    <div class="card-body">
                      <h5 class="card-title">Rivayaat covered </h5>
                      <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                          <i class="bx bx-checkbox-checked"></i>
                        </div>
                        <div class="ps-3">
                           <h6><?php echo' will be live soon'; ?></h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
                
            </div>
          </div>

        </div>
       

      </div><!-- End Left side columns -->

      <!-- Right side columns -->
      <div class="col-lg-4">

        <?php
        require_once(__DIR__ . '/inc/downloads.php')
        ?>

        <?php
        $mauze = $rowdata['miqaat_mauze'];
        // var_dump($rowdata[0]['miqaat_mauze']);exit();
        $query = "SELECT `u`.`full_name_en`,`j`.`primary_email`,`j`.`aamil_masool_its_id` FROM `its_jamaat_master` `j`
              LEFT JOIN user `u` ON `j`.`aamil_masool_its_id` = `u`.`its_id`
              WHERE `j`.`jamaat` = '$mauze'";
        $result = mysqli_query($coremysqli, $query);
        $row = $result->fetch_all(MYSQLI_ASSOC);
        ?>
        <!-- Aamil Saheb Info card -->
        <div class="card">
          <div class="card-header">Aamil Saheb Info</div>
          <div class="card-body text-center">
            <h5 class="card-title"><?= $row[0]['full_name_en'] ?></h5>
            <!-- <img src="https://www.talabulilm.com/mumin_images/<?= $row[0]['aamil_masool_its_id'] ?>.png" /> -->
            <?php
            $imagePath = "https://www.talabulilm.com/mumin_images/" . $row[0]['aamil_masool_its_id'] . ".png";

            if (@getimagesize($imagePath)) {
              echo '<img src="' . $imagePath . '" style="width:80px" />';
            } else {
              echo '<img src="https://www.talabulilm.com/placeholder_image/placeholder.png" style="width:80px" />';
            }
            ?>

          </div>
          <div class="card-footer">
            <!-- <p>Mobile: <a href="tel:+91 9876543210">+91 9876543210</a></p> -->
            <p>Email: <a href="mailto:<?= $row[0]['primary_email'] ?>"><?= $row[0]['primary_email'] ?></a></p>
            <!--<p>mobile: <a href="mailto:aamil@alvazarat.org"><?= $row[0]['primary_email'] ?></a></p>-->
            <!--<p><a href="zone_details.php">List of BQI Khidmat Guzaars</a></p>-->
            <!--<p><a href="umoor-talimiyah-members.php">List of Umoor Taalimiyah Members</a></p>-->
          </div>
        </div><!-- End Aamil Saheb Info card -->

      </div><!-- End Right side columns -->

    </div>
  </section>

</main><!-- End #main -->

<?php
require_once(__DIR__ . '/inc/footer.php');
?>

<?php
require_once(__DIR__ . '/inc/js-block.php');
?>