<?php
$current_page = 'dashboard';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

// Data fetching via the new DB Class
$mauze_its_subquery = "SELECT its_id FROM users_mamureen WHERE miqaat_mauze = ?";

// Stats
$stats = [
    'parties' => $db->count("SELECT COUNT(*) FROM bqi_zakereen_parties WHERE is_active = 1 AND added_its IN ($mauze_its_subquery)", [$mauze]),
    'farzando' => $db->count("SELECT COUNT(*) FROM bqi_zakereen_farzando WHERE added_its IN ($mauze_its_subquery)", [$mauze]),
    'training' => $db->count("SELECT COUNT(*) FROM bqi_training_sessions WHERE user_its IN ($mauze_its_subquery) AND program_type LIKE '%Zakereen Farzando Training%'", [$mauze]),
    'social' => $db->count("SELECT COUNT(*) FROM bqi_training_sessions WHERE user_its IN ($mauze_its_subquery) AND program_type LIKE '%Social Media Awareness%'", [$mauze]),
    'challenges' => $db->count("SELECT COUNT(*) FROM bqi_challenges_solutions WHERE added_its IN ($mauze_its_subquery)", [$mauze]),
    'noteworthy' => $db->count("SELECT COUNT(*) FROM bqi_noteworthy_experiences WHERE added_its IN ($mauze_its_subquery)", [$mauze]),
];

// Fetch Aamil Info from Core DB
$aamil = [];
if (isset($core_pdo)) {
    $stmt = $core_pdo->prepare("SELECT u.full_name_en, j.primary_email, j.aamil_masool_its_id FROM its_jamaat_master j LEFT JOIN user u ON j.aamil_masool_its_id = u.its_id WHERE j.jamaat = ?");
    $stmt->execute([$mauze]);
    $aamil = $stmt->fetch() ?: [];
}
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Dashboard Overview</h1>
            <p class="text-muted small mb-0">Welcome back, <?= h($fullname) ?>. Here is what's happening today.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-download me-1"></i> Export Data
            </button>
            <a href="raise-ticket.php" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> New Entry
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Section: Key Metrics -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="stat-box">
                    <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= $stats['parties'] ?></div>
                        <div class="stat-label">Zakereen Parties</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="stat-box">
                    <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= $stats['farzando'] ?></div>
                        <div class="stat-label">Active Farzando</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="stat-box">
                    <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= $stats['training'] ?></div>
                        <div class="stat-label">Sessions Held</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="stat-box">
                    <div class="stat-icon-wrapper bg-info bg-opacity-10 text-info">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= $stats['social'] ?></div>
                        <div class="stat-label">Social Impact</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Performance Metrics</span>
                    <i class="bi bi-three-dots text-muted"></i>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 bg-light bg-opacity-50 border border-dashed text-center">
                                <h2 class="fw-bold mb-1"><?= $stats['challenges'] ?></h2>
                                <p class="text-muted small mb-0">Challenges Reported</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 bg-light bg-opacity-50 border border-dashed text-center">
                                <h2 class="fw-bold mb-1 text-primary"><?= $stats['noteworthy'] ?></h2>
                                <p class="text-muted small mb-0">Noteworthy Experiences</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Navigation Blocks -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 p-4 border-0 bg-primary text-white" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                        <h5 class="fw-bold mb-3">Quick Submission</h5>
                        <p class="small opacity-75 mb-4">Quickly register a new Zakereen party or farzando record in just few clicks.</p>
                        <div class="mt-auto">
                            <a href="zakereen_parties.php" class="btn btn-light btn-sm rounded-pill px-4 fw-bold text-primary">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 p-4">
                        <h5 class="fw-bold mb-3">Resources</h5>
                        <p class="text-muted small mb-4">Download the latest documents, guidelines, and resource kits for this miqaat.</p>
                        <div class="mt-auto">
                            <a href="resources.php" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold">View Library</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Right Info Column -->
        <div class="col-lg-4">
            <!-- Aamil Saheb Profile Card -->
            <div class="card text-center p-4 border-0 bg-white mb-4">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    <?php if (!empty($aamil['aamil_masool_its_id'])): ?>
                        <img src="<?= user_photo_url($aamil['aamil_masool_its_id']) ?>" class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #f8fafc;">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px;">
                            <i class="bi bi-person text-secondary h1 mb-0"></i>
                        </div>
                    <?php endif; ?>
                    <span class="position-absolute bottom-0 end-0 bg-success border border-white border-2 rounded-circle" style="width: 18px; height: 18px;"></span>
                </div>
                <h5 class="fw-bold mb-0"><?= h($aamil['full_name_en'] ?? 'N/A') ?></h5>
                <p class="text-muted small mb-3">Aamil Saheb / Masool</p>
                <a href="mailto:<?= h($aamil['primary_email'] ?? '') ?>" class="btn btn-accent btn-sm rounded-pill w-100 border py-2">
                    <i class="bi bi-envelope me-2"></i>Contact
                </a>
            </div>

            <!-- Helpful Links -->
            <div class="card p-4">
                <h6 class="fw-bold mb-3">Recent Downloads</h6>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-2 rounded text-danger"><i class="bi bi-file-pdf h5 mb-0"></i></div>
                        <div>
                            <div class="fw-bold small">Guidelines 1447.pdf</div>
                            <div class="text-muted" style="font-size: 0.75rem;">2.4 MB • 2 days ago</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-2 rounded text-success"><i class="bi bi-file-excel h5 mb-0"></i></div>
                        <div>
                            <div class="fw-bold small">Format_Report.xlsx</div>
                            <div class="text-muted" style="font-size: 0.75rem;">1.1 MB • 5 days ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php 
require_once __DIR__ . '/inc/footer.php'; 
require_once __DIR__ . '/inc/js-block.php'; 
?>
