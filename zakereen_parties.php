<?php
    require_once __DIR__ . '/session.php';
    $current_page = 'zakereen_parties';

    require_once __DIR__ . '/inc/header.php';

    // Handle Add Party
    if (isset($_POST['add_party'])) {
    $party_name = trim(mysqli_real_escape_string($mysqli, $_POST['party_name']));

    if (empty($party_name)) {
        $message = ['text' => 'Party name cannot be blank.', 'tag' => 'danger'];
    } else {
        $query = "INSERT INTO `bqi_zakereen_parties` (`party_name`, `is_active`, `added_its`)
                  VALUES ('$party_name', 1, '$user_its')";
        try {
            mysqli_query($mysqli, $query);
            $message    = ['text' => 'Party added successfully.', 'tag' => 'success'];
            $show_popup = true;
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    }
    }

    // Handle Toggle Active
    if (isset($_POST['toggle_active'])) {
    $party_id   = (int) $_POST['party_id'];
    $new_status = (int) $_POST['new_status'];
    $query      = "UPDATE `bqi_zakereen_parties` SET `is_active` = '$new_status', `update_its` = '$user_its', `update_ts` = NOW() WHERE `id` = '$party_id'";
    try {
        mysqli_query($mysqli, $query);
        $message = ['text' => 'Party status updated.', 'tag' => 'success'];
    } catch (Exception $e) {
        $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
    }
    }

    // Fetch parties
    $query   = "SELECT * FROM `bqi_zakereen_parties` ORDER BY `added_ts` DESC";
    $result  = mysqli_query($mysqli, $query);
    $parties = $result->fetch_all(MYSQLI_ASSOC);
?>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <?php require_once __DIR__ . '/inc/messages.php'; ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Zakereen Party Registration</h5>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Add New Party</h5>
                                    <form method="post">
                                        <div class="row">
                                            <div class="col-md-7">
                                                <input type="text" name="party_name" class="form-control" placeholder="Enter Party Name" required>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-grid gap-2">
                                                    <button class="btn btn-primary" name="add_party" type="submit">Save Party</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12" style="overflow-x: auto;">
                            <h5 class="card-title">Existing Parties</h5>
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Party Name</th>
                                        <th>Status</th>
                                        <th>Added By</th>
                                        <th>Added On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($parties as $key => $party): ?>
                                        <tr>
                                            <td><?php echo $key + 1 ?></td>
                                            <td><?php echo htmlspecialchars($party['party_name']) ?></td>
                                            <td>
                                                <?php if ($party['is_active'] == 1): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $party['added_its'] ?></td>
                                            <td><?php echo date('d-M-Y H:i', strtotime($party['added_ts'])) ?></td>
                                            <td>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="party_id" value="<?php echo $party['id'] ?>">
                                                    <?php if ($party['is_active'] == 1): ?>
                                                        <input type="hidden" name="new_status" value="0">
                                                        <button type="submit" name="toggle_active" class="btn btn-sm btn-outline-warning" title="Deactivate">
                                                            <i class="bi bi-toggle-on"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <input type="hidden" name="new_status" value="1">
                                                        <button type="submit" name="toggle_active" class="btn btn-sm btn-outline-success" title="Activate">
                                                            <i class="bi bi-toggle-off"></i>
                                                        </button>
                                                    <?php endif; ?>
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

<?php require_once __DIR__ . '/inc/footer.php'; ?>
<script>
<?php if (! empty($show_popup)): ?>
    alert('Party added successfully!');
<?php endif; ?>

$(document).ready(function () {
    $('#datatable').DataTable({
        dom: 'Bfrtip',
        pageLength: 10,
        buttons: [
            { extend: 'copy', filename: function(){ return 'zakereen_parties-' + Date.now(); } },
            { extend: 'csv', filename: function(){ return 'zakereen_parties-' + Date.now(); } },
            { extend: 'excel', filename: function(){ return 'zakereen_parties-' + Date.now(); } }
        ]
    });
});
</script>
