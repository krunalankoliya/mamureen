<?php
$current_page = 'report_zakereen_farzando';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$records = $db->fetchAll("
    SELECT f.*, p.party_name, um.fullname AS added_by, um.miqaat_mauze 
    FROM bqi_zakereen_farzando f
    LEFT JOIN bqi_zakereen_parties p ON f.party_id = p.id
    LEFT JOIN users_mamureen um ON um.its_id = f.added_its
    ORDER BY f.added_ts DESC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Farzando Registry Report</h1>
            <p class="text-muted small mb-0">Complete list of participants registered under all Zakereen parties.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= MODULE_PATH ?>admin/bqi_dashboard_export.php?type=zakereen_farzando" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Full CSV
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Participant Directory</h5>
                    <span class="badge bg-light text-primary border rounded-pill px-3"><?= count($records) ?> Farzando</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Farzand Details</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Assigned Party</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Home Jamaat</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Method</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Registrar info</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= user_photo_url($r['its_id']) ?>" class="rounded-circle border shadow-sm" width="40" height="40">
                                            <div>
                                                <div class="fw-bold text-dark small"><?= h($r['full_name']) ?></div>
                                                <div class="text-muted" style="font-size: 0.7rem;">ITS: <?= $r['its_id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="small fw-semibold text-primary"><?= h($r['party_name'] ?? 'N/A') ?></div>
                                    </td>
                                    <td class="py-3 small text-secondary">
                                        <?= h($r['jamaat']) ?>
                                    </td>
                                    <td class="py-3">
                                        <?php if ($r['is_bulk_upload']): ?>
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2" style="font-size: 0.65rem;">BULK</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2" style="font-size: 0.65rem;">MANUAL</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="small fw-bold text-dark"><?= h($r['added_by'] ?? 'System') ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem;"><?= h($r['miqaat_mauze']) ?></div>
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
