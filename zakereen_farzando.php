<?php
    require_once __DIR__ . '/session.php';
    $current_page = 'zakereen_farzando';

    require_once __DIR__ . '/inc/header.php';

    // Initialize variables
    $verified_user = null;

    // Function to fetch user data from API (server-side, like add_certificate.php)
    function fetchUserDataByITS($its_id)
    {
    if (empty($its_id)) {
        return null;
    }

    $user_its = $_COOKIE['user_its'] ?? '';
    $ver      = $_COOKIE['ver'] ?? '';

    if (empty($user_its) || empty($ver)) {
        return null;
    }

    $api_url = "https://www.talabulilm.com/api2022/core/user/getUserDetailsByItsID/" . urlencode($its_id);
    $auth    = base64_encode("$user_its:$ver");
    $headers = ["Authorization: Basic $auth"];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RESOLVE, ['www.talabulilm.com:443:66.85.132.227']);
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error || empty($response)) {
        return null;
    }

    $data = json_decode($response, true);
    if (empty($data) || ! isset($data['its_id'])) {
        return null;
    }

    return $data;
    }

    // Handle Verify ITS (server-side)
    if (isset($_POST['verify_its'])) {
    $verify_its_id  = (int) $_POST['verify_its_id'];
    $selected_party = (int) ($_POST['party_id'] ?? 0);

    if ($verify_its_id <= 0 || strlen((string) $verify_its_id) < 8) {
        $message = ['text' => 'Please enter a valid 8-digit ITS ID.', 'tag' => 'danger'];
    } else {
        $verified_user = fetchUserDataByITS($verify_its_id);
        if (! $verified_user) {
            $message = ['text' => "ITS ID $verify_its_id not found. Please check and try again.", 'tag' => 'danger'];
        }
    }
    }

    // Handle Delete
    if (isset($_POST['delete_farzand'])) {
    $delete_id = (int) $_POST['delete_id'];
    $result    = mysqli_query($mysqli, "DELETE FROM `bqi_zakereen_farzando` WHERE `id` = '$delete_id' AND `added_its` = '$user_its'");
    if ($result) {
        $message = ['text' => 'Farzand deleted successfully.', 'tag' => 'success'];
    } else {
        $message = ['text' => 'Failed to delete: ' . mysqli_error($mysqli), 'tag' => 'danger'];
    }
    }

    // Handle Update (party reassignment)
    if (isset($_POST['update_farzand'])) {
    $edit_id  = (int) $_POST['edit_id'];
    $party_id = (int) $_POST['edit_party_id'];
    $result   = mysqli_query($mysqli, "UPDATE `bqi_zakereen_farzando` SET `party_id` = '$party_id' WHERE `id` = '$edit_id' AND `added_its` = '$user_its'");
    if ($result) {
        $message = ['text' => 'Farzand updated successfully.', 'tag' => 'success'];
    } else {
        $message = ['text' => 'Failed to update: ' . mysqli_error($mysqli), 'tag' => 'danger'];
    }
    }

    // Handle Quick-Add Party (from modal)
    if (isset($_POST['quick_add_party'])) {
    $party_name = trim(mysqli_real_escape_string($mysqli, $_POST['party_name']));
    if (! empty($party_name)) {
        $query = "INSERT INTO `bqi_zakereen_parties` (`party_name`, `is_active`, `added_its`) VALUES ('$party_name', 0, '$user_its')";
        try {
            mysqli_query($mysqli, $query);
            $message = ['text' => 'Party added successfully.', 'tag' => 'success'];
        } catch (Exception $e) {
            $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
        }
    }
    }

    // Handle Individual Add (Save Farzand)
    if (isset($_POST['add_farzand'])) {
    $party_id   = (int) $_POST['party_id'];
    $its_id     = (int) $_POST['its_id'];
    $full_name  = mysqli_real_escape_string($mysqli, $_POST['full_name']);
    $gender     = mysqli_real_escape_string($mysqli, $_POST['gender']);
    $dob        = mysqli_real_escape_string($mysqli, $_POST['dob']);
    $jamaat_val = mysqli_real_escape_string($mysqli, $_POST['jamaat_val']);
    $jamiat_val = mysqli_real_escape_string($mysqli, $_POST['jamiat_val']);
    $mobile     = mysqli_real_escape_string($mysqli, $_POST['mobile']);
    $email      = mysqli_real_escape_string($mysqli, $_POST['email']);

    if ($party_id <= 0 || $its_id <= 0 || empty($full_name)) {
        $message = ['text' => 'Please select a party and verify an ITS ID first.', 'tag' => 'danger'];
    } else {
        // Check duplicate
        $check = mysqli_query($mysqli, "SELECT id FROM `bqi_zakereen_farzando` WHERE `its_id` = '$its_id' AND `party_id` = '$party_id'");
        if ($check->num_rows > 0) {
            $message = ['text' => "ITS $its_id is already registered in this party.", 'tag' => 'danger'];
        } else {
            $query = "INSERT INTO `bqi_zakereen_farzando` (`party_id`, `its_id`, `full_name`, `gender`, `dob`, `jamaat`, `jamiat`, `mobile`, `email`, `is_bulk_upload`, `added_its`)
                      VALUES ('$party_id', '$its_id', '$full_name', '$gender', '$dob', '$jamaat_val', '$jamiat_val', '$mobile', '$email', 0, '$user_its')";
            try {
                mysqli_query($mysqli, $query);
                $message    = ['text' => "Farzand $full_name ($its_id) added successfully.", 'tag' => 'success'];
                $show_popup = true;
            } catch (Exception $e) {
                $message = ['text' => $e->getMessage(), 'tag' => 'danger'];
            }
        }
    }
    }

    // Handle Bulk CSV Upload
    if (isset($_POST['bulk_upload'])) {
    $party_id = (int) $_POST['party_id'];

    if ($party_id <= 0) {
        $message = ['text' => 'Please select a party first.', 'tag' => 'danger'];
    } elseif (! isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $message = ['text' => 'Please upload a valid CSV file.', 'tag' => 'danger'];
    } else {
        $file            = fopen($_FILES['csv_file']['tmp_name'], 'r');
        $user_its_cookie = $_COOKIE['user_its'];
        $ver             = $_COOKIE['ver'];
        $auth            = base64_encode("$user_its_cookie:$ver");

        $success_count = 0;
        $fail_count    = 0;
        $fail_ids      = [];

        while (($line = fgetcsv($file)) !== false) {
            $csv_its = (int) trim($line[0]);
            if ($csv_its <= 0) {
                continue;
            }

            // Check duplicate
            $check = mysqli_query($mysqli, "SELECT id FROM `bqi_zakereen_farzando` WHERE `its_id` = '$csv_its' AND `party_id` = '$party_id'");
            if ($check->num_rows > 0) {
                $fail_count++;
                $fail_ids[] = "$csv_its (duplicate)";
                continue;
            }

            // Call API
            $api_url = "https://www.talabulilm.com/api2022/core/user/getUserDetailsByItsID/$csv_its";
            $ch      = curl_init();
            curl_setopt($ch, CURLOPT_RESOLVE, ['www.talabulilm.com:443:66.85.132.227']);
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $auth"]);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            if ($data && ! empty($data['full_name_en'])) {
                $fn = mysqli_real_escape_string($mysqli, $data['full_name_en']);
                $g  = mysqli_real_escape_string($mysqli, $data['gender'] ?? '');
                $d  = mysqli_real_escape_string($mysqli, $data['dob'] ?? '');
                $j  = mysqli_real_escape_string($mysqli, $data['jamaat'] ?? '');
                $ji = mysqli_real_escape_string($mysqli, $data['jamiat'] ?? '');
                $m  = mysqli_real_escape_string($mysqli, $data['mobile'] ?? '');
                $e  = mysqli_real_escape_string($mysqli, $data['email'] ?? '');

                $query = "INSERT INTO `bqi_zakereen_farzando` (`party_id`, `its_id`, `full_name`, `gender`, `dob`, `jamaat`, `jamiat`, `mobile`, `email`, `is_bulk_upload`, `added_its`)
                          VALUES ('$party_id', '$csv_its', '$fn', '$g', '$d', '$j', '$ji', '$m', '$e', 1, '$user_its')";
                try {
                    mysqli_query($mysqli, $query);
                    $success_count++;
                } catch (Exception $ex) {
                    $fail_count++;
                    $fail_ids[] = "$csv_its (DB error)";
                }
            } else {
                $fail_count++;
                $fail_ids[] = "$csv_its (not found)";
            }
        }
        fclose($file);

        $bulk_result = [
            'success_count' => $success_count,
            'fail_count'    => $fail_count,
            'fail_ids'      => $fail_ids,
            'total'         => $success_count + $fail_count,
        ];
    }
    }

    // Fetch parties for dropdown
    $query   = "SELECT * FROM `bqi_zakereen_parties` WHERE `is_active` = 1 AND `added_its` = '$user_its' ORDER BY `party_name` ASC";
    $result  = mysqli_query($mysqli, $query);
    $parties = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch farzando records
    $query = "SELECT f.*, p.party_name FROM `bqi_zakereen_farzando` f
          LEFT JOIN `bqi_zakereen_parties` p ON f.party_id = p.id
          WHERE f.added_its = '$user_its'
          ORDER BY f.added_ts DESC";
    $result        = mysqli_query($mysqli, $query);
    $farzando_list = $result->fetch_all(MYSQLI_ASSOC);
?>

<main id="main" class="main bqi-1447">
    <section class="section dashboard">
        <div class="row">
            <?php require_once __DIR__ . '/inc/messages.php'; ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Zakereen Farzando Registration</h5>

                    <div class="row">
                        <!-- Individual Add Form -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Add Individual Farzand</h5>

                                    <?php if ($verified_user): ?>
                                        <!-- Step 2: ITS Verified - Show details and Save form -->
                                        <div class="alert alert-success py-2">
                                            <strong>ITS Verified Successfully</strong>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3 text-center">
                                                <img src="https://www.talabulilm.com/mumin_images/<?php echo htmlspecialchars($verified_user['its_id']) ?>.png"
                                                     class="rounded-circle" width="80" height="80"
                                                     alt="<?php echo htmlspecialchars($verified_user['full_name_en']) ?>">
                                            </div>
                                            <div class="col-md-9">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tr><td><strong>ITS ID:</strong></td><td><?php echo htmlspecialchars($verified_user['its_id']) ?></td></tr>
                                                    <tr><td><strong>Name:</strong></td><td><?php echo htmlspecialchars($verified_user['full_name_en']) ?></td></tr>
                                                    <tr><td><strong>Gender:</strong></td><td><?php echo htmlspecialchars($verified_user['gender'] ?? '') ?></td></tr>
                                                    <tr><td><strong>Jamaat:</strong></td><td><?php echo htmlspecialchars($verified_user['jamaat'] ?? '') ?></td></tr>
                                                </table>
                                            </div>
                                        </div>

                                        <form method="post">
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label">Select Party<span class="required_star">*</span></label>
                                                <div class="col-sm-7">
                                                    <select name="party_id" class="form-select" required>
                                                        <option value="" disabled <?php echo empty($selected_party) ? 'selected' : '' ?>>Select Party...</option>
                                                        <?php foreach ($parties as $p): ?>
                                                            <option value="<?php echo $p['id'] ?>" <?php echo($selected_party == $p['id']) ? 'selected' : '' ?>><?php echo htmlspecialchars($p['party_name']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-1">
                                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPartyModal" title="Quick Add Party">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Hidden fields from verified data -->
                                            <input type="hidden" name="its_id" value="<?php echo htmlspecialchars($verified_user['its_id']) ?>">
                                            <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($verified_user['full_name_en']) ?>">
                                            <input type="hidden" name="gender" value="<?php echo htmlspecialchars($verified_user['gender'] ?? '') ?>">
                                            <input type="hidden" name="dob" value="<?php echo htmlspecialchars($verified_user['dob'] ?? '') ?>">
                                            <input type="hidden" name="jamaat_val" value="<?php echo htmlspecialchars($verified_user['jamaat'] ?? '') ?>">
                                            <input type="hidden" name="jamiat_val" value="<?php echo htmlspecialchars($verified_user['jamiat'] ?? '') ?>">
                                            <input type="hidden" name="mobile" value="<?php echo htmlspecialchars($verified_user['mobile'] ?? '') ?>">
                                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($verified_user['email'] ?? '') ?>">

                                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary">Cancel</a>
                                                <button type="submit" name="add_farzand" class="btn btn-primary">Save Farzand</button>
                                            </div>
                                        </form>

                                    <?php else: ?>
                                        <!-- Step 1: Enter ITS ID and Verify -->
                                        <form method="post">
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label">ITS ID<span class="required_star">*</span></label>
                                                <div class="col-sm-5">
                                                    <input type="number" name="verify_its_id" class="form-control" placeholder="Enter ITS ID" required>
                                                </div>
                                                <div class="col-sm-3">
                                                    <button type="submit" name="verify_its" class="btn btn-info btn-sm">Verify ITS</button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>

                        <!-- Bulk Upload -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Bulk Upload (CSV)</h5>
                                    <p class="text-muted">
                                        Upload a CSV file with ITS IDs (one per row, first column).
                                        <a href="<?php echo MODULE_PATH ?>samples/sample_farzando_bulk_upload.csv" download class="btn btn-info btn-sm text-white ms-2"><i class="bi bi-file-earmark-arrow-down"></i> Download Sample CSV</a>
                                    </p>
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Select Party<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <select name="party_id" class="form-select" required>
                                                    <option value="" disabled selected>Select Party...</option>
                                                    <?php foreach ($parties as $p): ?>
                                                        <option value="<?php echo $p['id'] ?>"><?php echo htmlspecialchars($p['party_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">CSV File<span class="required_star">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                                            </div>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" name="bulk_upload" class="btn btn-success">Upload CSV</button>
                                        </div>
                                    </form>

                                    <?php if (! empty($bulk_result)): ?>
                                        <div class="mt-3">
                                            <h6 class="fw-bold">Upload Result</h6>
                                            <div class="row text-center mb-2">
                                                <div class="col-4">
                                                    <div class="card bg-light">
                                                        <div class="card-body py-2">
                                                            <h4 class="mb-0"><?php echo $bulk_result['total'] ?></h4>
                                                            <small class="text-muted">Total</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="card bg-success text-white">
                                                        <div class="card-body py-2">
                                                            <h4 class="mb-0"><?php echo $bulk_result['success_count'] ?></h4>
                                                            <small>Successful</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="card bg-danger text-white">
                                                        <div class="card-body py-2">
                                                            <h4 class="mb-0"><?php echo $bulk_result['fail_count'] ?></h4>
                                                            <small>Failed</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if (! empty($bulk_result['fail_ids'])): ?>
                                                <div class="alert alert-danger py-2 mb-0" style="max-height: 150px; overflow-y: auto;">
                                                    <strong>Failed ITS IDs:</strong><br>
                                                    <?php foreach ($bulk_result['fail_ids'] as $fid): ?>
                                                        <span class="badge bg-danger me-1 mb-1"><?php echo htmlspecialchars($fid) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Records Table -->
                    <div class="row mt-3">
                        <div class="col-md-12" style="overflow-x: auto;">
                            <h5 class="card-title">Registered Farzando</h5>
                            <table class="table table-striped" id="datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>ITS ID</th>
                                        <th>Name</th>
                                        <th>Party</th>
                                        <th>Jamaat</th>
                                        <th>Mobile</th>
                                        <th>Source</th>
                                        <th>Added On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($farzando_list as $key => $f): ?>
                                        <tr>
                                            <td><?php echo $key + 1 ?></td>
                                            <td><?php echo $f['its_id'] ?></td>
                                            <td><?php echo htmlspecialchars($f['full_name']) ?></td>
                                            <td><?php echo htmlspecialchars($f['party_name'] ?? 'N/A') ?></td>
                                            <td><?php echo htmlspecialchars($f['jamaat'] ?? '') ?></td>
                                            <td><?php echo htmlspecialchars($f['mobile'] ?? '') ?></td>
                                            <td><?php echo $f['is_bulk_upload'] ? '<span class="badge bg-info">CSV</span>' : '<span class="badge bg-primary">Manual</span>' ?></td>
                                            <td><?php echo date('d-M-Y H:i', strtotime($f['added_ts'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info edit-farzand-btn"
                                                    data-id="<?php echo $f['id'] ?>"
                                                    data-name="<?php echo htmlspecialchars($f['full_name']) ?>"
                                                    data-party-id="<?php echo $f['party_id'] ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editFarzandModal">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="delete_id" value="<?php echo $f['id'] ?>">
                                                    <button type="submit" name="delete_farzand" class="btn btn-sm btn-outline-danger delete-btn">
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

<!-- Quick Add Party Modal -->
<div class="modal fade" id="addPartyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add Party</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Party Name<span class="required_star">*</span></label>
                        <input type="text" name="party_name" class="form-control" placeholder="Enter Party Name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="quick_add_party" class="btn btn-primary">Save Party</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Farzand Modal -->
<div class="modal fade" id="editFarzandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Farzand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_farzand_id">
                    <div class="mb-3">
                        <label class="form-label"><strong>Name:</strong> <span id="edit_farzand_name"></span></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reassign Party</label>
                        <select name="edit_party_id" id="edit_farzand_party" class="form-select" required>
                            <option value="" disabled>Select Party...</option>
                            <?php foreach ($parties as $p): ?>
                                <option value="<?php echo $p['id'] ?>"><?php echo htmlspecialchars($p['party_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_farzand" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="page-state" data-show-popup="<?php echo ! empty($show_popup) ? '1' : '0' ?>"></div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
<script src="assets/js/zakereen_farzando.js"></script>
