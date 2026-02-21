<?php
require_once __DIR__ . '/../session.php';
$current_page = 'report_individual_tafheem';
require_once __DIR__ . '/../inc/header.php';

// Reasons map
$reasons_map = [];
$rr = mysqli_query($mysqli, "SELECT id, reason_name FROM `bqi_tafheem_reasons` ORDER BY id");
while ($row = $rr->fetch_assoc()) {
    $reasons_map[$row['id']] = $row['reason_name'];
}

// Pre-load all attachments for tafheem
$attachments = [];
$ar = mysqli_query($mysqli, "SELECT * FROM `bqi_file_attachments` WHERE `module` = 'tafheem'");
while ($row = $ar->fetch_assoc()) {
    $attachments[$row['record_id']][] = $row;
}

// Fetch all records
$result  = mysqli_query($mysqli, "SELECT t.*, u.fullname AS submitted_by
    FROM `bqi_individual_tafheem` t
    LEFT JOIN `users_mamureen` u ON t.added_its = u.its_id
    ORDER BY t.id DESC");
$records = $result->fetch_all(MYSQLI_ASSOC);
?>
<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        Individual Tafheem Report
                        <span class="badge bg-secondary ms-2"><?= count($records) ?> records</span>
                    </h5>
                    <div id="app-config" data-base-url="<?= htmlspecialchars(MODULE_PATH) ?>"></div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Target ITS</th>
                                    <th>Target Name</th>
                                    <th>Type</th>
                                    <th>Jamaat</th>
                                    <th>Submitted By</th>
                                    <th>Date</th>
                                    <th>Files</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $i => $r):
                                    // Build reason names
                                    $reason_ids   = array_filter(array_map('intval', explode(',', $r['reason_id'] ?? '')));
                                    $reason_names = implode(', ', array_map(fn($id) => $reasons_map[$id] ?? '#' . $id, $reason_ids));

                                    // Build files JSON
                                    $record_files = $attachments[$r['id']] ?? [];
                                    $files_arr    = array_map(fn($f) => ['path' => $f['file_path'], 'type' => $f['file_type'], 'name' => $f['file_name']], $record_files);

                                    // Build fields JSON
                                    $fields_arr = [
                                        ['label' => 'Target ITS',  'value' => (string)$r['target_its_id']],
                                        ['label' => 'Target Name', 'value' => $r['target_name']],
                                        ['label' => 'Type',        'value' => $r['type'] ?? ''],
                                        ['label' => 'Reason(s)',   'value' => $reason_names],
                                        ['label' => 'Jamaat',      'value' => $r['jamaat'] ?? ''],
                                        ['label' => 'Details',     'value' => $r['report_details'] ?? ''],
                                        ['label' => 'Submitted By','value' => ($r['submitted_by'] ?? '') . ' (' . $r['added_its'] . ')'],
                                        ['label' => 'Date',        'value' => date('d-M-Y H:i', strtotime($r['added_ts']))],
                                    ];

                                    $fields_json = htmlspecialchars(json_encode($fields_arr, JSON_UNESCAPED_UNICODE), ENT_QUOTES);
                                    $files_json  = htmlspecialchars(json_encode($files_arr,  JSON_UNESCAPED_UNICODE), ENT_QUOTES);
                                    $file_count  = count($record_files);
                                ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= $r['target_its_id'] ?></td>
                                        <td><?= htmlspecialchars($r['target_name']) ?></td>
                                        <td><?= htmlspecialchars($r['type'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($r['jamaat'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($r['submitted_by'] ?? $r['added_its']) ?></td>
                                        <td><?= date('d-M-Y', strtotime($r['added_ts'])) ?></td>
                                        <td>
                                            <?php if ($file_count > 0): ?>
                                                <span class="badge bg-info"><?= $file_count ?> file<?= $file_count > 1 ? 's' : '' ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-btn"
                                                data-title="<?= htmlspecialchars($r['target_name']) ?>"
                                                data-fields="<?= $fields_json ?>"
                                                data-files="<?= $files_json ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-fields"></div>
                <div id="modal-files"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
<script src="<?= MODULE_PATH ?>assets/js/admin_reports.js"></script>
