<?php
    require_once __DIR__ . '/../session.php';
    $current_page = 'manage_program_titles';
    require_once __DIR__ . '/../inc/header.php';

    $program_type_options = [
        'Zakereen Farzando Training',
        'Social Media Awareness',
        'Shaadi Tafheem',
    ];

    // Handle Add
    if (isset($_POST['add_title'])) {
        $title_name   = trim(mysqli_real_escape_string($mysqli, $_POST['title_name']));
        $program_type = mysqli_real_escape_string($mysqli, $_POST['program_type']);
        $sort_order   = (int) ($_POST['sort_order'] ?? 0);

        if (empty($title_name)) {
            $message = ['text' => 'Program title name is required.', 'tag' => 'danger'];
        } elseif (!in_array($program_type, $program_type_options)) {
            $message = ['text' => 'Invalid program type selected.', 'tag' => 'danger'];
        } else {
            $query = "INSERT INTO `bqi_program_titles` (`title_name`, `program_type`, `is_active`, `sort_order`)
                      VALUES ('$title_name', '$program_type', 1, '$sort_order')";
            try {
                mysqli_query($mysqli, $query);
                $message = ['text' => "Program title '$title_name' added successfully.", 'tag' => 'success'];
            } catch (\Exception $e) {
                $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
            }
        }
    }

    // Handle Edit
    if (isset($_POST['update_title'])) {
        $edit_id      = (int) $_POST['edit_id'];
        $title_name   = trim(mysqli_real_escape_string($mysqli, $_POST['title_name']));
        $program_type = mysqli_real_escape_string($mysqli, $_POST['program_type']);
        $sort_order   = (int) ($_POST['sort_order'] ?? 0);

        if (empty($title_name)) {
            $message = ['text' => 'Program title name is required.', 'tag' => 'danger'];
        } elseif (!in_array($program_type, $program_type_options)) {
            $message = ['text' => 'Invalid program type selected.', 'tag' => 'danger'];
        } else {
            $query = "UPDATE `bqi_program_titles`
                      SET `title_name` = '$title_name', `program_type` = '$program_type', `sort_order` = '$sort_order'
                      WHERE `id` = '$edit_id'";
            try {
                mysqli_query($mysqli, $query);
                $message = ['text' => 'Program title updated successfully.', 'tag' => 'success'];
            } catch (\Exception $e) {
                $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
            }
        }
    }

    // Handle Toggle Active/Inactive
    if (isset($_POST['toggle_active'])) {
        $toggle_id  = (int) $_POST['toggle_id'];
        $new_status = (int) $_POST['new_status'];
        $query      = "UPDATE `bqi_program_titles` SET `is_active` = '$new_status' WHERE `id` = '$toggle_id'";
        try {
            mysqli_query($mysqli, $query);
            $message = ['text' => 'Program title status updated.', 'tag' => 'success'];
        } catch (\Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    }

    // Handle Delete
    if (isset($_POST['delete_title'])) {
        $delete_id = (int) $_POST['delete_id'];

        $check = mysqli_query($mysqli, "SELECT COUNT(*) as cnt FROM `meeting_and_photo_reports`
                                        WHERE FIND_IN_SET('$delete_id', `program_title_ids`) > 0");
        $row   = $check->fetch_assoc();

        if ($row['cnt'] > 0) {
            $message = ['text' => 'Cannot delete: this program title is in use. You can deactivate it instead.', 'tag' => 'danger'];
        } else {
            $query = "DELETE FROM `bqi_program_titles` WHERE `id` = '$delete_id'";
            try {
                mysqli_query($mysqli, $query);
                $message = ['text' => 'Program title deleted successfully.', 'tag' => 'success'];
            } catch (\Exception $e) {
                $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
            }
        }
    }

    // Fetch all titles
    $query  = "SELECT * FROM `bqi_program_titles` ORDER BY `program_type`, `sort_order`, `title_name`";
    $result = mysqli_query($mysqli, $query);
    $titles = $result->fetch_all(MYSQLI_ASSOC);
?>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once __DIR__ . '/../inc/messages.php'; ?>
            <div class="card">
                <div class="card-body" style="overflow-y: auto;">
                    <h5 class="card-title">Manage Program Titles</h5>

                    <div class="row">
                        <!-- Add New Title Form -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Add New Program Title</h5>
                                    <form method="post">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-4">
                                                <label for="add_program_type" class="form-label">Program Type</label>
                                                <select id="add_program_type" name="program_type" class="form-select" required>
                                                    <option value="" disabled selected>Select Program Type...</option>
                                                    <?php foreach ($program_type_options as $opt): ?>
                                                        <option value="<?php echo $opt ?>"><?php echo $opt ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="add_title_name" class="form-label">Program Title Name</label>
                                                <input id="add_title_name" type="text" name="title_name" class="form-control" placeholder="Enter program title" required>
                                            </div>
                                            <div class="col-md-1">
                                                <label for="add_sort_order" class="form-label">Order</label>
                                                <input id="add_sort_order" type="number" name="sort_order" class="form-control" value="0" min="0">
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-primary w-100" name="add_title" type="submit">Add Title</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Titles Table -->
                        <div class="col-md-12">
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Program Type</th>
                                        <th>Program Title</th>
                                        <th>Sort Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($titles as $key => $t): ?>
                                        <tr>
                                            <td><?php echo $key + 1 ?></td>
                                            <td><?php echo htmlspecialchars($t['program_type'] ?? '') ?></td>
                                            <td><?php echo htmlspecialchars($t['title_name']) ?></td>
                                            <td><?php echo $t['sort_order'] ?></td>
                                            <td>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="toggle_id" value="<?php echo $t['id'] ?>">
                                                    <input type="hidden" name="new_status" value="<?php echo $t['is_active'] ? 0 : 1 ?>">
                                                    <button type="submit" name="toggle_active" class="btn btn-sm <?php echo $t['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                                                        <?php echo $t['is_active'] ? 'Active' : 'Inactive' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info edit-btn"
                                                    data-id="<?php echo $t['id'] ?>"
                                                    data-name="<?php echo htmlspecialchars($t['title_name']) ?>"
                                                    data-type="<?php echo htmlspecialchars($t['program_type'] ?? '') ?>"
                                                    data-sort="<?php echo $t['sort_order'] ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editModal">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="delete_id" value="<?php echo $t['id'] ?>">
                                                    <button type="submit" name="delete_title" class="btn btn-sm btn-outline-danger delete-btn">
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
                    <h5 class="modal-title">Edit Program Title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Program Type</label>
                        <select name="program_type" id="edit_type" class="form-select" required>
                            <option value="" disabled>Select Program Type...</option>
                            <?php foreach ($program_type_options as $opt): ?>
                                <option value="<?php echo $opt ?>"><?php echo $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Program Title Name</label>
                        <input type="text" name="title_name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_title" class="btn btn-primary">Update</button>
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
        $('#edit_sort').val($(this).data('sort'));
        $('#edit_type').val($(this).data('type'));
    });

    // Delete confirmation
    $('.delete-btn').click(function(event) {
        if (!confirm("Are you sure you want to delete this program title?")) {
            event.preventDefault();
        }
    });

    // DataTable
    $('#datatable').DataTable({
        dom: 'Bfrtip',
        pageLength: 25,
        buttons: [
            { extend: 'copy', filename: function(){ return 'program_titles-' + Date.now(); } },
            { extend: 'csv', filename: function(){ return 'program_titles-' + Date.now(); } },
            { extend: 'excel', filename: function(){ return 'program_titles-' + Date.now(); } }
        ]
    });
});
</script>
</html>
