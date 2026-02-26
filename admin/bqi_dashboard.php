<?php
$current_page = 'bqi_dashboard';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

// Stats via Database Class
$stats = [
    'parties'    => $db->count("SELECT COUNT(*) FROM bqi_zakereen_parties WHERE is_active = 1"),
    'farzando'   => $db->count("SELECT COUNT(*) FROM bqi_zakereen_farzando"),
    'training'   => $db->count("SELECT COUNT(*) FROM bqi_training_sessions WHERE program_type LIKE '%Zakereen Farzando Training%'"),
    'social'     => $db->count("SELECT COUNT(*) FROM bqi_training_sessions WHERE program_type LIKE '%Social Media Awareness%'"),
    'counseling' => $db->count("SELECT COUNT(*) FROM bqi_individual_tafheem WHERE type LIKE '%Social Media Awareness%'"),
    'shaadi_b'   => $db->count("SELECT COUNT(*) FROM bqi_training_sessions WHERE program_type LIKE '%Shaadi Tafheem%'"),
    'shaadi_t'   => $db->count("SELECT COUNT(*) FROM bqi_individual_tafheem WHERE type LIKE '%Shaadi Tafheem%'"),
    'challenges' => $db->count("SELECT COUNT(*) FROM bqi_challenges_solutions"),
    'noteworthy' => $db->count("SELECT COUNT(*) FROM bqi_noteworthy_experiences"),
    'khidmat'    => $db->count("SELECT COUNT(*) FROM bqi_khidmat_preparations"),
];

$export_url = MODULE_PATH . 'admin/bqi_dashboard_export.php?type=';
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Admin Control Center</h1>
            <p class="text-muted small mb-0">Global overview and data management for BQI 1447.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-primary btn-sm rounded-pill px-4 dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-cloud-download me-1"></i> Global Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3">
                    <li><a class="dropdown-item small" href="<?= $export_url ?>zakereen_farzando">All Farzando</a></li>
                    <li><a class="dropdown-item small" href="<?= $export_url ?>zakereen_training">All Trainings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item small fw-bold" href="#">Full System Backup</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- SECTION: ZAKEREEN -->
        <div class="col-12">
            <div class="card border-0 shadow-sm p-4">
                <h6 class="fw-bold text-primary text-uppercase small mb-4" style="letter-spacing: 0.05rem;">Zakereen Management</h6>
                <div class="row g-4">
                    <div class="col-md-4">
                        <a href="<?= $export_url ?>zakereen_parties" class="text-decoration-none">
                            <div class="p-4 rounded-4 bg-light border transition-all hover-shadow-sm d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3"><i class="bi bi-flag h4 mb-0"></i></div>
                                <div>
                                    <div class="stat-value small"><?= $stats['parties'] ?></div>
                                    <div class="text-muted small">Parties</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= $export_url ?>zakereen_farzando" class="text-decoration-none">
                            <div class="p-4 rounded-4 bg-light border transition-all hover-shadow-sm d-flex align-items-center gap-3">
                                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3"><i class="bi bi-person-badge h4 mb-0"></i></div>
                                <div>
                                    <div class="stat-value small"><?= $stats['farzando'] ?></div>
                                    <div class="text-muted small">Farzando</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= $export_url ?>zakereen_training" class="text-decoration-none">
                            <div class="p-4 rounded-4 bg-light border transition-all hover-shadow-sm d-flex align-items-center gap-3">
                                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3"><i class="bi bi-mortarboard h4 mb-0"></i></div>
                                <div>
                                    <div class="stat-value small"><?= $stats['training'] ?></div>
                                    <div class="text-muted small">Trainings</div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION: SOCIAL & SHAADI -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-success text-uppercase small mb-4" style="letter-spacing: 0.05rem;">Social Media Impact</h6>
                <div class="row g-3">
                    <div class="col-6">
                        <a href="<?= $export_url ?>social_media_baramij" class="text-decoration-none">
                            <div class="p-3 rounded-4 bg-light border text-center">
                                <div class="h3 fw-bold text-dark mb-0"><?= $stats['social'] ?></div>
                                <div class="text-muted small">Baramij</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= $export_url ?>social_media_counseling" class="text-decoration-none">
                            <div class="p-3 rounded-4 bg-light border text-center">
                                <div class="h3 fw-bold text-dark mb-0"><?= $stats['counseling'] ?></div>
                                <div class="text-muted small">Counseling</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-danger text-uppercase small mb-4" style="letter-spacing: 0.05rem;">Shaadi Umoor</h6>
                <div class="row g-3">
                    <div class="col-6">
                        <a href="<?= $export_url ?>shaadi_baramij" class="text-decoration-none">
                            <div class="p-3 rounded-4 bg-light border text-center">
                                <div class="h3 fw-bold text-dark mb-0"><?= $stats['shaadi_b'] ?></div>
                                <div class="text-muted small">Farzando Baramij</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= $export_url ?>shaadi_tafheem" class="text-decoration-none">
                            <div class="p-3 rounded-4 bg-light border text-center">
                                <div class="h3 fw-bold text-dark mb-0"><?= $stats['shaadi_t'] ?></div>
                                <div class="text-muted small">Individual Tafheem</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION: REPORTS -->
        <div class="col-12">
            <div class="card border-0 shadow-sm p-4">
                <h6 class="fw-bold text-secondary text-uppercase small mb-4" style="letter-spacing: 0.05rem;">Analytical Reports</h6>
                <div class="row g-4">
                    <div class="col-md-4">
                        <a href="<?= $export_url ?>challenges" class="text-decoration-none">
                            <div class="p-4 rounded-4 bg-dark text-white d-flex align-items-center gap-3 shadow-lg">
                                <div class="bg-white bg-opacity-20 p-3 rounded-3 text-warning"><i class="bi bi-exclamation-triangle h4 mb-0"></i></div>
                                <div>
                                    <div class="h4 fw-bold mb-0 text-white"><?= $stats['challenges'] ?></div>
                                    <div class="opacity-75 small">Challenges</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= $export_url ?>noteworthy" class="text-decoration-none">
                            <div class="p-4 rounded-4 bg-dark text-white d-flex align-items-center gap-3 shadow-lg">
                                <div class="bg-white bg-opacity-20 p-3 rounded-3 text-info"><i class="bi bi-star h4 mb-0"></i></div>
                                <div>
                                    <div class="h4 fw-bold mb-0 text-white"><?= $stats['noteworthy'] ?></div>
                                    <div class="opacity-75 small">Noteworthy</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= $export_url ?>khidmat" class="text-decoration-none">
                            <div class="p-4 rounded-4 bg-dark text-white d-flex align-items-center gap-3 shadow-lg">
                                <div class="bg-white bg-opacity-20 p-3 rounded-3 text-success"><i class="bi bi-tools h4 mb-0"></i></div>
                                <div>
                                    <div class="h4 fw-bold mb-0 text-white"><?= $stats['khidmat'] ?></div>
                                    <div class="opacity-75 small">Preparations</div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
<?php require_once __DIR__ . '/../inc/js-block.php'; ?>
