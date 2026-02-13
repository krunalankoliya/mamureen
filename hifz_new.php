<?php
// Initialize session and dependencies
require_once(__DIR__ . '/session.php');
$current_page = 'hifz_nasaih';
require_once(__DIR__ . '/inc/header.php');

// Initialize variables
$its_ids = isset($_GET['its_id']) ? filter_var($_GET['its_id'], FILTER_SANITIZE_STRING) : '';
$edit_its_id = isset($_GET['edit_its_id']) ? filter_var($_GET['edit_its_id'], FILTER_SANITIZE_NUMBER_INT) : '';
$userdata_list = [];
$not_found_its = [];
$lddata = [];
$message = [];
$active_tab = isset($_GET['tab']) ? filter_var($_GET['tab'], FILTER_SANITIZE_STRING) : 'your_entries';
// Function to log queries to a file
function logQuery($query, $message = null)
{
    $logFilePath = __DIR__ . '/hifz_nasaih.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "$timestamp - Query: $query" . PHP_EOL;
    
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
    $ver = $_COOKIE['ver'] ?? '';
    
    if (empty($user_its) || empty($ver)) {
        return null;
    }
    
    $api_url = "https://www.talabulilm.com/api2022/core/user/getUserDetailsByItsID/" . urlencode($its_id);
    $auth = base64_encode("$user_its:$ver");
    $headers = ["Authorization: Basic $auth"];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RESOLVE, ['www.talabulilm.com:443:23.111.171.44']);
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        logQuery($error, 'API Error');
        return null;
    }
    
    $data = json_decode($response, true);
    if (empty($data) || !isset($data['its_id'])) {
        return null;
    }
    
    return $data;
}

// Function to fetch multiple user data
function fetchMultipleUserData($its_ids_str) 
{
    $its_ids = preg_split('/[\s,;]+/', $its_ids_str, -1, PREG_SPLIT_NO_EMPTY);
    $result = [];
    $not_found = [];
    
    foreach ($its_ids as $its_id) {
        $its_id = trim($its_id);
        if (!is_numeric($its_id)) {
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
        'found' => $result,
        'not_found' => $not_found
    ];
}

// Function to insert LD record
function insertLDRecord($mysqli, $data, $user_its) 
{
    $its_id = (int)$data['valid_its'];
    
    if ($its_id === 0) {
        return [
            'success' => false,
            'message' => 'ITS ID cannot be 0.',
            'tag' => 'danger'
        ];
    }
    
    // Check if record already exists
    $check_query = "SELECT * FROM `hifz_nasaih` WHERE `its_id` = $its_id";
    $check_result = $mysqli->query($check_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        return [
            'success' => false,
            'message' => 'A record with ITS ID ' . $its_id . ' already exists.',
            'tag' => 'danger'
        ];
    }
    
    $fields = [
        'its_id' => $its_id,
        'full_name' => $mysqli->real_escape_string($data['full_name_en']),
        'full_name_ar' => $mysqli->real_escape_string($data['full_name_ar']),
        'gender' => $mysqli->real_escape_string($data['gender']),
        'dob' => $mysqli->real_escape_string($data['dob']),
        'jamaat' => $mysqli->real_escape_string($data['jamaat']),
        'jamiat' => $mysqli->real_escape_string($data['jamiat']),
        'email' => $mysqli->real_escape_string($data['email']),
        'mobile' => $mysqli->real_escape_string($data['mobile']),
        'added_its' => (int)$user_its,
        'added_ts' => date('Y-m-d H:i:s')
    ];
    
    $columns = implode('`, `', array_keys($fields));
    $values = implode("', '", array_values($fields));
    
    $query = "INSERT INTO `hifz_nasaih` (`$columns`) VALUES ('$values')";
    $result = $mysqli->query($query);
    
    logQuery($query, $result ? 'Hifz Nasaih record inserted successfully' : 'Hifz Nasaih insertion failed: ' . $mysqli->error);
    
    return [
        'success' => (bool)$result,
        'message' => $result ? 'Hifz Nasaih Record inserted Successfully for ITS ID ' . $its_id : 'Database Error for ITS ID ' . $its_id . ': ' . $mysqli->error,
        'tag' => $result ? 'success' : 'danger'
    ];
}

// Function to delete LD record
function deleteLDRecord($mysqli, $its_id) 
{
    $its_id = (int)$its_id;
    
    if ($its_id === 0) {
        return [
            'success' => false,
            'message' => 'Invalid ITS ID.',
            'tag' => 'danger'
        ];
    }
    
    $query = "DELETE FROM `hifz_nasaih` WHERE `its_id` = $its_id";
    $result = $mysqli->query($query);
    
    logQuery($query, $result ? 'Hifz Nasaih record deleted successfully' : 'LD deletion failed: ' . $mysqli->error);
    
    return [
        'success' => (bool)$result,
        'message' => $result ? 'Hifz Nasaih Record deleted Successfully' : 'Database Error: ' . $mysqli->error,
        'tag' => $result ? 'success' : 'danger'
    ];
}

// Function to fetch LD record by ITS ID
function getLDRecord($mysqli, $its_id) 
{
    $its_id = (int)$its_id;
    
    if ($its_id === 0) {
        return null;
    }
    
    $query = "SELECT * FROM `hifz_nasaih` WHERE `its_id` = $its_id";
    $result = $mysqli->query($query);
    
    if (!$result || $result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

// Process fetch request for multiple ITS IDs
if (isset($_POST['fetch']) && !empty($_POST['its_ids'])) {
    $its_ids = $_POST['its_ids'];
    $fetch_result = fetchMultipleUserData($its_ids);
    
    $userdata_list = $fetch_result['found'];
    $not_found_its = $fetch_result['not_found'];
    
    if (empty($userdata_list) && !empty($not_found_its)) {
        $message = [
            'text' => "No valid ITS IDs found. Please check your input.",
            'tag' => 'danger'
        ];
    } elseif (!empty($not_found_its)) {
        $message = [
            'text' => "Some ITS IDs were not found: " . implode(', ', $not_found_its),
            'tag' => 'warning'
        ];
    }
}

// Handle delete request
if (isset($_GET['delete_its_id'])) {
    $delete_its_id = filter_var($_GET['delete_its_id'], FILTER_SANITIZE_NUMBER_INT);
    $result = deleteLDRecord($mysqli, $delete_its_id);
    
    $message = [
        'text' => $result['message'],
        'tag' => $result['tag']
    ];
}

// Handle edit request
if (!empty($edit_its_id)) {
    $lddata = getLDRecord($mysqli, $edit_its_id);
    
    if (!$lddata) {
        $message = [
            'text' => "Record not found with ITS ID: " . htmlspecialchars($edit_its_id),
            'tag' => 'danger'
        ];
    }
}

// Process form submission
if (isset($_POST['submit']) && isset($_POST['valid_its_array'])) {
    $success_count = 0;
    $error_count = 0;
    $error_messages = [];
    
    // Process each selected ITS ID
    foreach ($_POST['valid_its_array'] as $index => $its_id) {
        $data = [
            'valid_its' => $its_id,
            'full_name_en' => $_POST['full_name_en'][$index],
            'full_name_ar' => $_POST['full_name_ar'][$index],
            'gender' => $_POST['gender'][$index],
            'dob' => $_POST['dob'][$index],
            'jamaat' => $_POST['jamaat'][$index],
            'jamiat' => $_POST['jamiat'][$index],
            'mobile' => $_POST['mobile'][$index],
            'email' => $_POST['email'][$index]
        ];
        
        $result = insertLDRecord($mysqli, $data, $user_its ?? 0);
        
        if ($result['success']) {
            $success_count++;
        } else {
            $error_count++;
            $error_messages[] = $result['message'];
        }
    }
    
    if ($success_count > 0 && $error_count == 0) {
        $message = [
            'text' => "$success_count records inserted successfully.",
            'tag' => 'success'
        ];
    } elseif ($success_count > 0 && $error_count > 0) {
        $message = [
            'text' => "$success_count records inserted successfully. $error_count failed: " . implode('; ', $error_messages),
            'tag' => 'warning'
        ];
    } else {
        $message = [
            'text' => "Failed to insert records: " . implode('; ', $error_messages),
            'tag' => 'danger'
        ];
    }
}

// Fetch existing LD records
$mauze = $mysqli->real_escape_string($mauze ?? '');
// $query = "SELECT `hn`.*,`um`.`fullname` FROM `hifz_nasaih` `hn`
// LEFT JOIN `users_mamureen` `um` ON `hn`.`added_its` = `um`.`its_id`
// WHERE `hn`.`added_its` = '$user_its'";
// $result = $mysqli->query($query);
// $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];


// Fetch data for the selected tab
if ($active_tab === 'your_entries') {
    // Your entries
    $query = "SELECT `hn`.*,`um`.`fullname` FROM `hifz_nasaih` `hn`
              LEFT JOIN `users_mamureen` `um` ON `hn`.`added_its` = `um`.`its_id`
              WHERE `hn`.`added_its` = '$user_its'";
} else {
    // Your mauze entries
    $query = "SELECT `hn`.*,`um`.`fullname` FROM `hifz_nasaih` `hn`
              LEFT JOIN `users_mamureen` `um` ON `hn`.`added_its` = `um`.`its_id`
              WHERE `hn`.`jamaat` = '$mauze'";
}

$result = $mysqli->query($query);
$data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Hifz al-Nasa'ih</h1>
    </div>

    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <h5 class="card-title"><?= $edit_its_id ? 'Edit new Hifz entry' : 'Add new Hifz entry' ?></h5>
                        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                        <ul>
                            <li>All fields marked with <span class="required_star">*</span> are required.</li>
                            <li><b>Important: This entry page is for Mumineen who have memorized all Nisaab Nasa'ih. By submitting their ITS, you confirm that you have listened to their recitation and agree.</b></li>
                        </ul>
                        
                        <!-- Search student by ITS ID (only for new records) -->
                        <?php if (!$lddata): ?>
                            <div class="card">
                                <div class="card-body pt-3">
                                    <?php if (!empty($userdata_list)): ?>
                                        <form method="post" id="bulkSubmitForm">
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <h5>Found ITS Records (<?= count($userdata_list) ?>)</h5>
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
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($userdata_list as $index => $user): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <input type="checkbox" name="selected_its[]" value="<?= $index ?>" class="select-its" checked>
                                                                            <input type="hidden" name="valid_its_array[]" value="<?= htmlspecialchars($user['its_id']) ?>">
                                                                            <input type="hidden" name="full_name_en[]" value="<?= htmlspecialchars($user['full_name_en']) ?>">
                                                                            <input type="hidden" name="full_name_ar[]" value="<?= htmlspecialchars($user['full_name_ar']) ?>">
                                                                            <input type="hidden" name="gender[]" value="<?= htmlspecialchars($user['gender']) ?>">
                                                                            <input type="hidden" name="jamaat[]" value="<?= htmlspecialchars($user['jamaat']) ?>">
                                                                            <input type="hidden" name="jamiat[]" value="<?= htmlspecialchars($user['jamiat'] ?? '') ?>">
                                                                            <input type="hidden" name="mobile[]" value="<?= htmlspecialchars($user['mobile'] ?? '') ?>">
                                                                            <input type="hidden" name="email[]" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                                                            <input type="hidden" name="dob[]" value="<?= htmlspecialchars($user['dob'] ?? '') ?>">
                                                                        </td>
                                                                        <td><?= htmlspecialchars($user['its_id']) ?></td>
                                                                        <td>
                                                                            <img src="https://www.talabulilm.com/mumin_images/<?= htmlspecialchars($user['its_id']) ?>.png" 
                                                                                 class="fetch_photo" style="height: 50px; width: auto;" 
                                                                                 alt="<?= htmlspecialchars($user['full_name_en']) ?>">
                                                                        </td>
                                                                        <td><?= htmlspecialchars($user['full_name_en']) ?></td>
                                                                        <td><?= htmlspecialchars($user['gender']) ?></td>
                                                                        <td><?= htmlspecialchars($user['jamaat']) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($not_found_its)): ?>
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <div class="alert alert-warning">
                                                            <h5>Not Found ITS Records (<?= count($not_found_its) ?>)</h5>
                                                            <p>The following ITS IDs could not be found:</p>
                                                            <ul>
                                                                <?php foreach ($not_found_its as $its): ?>
                                                                    <li><?= htmlspecialchars($its) ?></li>
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
                                                        <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary">
                                                            Cancel
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                                            <div class="row mb-3">
                                                <label for="its_ids" class="col-sm-2 col-form-label">
                                                    ITS ID(s)<span class="required_star">*</span>
                                                </label>
                                                <div class="col-sm-8">
                                                    <textarea name="its_ids" id="its_ids" class="form-control" 
                                                           placeholder="Enter multiple ITS IDs separated by commas, spaces, or paste from Excel" 
                                                           rows="3" required><?= htmlspecialchars($its_ids) ?></textarea>
                                                    <small class="form-text text-muted">You can enter multiple ITS IDs separated by commas, 
                                                        spaces, or paste directly from Excel.</small>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="submit" value="Fetch" name="fetch" class="btn btn-primary">
                                                </div>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- List of existing records -->
                        <div class="col-md-12 mt-4">
                            <h5 class="card-title">List Of Hifz Nasaih</h5>
                            <ul class="nav nav-tabs" id="hifzTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= ($active_tab === 'your_entries') ? 'active' : '' ?>" id="your-entries-tab" data-bs-toggle="tab" data-bs-target="#your-entries" 
                                        type="button" role="tab" aria-controls="your-entries" aria-selected="true">
                                        Your Entries
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= ($active_tab === 'mauze_entries') ? 'active' : '' ?>" id="mauze-entries-tab" data-bs-toggle="tab" data-bs-target="#mauze-entries" 
                                        type="button" role="tab" aria-controls="mauze-entries" aria-selected="false">
                                        Your Mauze Entries
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="hifzTabContent">
                                <div class="tab-pane fade show active" id="your-entries" role="tabpanel" aria-labelledby="your-entries-tab">
                                    <p class='note' style="padding-top: 15px;"><?= ($active_tab === 'mauze_entries') ? "This page shows all the Nasihat hifz entries done in your Jamaat by various khidmat guzars. You will not see entries done by you in this report if they are not from your jamat. For viewing all the entries done by you, kindly check the (Report Name) report" : "This page shows all Nasihat hifz entries done by you on the module." ?></p>
                                    
                                    <!-- Your Entries Table -->
                                    <table class="table table-bordered cell-border" id="datatable_your_entries">
                                        <thead>
                                            <tr>
                                                <th scope="col">ITS ID</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Gender</th>
                                                <th scope="col">Mobile</th>
                                                <th scope="col">Email</th> 
                                                <th scope="col">Mamur ITS</th>
                                                <th scope="col">Mamur Name</th>
                                                <th scope="col">Certificate</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data as $student): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($student['its_id']) ?></td>
                                                    <td><?= htmlspecialchars($student['full_name']) ?></td>
                                                    <td><?= htmlspecialchars($student['gender']) ?></td>
                                                    <td><?= htmlspecialchars($student['mobile']) ?></td>
                                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                                    <td><?= htmlspecialchars($student['added_its']) ?></td>
                                                    <td><?= htmlspecialchars($student['fullname']) ?></td>
                                                    <td>
                                                        <a href="certificates.php?its_id=<?= $student['its_id'] ?>&gender=<?= $student['gender'] ?>&name=<?= urlencode($student['full_name_ar']) ?>" 
                                                           class="btn btn-sm btn-primary" target="_blank">
                                                            <i class="bi bi-file-earmark-text"></i> View
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="#" class="btn btn-sm btn-danger delete-record" 
                                                           data-its-id="<?= $student['its_id'] ?>" 
                                                           data-name="<?= htmlspecialchars($student['full_name']) ?>">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
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
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the record for <span id="delete-name"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirm-delete" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/inc/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const individualCheckboxes = document.querySelectorAll('.select-its');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            individualCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });
    }
    
    if (individualCheckboxes.length > 0) {
        individualCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(individualCheckboxes).every(cb => cb.checked);
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = !allChecked && 
                        Array.from(individualCheckboxes).some(cb => cb.checked);
                }
            });
        });
    }
    
    // Form submission validation
    const bulkSubmitForm = document.getElementById('bulkSubmitForm');
    if (bulkSubmitForm) {
        bulkSubmitForm.addEventListener('submit', function(e) {
            const selectedCheckboxes = document.querySelectorAll('.select-its:checked');
            if (selectedCheckboxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one record to submit');
                return false;
            }
            return true;
        });
    }
    
    // ITS IDs format enhancement
    const itsIdsTextarea = document.getElementById('its_ids');
    if (itsIdsTextarea) {
        itsIdsTextarea.addEventListener('paste', function(e) {
            // Let the paste happen normally
            setTimeout(function() {
                // Get the pasted content and normalize it
                const content = itsIdsTextarea.value;
                // Replace tabs, multiple spaces, newlines with commas
                const normalized = content.replace(/[\t\n\r]+/g, ',').replace(/\s*,\s*/g, ',').replace(/\s+/g, ',');
                itsIdsTextarea.value = normalized;
            }, 0);
        });
    }
    
    // Initialize DataTable
    if ($.fn.DataTable) {
        $("#datatable_your_entries").DataTable({
            dom: "lBfrtip",
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"],
            ],
            buttons: [
                {
                    extend: "copy",
                    filename: function() {
                        return "hifz_nasaih-" + Date.now();
                    }
                },
                {
                    extend: "csv",
                    filename: function() {
                        return "hifz_nasaih-" + Date.now();
                    }
                },
                {
                    extend: "excel",
                    filename: function() {
                        return "hifz_nasaih-" + Date.now();
                    }
                }
            ]
        });
    }
    
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-record');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteName = document.getElementById('delete-name');
    const confirmDelete = document.getElementById('confirm-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const itsId = this.getAttribute('data-its-id');
            const name = this.getAttribute('data-name');
            
            // Set the student name in the modal
            deleteName.textContent = name;
            
            // Set the delete confirmation link
            confirmDelete.href = `${window.location.pathname}?delete_its_id=${itsId}`;
            
            // Show the modal
            deleteModal.show();
        });
    });
});

// Handle tab change to reload data
    document.querySelectorAll('.nav-link').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('id');
            const activeTab = tabId === 'your-entries-tab' ? 'your_entries' : 'mauze_entries';
            window.location.href = `${window.location.pathname}?tab=${activeTab}`;
        });
    });
    
// Function to toggle the custom language input field
function toggleCustomLanguage() {
    const languageSelect = document.getElementById('preferred_language');
    const customLanguageContainer = document.getElementById('custom_language_container');
    const customLanguageInput = document.getElementById('custom_language');
    
    if (languageSelect && customLanguageContainer && customLanguageInput) {
        if (languageSelect.value === 'others') {
            customLanguageContainer.style.display = 'flex';
            customLanguageInput.required = true;
        } else {
            customLanguageContainer.style.display = 'none';
            customLanguageInput.required = false;
        }
    }
}

// Form validation
function validateForm() {
    const languageSelect = document.getElementById('preferred_language');
    const customLanguageInput = document.getElementById('custom_language');
    
    if (!languageSelect.value) {
        alert('Please select a preferred language');
        languageSelect.focus();
        return false;
    }
    
    // Validate custom language if "others" is selected
    if (languageSelect.value === 'others') {
        if (!customLanguageInput.value.trim()) {
            alert('Please specify the language');
            customLanguageInput.focus();
            return false;
        }
    }
    
    return true;
}
</script>