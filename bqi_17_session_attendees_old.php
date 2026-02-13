<?php
// Initialize session and dependencies
require_once(__DIR__ . '/session.php');
$current_page = 'bqi_17_session_attendees';
require_once(__DIR__ . '/inc/header.php');

// Initialize variables
$its_id = isset($_GET['its_id']) ? filter_var($_GET['its_id'], FILTER_SANITIZE_NUMBER_INT) : '';
$edit_its_id = isset($_GET['edit_its_id']) ? filter_var($_GET['edit_its_id'], FILTER_SANITIZE_NUMBER_INT) : '';
$userdata = [];
$lddata = [];
$message = [];

// Function to log queries to a file
function logQuery($query, $message = null)
{
    $logFilePath = __DIR__ . '/bqi_17_session_attendees.log';
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
    
    return json_decode($response, true);
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
    $check_query = "SELECT * FROM `bqi_17_session_attendees` WHERE `its_id` = $its_id";
    $check_result = $mysqli->query($check_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        return [
            'success' => false,
            'message' => 'A record with this ITS ID already exists.',
            'tag' => 'danger'
        ];
    }
    
    $fields = [
        'its_id' => $its_id,
        'full_name' => $mysqli->real_escape_string($data['full_name_en']),
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
    
    $query = "INSERT INTO `bqi_17_session_attendees` (`$columns`) VALUES ('$values')";
    $result = $mysqli->query($query);
    
    logQuery($query, $result ? 'bqi_17_session_attendees record inserted successfully' : 'bqi_17_session_attendees insertion failed: ' . $mysqli->error);
    
    return [
        'success' => (bool)$result,
        'message' => $result ? 'BQI 17 Session Attendees Record inserted Successfully' : 'Database Error: ' . $mysqli->error,
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
    
    $query = "DELETE FROM `bqi_17_session_attendees` WHERE `its_id` = $its_id";
    $result = $mysqli->query($query);
    
    logQuery($query, $result ? 'BQI 17 Session Attendees record deleted successfully' : 'BQI 17 Session Attendees deletion failed: ' . $mysqli->error);
    
    return [
        'success' => (bool)$result,
        'message' => $result ? 'BQI 17 Session Attendees Record deleted Successfully' : 'Database Error: ' . $mysqli->error,
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
    
    $query = "SELECT * FROM `bqi_17_session_attendees` WHERE `its_id` = $its_id";
    $result = $mysqli->query($query);
    
    if (!$result || $result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

// Process fetch request
if (isset($_POST['fetch']) && !empty($its_id)) {
    $userdata = fetchUserData($its_id);
    
    if (!$userdata) {
        $message = [
            'text' => "Invalid ITS ID: " . htmlspecialchars($its_id),
            'tag' => 'danger'
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
if (isset($_POST['submit'])) {
    logQuery(serialize($_POST), 'Post Data');
    $result = insertLDRecord($mysqli, $_POST, $user_its ?? 0);
    
    $message = [
        'text' => $result['message'],
        'tag' => $result['tag']
    ];
}

// Fetch existing LD records
$mauze = $mysqli->real_escape_string($mauze ?? '');
$query = "SELECT `bs`.*,`um`.`fullname` FROM `bqi_17_session_attendees` `bs`
LEFT JOIN `users_mamureen` `um` ON `bs`.`added_its` = `um`.`its_id`
WHERE `bs`.`added_its` = '$user_its'";
$result = $mysqli->query($query);
$data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>BQI 17 Session Attendees</h1>
    </div>

    <section class="section dashboard">
        <div class="row">
            <?php require_once(__DIR__ . '/inc/messages.php'); ?>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <h5 class="card-title"><?= $edit_its_id ? 'Edit BQI 17 Session Attendees' : 'Add new BQI 17 Session Attendees' ?></h5>
                        <p><strong>IMPORTANT INSTRUCTIONS:</strong></p>
                        <ul>
                            <li>All fields marked with <span class="required_star">*</span> are required.</li>
                            <li>This entry page is intended for Mumineen who have attended at least 17 sessions of the Lisan ud Dawat Barnamaj. By submitting their ITS IDs, you confirm that these Mumineen are eligible for certification and the Talabul Ilm online Ikhtebaar.</li>
                            <li>Please note that only the ITS IDs entered here will be eligible for certification, and only these individuals will have access to the online Talabul Ilm Imtehaan.</li>
                        </ul>
                        
                        <!-- Search student by ITS ID (only for new records) -->
                        <?php if (!$lddata): ?>
                            <div class="card">
                                <div class="card-body pt-3">
                                    <?php if ($userdata): ?>
                                        <div class="row pt-2">
                                            <div class="col-md-12 d-flex">
                                                <img src="https://www.talabulilm.com/mumin_images/<?= htmlspecialchars($userdata['its_id']) ?>.png" 
                                                     class="fetch_photo" 
                                                     alt="<?= htmlspecialchars($userdata['full_name_en']) ?>">
                                                <div class="fetch_name">
                                                    <h5 class="card-title pt-2 pb-0"><?= htmlspecialchars($userdata['full_name_en']) ?></h5>
                                                    <h5 class="card-title pt-2 pb-0">
                                                        ITS ID: <?= htmlspecialchars($userdata['its_id']) ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                                            <div class="row mb-3">
                                                <label for="its_id" class="col-sm-2 col-form-label">
                                                    ITS ID<span class="required_star">*</span>
                                                </label>
                                                <div class="col-sm-8">
                                                    <input type="number" name="its_id" id="its_id" class="form-control" 
                                                           placeholder="ITS ID" required>
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
                        
                        <!-- Student form -->
                        <div class="col-md-12">
                            <form method="post" id="myForm" onsubmit="return validateForm()">
                                <div class="row">
                                    <?php if ($userdata): ?>
                                        <!-- Hidden fields for student data (for new records) -->
                                        <input type="hidden" name="valid_its" value="<?= htmlspecialchars($userdata['its_id']) ?>">
                                        <input type="hidden" name="full_name_en" value="<?= htmlspecialchars($userdata['full_name_en']) ?>">
                                        <input type="hidden" name="gender" value="<?= htmlspecialchars($userdata['gender']) ?>">
                                        <input type="hidden" name="jamaat" value="<?= htmlspecialchars($userdata['jamaat']) ?>">
                                        <input type="hidden" name="jamiat" value="<?= htmlspecialchars($userdata['jamiat']) ?>">
                                        <input type="hidden" name="mobile" value="<?= htmlspecialchars($userdata['mobile'] ?? '') ?>">
                                        <input type="hidden" name="email" value="<?= htmlspecialchars($userdata['email'] ?? '') ?>">
                                        <input type="hidden" name="dob" value="<?= htmlspecialchars($userdata['dob'] ?? '') ?>">
                                    <?php endif; ?>
                                    
                                    <?php if ($lddata): ?>
                                        <!-- Hidden fields for editing existing record -->
                                        <input type="hidden" name="its_id" value="<?= htmlspecialchars($lddata['its_id']) ?>">
                                        <input type="hidden" name="editing" value="true">
                                        
                                        <!-- Display student info in edit mode -->
                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">ITS ID</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($lddata['its_id']) ?>" readonly>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label">Name</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($lddata['full_name']) ?>" readonly>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Form fields -->
                                    <div class="row mb-3">
                                        
                                    
                                    <div class="col-md-2">
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary" name="submit" type="submit"
                                                    <?= (!$userdata && !$lddata) ? 'disabled' : '' ?>>Submit</button>
                                        </div>
                                    </div>
                                    
                                    <?php if ($lddata): ?>
                                        <div class="col-md-2 ms-2">
                                            <div class="d-grid gap-2">
                                                <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary">
                                                    Cancel
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        
                        <!-- List of existing records -->
                        <div class="col-md-12 mt-4">
                            <h5 class="card-title">List Of BQI 17 Session Attendees</h5>
                            <table class="table table-bordered cell-border" id="datatable_hifz_nasaih">
                                <thead>
                                    <tr>
                                        <th scope="col">ITS ID</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Gender</th>
                                        <th scope="col">Mobile</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Mamur ITS</th>
                                        <th scope="col">Mamur Name</th>
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
    // Fetch button functionality
    const fetchForm = document.querySelector('form');
    const fetchButton = document.querySelector('input[name="fetch"]');
    const itsIdInput = document.querySelector('input[name="its_id"]');
    
    if (fetchButton && fetchForm) {
        fetchButton.addEventListener('click', function(e) {
            if (itsIdInput && itsIdInput.value.trim() !== '') {
                // Update URL with its_id parameter
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('its_id', itsIdInput.value.trim());
                fetchForm.action = currentUrl.toString();
            }
        });
    }
    
    // Initial call to set visibility of custom language field
    toggleCustomLanguage();
    
    // Initialize DataTable
    if ($.fn.DataTable) {
        $("#datatable_hifz_nasaih").DataTable({
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
                        return "bqi_17_session_attendees-" + Date.now();
                    }
                },
                {
                    extend: "csv",
                    filename: function() {
                        return "bqi_17_session_attendees-" + Date.now();
                    }
                },
                {
                    extend: "excel",
                    filename: function() {
                        return "bqi_17_session_attendees-" + Date.now();
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