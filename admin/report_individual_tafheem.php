<?php
$current_page = 'report_individual_tafheem';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$reasons_raw = $db->fetchAll("SELECT id, reason_name FROM bqi_tafheem_reasons");
$reasons_map = array_column($reasons_raw, 'reason_name', 'id');

$records = $db->fetchAll("
    SELECT t.*, um.fullname AS submitted_by, um.miqaat_mauze 
    FROM bqi_individual_tafheem t
    LEFT JOIN users_mamureen um ON um.its_id = t.added_its
    ORDER BY t.added_ts DESC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Individual Tafheem Report</h1>
            <p class="text-muted small mb-0">Global log of all personalized counseling and awareness sessions conducted.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= MODULE_PATH ?>admin/bqi_dashboard_export.php?type=shaadi_tafheem" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Master CSV
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Session History</h5>
                    <span class="badge bg-light text-primary border rounded-pill px-3"><?= count($records) ?> Sessions Recorded</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Target Member</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Category & Reason</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Miqaat</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Registrar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= user_photo_url($r['target_its_id']) ?>" class="rounded-circle border" width="32" height="32">
                                            <div>
                                                <div class="fw-bold text-dark small"><?= h($r['target_name']) ?></div>
                                                <div class="text-muted" style="font-size: 0.65rem;">ITS: <?= $r['target_its_id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 mb-1" style="font-size: 0.65rem;"><?= h($r['type']) ?></div>
                                        <div class="text-muted small truncate" style="max-width: 250px;">
                                            <?php 
                                            $ids = explode(',', $r['reason_id']);
                                            $names = [];
                                            foreach($ids as $id) if(!empty($reasons_map[$id])) $names[] = $reasons_map[$id];
                                            echo h(implode(', ', $names));
                                            ?>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="small fw-semibold text-dark"><?= h($r['miqaat_mauze']) ?></div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="small fw-bold text-dark"><?= h($r['submitted_by'] ?? 'N/A') ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem;"><?= date('d M, Y', strtotime($r['added_ts'])) ?></div>
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
