<?php
$current_page = 'Manage_link';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$message = null;

/**
 * Logic: Create Link
 */
if (isset($_POST['submit'])) {
    try {
        $db->insert('my_links', [
            'upload_date' => date('Y-m-d'),
            'title' => $_POST['title'],
            'link' => $_POST['link'],
            'added_its' => $user_its
        ]);
        $message = ['text' => "Link added successfully.", 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Update Link
 */
if (isset($_POST['update'])) {
    try {
        $db->query("UPDATE my_links SET title = ? WHERE id = ?", [$_POST['title'], (int)$_POST['id']]);
        $message = ['text' => "Link updated.", 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Delete Link
 */
if (isset($_POST['delete'])) {
    $db->query("DELETE FROM my_links WHERE id = ?", [(int)$_POST['id']]);
    $message = ['text' => "Link deleted.", 'tag' => 'info'];
}

$links = $db->fetchAll("SELECT * FROM `my_links` ORDER BY upload_date DESC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Manage External Links</h1>
            <p class="text-muted small mb-0">Control the list of useful URLs and external resources for users.</p>
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
                <h5 class="fw-bold mb-3">Add New Resource</h5>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Link Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Orientation Video" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">URL / Link</label>
                        <input type="url" name="link" class="form-control" placeholder="https://..." required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm">
                        Publish Link
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">Existing Links</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Title</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Date</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($links as $l): ?>
                                <tr>
                                    <form method="post">
                                        <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                        <td class="px-4 py-3">
                                            <input type="text" name="title" class="form-control form-control-sm border-0 bg-light rounded-pill px-3" value="<?= h($l['title']) ?>" required>
                                            <div class="text-muted truncate small mt-1 ps-2" style="max-width: 200px; font-size: 0.65rem;"><?= h($l['link']) ?></div>
                                        </td>
                                        <td class="py-3 small text-muted">
                                            <?= date('d/m/y', strtotime($l['upload_date'])) ?>
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button type="submit" name="update" class="btn btn-light btn-sm rounded-pill text-primary">
                                                    <i class="bi bi-save"></i>
                                                </button>
                                                <button type="submit" name="delete" class="btn btn-light btn-sm rounded-pill text-danger" onclick="return confirm('Delete this link?');">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </form>
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
