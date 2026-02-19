<?php
// form_webhook.php - Receive Google Form submissions via callback URL
require_once(__DIR__ . '/session.php');

// Enable detailed error logging
ini_set('display_errors', 1);
error_log('Form webhook accessed: ' . date('Y-m-d H:i:s'));
error_log('GET params: ' . print_r($_GET, true));

// We'll respond based on the request type
if (isset($_GET['token']) && !empty($_GET['token'])) {
    // This is a callback from a pre-filled form
    handleCallbackSubmission($_GET['token']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This is a direct POST request with JSON data
    handlePostSubmission();
} else {
    // Invalid request
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

/**
 * Handle Google Form callback via URL parameter
 */
function handleCallbackSubmission($token) {
    global $mysqli;
    
    // Log the token
    error_log('Processing submission with token: ' . $token);
    
    // Validate token and get associated form_id and user_its
    $token = mysqli_real_escape_string($mysqli, $token);
    $query = "SELECT * FROM `form_submission_tokens` WHERE `token` = '$token' AND `used` = 0 LIMIT 1";
    error_log("Token query: $query");
    $result = mysqli_query($mysqli, $query);
    
    if (!$result || mysqli_num_rows($result) === 0) {
        error_log('Invalid or already used token: ' . $token);
        displayCallbackPage('Error: Invalid or expired submission token', false);
        return;
    }
    
    // Get the token data
    $tokenData = mysqli_fetch_assoc($result);
    $form_id = (int)$tokenData['form_id'];
    $user_its = (int)$tokenData['user_its'];
    
    error_log("Found token data - form_id: $form_id, user_its: $user_its");
    
    // Mark token as used
    $updateQuery = "UPDATE `form_submission_tokens` SET `used` = 1, `used_at` = NOW() WHERE `token` = '$token'";
    mysqli_query($mysqli, $updateQuery);
    
    // Capture form response data 
    $submission_data = [
        'submission_time' => date('Y-m-d H:i:s'),
        'submission_method' => 'callback',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        // Capture any additional GET parameters that might contain form data
        'form_params' => $_GET
    ];
    
    // Convert to JSON and escape for database
    $form_data_json = mysqli_real_escape_string($mysqli, json_encode($submission_data));
    
    // Insert submission into database with explicit column names
    $insertQuery = "INSERT INTO `google_form_submissions` 
                   (`form_id`, `user_its`, `submission_data`, `submission_date`) 
                   VALUES ($form_id, $user_its, '$form_data_json', NOW())";
    
    error_log("Insert query: $insertQuery");
    
    if (mysqli_query($mysqli, $insertQuery)) {
        error_log('Submission saved successfully for form: ' . $form_id . ', user: ' . $user_its);
        displayCallbackPage('Your form has been submitted successfully!', true);
    } else {
        error_log('Error saving submission: ' . mysqli_error($mysqli));
        displayCallbackPage('There was an error processing your submission: ' . mysqli_error($mysqli), false);
    }
}

/**
 * Handle direct POST submissions with JSON data
 */
function handlePostSubmission() {
    global $mysqli;
    
    header('Content-Type: application/json');
    
    // Initialize response
    $response = ['success' => false, 'message' => ''];
    
    // Get the POST data
    $post_data = file_get_contents('php://input');
    error_log('Received POST data: ' . $post_data);
    
    if (empty($post_data)) {
        $response['message'] = 'No data received';
        error_log('No data received in POST request');
        echo json_encode($response);
        exit;
    }
    
    // Decode JSON data
    $submission_data = json_decode($post_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Invalid JSON data: ' . json_last_error_msg();
        error_log('JSON decode error: ' . json_last_error_msg());
        echo json_encode($response);
        exit;
    }
    
    // Verify required fields
    if (!isset($submission_data['form_id']) || !isset($submission_data['user_its'])) {
        $response['message'] = 'Missing required fields';
        error_log('Missing required fields in submission data');
        echo json_encode($response);
        exit;
    }
    
    // Extract data
    $form_id = intval($submission_data['form_id']);
    $user_its = intval($submission_data['user_its']);
    $form_data = isset($submission_data['form_data']) ? $submission_data['form_data'] : [];
    $form_data_json = mysqli_real_escape_string($mysqli, json_encode($form_data));
    
    // Insert data into database
    $query = "INSERT INTO `google_form_submissions` 
              (`form_id`, `user_its`, `submission_data`, `submission_date`) 
              VALUES ($form_id, $user_its, '$form_data_json', NOW())";
    
    error_log("POST Insert query: $query");
    
    if (mysqli_query($mysqli, $query)) {
        $response['success'] = true;
        $response['message'] = 'Submission saved successfully';
        $response['submission_id'] = mysqli_insert_id($mysqli);
        error_log('Submission saved successfully via POST for form: ' . $form_id . ', user: ' . $user_its);
    } else {
        $response['message'] = 'Error saving submission: ' . mysqli_error($mysqli);
        error_log('Database error: ' . mysqli_error($mysqli));
    }
    
    // Return response
    echo json_encode($response);
}

/**
 * Display a simple callback page for Google Forms redirects
 */
function displayCallbackPage($message, $success) {
    // Send an HTML response that will be shown after form submission
    header('Content-Type: text/html');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Form Submission</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                text-align: center;
            }
            .message {
                margin: 20px auto;
                padding: 15px;
                border-radius: 5px;
                max-width: 600px;
            }
            .success {
                background-color: #dff0d8;
                color: #3c763d;
                border: 1px solid #d6e9c6;
            }
            .error {
                background-color: #f2dede;
                color: #a94442;
                border: 1px solid #ebccd1;
            }
            .btn {
                display: inline-block;
                margin: 10px 0;
                padding: 8px 16px;
                background-color: #337ab7;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class="message <?= $success ? 'success' : 'error' ?>">
            <h3><?= htmlspecialchars($message) ?></h3>
        </div>
        <a href="javascript:window.close();" class="btn">Close Window</a>
        <script>
            // Notify parent window that form submission is complete
            if (window.parent) {
                window.parent.postMessage({
                    formSubmitted: true,
                    success: <?= $success ? 'true' : 'false' ?>,
                    message: <?= json_encode($message) ?>
                }, '*');
            }
            
            // Auto-close this window/tab after 5 seconds
            setTimeout(function() {
                window.close();
            }, 5000);
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>