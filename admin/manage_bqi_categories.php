<?php
    require_once __DIR__ . '/../session.php';
    $current_page = 'manage_bqi_categories';
    require_once __DIR__ . '/../inc/header.php';

    // Category types mapping
    $category_types = [
    'challenge'   => 'Challenges / Solutions',
    'experience'  => 'Noteworthy Experiences',
    'preparation' => 'Khidmat Preparations',
    ];

    // Handle Add
    if (isset($_POST['add_category'])) {
    $category_type    = mysqli_real_escape_string($mysqli, $_POST['category_type']);
    $category_name    = trim(mysqli_real_escape_string($mysqli, $_POST['category_name']));
    $sort_order       = (int) ($_POST['sort_order'] ?? 0);

    if (empty($category_name) || ! array_key_exists($category_type, $category_types)) {
        $message = ['text' => 'Category name and valid type are required.', 'tag' => 'danger'];
    } else {
        $query = "INSERT INTO `bqi_categories` (`category_type`, `category_name`, `is_active`, `sort_order`)
                  VALUES ('$category_type', '$category_name', 1, '$sort_order')";
        try {
            mysqli_query($mysqli, $query);
            $message = ['text' => "Category '$category_name' added successfully.", 'tag' => 'success'];
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    }
    }

    // Handle Edit
    if (isset($_POST['update_category'])) {
    $edit_id          = (int) $_POST['edit_id'];
    $category_type    = mysqli_real_escape_string($mysqli, $_POST['category_type']);
    $category_name    = trim(mysqli_real_escape_string($mysqli, $_POST['category_name']));
    $sort_order       = (int) ($_POST['sort_order'] ?? 0);

    if (empty($category_name) || ! array_key_exists($category_type, $category_types)) {
        $message = ['text' => 'Category name and valid type are required.', 'tag' => 'danger'];
    } else {
        $query = "UPDATE `bqi_categories` SET `category_type` = '$category_type', `category_name` = '$category_name',
                  `sort_order` = '$sort_order' WHERE `id` = '$edit_id'";
        try {
            mysqli_query($mysqli, $query);
            $message = ['text' => 'Category updated successfully.', 'tag' => 'success'];
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    }
    }

    // Handle Toggle Active/Inactive
    if (isset($_POST['toggle_active'])) {
    $toggle_id  = (int) $_POST['toggle_id'];
    $new_status = (int) $_POST['new_status'];
    $query      = "UPDATE `bqi_categories` SET `is_active` = '$new_status' WHERE `id` = '$toggle_id'";
    try {
        mysqli_query($mysqli, $query);
        $message = ['text' => 'Category status updated.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
    }

    // Handle Delete
    if (isset($_POST['delete_category'])) {
    $delete_id = (int) $_POST['delete_id'];

    // Check if category is in use
    $in_use = false;
    $tables = ['bqi_challenges_solutions', 'bqi_noteworthy_experiences', 'bqi_khidmat_preparations'];
    foreach ($tables as $table) {
        $check = mysqli_query($mysqli, "SELECT COUNT(*) as cnt FROM `$table` WHERE `category_id` = '$delete_id'");
        $row   = $check->fetch_assoc();
        if ($row['cnt'] > 0) {
            $in_use = true;
            break;
        }
    }

    if ($in_use) {
        $message = ['text' => 'Cannot delete: this category is in use. You can deactivate it instead.', 'tag' => 'danger'];
    } else {
        $query = "DELETE FROM `bqi_categories` WHERE `id` = '$delete_id'";
        try {
            mysqli_query($mysqli, $query);
            $message = ['text' => 'Category deleted successfully.', 'tag' => 'success'];
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    }
    }

    // Filter by type
    $filter_type = $_GET['type'] ?? '';

    // Fetch all categories
    $where = "";
    if (! empty($filter_type) && array_key_exists($filter_type, $category_types)) {
    $filter_type_esc = mysqli_real_escape_string($mysqli, $filter_type);
    $where           = "WHERE `category_type` = '$filter_type_esc'";
    }
    $query      = "SELECT * FROM `bqi_categories` $where ORDER BY `category_type`, `sort_order`, `category_name`";
    $result     = mysqli_query($mysqli, $query);
    $categories = $result->fetch_all(MYSQLI_ASSOC);
?>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once __DIR__ . '/../inc/messages.php'; ?>
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">Manage BQI 1447 Categories</h5>

                    <div class="row">
                        <!-- Add New Category Form -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Add New Category</h5>
                                    <form method="post">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-3">
                                                <label for="add_category_type" class="form-label">Type</label>
                                                <select id="add_category_type" name="category_type" class="form-select" required>
                                                    <option value="" disabled selected>Select Type...</option>
                                                    <?php foreach ($category_types as $val => $label): ?>
                                                        <option value="<?php echo $val ?>"><?php echo $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label for="add_category_name" class="form-label">Category Name</label>
                                                <input id="add_category_name" type="text" name="category_name" class="form-control" dir="auto" placeholder="e.g., Community Service / خدمت" required>
                                            </div>
                                            <div class="col-md-1">
                                                <label for="add_sort_order" class="form-label">Order</label>
                                                <input id="add_sort_order" type="number" name="sort_order" class="form-control" value="0" min="0">
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-primary w-100" name="add_category" type="submit">Add Category</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Tabs -->
                        <div class="col-md-12">
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo empty($filter_type) ? 'active' : '' ?>" href="?">All</a>
                                </li>
                                <?php foreach ($category_types as $val => $label): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo($filter_type === $val) ? 'active' : '' ?>" href="?type=<?php echo $val ?>"><?php echo $label ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Categories Table -->
                        <div class="col-md-12">
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Category Name</th>
                                        <th>Sort Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $key => $c): ?>
                                        <tr>
                                            <td><?php echo $key + 1 ?></td>
                                            <td><span class="badge bg-<?php echo $c['category_type'] === 'challenge' ? 'warning' : ($c['category_type'] === 'experience' ? 'info' : 'primary') ?>"><?php echo htmlspecialchars($category_types[$c['category_type']] ?? $c['category_type']) ?></span></td>
                                            <td dir="auto"><?php echo htmlspecialchars($c['category_name']) ?></td>
                                            <td><?php echo $c['sort_order'] ?></td>
                                            <td>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="toggle_id" value="<?php echo $c['id'] ?>">
                                                    <input type="hidden" name="new_status" value="<?php echo $c['is_active'] ? 0 : 1 ?>">
                                                    <button type="submit" name="toggle_active" class="btn btn-sm <?php echo $c['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                                                        <?php echo $c['is_active'] ? 'Active' : 'Inactive' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info edit-btn"
                                                    data-id="<?php echo $c['id'] ?>"
                                                    data-type="<?php echo htmlspecialchars($c['category_type']) ?>"
                                                    data-name="<?php echo htmlspecialchars($c['category_name']) ?>"
                                                    data-sort="<?php echo $c['sort_order'] ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editModal">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="delete_id" value="<?php echo $c['id'] ?>">
                                                    <button type="submit" name="delete_category" class="btn btn-sm btn-outline-danger delete-btn">
                                                        <i class="bi bi-trash-fill"></i>
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
        </div>
    </section>
</main>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="category_type" id="edit_type" class="form-select" required>
                            <?php foreach ($category_types as $val => $label): ?>
                                <option value="<?php echo $val ?>"><?php echo $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category_name" id="edit_name" class="form-control" dir="auto" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_category" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
</body>
<script>
$(document).ready(function () {
    // Edit modal populate
    $('.edit-btn').click(function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_type').val($(this).data('type'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_sort').val($(this).data('sort'));
    });

    // Delete confirmation
    $('.delete-btn').click(function(event) {
        if (!confirm("Are you sure you want to delete this category?")) {
            event.preventDefault();
        }
    });

    // DataTable
    $('#datatable').DataTable({
        dom: 'Bfrtip',
        pageLength: 25,
        buttons: [
            { extend: 'copy', filename: function(){ return 'bqi_categories-' + Date.now(); } },
            { extend: 'csv', filename: function(){ return 'bqi_categories-' + Date.now(); } },
            { extend: 'excel', filename: function(){ return 'bqi_categories-' + Date.now(); } }
        ]
    });
});
</script>
</html>
