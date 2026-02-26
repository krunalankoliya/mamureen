<?php
$current_page = 'manage_program_titles';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$message = null;
$program_types = ['Zakereen Farzando Training', 'Social Media Awareness', 'Shaadi Tafheem'];

/**
 * Logic: Add Title
 */
if (isset($_POST['add_title'])) {
    try {
        $db->insert('bqi_program_titles', [
            'title_name' => trim($_POST['title_name']),
            'program_type' => $_POST['program_type'],
            'sort_order' => (int)$_POST['sort_order'],
            'is_active' => 1
        ]);
        $message = ['text' => 'Program title added.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Update
 */
if (isset($_POST['update_title'])) {
    try {
        $db->query("UPDATE bqi_program_titles SET title_name = ?, program_type = ?, sort_order = ? WHERE id = ?", [
            $_POST['title_name'], $_POST['program_type'], (int)$_POST['sort_order'], (int)$_POST['edit_id']
        ]);
        $message = ['text' => 'Title updated.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Status Toggle
 */
if (isset($_POST['toggle_active'])) {
    $db->query("UPDATE bqi_program_titles SET is_active = ? WHERE id = ?", [(int)$_POST['new_status'], (int)$_POST['toggle_id']]);
}

$titles = $db->fetchAll("SELECT * FROM bqi_program_titles ORDER BY program_type ASC, sort_order ASC");
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Program Library</h1>
            <p class="text-muted small mb-0">Manage the global list of program titles and categories for reporting.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['tag']; ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $message['text']; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Define New Title</h5>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Parent Category</label>
                        <select name="program_type" class="form-select" required>
                            <option value="">Choose Category...</option>
                            <?php foreach ($program_types as $pt): ?>
                                <option value="<?= h($pt) ?>"><?= h($pt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Title Name</label>
                        <input type="text" name="title_name" class="form-control" placeholder="e.g. Basic Orientation" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <button type="submit" name="add_title" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm">
                        Save to Library
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0">Configured Titles</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Program Title</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Category</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Order</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($titles as $t): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark small"><?= h($t['title_name']) ?></div>
                                        <?php if($t['is_active']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.6rem;">ACTIVE</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill" style="font-size: 0.6rem;">DISABLED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3">
                                        <span class="small text-muted"><?= h($t['program_type']) ?></span>
                                    </td>
                                    <td class="py-3 small"><?= $t['sort_order'] ?></td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button class="btn btn-light btn-sm rounded-pill edit-btn" 
                                                    data-id="<?= $t['id'] ?>" data-name="<?= h($t['title_name']) ?>" 
                                                    data-type="<?= h($t['program_type']) ?>" data-sort="<?= $t['sort_order'] ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="toggle_id" value="<?= $t['id'] ?>">
                                                <input type="hidden" name="new_status" value="<?= $t['is_active'] ? 0 : 1 ?>">
                                                <button type="submit" name="toggle_active" class="btn btn-light btn-sm rounded-pill <?= $t['is_active'] ? 'text-warning' : 'text-success' ?>">
                                                    <i class="bi <?= $t['is_active'] ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                                </button>
                                            </form>
                                        </div>
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
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="post">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Edit Program Title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Title Name</label>
                        <input type="text" name="title_name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Program Type</label>
                        <select name="program_type" id="edit_type" class="form-select" required>
                            <?php foreach ($program_types as $pt): ?>
                                <option value="<?= h($pt) ?>"><?= h($pt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_title" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/../inc/footer.php'; 
require_once __DIR__ . '/../inc/js-block.php'; 
?>
<script>
$(document).ready(function(){
    $('.edit-btn').click(function(){
        $('#edit_id').val($(this).data('id'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_type').val($(this).data('type'));
        $('#edit_sort').val($(this).data('sort'));
    });
});
</script>
