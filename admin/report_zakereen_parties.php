<?php
$current_page = 'report_zakereen_parties';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$records = $db->fetchAll("
    SELECT p.*, u.fullname AS added_by, u.miqaat_mauze 
    FROM `bqi_zakereen_parties` p 
    LEFT JOIN `users_mamureen` u ON p.added_its = u.its_id 
    ORDER BY p.added_ts DESC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Zakereen Parties Report</h1>
            <p class="text-muted small mb-0">Full registry of parties registered across all miqaat locations.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= MODULE_PATH ?>admin/bqi_dashboard_export.php?type=zakereen_parties" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Master CSV
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Global Registry</h5>
                    <span class="badge bg-light text-primary border rounded-pill px-3"><?= count($records) ?> Records</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Party Information</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Registrar Details</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Miqaat Mauze</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Status</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark"><?= h($r['party_name']) ?></div>
                                        <div class="text-muted small" style="font-size: 0.65rem;">ID: #<?= $r['id'] ?></div>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= user_photo_url($r['added_its']) ?>" class="rounded-circle border" width="28" height="28">
                                            <div>
                                                <div class="small fw-semibold text-dark"><?= h($r['added_by'] ?? 'N/A') ?></div>
                                                <div class="text-muted" style="font-size: 0.65rem;">ITS: <?= $r['added_its'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2" style="font-size: 0.75rem;"><?= h($r['miqaat_mauze']) ?></span>
                                    </td>
                                    <td class="py-3">
                                        <?php if ($r['is_active']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="small fw-bold text-dark"><?= date('d M, Y', strtotime($r['added_ts'])) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php 
require_once __DIR__ . '/../inc/footer.php'; 
require_once __DIR__ . '/../inc/js-block.php'; 
?>
