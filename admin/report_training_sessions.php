<?php
$current_page = 'report_training_sessions';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$titles_raw = $db->fetchAll("SELECT id, title_name FROM bqi_program_titles");
$titles_map = array_column($titles_raw, 'title_name', 'id');

$records = $db->fetchAll("
    SELECT ts.*, um.fullname AS submitted_by, um.miqaat_mauze 
    FROM bqi_training_sessions ts
    LEFT JOIN users_mamureen um ON um.its_id = ts.user_its
    ORDER BY ts.added_ts DESC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Training Sessions Report</h1>
            <p class="text-muted small mb-0">Global log of all seminars and training baramij conducted during this miqaat.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= MODULE_PATH ?>admin/bqi_dashboard_export.php?type=zakereen_training" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Data
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Session Logs</h5>
                    <span class="badge bg-light text-primary border rounded-pill px-3"><?= count($records) ?> Reports</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Program Type & Title</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Participants</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Location</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Evidence</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Registrar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark small"><?= h($r['program_type']) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem;">
                                            <?php 
                                            $ids = explode(',', $r['program_title_ids']);
                                            $names = [];
                                            foreach($ids as $id) $names[] = $titles_map[$id] ?? 'N/A';
                                            echo h(implode(', ', $names));
                                            ?>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3"><?= $r['attendee_count'] ?> Members</div>
                                        <div class="text-muted x-small mt-1" style="font-size: 0.65rem;"><?= $r['duration_minutes'] ?> mins • <?= date('d M', strtotime($r['session_date'])) ?></div>
                                    </td>
                                    <td class="py-3">
                                        <div class="small fw-semibold text-dark"><?= h($r['miqaat_mauze']) ?></div>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex gap-1">
                                            <?php for($i=1; $i<=3; $i++): $img = $r["upload_photo_$i"]; if($img): ?>
                                                <a href="<?= MODULE_PATH ?>user_uploads/<?= $img ?>" target="_blank" class="bg-light rounded p-1 border hover-shadow-sm transition-all">
                                                    <i class="bi bi-image text-primary"></i>
                                                </a>
                                            <?php endif; endfor; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="small fw-bold text-dark"><?= h($r['submitted_by'] ?? 'N/A') ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem;">Added: <?= date('d M, Y', strtotime($r['added_ts'])) ?></div>
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
