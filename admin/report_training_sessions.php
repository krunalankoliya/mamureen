<?php
require_once __DIR__ . '/../session.php';
$current_page = 'report_training_sessions';
require_once __DIR__ . '/../inc/header.php';

// Titles map
$titles_map = [];
$tr = mysqli_query($mysqli, "SELECT id, title_name FROM `bqi_program_titles` ORDER BY id");
while ($row = $tr->fetch_assoc()) {
    $titles_map[$row['id']] = $row['title_name'];
}

// Fetch all records
$result  = mysqli_query($mysqli, "SELECT s.*, u.fullname AS submitted_by
    FROM `bqi_training_sessions` s
    LEFT JOIN `users_mamureen` u ON s.user_its = u.its_id
    ORDER BY s.id DESC");
$records = $result->fetch_all(MYSQLI_ASSOC);
?>
<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        Training Sessions Report
                        <span class="badge bg-secondary ms-2"><?= count($records) ?> records</span>
                    </h5>
                    <div id="app-config" data-base-url="<?= htmlspecialchars(MODULE_PATH) ?>"></div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Program Type</th>
                                    <th>Program Title(s)</th>
                                    <th>Session Date</th>
                                    <th>Duration (min)</th>
                                    <th>Attendees</th>
                                    <th>Jamaat</th>
                                    <th>Submitted By</th>
                                    <th>Photos</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $i => $r):
                                    // Resolve title names
                                    $title_ids   = !empty($r['program_title_ids']) ? explode(',', $r['program_title_ids']) : [];
                                    $title_names = implode(', ', array_map(fn($id) => $titles_map[(int)$id] ?? '#' . $id, $title_ids));

                                    // Build photos for files JSON
                                    $files_arr = [];
                                    foreach (['upload_photo_1', 'upload_photo_2', 'upload_photo_3'] as $col) {
                                        if (!empty($r[$col])) {
                                            $ext = strtolower(pathinfo($r[$col], PATHINFO_EXTENSION));
                                            $files_arr[] = ['path' => $r[$col], 'type' => $ext, 'name' => $col];
                                        }
                                    }
                                    $photo_count = count($files_arr);

                                    // Fields JSON
                                    $fields_arr = [
                                        ['label' => 'Program Type',   'value' => $r['program_type'] ?? ''],
                                        ['label' => 'Program Title(s)','value' => $title_names],
                                        ['label' => 'Description',    'value' => $r['description'] ?? ''],
                                        ['label' => 'Session Date',   'value' => date('d-M-Y', strtotime($r['session_date']))],
                                        ['label' => 'Duration',       'value' => $r['duration_minutes'] . ' minutes'],
                                        ['label' => 'Attendees',      'value' => (string)$r['attendee_count']],
                                        ['label' => 'Jamaat',         'value' => $r['jamaat'] ?? ''],
                                        ['label' => 'Submitted By',   'value' => ($r['submitted_by'] ?? '') . ' (' . $r['user_its'] . ')'],
                                        ['label' => 'Submitted On',   'value' => date('d-M-Y H:i', strtotime($r['added_ts']))],
                                    ];

                                    $fields_json = htmlspecialchars(json_encode($fields_arr, JSON_UNESCAPED_UNICODE), ENT_QUOTES);
                                    $files_json  = htmlspecialchars(json_encode($files_arr,  JSON_UNESCAPED_UNICODE), ENT_QUOTES);
                                ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($r['program_type']) ?></td>
                                        <td><?= htmlspecialchars(mb_strimwidth($title_names, 0, 40, '…')) ?></td>
                                        <td><?= date('d-M-Y', strtotime($r['session_date'])) ?></td>
                                        <td><?= $r['duration_minutes'] ?></td>
                                        <td><?= $r['attendee_count'] ?></td>
                                        <td><?= htmlspecialchars($r['jamaat'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($r['submitted_by'] ?? $r['user_its']) ?></td>
                                        <td>
                                            <?php if ($photo_count > 0): ?>
                                                <span class="badge bg-info"><?= $photo_count ?> photo<?= $photo_count > 1 ? 's' : '' ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-btn"
                                                data-title="<?= htmlspecialchars($r['program_type']) ?>"
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
