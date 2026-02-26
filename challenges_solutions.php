<?php
$current_page = 'challenges_solutions';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/inc/header.php';

$message = null;

/**
 * Logic: Submit Challenge
 */
if (isset($_POST['submit_challenge'])) {
    try {
        $record_id = $db->insert('bqi_challenges_solutions', [
            'category_id' => (int)$_POST['category_id'],
            'challenge_text' => $_POST['challenge_text'],
            'solution_text' => $_POST['solution_text'],
            'user_its' => $user_its,
            'jamaat' => $mauze,
            'added_its' => $user_its
        ]);

        // Process attachments
        if (isset($_FILES['attachments'])) {
            foreach ($_FILES['attachments']['name'] as $i => $name) {
                if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $newName = $user_its . '_challenge_' . date('YmdHis') . '_' . $i . '.' . $ext;
                    if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], __DIR__ . "/user_uploads/" . $newName)) {
                        $db->insert('bqi_file_attachments', [
                            'module' => 'challenge',
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
        $message = ['text' => 'Challenge & Solution report submitted.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

// Master Data
$categories = $db->fetchAll("SELECT * FROM `bqi_categories` WHERE `category_type` = 'challenge' AND `is_active` = 1 ORDER BY `sort_order` ASC");
$records = $db->fetchAll("
    SELECT cs.*, c.category_name, um.fullname AS submitted_by 
    FROM bqi_challenges_solutions cs
    LEFT JOIN bqi_categories c ON cs.category_id = c.id
    LEFT JOIN users_mamureen um ON um.its_id = cs.added_its
    WHERE cs.added_its IN (SELECT its_id FROM users_mamureen WHERE miqaat_mauze = ?)
    ORDER BY cs.added_ts DESC", [$mauze]);
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Challenges & Solutions</h1>
            <p class="text-muted small mb-0">Document critical obstacles and the strategies used to overcome them.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['tag']; ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $message['text']; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Submit Report</h5>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Impact Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category...</option>
                            <?php foreach ($categories as $c) : ?>
                                <option value="<?= $c['id'] ?>"><?= h($c['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">The Challenge</label>
                        <textarea name="challenge_text" class="form-control" rows="3" placeholder="Describe the obstacle..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">The Applied Solution</label>
                        <textarea name="solution_text" class="form-control" rows="3" placeholder="How was it resolved?" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Evidence (Files/Photos)</label>
                        <input type="file" name="attachments[]" class="form-control" accept="image/*,video/*,audio/*,.pdf" multiple>
                    </div>

                    <button type="submit" name="submit_challenge" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                        Submit Report
                    </button>
                </form>
            </div>
        </div>

        <!-- History -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">Submission History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Challenge Details</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Solution</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Registrar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 mb-1" style="font-size: 0.65rem;"><?= h($r['category_name']) ?></div>
                                        <div class="text-dark small fw-semibold"><?= h(mb_substr($r['challenge_text'], 0, 60)) ?>...</div>
                                    </td>
                                    <td class="py-3">
                                        <div class="text-muted small"><?= h(mb_substr($r['solution_text'], 0, 60)) ?>...</div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="small fw-bold text-dark"><?= h($r['submitted_by'] ?? 'N/A') ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?= date('d M, Y', strtotime($r['added_ts'])) ?></div>
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
