<?php
$current_page = 'individual_tafheem';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

$message = null;
$verified_user = null;

$program_types = [
    'Zakereen Farzando Training',
    'Social Media Awareness',
    'Shaadi Tafheem',
];

/**
 * Logic: Verify Member
 */
if (isset($_POST['verify_its'])) {
    $verify_id = (int)($_POST['verify_its_id'] ?? 0);
    if ($verify_id > 10000000) {
        $verified_user = ApiService::getUserDetails($verify_id);
        if (!$verified_user) $message = ['text' => "ITS ID $verify_id not found.", 'tag' => 'danger'];
    }
}

/**
 * Logic: Submit Report
 */
if (isset($_POST['submit_tafheem'])) {
    try {
        $target_id = (int)$_POST['target_its_id'];
        $reasons = $_POST['reason_ids'] ?? [];
        
        $record_id = $db->insert('bqi_individual_tafheem', [
            'target_its_id' => $target_id,
            'target_name' => $_POST['target_name'],
            'type' => $_POST['type'],
            'reason_id' => implode(',', array_map('intval', $reasons)),
            'report_details' => $_POST['report_details'],
            'user_its' => $user_its,
            'jamaat' => $mauze,
            'added_its' => $user_its
        ]);

        // Process attachments
        if (isset($_FILES['attachments'])) {
            foreach ($_FILES['attachments']['name'] as $i => $name) {
                if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $newName = $user_its . '_tafheem_' . date('YmdHis') . '_' . $i . '.' . $ext;
                    if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], __DIR__ . "/user_uploads/" . $newName)) {
                        $db->insert('bqi_file_attachments', [
                            'module' => 'tafheem',
                            'record_id' => $record_id,
                            'file_name' => $name,
                            'file_path' => $newName,
                            'file_type' => $ext,
                            'file_size' => $_FILES['attachments']['size'][$i],
                            'added_its' => $user_its
                        ]);
                    }
                }
            }
        }
        $message = ['text' => 'Tafheem report submitted successfully.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

// Fetch Master Data
$reasons_raw = $db->fetchAll("SELECT * FROM `bqi_tafheem_reasons` WHERE `is_active` = 1 ORDER BY `sort_order` ASC");
$reasons_map = array_column($reasons_raw, 'reason_name', 'id');

$reports = $db->fetchAll("
    SELECT t.*, um.fullname AS submitted_by 
    FROM bqi_individual_tafheem t 
    LEFT JOIN users_mamureen um ON um.its_id = t.added_its 
    WHERE t.added_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = ?) 
    ORDER BY t.added_ts DESC", [$mauze]);
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Individual Tafheem</h1>
            <p class="text-muted small mb-0">Record personalized counseling and awareness sessions.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['tag']; ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $message['text']; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Report Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">Create New Report</h5>

                <?php if (!$verified_user): ?>
                    <form method="post" class="row g-2">
                        <div class="col-8">
                            <label class="form-label small fw-bold">Target ITS ID</label>
                            <input type="number" name="verify_its_id" class="form-control" placeholder="Enter ITS ID" required>
                        </div>
                        <div class="col-4 d-flex align-items-end">
                            <button type="submit" name="verify_its" class="btn btn-primary w-100 rounded-pill">Verify</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="p-3 rounded-4 bg-light border mb-4 d-flex align-items-center gap-3">
                        <img src="<?= user_photo_url($verified_user['its_id']) ?>" class="rounded-circle" width="48" height="48">
                        <div>
                            <div class="fw-bold small"><?= h($verified_user['full_name_en']) ?></div>
                            <div class="text-muted small"><?= $verified_user['its_id'] ?></div>
                        </div>
                        <a href="individual_tafheem.php" class="ms-auto btn btn-sm btn-outline-secondary rounded-pill border-0">Reset</a>
                    </div>

                    <form method="post" enctype="multipart/form-data" id="uploadForm">
                        <input type="hidden" name="target_its_id" value="<?= $verified_user['its_id'] ?>">
                        <input type="hidden" name="target_name" value="<?= $verified_user['full_name_en'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Session Type</label>
                            <select name="type" id="tf_type_select" class="form-select" required>
                                <option value="">Select Category...</option>
                                <?php foreach ($program_types as $pt): ?>
                                    <option value="<?= h($pt) ?>"><?= h($pt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Primary Reason(s)</label>
                            <div class="bg-light p-3 rounded-3 border" style="max-height: 150px; overflow-y: auto;" id="tf_filter_list">
                                <p class="small text-muted mb-0">Select a session type first...</p>
                            </div>
                            <div id="tf_hidden_inputs"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Report Details</label>
                            <textarea name="report_details" class="form-control" rows="4" placeholder="Detailed notes about the session..." required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Attachments (Optional)</label>
                            <input type="file" name="attachments[]" class="form-control" multiple accept="image/*">
                        </div>

                        <button type="submit" name="submit_tafheem" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                            Save Report
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- History -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">Tafheem History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Target Member</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Type</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= user_photo_url($r['target_its_id']) ?>" class="rounded-circle" width="36" height="36">
                                            <div>
                                                <div class="fw-bold text-dark"><?= h($r['target_name']) ?></div>
                                                <div class="text-muted small" style="font-size: 0.75rem;">ITS: <?= $r['target_its_id'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="small fw-semibold text-primary"><?= h($r['type']) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            <?php 
                                            $ids = explode(',', $r['reason_id']);
                                            $names = [];
                                            foreach($ids as $id) if(!empty($reasons_map[$id])) $names[] = $reasons_map[$id];
                                            echo h(implode(', ', $names));
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="small fw-bold"><?= date('d M, Y', strtotime($r['added_ts'])) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;">By: <?= h($r['submitted_by'] ?? 'System') ?></div>
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
require_once __DIR__ . '/inc/footer.php'; 
require_once __DIR__ . '/inc/js-block.php'; 
?>
<script src="assets/js/individual_tafheem.js"></script>
