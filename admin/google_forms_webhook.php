<?php
require_once(__DIR__. '/../session.php');
$current_page = 'Google_Forms';
require_once(__DIR__. '/../inc/header.php');
$user_its = (int) $_SESSION['USER_ITS'];


// Log incoming requests for debugging
function logRequest($data) {
    $logFile = __DIR__ . '/logs/form_submissions.log';
    $timestamp = date('Y-m-d H:i:s');
    $logData = $timestamp . " - " . json_encode($data) . PHP_EOL;
    file_put_contents($logFile, $logData, FILE_APPEND);
}

// Function to sanitize input data
function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
        return $data;
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Extract form ID from Google Form URL
function extractFormIdFromUrl($url) {
    if (preg_match('/\/forms\/d\/e\/([^\/]+)\//', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the raw request
    logRequest($_POST);
    
    // Get form ID from the request
    $googleFormId = isset($_POST['form_id']) ? sanitizeInput($_POST['form_id']) : null;
    
    if (!$googleFormId) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing form ID']);
        exit;
    }
    
    // Find the form in our database by matching the form_id with the ID in form_link
    $stmt = $conn->prepare("SELECT id FROM google_forms WHERE form_link LIKE ?");
    $searchPattern = '%' . $googleFormId . '%';
    $stmt->bind_param("s", $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Form not found in database']);
        exit;
    } else {
        $row = $result->fetch_assoc();
        $formId = $row['id'];
    }
    
    // Remove form_id and form_name from the data
    $formData = $_POST;
    unset($formData['form_id']);
    unset($formData['form_name']);
    
    // Sanitize all form data
    $formData = sanitizeInput($formData);
    
    // Convert to JSON
    $jsonData = json_encode($formData);
    
    // Store the submission
    $stmt = $conn->prepare("INSERT INTO form_submissions (form_id, user_its, submission_data) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $formId, $user_its, $jsonData);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Form data saved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save form data: ' . $conn->error]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}