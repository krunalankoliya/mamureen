<?php
$current_page = 'training_sessions';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

$message = null;

/**
 * Logic: Delete Training
 */
if (isset($_POST['delete_training'])) {
    $delete_id = (int) $_POST['delete_id'];
    try {
        $db->query("DELETE FROM `bqi_training_sessions` WHERE `id` = ? AND `user_its` = ?", [$delete_id, $user_its]);
        $message = ['text' => 'Record deleted successfully.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => 'Error: ' . $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Update Training
 */
if (isset($_POST['update_training'])) {
    $edit_id = (int) $_POST['edit_id'];
    try {
        $db->query("UPDATE `bqi_training_sessions` SET `description` = ?, `session_date` = ?, `duration_minutes` = ?, `attendee_count` = ? WHERE `id` = ? AND `user_its` = ?", [
            $_POST['description'], $_POST['session_date'], (int)$_POST['duration_minutes'], (int)$_POST['attendee_count'], $edit_id, $user_its
        ]);
        $message = ['text' => 'Record updated successfully.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => 'Error: ' . $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Handle Submit (Add)
 */
if (isset($_POST['submit'])) {
    $errors = [];
    $uploaded_files = [];

    // Simple File Upload Wrapper
    foreach (['upload_photo_1', 'upload_photo_2', 'upload_photo_3'] as $tag) {
        if (isset($_FILES[$tag]) && $_FILES[$tag]['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES[$tag]['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png']) && $_FILES[$tag]['size'] <= 4194304) {
                $newName = $user_its . $tag . date('YmdHis') . '.' . $ext;
                if (move_uploaded_file($_FILES[$tag]['tmp_name'], __DIR__ . '/user_uploads/' . $newName)) {
                    $uploaded_files[$tag] = $newName;
                }
            }
        }
    }

    $selected_titles = $_POST['program_title_ids'] ?? [];
    if (empty($selected_titles)) $errors[] = 'Please select at least one program title.';
    if (empty($_POST['session_date'])) $errors[] = 'Session date is required.';

    if (empty($errors)) {
        try {
            $db->insert('bqi_training_sessions', [
                'user_its' => $user_its,
                'jamaat' => $mauze,
                'program_type' => $_POST['program_type'],
                'program_title_ids' => implode(',', array_map('intval', $selected_titles)),
                'session_date' => $_POST['session_date'],
                'description' => $_POST['description'],
                'duration_minutes' => (int)$_POST['duration_minutes'],
                'attendee_count' => (int)$_POST['attendee_count'],
                'upload_photo_1' => $uploaded_files['upload_photo_1'] ?? '',
                'upload_photo_2' => $uploaded_files['upload_photo_2'] ?? '',
                'upload_photo_3' => $uploaded_files['upload_photo_3'] ?? '',
                'uploaded_file_count' => count($uploaded_files)
            ]);
            $message = ['text' => 'Training session report submitted successfully.', 'tag' => 'success'];
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    } else {
        $message = ['text' => implode('<br>', $errors), 'tag' => 'danger'];
    }
}

// Fetch Master Data
$program_types = $db->fetchAll("SELECT DISTINCT `program_type` FROM `bqi_program_titles` WHERE `is_active` = 1 AND `program_type` != '' ORDER BY `program_type` ASC");
$titles_raw = $db->fetchAll("SELECT id, title_name FROM bqi_program_titles");
$titles_map = array_column($titles_raw, 'title_name', 'id');

$reports = $db->fetchAll("
    SELECT ts.*, um.fullname AS submitted_by 
    FROM bqi_training_sessions ts 
    LEFT JOIN users_mamureen um ON um.its_id = ts.user_its 
    WHERE ts.user_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = ?) 
    ORDER BY ts.id DESC", [$mauze]);
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Trainings & Sessions</h1>
            <p class="text-muted small mb-0">Record and monitor all local training baramij.</p>
        </div>
        <div>
            <span class="badge bg-light text-primary border px-3 py-2 rounded-pill">
                Reports: <?= count($reports) ?>
            </span>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['tag']; ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $message['text']; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Input Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Submit New Report</h5>
                <form method="post" enctype="multipart/form-data" id="uploadForm">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Program Type</label>
                        <select name="program_type" id="program_type_select" class="form-select" required>
                            <option value="">Select Category...</option>
                            <?php foreach ($program_types as $pt): ?>
                                <option value="<?= h($pt['program_type']) ?>"><?= h($pt['program_type']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Program Title(s)</label>
                        <div class="bg-light p-3 rounded-3 border" style="max-height: 150px; overflow-y: auto;" id="ts_filter_list">
                            <p class="small text-muted mb-0">Select a program type first...</p>
                        </div>
                        <div id="ts_hidden_inputs"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Session Date</label>
                        <input type="date" name="session_date" class="form-control" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Duration (Min)</label>
                            <input type="number" name="duration_minutes" class="form-control" placeholder="60" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Attendees</label>
                            <input type="number" name="attendee_count" class="form-control" placeholder="0" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Evidence / Photos (Max 3)</label>
                        <input type="file" name="upload_photo_1" class="form-control mb-2" accept="image/*" required>
                        <input type="file" name="upload_photo_2" class="form-control mb-2" accept="image/*">
                        <input type="file" name="upload_photo_3" class="form-control" accept="image/*">
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                        <i class="bi bi-cloud-arrow-up me-2"></i> Submit Report
                    </button>
                </form>
            </div>
        </div>

        <!-- History Table -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">Recent Activity</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Type & Title</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Attendees</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Date</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark"><?= h($r['program_type']) ?></div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                            <?php 
                                            $ids = explode(',', $r['program_title_ids']);
                                            $names = [];
                                            foreach($ids as $id) $names[] = $titles_map[$id] ?? 'Unknown';
                                            echo h(implode(', ', $names));
                                            ?>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3"><?= $r['attendee_count'] ?></div>
                                    </td>
                                    <td class="py-3 small text-secondary">
                                        <?= date('d M, Y', strtotime($r['session_date'])) ?>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <button class="btn btn-light btn-sm rounded-pill edit-session-btn" 
                                                data-id="<?= $r['id'] ?>"
                                                data-desc="<?= h($r['description']) ?>"
                                                data-date="<?= h($r['session_date']) ?>"
                                                data-duration="<?= $r['duration_minutes'] ?>"
                                                data-attendees="<?= $r['attendee_count'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#editSessionModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="delete_id" value="<?= $r['id'] ?>">
                                            <button type="submit" name="delete_training" class="btn btn-light btn-sm rounded-pill text-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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

<!-- Edit Modal -->
<div class="modal fade" id="editSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="post">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Edit Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <input type="hidden" name="edit_id" id="edit_session_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Date</label>
                        <input type="date" name="session_date" id="edit_session_date" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Duration (Min)</label>
                            <input type="number" name="duration_minutes" id="edit_session_duration" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Attendees</label>
                            <input type="number" name="attendee_count" id="edit_session_attendees" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" id="edit_session_desc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_training" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/inc/footer.php'; 
require_once __DIR__ . '/inc/js-block.php'; 
?>
<script src="assets/js/training_sessions.js"></script>
