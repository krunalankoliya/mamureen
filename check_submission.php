<?php
// check_submission.php - Check if a form submission exists
require_once(__DIR__ . '/session.php');
header('Content-Type: application/json');

// Initialize response
$response = ['success' => false, 'message' => ''];

// Get user ITS from session
$user_its = (int) $_SESSION[USER_ITS];

// Check if form_id is provided
if (!isset($_GET['form_id']) || empty($_GET['form_id'])) {
    $response['message'] = 'Missing form ID';
    echo json_encode($response);
    exit;
}

$form_id = intval($_GET['form_id']);

// Connect to database
// Using the existing connection from session.php if available
if (!isset($mysqli) || !$mysqli) {
    require_once(__DIR__ . '/config.php');
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        $response['message'] = 'Database connection failed';
        echo json_encode($response);
        exit;
    }
}

// Check recent submissions (last 5 minutes) for this user and form
$query = "SELECT id FROM `google_form_submissions` 
          WHERE `form_id` = $form_id AND `user_its` = $user_its 
          AND `submission_date` > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
          ORDER BY `submission_date` DESC LIMIT 1";

error_log("Check submission query: $query");
$result = mysqli_query($mysqli, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $response['success'] = true;
    $response['message'] = 'Submission found';
    $response['submission_id'] = $row['id'];
} else {
    // If not found, check if there's an unused token for this form/user
    $tokenQuery = "SELECT * FROM `form_submission_tokens` 
                  WHERE `form_id` = $form_id AND `user_its` = $user_its 
                  AND `used` = 0 
                  AND `created_at` > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                  ORDER BY `created_at` DESC LIMIT 1";
                  
    $tokenResult = mysqli_query($mysqli, $tokenQuery);
    
    if ($tokenResult && mysqli_num_rows($tokenResult) > 0) {
        $token = mysqli_fetch_assoc($tokenResult);
        $response['token_found'] = true;
        $response['token_id'] = $token['id'];
        $response['message'] = 'Form is currently being processed';
    } else {
        $response['message'] = 'No recent submission found';
    }
}

// Close database connection if we created one
if (!isset($_SESSION['mysqli'])) {
    $mysqli->close();
}

// Return response
echo json_encode($response);
?>