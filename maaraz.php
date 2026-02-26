<?php
$current_page = 'maaraz';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';
?>

<main id="main" class="main main-content">
    <div class="row min-vh-100 align-items-center justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card border-0 shadow-sm p-5 rounded-5">
                <div class="mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 100px; height: 100px;">
                        <i class="bi bi-rocket-takeoff h1 mb-0"></i>
                    </div>
                </div>
                <h1 class="fw-bold text-dark mb-2">Ma'araz Underway</h1>
                <p class="text-muted mb-4 px-lg-5">The Ma'araz (Exhibition) module is currently being finalized to bring you a comprehensive visual experience of your khidmat. Stay tuned for the launch!</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="index.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Return to Dashboard</a>
                    <a href="support_tickets.php" class="btn btn-outline-secondary rounded-pill px-4 py-2">Get Updates</a>
                </div>
                
                <div class="mt-5 pt-4 border-top">
                    <div class="small fw-bold text-uppercase text-muted ls-wide mb-3" style="letter-spacing: 0.1rem; font-size: 0.7rem;">Launch Progress</div>
                    <div class="progress rounded-pill shadow-sm" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
