<?php
$current_page = 'manage_google_forms';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$message = null;

/**
 * Logic: Add Form
 */
if (isset($_POST['submit'])) {
    try {
        $db->insert('google_forms', [
            'upload_date' => date('Y-m-d'),
            'form_title' => $_POST['form_title'],
            'form_link' => $_POST['form_link'],
            'added_its' => $user_its
        ]);
        $message = ['text' => 'Daily form published successfully.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Update
 */
if (isset($_POST['update'])) {
    try {
        $db->query("UPDATE google_forms SET form_title = ?, form_link = ? WHERE id = ?", [$_POST['form_title'], $_POST['form_link'], (int)$_POST['id']]);
        $message = ['text' => 'Form configuration updated.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Delete
 */
if (isset($_POST['delete'])) {
    $db->query("DELETE FROM google_forms WHERE id = ?", [(int)$_POST['id']]);
    $message = ['text' => 'Form removed.', 'tag' => 'info'];
}

$forms = $db->fetchAll("SELECT * FROM `google_forms` ORDER BY id DESC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Feedback Forms Console</h1>
            <p class="text-muted small mb-0">Manage the dynamic list of external reporting forms for the field team.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['tag']; ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $message['text']; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">Publish New Form</h5>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Form Title</label>
                        <input type="text" name="form_title" class="form-control" placeholder="e.g. Session Feedback" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Embedded Link / URL</label>
                        <textarea name="form_link" class="form-control" rows="5" placeholder="Paste form link here..." required></textarea>
                        <div class="mt-2 p-2 bg-light rounded small text-muted">
                            Use placeholders: <code>ITS_ID</code>, <code>NAME</code>, <code>MAUZE</code> for auto-fill.
                        </div>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm">
                        Confirm Publication
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Active Forms</h5>
                    <span class="badge bg-light text-primary border rounded-pill px-3"><?= count($forms) ?> Total</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Form Details</th>
                                <th class="py-3 small text-muted text-uppercase border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($forms as $f): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark small"><?= h($f['form_title']) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem;">Created: <?= date('d M, Y', strtotime($f['upload_date'])) ?></div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button class="btn btn-light btn-sm rounded-pill text-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $f['id'] ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-light btn-sm rounded-pill text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $f['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Modal (SaaS Style) -->
                                <div class="modal fade" id="editModal<?= $f['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <form method="post">
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <div class="modal-header border-0 pt-4 px-4">
                                                    <h5 class="fw-bold mb-0">Edit Configuration</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body px-4">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Title</label>
                                                        <input type="text" name="form_title" class="form-control" value="<?= h($f['form_title']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Link</label>
                                                        <textarea name="form_link" class="form-control" rows="8" required><?= h($f['form_link']) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pb-4 px-4">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="update" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Confirmation -->
                                <div class="modal fade" id="deleteModal<?= $f['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-body p-4 text-center">
                                                <i class="bi bi-exclamation-circle text-danger h1 mb-3 d-block"></i>
                                                <h6 class="fw-bold">Confirm Deletion</h6>
                                                <p class="text-muted small mb-4">Are you sure you want to remove this form entry?</p>
                                                <form method="post">
                                                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-light w-100 rounded-pill" data-bs-dismiss="modal">No</button>
                                                        <button type="submit" name="delete" class="btn btn-danger w-100 rounded-pill">Yes, Delete</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
