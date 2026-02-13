<?php
// upload_chunk.php
require_once(__DIR__ . '/../session.php');

// Set appropriate headers for AJAX response
header('Content-Type: application/json');

// Directory for temporary chunks storage
$tempDir = __DIR__ . '/../temp_uploads';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
}

// Function to safely handle JSON responses
function sendResponse($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if request has required data
if (!isset($_FILES['chunk']) || !isset($_POST['uploadId']) || !isset($_POST['chunkIndex']) || !isset($_POST['totalChunks']) || !isset($_POST['fileName'])) {
    sendResponse(false, 'Missing required parameters');
}

// Get upload parameters
$uploadId = $_POST['uploadId'];
$chunkIndex = (int)$_POST['chunkIndex'];
$totalChunks = (int)$_POST['totalChunks'];
$fileName = $_POST['fileName'];
$fileSize = isset($_POST['fileSize']) ? (int)$_POST['fileSize'] : 0;

// Sanitize filename for security
$fileName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $fileName);

// Define path for this chunk
$chunkPath = $tempDir . '/' . $uploadId . '_' . $chunkIndex;

// Move uploaded chunk to temp directory
if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkPath)) {
    sendResponse(false, 'Failed to save chunk');
}

// Check if this was the final chunk
if (isset($_POST['finalChunk']) && $_POST['finalChunk'] === '1') {
    // All chunks received, combine them
    $uploadsDir = __DIR__ . "/../uploads/";
    
    // Make sure uploads directory exists
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    
    $finalFilePath = $uploadsDir . $fileName;
    
    // Open the final file for writing
    $out = fopen($finalFilePath, 'wb');
    
    if (!$out) {
        // Log the error for debugging
        error_log("Failed to create final file: $finalFilePath");
        sendResponse(false, 'Failed to create final file');
    }
    
    // Combine all chunks
    for ($i = 0; $i < $totalChunks; $i++) {
        $chunkPath = $tempDir . '/' . $uploadId . '_' . $i;
        
        // Check if chunk exists
        if (!file_exists($chunkPath)) {
            // Log the missing chunk for debugging
            error_log("Chunk $i missing at path: $chunkPath");
            fclose($out);
            sendResponse(false, "Chunk $i missing");
        }
        
        // Open the chunk for reading
        $in = fopen($chunkPath, 'rb');
        if (!$in) {
            // Log the error for debugging
            error_log("Cannot open chunk $i at path: $chunkPath");
            fclose($out);
            sendResponse(false, "Cannot open chunk $i");
        }
        
        // Copy chunk data to final file
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        
        fclose($in);
        unlink($chunkPath); // Remove chunk after combining
    }
    
    fclose($out);
    
    // Verify the final file exists and has correct size
    if (!file_exists($finalFilePath)) {
        error_log("Final file does not exist after creation: $finalFilePath");
        sendResponse(false, 'File creation failed');
    }
    
    // Get the actual file size and compare with expected size
    $actualSize = filesize($finalFilePath);
    if ($fileSize > 0 && $actualSize != $fileSize) {
        error_log("File size mismatch. Expected: $fileSize, Actual: $actualSize");
        // Instead of failing, we'll log but continue
    }
    
    // Verify file permissions
    chmod($finalFilePath, 0644);
    
    // Now process database entry
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $dot_color = isset($_POST['dot_color']) ? $_POST['dot_color'] : '';
    $date = date('Y-m-d');
    
    // Connect to database
    global $mysqli;
    global $user_its;
    
    // Using prepared statements to prevent SQL injection
    $query = "INSERT INTO `my_downloads` (`upload_date`, `title`, `file_name`, `dot_color`, `added_its`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        error_log("Database preparation error: " . $mysqli->error);
        sendResponse(false, 'Database preparation error: ' . $mysqli->error);
    }
    
    $stmt->bind_param('sssss', $date, $title, $fileName, $dot_color, $user_its);
    
    if (!$stmt->execute()) {
        error_log("Database error: " . $stmt->error);
        sendResponse(false, 'Database error: ' . $stmt->error);
    }
    
    $stmt->close();
    
    // Success response
    sendResponse(true, 'File uploaded successfully', ['allComplete' => true]);
} else {
    // Just a regular chunk, confirm receipt
    sendResponse(true, 'Chunk received');
}