<?php
$current_page = 'Manage_Resources';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$message = null;

/**
 * Logic: Update Resource Title
 */
if (isset($_POST['update'])) {
    try {
        $db->query("UPDATE my_downloads SET title = ? WHERE id = ?", [$_POST['title'], (int)$_POST['id']]);
        $message = ['text' => "Resource title updated.", 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Delete Resource
 */
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $file = $db->fetch("SELECT file_name FROM my_downloads WHERE id = ?", [$id]);
    if ($file) {
        $path = __DIR__ . "/../uploads/" . $file['file_name'];
        if (file_exists($path)) @unlink($path);
        $db->query("DELETE FROM my_downloads WHERE id = ?", [$id]);
        $message = ['text' => "Resource deleted successfully.", 'tag' => 'info'];
    }
}

$resources = $db->fetchAll("SELECT * FROM `my_downloads` ORDER BY upload_date DESC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Resource Management</h1>
            <p class="text-muted small mb-0">Upload and manage official documents available for user download.</p>
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
                <h5 class="fw-bold mb-3">Upload New File</h5>
                <form method="post" id="addUserForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Document Title</label>
                        <input type="text" name="title" id="inputTitle" class="form-control" placeholder="e.g. Activity Guidelines" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">File Source</label>
                        <input type="file" name="upload_photo" id="uploadFile" class="form-control" required>

                        <div id="progressContainer" class="mt-3 d-none">
                            <div class="progress rounded-pill shadow-sm" style="height: 10px;">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1 small text-muted">
                                <span><span id="uploadedSize">0</span> MB / <span id="totalSize">0</span> MB</span>
                                <span id="progressMsg">0%</span>
                            </div>
                            <div id="uploadMessage" class="mt-2 text-center fw-bold text-primary d-none">
                                Upload in progress, please wait...
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="dot_color" id="inputState" class="form-select" required>
                            <option value="" disabled selected hidden>Select category...</option>
                            <option value="text-success">Social Media</option>
                            <option value="text-info">Zakereen</option>
                            <option value="text-danger">Shaadi Tafheem</option>
                            <option value="text-warning">General</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="reset" value="Reset" id="resetBtn" class="btn btn-outline-secondary w-50 rounded-pill" />
                        <input type="button" value="Upload" id="submitBtn" class="btn btn-primary w-50 rounded-pill" />
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">Published Resources</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">File Title</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Date</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $res): ?>
                                <tr>
                                    <form method="post">
                                        <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-circle-fill <?= $res['dot_color'] ?> small"></i>
                                                <input type="text" name="title" class="form-control form-control-sm border-0 bg-light rounded-pill px-3" value="<?= h($res['title']) ?>" required>
                                            </div>
                                            <div class="text-muted small mt-1 ps-4" style="font-size: 0.65rem;"><?= h($res['file_name']) ?></div>
                                        </td>
                                        <td class="py-3 small text-muted">
                                            <?= date('d M, y', strtotime($res['upload_date'])) ?>
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button type="submit" name="update" class="btn btn-light btn-sm rounded-pill text-primary">
                                                    <i class="bi bi-save"></i>
                                                </button>
                                                <button type="submit" name="delete" class="btn btn-light btn-sm rounded-pill text-danger" onclick="return confirm('Permanently delete this file?');">
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
<script src="../assets/js/manage-download.js"></script>
