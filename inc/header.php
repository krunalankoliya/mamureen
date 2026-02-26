<?php
require_once __DIR__ . '/../session.php';

/**
 * Modern Header Refactor
 * Implements a SaaS-style navigation
 */

$user_its = (int) $_SESSION[USER_ITS];

// Fetch Profile using the new OOP approach
$userData = $db->fetch("
    SELECT fullname, miqaat_mauze FROM users_mamureen WHERE its_id = ?
    UNION SELECT fullname, miqaat_mauze FROM users_admin WHERE its_id = ?
    UNION SELECT fullname, miqaat_mauze FROM users_sub_admin WHERE its_id = ?
    LIMIT 1", [$user_its, $user_its, $user_its]);

$fullname = $userData['fullname'] ?? 'Guest';
$mauze    = $userData['miqaat_mauze'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Mamureen 1447 | Dashboard</title>
  
  <link href="<?= MODULE_PATH ?>assets/img/favicon.png" rel="icon">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <link href="<?= MODULE_PATH ?>assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= MODULE_PATH ?>assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= MODULE_PATH ?>assets/css/style.css?v=1.6" rel="stylesheet">
  <link href="<?= MODULE_PATH ?>assets/css/modern.css?v=2.0" rel="stylesheet">
</head>

<body>

  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="container-fluid d-flex align-items-center justify-content-between px-4">
      
      <div class="d-flex align-items-center gap-3">
        <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
          <div class="bg-primary rounded-3 p-1 me-2 shadow-sm">
            <img src="<?= MODULE_PATH ?>assets/img/logo_1.jpeg" alt="" style="height: 32px; border-radius: 4px;">
          </div>
          <span class="d-none d-lg-block fw-bold h5 mb-0 text-dark">Mamureen<span class="text-primary">1447</span></span>
        </a>
        <button class="btn btn-light btn-sm toggle-sidebar-btn d-lg-none">
          <i class="bi bi-list"></i>
        </button>
      </div>

      <nav class="header-nav">
        <ul class="d-flex align-items-center gap-3 list-unstyled mb-0">
          
          <li class="nav-item d-none d-md-block">
            <div class="bg-light rounded-pill px-3 py-1 small text-muted border">
              <i class="bi bi-geo-alt-fill text-primary me-1"></i> <?= h($mauze) ?>
            </div>
          </li>

          <li class="nav-item dropdown pe-3">
            <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
              <div class="position-relative">
                <img src="<?= user_photo_url($user_its) ?>" alt="Profile" class="rounded-circle border border-2 border-white shadow-sm" style="width: 36px; height: 36px; object-fit: cover;">
                <span class="position-absolute bottom-0 end-0 bg-success border border-white border-2 rounded-circle" style="width: 10px; height: 10px;"></span>
              </div>
              <span class="d-none d-md-block dropdown-toggle ps-2 fw-semibold text-dark"><?= h($fullname) ?></span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile border-0 shadow-lg p-2 rounded-4 mt-3">
              <li class="dropdown-header text-start py-3 px-3">
                <h6 class="fw-bold mb-0"><?= h($fullname) ?></h6>
                <p class="text-muted small mb-0"><?= h($mauze) ?></p>
              </li>
              <li><hr class="dropdown-divider opacity-50"></li>
              <li>
                <a class="dropdown-item d-flex align-items-center py-2 rounded-3" href="https://www.talabulilm.com/log-out/">
                  <i class="bi bi-box-arrow-right text-danger me-2"></i>
                  <span>Sign Out</span>
                </a>
              </li>
            </ul>
          </li>

        </ul>
      </nav>

    </div>
  </header>

  <?php require_once __DIR__ . '/sidebar.php'; ?>
