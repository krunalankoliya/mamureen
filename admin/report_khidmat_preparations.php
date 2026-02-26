<?php
$current_page = 'report_khidmat_preparations';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$records = $db->fetchAll("
    SELECT kp.*, c.category_name, um.fullname AS submitted_by, um.miqaat_mauze 
    FROM bqi_khidmat_preparations kp
    LEFT JOIN bqi_categories c ON kp.category_id = c.id
    LEFT JOIN users_mamureen um ON um.its_id = kp.added_its
    ORDER BY kp.added_ts DESC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Khidmat Preparations Report</h1>
            <p class="text-muted small mb-0">Global activity log detailing strategic and logistical preparations for khidmat.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= MODULE_PATH ?>admin/bqi_dashboard_export.php?type=khidmat" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Master CSV
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Preparation Logs</h5>
                    <span class="badge bg-light text-primary border rounded-pill px-3"><?= count($records) ?> Logs</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Task Description</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Location</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Attachments</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Registrar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 mb-2" style="font-size: 0.65rem;"><?= h($r['category_name']) ?></div>
                                        <div class="text-dark small fw-semibold" style="max-width: 400px;"><?= h($r['description_text']) ?></div>
                                    </td>
                                    <td class="py-3">
                                        <div class="small fw-semibold text-dark"><?= h($r['miqaat_mauze']) ?></div>
                                    </td>
                                    <td class="py-3">
                                        <?php 
                                        $files = $db->fetchAll("SELECT * FROM bqi_file_attachments WHERE module='preparation' AND record_id=?", [$r['id']]);
                                        foreach($files as $f): ?>
                                            <a href="<?= MODULE_PATH ?>user_uploads/<?= $f['file_path'] ?>" target="_blank" class="badge bg-light text-primary border text-decoration-none">
                                                <i class="bi bi-file-earmark"></i> Doc
                                            </a>
                                        <?php endforeach; ?>
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
