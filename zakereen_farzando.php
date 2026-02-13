<?php
    require_once __DIR__ . '/session.php';
    $current_page = 'zakereen_farzando';

    require_once __DIR__ . '/inc/header.php';

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

    // Handle Individual Add
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
                $message = ['text' => "Farzand $full_name ($its_id) added successfully.", 'tag' => 'success'];
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

        $msg = "Bulk upload complete: $success_count added successfully.";
        if ($fail_count > 0) {
            $msg .= " $fail_count failed: " . implode(', ', $fail_ids);
        }
        $message = ['text' => $msg, 'tag' => $fail_count > 0 ? 'warning' : 'success'];
    }
    }

    // Fetch parties for dropdown
    $query   = "SELECT * FROM `bqi_zakereen_parties` ORDER BY `party_name` ASC";
    $result  = mysqli_query($mysqli, $query);
    $parties = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch farzando records
    $query = "SELECT f.*, p.party_name FROM `bqi_zakereen_farzando` f
          LEFT JOIN `bqi_zakereen_parties` p ON f.party_id = p.id
          ORDER BY f.added_ts DESC";
    $result        = mysqli_query($mysqli, $query);
    $farzando_list = $result->fetch_all(MYSQLI_ASSOC);
?>

<main id="main" class="main">
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
                                    <form method="post" id="addFarzandForm">
                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Select Party<span class="required_star">*</span></label>
                                            <div class="col-sm-7">
                                                <select name="party_id" id="party_id" class="form-select" required>
                                                    <option value="" disabled selected>Select Party...</option>
                                                    <?php foreach ($parties as $p): ?>
                                                        <option value="<?php echo $p['id'] ?>"><?php echo htmlspecialchars($p['party_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-1">
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPartyModal" title="Quick Add Party">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">ITS ID<span class="required_star">*</span></label>
                                            <div class="col-sm-5">
                                                <input type="number" id="its_id_input" class="form-control" placeholder="Enter ITS ID" required>
                                            </div>
                                            <div class="col-sm-3">
                                                <button type="button" id="verifyBtn" class="btn btn-info btn-sm">Verify ITS</button>
                                            </div>
                                        </div>

                                        <!-- Verified Info Display -->
                                        <div id="verifiedInfo" class="d-none mb-3">
                                            <div class="row mb-2">
                                                <div class="col-md-3 text-center">
                                                    <img id="itsPhoto" src="" class="rounded-circle" width="60" height="60" alt="Photo">
                                                </div>
                                                <div class="col-md-9">
                                                    <strong id="itsName"></strong><br>
                                                    <small id="itsDetails" class="text-muted"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hidden fields populated by AJAX -->
                                        <input type="hidden" name="its_id" id="hid_its_id">
                                        <input type="hidden" name="full_name" id="hid_full_name">
                                        <input type="hidden" name="gender" id="hid_gender">
                                        <input type="hidden" name="dob" id="hid_dob">
                                        <input type="hidden" name="jamaat_val" id="hid_jamaat">
                                        <input type="hidden" name="jamiat_val" id="hid_jamiat">
                                        <input type="hidden" name="mobile" id="hid_mobile">
                                        <input type="hidden" name="email" id="hid_email">

                                        <div class="d-grid gap-2">
                                            <button type="submit" name="add_farzand" id="saveFarzandBtn" class="btn btn-primary" disabled>Save Farzand</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Upload -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Bulk Upload (CSV)</h5>
                                    <p class="text-muted">Upload a CSV file with ITS IDs (one per row, first column).</p>
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

<?php require_once __DIR__ . '/inc/footer.php'; ?>
<script>
// ITS Verification
$('#verifyBtn').click(function() {
    var its_id = $('#its_id_input').val();
    if (!its_id || its_id.length < 8) {
        alert('Please enter a valid ITS ID');
        return;
    }

    $(this).prop('disabled', true).text('Verifying...');

    $.ajax({
        url: '<?php echo MODULE_PATH ?>ajax_its_verify.php',
        type: 'POST',
        data: { its_id: its_id },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#itsPhoto').attr('src', res.photo_url);
                $('#itsName').text(res.full_name);
                $('#itsDetails').text(res.jamaat + ' | ' + res.gender);
                $('#verifiedInfo').removeClass('d-none');

                // Populate hidden fields
                $('#hid_its_id').val(res.its_id);
                $('#hid_full_name').val(res.full_name);
                $('#hid_gender').val(res.gender);
                $('#hid_dob').val(res.dob);
                $('#hid_jamaat').val(res.jamaat);
                $('#hid_jamiat').val(res.jamiat);
                $('#hid_mobile').val(res.mobile);
                $('#hid_email').val(res.email);

                $('#saveFarzandBtn').prop('disabled', false);
            } else {
                alert(res.message || 'ITS ID not found');
                $('#verifiedInfo').addClass('d-none');
                $('#saveFarzandBtn').prop('disabled', true);
            }
        },
        error: function() {
            alert('Error verifying ITS ID. Please try again.');
        },
        complete: function() {
            $('#verifyBtn').prop('disabled', false).text('Verify ITS');
        }
    });
});

$(document).ready(function () {
    $('#datatable').DataTable({
        dom: 'Bfrtip',
        pageLength: 10,
        buttons: [
            { extend: 'copy', filename: function(){ return 'zakereen_farzando-' + Date.now(); } },
            { extend: 'csv', filename: function(){ return 'zakereen_farzando-' + Date.now(); } },
            { extend: 'excel', filename: function(){ return 'zakereen_farzando-' + Date.now(); } }
        ]
    });
});
</script>
