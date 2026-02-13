<?php
    // Initialize session and dependencies
    require_once __DIR__ . '/session.php';
    $current_page = 'crs_certificate';
    require_once __DIR__ . '/inc/header.php';

    // Initialize variables
    $its_ids       = isset($_GET['its_id']) ? $_GET['its_id'] : '';
    $userdata_list = [];
    $not_found_its = [];
    $message       = [];

    // Function to log queries to a file
    function logQuery($query, $message = null)
    {
    $logFilePath = __DIR__ . '/crs_certificate.log';
    $timestamp   = date('Y-m-d H:i:s');
    $logMessage  = "$timestamp - Query: $query" . PHP_EOL;

    if ($message) {
        $logMessage .= "Message: $message" . PHP_EOL;
    }

    file_put_contents($logFilePath, $logMessage, FILE_APPEND);
    }

    // Function to fetch user data from API
    function fetchUserData($its_id)
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

    if ($error) {
        logQuery($error, 'API Error');
        return null;
    }

    $data = json_decode($response, true);
    if (empty($data) || ! isset($data['its_id'])) {
        return null;
    }

    return $data;
    }

    // Function to fetch multiple user data
    function fetchMultipleUserData($its_ids_str)
    {
    $its_ids   = preg_split('/[\s,;]+/', $its_ids_str, -1, PREG_SPLIT_NO_EMPTY);
    $result    = [];
    $not_found = [];

    foreach ($its_ids as $its_id) {
        $its_id = trim($its_id);
        if (! is_numeric($its_id)) {
            $not_found[] = $its_id;
            continue;
        }

        $data = fetchUserData($its_id);
        if ($data) {
            $result[] = $data;
        } else {
            $not_found[] = $its_id;
        }
    }

    return [
        'found'     => $result,
        'not_found' => $not_found,
    ];
    }

    // Function to insert certificate record
    function insertCertificateRecord($mysqli, $data, $user_its)
    {
    $its_id = (int) $data['valid_its'];
    $gender = $data['gender'];

    // Set certificate_id based on gender
    $certificate_id = ($gender === 'F') ? 39 : 38;

    if ($its_id === 0) {
        return [
            'success' => false,
            'message' => 'ITS ID cannot be 0.',
            'tag'     => 'danger',
        ];
    }

    // Current date for value1
    $current_date = date('Y-m-d');

    // Use INSERT IGNORE to handle duplicate entries
    // This will silently ignore duplicates without throwing an error
    $query = "INSERT IGNORE INTO `talabulilm_istifadah`.`crs_certificate_its` (`certificate_id`, `its_id`, `value1`)
              VALUES ($certificate_id, $its_id, '$current_date')";

    $result = $mysqli->query($query);

    // Check if a new record was inserted (affected_rows > 0) or if it was a duplicate (affected_rows = 0)
    if ($mysqli->affected_rows > 0) {
        logQuery($query, 'Certificate record inserted successfully');
        return [
            'success' => true,
            'message' => 'Certificate Record inserted Successfully for ITS ID ' . $its_id,
            'tag'     => 'success',
        ];
    } else if ($mysqli->affected_rows === 0) {
        // Check if it was ignored due to duplicate
        $check_query  = "SELECT * FROM `talabulilm_istifadah`.`crs_certificate_its` WHERE `its_id` = $its_id AND `certificate_id` = $certificate_id";
        $check_result = $mysqli->query($check_query);

        if ($check_result && $check_result->num_rows > 0) {
            logQuery($query, 'Certificate record already exists');
            return [
                'success' => false,
                'message' => 'A certificate record with ITS ID ' . $its_id . ' already exists.',
                'tag'     => 'warning', // Changed from danger to warning as this is not a critical error
            ];
        }
    }

    // If we get here, there was an error
    logQuery($query, 'Certificate insertion failed: ' . $mysqli->error);
    return [
        'success' => false,
        'message' => 'Database Error for ITS ID ' . $its_id . ': ' . $mysqli->error,
        'tag'     => 'danger',
    ];
    }
    // Process fetch request for multiple ITS IDs
    if (isset($_POST['fetch']) && ! empty($_POST['its_ids'])) {
    $its_ids = $_POST['its_ids'];

    // Split and remove empty entries
    $its_ids_array = preg_split('/[\s,;]+/', $its_ids, -1, PREG_SPLIT_NO_EMPTY);

    // Check if number of ITS IDs exceeds 50
    if (count($its_ids_array) > 50) {
        $message = [
            'text' => "Error: You entered " . count($its_ids_array) . " ITS IDs. Maximum allowed is 50. Please reduce the number of ITS IDs and try again.",
            'tag'  => 'danger',
        ];

        // Clear userdata_list to prevent processing
        $userdata_list = [];
        $not_found_its = [];
    } else {
        // Proceed with fetching data if within limit
        $fetch_result = fetchMultipleUserData($its_ids);

        $userdata_list = $fetch_result['found'];
        $not_found_its = $fetch_result['not_found'];

        if (empty($userdata_list) && ! empty($not_found_its)) {
            $message = [
                'text' => "No valid ITS IDs found. Please check your input.",
                'tag'  => 'danger',
            ];
        } elseif (! empty($not_found_its)) {
            $message = [
                'text' => "Some ITS IDs were not found: " . implode(', ', $not_found_its),
                'tag'  => 'warning',
            ];
        }
    }
    }

    // Process form submission
    if (isset($_POST['submit']) && isset($_POST['valid_its_array'])) {
    $success_count  = 0;
    $error_count    = 0;
    $error_messages = [];

    // Process each selected ITS ID
    foreach ($_POST['valid_its_array'] as $index => $its_id) {
        $data = [
            'valid_its' => $its_id,
            'gender'    => $_POST['gender'][$index],
        ];

        $result = insertCertificateRecord($mysqli, $data, $user_its ?? 0);

        if ($result['success']) {
            $success_count++;
        } else {
            $error_count++;
            $error_messages[] = $result['message'];
        }
    }

    if ($success_count > 0 && $error_count == 0) {
        $message = [
            'text' => "$success_count certificate records inserted successfully.",
            'tag'  => 'success',
        ];
    } elseif ($success_count > 0 && $error_count > 0) {
        $message = [
            'text' => "$success_count certificate records inserted successfully. $error_count failed: " . implode('; ', $error_messages),
            'tag'  => 'warning',
        ];
    } else {
        $message = [
            'text' => "Failed to insert certificate records: " . implode('; ', $error_messages),
            'tag'  => 'danger',
        ];
    }
    }

?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Certificate Management</h1>
    </div>

    <section class="section dashboard">
        <div class="row">
            <?php if (! empty($message)): ?>
                <div class="col-12">
                    <div class="alert alert-<?php echo $message['tag'] ?> alert-dismissible fade show" role="alert">
                        <?php echo $message['text'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <h5 class="card-title">Add New Certificate Entries</h5>
                        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                        <ul>
                            <li>You can add up to 50 ITS IDs at once.</li>
                        </ul>

                        <!-- Search student by ITS ID -->
                        <div class="card">
                            <div class="card-body pt-3">
                                <?php if (! empty($userdata_list)): ?>
                                    <form method="post" id="bulkSubmitForm">
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <h5>Found ITS Records (<?php echo count($userdata_list) ?>)</h5>
                                                <p>The following records were found. Select the records you want to add.</p>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th><input type="checkbox" id="selectAll" checked></th>
                                                                <th>ITS ID</th>
                                                                <th>Photo</th>
                                                                <th>Name</th>
                                                                <th>Gender</th>
                                                                <th>Jamaat</th>
                                                                <th>Certificate Type</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($userdata_list as $index => $user): ?>
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" name="selected_its[]" value="<?php echo $index ?>" class="select-its" checked>
                                                                        <input type="hidden" name="valid_its_array[]" value="<?php echo htmlspecialchars($user['its_id']) ?>">
                                                                        <input type="hidden" name="gender[]" value="<?php echo htmlspecialchars($user['gender']) ?>">
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($user['its_id']) ?></td>
                                                                    <td>
                                                                        <img src="https://www.talabulilm.com/mumin_images/<?php echo htmlspecialchars($user['its_id']) ?>.png"
                                                                             class="fetch_photo" style="height: 50px; width: auto;"
                                                                             alt="<?php echo htmlspecialchars($user['full_name_en']) ?>">
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($user['full_name_en']) ?></td>
                                                                    <td><?php echo htmlspecialchars($user['gender']) ?></td>
                                                                    <td><?php echo htmlspecialchars($user['jamaat']) ?></td>
                                                                    <td>
                                                                        <?php echo($user['gender'] === 'F') ? 'Female Certificate (36)' : 'Male Certificate (37)'; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (! empty($not_found_its)): ?>
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <div class="alert alert-warning">
                                                        <h5>Not Found ITS Records (<?php echo count($not_found_its) ?>)</h5>
                                                        <p>The following ITS IDs could not be found:</p>
                                                        <ul>
                                                            <?php foreach ($not_found_its as $its): ?>
                                                                <li><?php echo htmlspecialchars($its) ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                    <button type="submit" name="submit" class="btn btn-primary">
                                                        Submit Selected Records
                                                    </button>
                                                    <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary">
                                                        Cancel
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                                        <div class="row mb-3">
                                            <label for="its_ids" class="col-sm-2 col-form-label">
                                                ITS ID(s)<span class="required_star">*</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <textarea name="its_ids" id="its_ids" class="form-control"
                                                       placeholder="Enter multiple ITS IDs separated by commas, spaces, or paste from Excel"
                                                       rows="3" required><?php echo htmlspecialchars($its_ids) ?></textarea>
                                                <small class="form-text text-muted">You can enter multiple ITS IDs separated by commas,
                                                    spaces, or paste directly from Excel. Maximum 50 ITS IDs allowed.</small>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="submit" value="Fetch" name="fetch" class="btn btn-primary">
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

