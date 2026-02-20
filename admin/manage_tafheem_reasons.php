<?php
    require_once __DIR__ . '/../session.php';
    $current_page = 'manage_tafheem_reasons';
    require_once __DIR__ . '/../inc/header.php';

    $program_type_options = [
        'Zakereen Farzando Training',
        'Social Media Awareness',
        'Shaadi Tafheem',
    ];

    // Handle Add
    if (isset($_POST['add_reason'])) {
        $reason_name    = trim(mysqli_real_escape_string($mysqli, $_POST['reason_name']));
        $type           = mysqli_real_escape_string($mysqli, $_POST['type'] ?? '');
        $sort_order     = (int) ($_POST['sort_order'] ?? 0);

        if (empty($reason_name) || empty($type)) {
            $message = ['text' => 'Reason name and type are required.', 'tag' => 'danger'];
        } else {
            $query = "INSERT INTO `bqi_tafheem_reasons` (`type`, `reason_name`, `is_active`, `sort_order`)
                      VALUES ('$type', '$reason_name', 1, '$sort_order')";
            try {
                mysqli_query($mysqli, $query);
                $message = ['text' => "Reason '$reason_name' added successfully.", 'tag' => 'success'];
            } catch (Exception $e) {
                $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
            }
        }
    }

    // Handle Edit
    if (isset($_POST['update_reason'])) {
        $edit_id        = (int) $_POST['edit_id'];
        $reason_name    = trim(mysqli_real_escape_string($mysqli, $_POST['reason_name']));
        $type           = mysqli_real_escape_string($mysqli, $_POST['type'] ?? '');
        $sort_order     = (int) ($_POST['sort_order'] ?? 0);

        if (empty($reason_name) || empty($type)) {
            $message = ['text' => 'Reason name and type are required.', 'tag' => 'danger'];
        } else {
            $query = "UPDATE `bqi_tafheem_reasons` SET `type` = '$type', `reason_name` = '$reason_name',
                      `sort_order` = '$sort_order' WHERE `id` = '$edit_id'";
            try {
                mysqli_query($mysqli, $query);
                $message = ['text' => 'Reason updated successfully.', 'tag' => 'success'];
            } catch (Exception $e) {
                $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
            }
        }
    }

    // Handle Toggle Active/Inactive
    if (isset($_POST['toggle_active'])) {
        $toggle_id  = (int) $_POST['toggle_id'];
        $new_status = (int) $_POST['new_status'];
        $query      = "UPDATE `bqi_tafheem_reasons` SET `is_active` = '$new_status' WHERE `id` = '$toggle_id'";
        try {
            mysqli_query($mysqli, $query);
            $message = ['text' => 'Reason status updated.', 'tag' => 'success'];
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    }

    // Handle Delete
    if (isset($_POST['delete_reason'])) {
        $delete_id = (int) $_POST['delete_id'];

        // Check if reason is in use
        $check  = mysqli_query($mysqli, "SELECT COUNT(*) as cnt FROM `bqi_individual_tafheem` WHERE FIND_IN_SET('$delete_id', `reason_id`) > 0");
        $row    = $check->fetch_assoc();

        if ($row['cnt'] > 0) {
            $message = ['text' => 'Cannot delete: this reason is in use. You can deactivate it instead.', 'tag' => 'danger'];
        } else {
            $query = "DELETE FROM `bqi_tafheem_reasons` WHERE `id` = '$delete_id'";
            try {
                mysqli_query($mysqli, $query);
                $message = ['text' => 'Reason deleted successfully.', 'tag' => 'success'];
            } catch (Exception $e) {
                $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
            }
        }
    }

    // Fetch all reasons
    $query   = "SELECT * FROM `bqi_tafheem_reasons` ORDER BY `sort_order`, `reason_name`";
    $result  = mysqli_query($mysqli, $query);
    $reasons = $result->fetch_all(MYSQLI_ASSOC);
?>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once __DIR__ . '/../inc/messages.php'; ?>
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">Manage Tafheem Reasons</h5>

                    <div class="row">
                        <!-- Add New Reason Form -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Add New Reason</h5>
                                    <form method="post">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-3">
                                                <label for="add_type" class="form-label">Type</label>
                                                <select id="add_type" name="type" class="form-select" required>
                                                    <option value="" disabled selected>Select Type...</option>
                                                    <?php foreach ($program_type_options as $opt): ?>
                                                        <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label for="add_reason_name" class="form-label">Reason Name</label>
                                                <input id="add_reason_name" type="text" name="reason_name" class="form-control" dir="auto" placeholder="e.g., Academic Counseling / تعلیمی مشاورت" required>
                                            </div>
                                            <div class="col-md-1">
                                                <label for="add_sort_order" class="form-label">Order</label>
                                                <input id="add_sort_order" type="number" name="sort_order" class="form-control" value="0" min="0">
                                            </div>
                                            <div class="col-md-3">
                                                <button class="btn btn-primary w-100" name="add_reason" type="submit">Add Reason</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reasons Table -->
                        <div class="col-md-12">
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Reason Name</th>
                                        <th>Type</th>
                                        <th>Sort Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reasons as $key => $r): ?>
                                        <tr>
                                            <td><?php echo $key + 1 ?></td>
                                            <td dir="auto"><?php echo htmlspecialchars($r['reason_name']) ?></td>
                                            <td><?php echo htmlspecialchars($r['type']) ?></td>
                                            <td><?php echo $r['sort_order'] ?></td>
                                            <td>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="toggle_id" value="<?php echo $r['id'] ?>">
                                                    <input type="hidden" name="new_status" value="<?php echo $r['is_active'] ? 0 : 1 ?>">
                                                    <button type="submit" name="toggle_active" class="btn btn-sm <?php echo $r['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                                                        <?php echo $r['is_active'] ? 'Active' : 'Inactive' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info edit-btn"
                                                    data-id="<?php echo $r['id'] ?>"
                                                    data-name="<?php echo htmlspecialchars($r['reason_name']) ?>"
                                                    data-type="<?php echo htmlspecialchars($r['type']) ?>"
                                                    data-sort="<?php echo $r['sort_order'] ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editModal">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="delete_id" value="<?php echo $r['id'] ?>">
                                                    <button type="submit" name="delete_reason" class="btn btn-sm btn-outline-danger delete-btn">
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
                    <h5 class="modal-title">Edit Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Reason Name</label>
                        <input type="text" name="reason_name" id="edit_name" class="form-control" dir="auto" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="edit_type" class="form-select" required>
                            <option value="" disabled>Select Type...</option>
                            <?php foreach ($program_type_options as $opt): ?>
                                <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_reason" class="btn btn-primary">Update</button>
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
        $('#edit_name').val($(this).data('name'));
        $('#edit_type').val($(this).data('type'));
        $('#edit_sort').val($(this).data('sort'));
    });

    // Delete confirmation
    $('.delete-btn').click(function(event) {
        if (!confirm("Are you sure you want to delete this reason?")) {
            event.preventDefault();
        }
    });

    // DataTable
    $('#datatable').DataTable({
        dom: 'Bfrtip',
        pageLength: 25,
        buttons: [
            { extend: 'copy', filename: function(){ return 'tafheem_reasons-' + Date.now(); } },
            { extend: 'csv', filename: function(){ return 'tafheem_reasons-' + Date.now(); } },
            { extend: 'excel', filename: function(){ return 'tafheem_reasons-' + Date.now(); } }
        ]
    });
});
</script>
</html>
