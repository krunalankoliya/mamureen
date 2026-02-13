<?php
require_once(__DIR__ . '/../session.php');

$query = "SELECT its_id,miqaat_mauze,miqaat_jamiat,`zone`,fullname FROM users_mamureen WHERE its_id = $user_its UNION SELECT its_id,miqaat_mauze,miqaat_jamiat,`zone`,fullname FROM users_admin WHERE its_id = $user_its";
$result = mysqli_query($mysqli, $query);
$rowdata = $result->fetch_assoc();

$mauze = $rowdata['miqaat_mauze'];
$zone = $rowdata['zone'];
$jamiat = $rowdata['miqaat_jamiat'];
$fullname = $rowdata['fullname'];

$query = "SELECT `city` FROM `its_jamaat_master` WHERE `jamaat` = '$mauze'";
$result = mysqli_query($coremysqli, $query);
$aamilsahebDetails = $result->fetch_assoc();
$city = $aamilsahebDetails['city'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Mamureen Dashboard - Shehrullah 1445h</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <link href="<?= MODULE_PATH ?>assets/img/favicon.png" rel="icon">
  <link href="<?= MODULE_PATH ?>assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <link href="<?= MODULE_PATH ?>assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= MODULE_PATH ?>assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= MODULE_PATH ?>assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="<?= MODULE_PATH ?>assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="<?= MODULE_PATH ?>assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="<?= MODULE_PATH ?>assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="<?= MODULE_PATH ?>assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <link href="<?= MODULE_PATH ?>assets/css/style.css?v=1.55" rel="stylesheet">
  <!-- Datatable CSS -->
  <link href='https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css' rel='stylesheet' type='text/css'>
  <link href='https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css' rel='stylesheet' type='text/css'>

</head>

<body>

  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="https://www.talabulilm.com/mamureen/assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">Barnamaj al-Quran wal Ilm</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="https://www.talabulilm.com/mumin_images/<?= $_SESSION[USER_ITS] ?>.png" alt="Profile" class="rounded-circle">
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?= $rowdata['fullname'] ?></h6>
              <span><?= $rowdata['miqaat_mauze'] ?></span>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="https://www.talabulilm.com/log-out/">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->
  <?php require_once(__DIR__ . '/sidebar.php'); ?>