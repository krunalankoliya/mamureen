<?php
$current_page = 'manage_bqi_categories';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../inc/header.php';

$message = null;
$category_types = [
    'challenge'   => 'Challenges / Solutions',
    'experience'  => 'Noteworthy Experiences',
    'preparation' => 'Khidmat Preparations',
];

/**
 * Logic: Add
 */
if (isset($_POST['add_category'])) {
    try {
        $db->insert('bqi_categories', [
            'category_type' => $_POST['category_type'],
            'category_name' => trim($_POST['category_name']),
            'sort_order' => (int)$_POST['sort_order'],
            'is_active' => 1
        ]);
        $message = ['text' => 'New category defined.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Update
 */
if (isset($_POST['update_category'])) {
    try {
        $db->query("UPDATE bqi_categories SET category_type = ?, category_name = ?, sort_order = ? WHERE id = ?", [
            $_POST['category_type'], $_POST['category_name'], (int)$_POST['sort_order'], (int)$_POST['edit_id']
        ]);
        $message = ['text' => 'Category configuration updated.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
}

/**
 * Logic: Toggle
 */
if (isset($_POST['toggle_active'])) {
    $db->query("UPDATE bqi_categories SET is_active = ? WHERE id = ?", [(int)$_POST['new_status'], (int)$_POST['toggle_id']]);
}

$filter_type = $_GET['type'] ?? '';
$where = $filter_type ? "WHERE category_type = ?" : "WHERE 1=1";
$params = $filter_type ? [$filter_type] : [];

$categories = $db->fetchAll("SELECT * FROM bqi_categories $where ORDER BY category_type ASC, sort_order ASC", $params);
?>

<main id="main" class="main main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Impact Categories</h1>
            <p class="text-muted small mb-0">Classify challenges, experiences, and preparations for advanced reporting.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['tag']; ?> border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> <?= $message['text']; ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Definition Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Define Category</h5>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Classification Type</label>
                        <select name="category_type" class="form-select" required>
                            <option value="">Choose Type...</option>
                            <?php foreach ($category_types as $val => $label): ?>
                                <option value="<?= $val ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Label / Name</label>
                        <input type="text" name="category_name" class="form-control" placeholder="e.g. Logistics" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm">
                        Create Category
                    </button>
                </form>
            </div>
        </div>

        <!-- Directory Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Category Directory</h5>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-pill px-3 border dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-filter me-1"></i> Filter
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3">
                            <li><a class="dropdown-item small" href="?">All Types</a></li>
                            <?php foreach($category_types as $v => $l): ?>
                                <li><a class="dropdown-item small" href="?type=<?= $v ?>"><?= $l ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0">Label</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Classification</th>
                                <th class="py-3 small text-muted text-uppercase border-0">Order</th>
                                <th class="px-4 py-3 small text-muted text-uppercase border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $c): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark small"><?= h($c['category_name']) ?></div>
                                        <?php if($c['is_active']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.6rem;">ACTIVE</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill" style="font-size: 0.6rem;">DISABLED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3">
                                        <span class="small text-muted"><?= $category_types[$c['category_type']] ?? $c['category_type'] ?></span>
                                    </td>
                                    <td class="py-3 small"><?= $c['sort_order'] ?></td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button class="btn btn-light btn-sm rounded-pill edit-btn" 
                                                    data-id="<?= $c['id'] ?>" data-name="<?= h($c['category_name']) ?>" 
                                                    data-type="<?= $c['category_type'] ?>" data-sort="<?= $c['sort_order'] ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="toggle_id" value="<?= $c['id'] ?>">
                                                <input type="hidden" name="new_status" value="<?= $c['is_active'] ? 0 : 1 ?>">
                                                <button type="submit" name="toggle_active" class="btn btn-light btn-sm rounded-pill text-muted">
                                                    <i class="bi bi-power"></i>
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
                    <h5 class="fw-bold mb-0">Update Classification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Classification Type</label>
                        <select name="category_type" id="edit_type" class="form-select" required>
                            <?php foreach ($category_types as $val => $label): ?>
                                <option value="<?= $val ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Label / Name</label>
                        <input type="text" name="category_name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_category" class="btn btn-primary rounded-pill px-4">Save Changes</button>
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
