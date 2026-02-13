<?php
require_once(__DIR__ . '/session.php');

// Set unlimited execution time for processing large uploads
set_time_limit(0);

// Create a response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Function to safely create directory if it doesn't exist
function ensureDirectoryExists($path) {
    if (!file_exists($path)) {
        if (!mkdir($path, 0755, true)) {
            throw new Exception("Failed to create directory: " . $path);
        }
    }
}

try {
    // Check if user is logged in
    if (!isset($user_its)) {
        throw new Exception("User not authenticated");
    }

    // Check if necessary data is provided
    if (!isset($_FILES['chunk']) || !isset($_POST['uploadId']) || !isset($_POST['chunkIndex']) || 
        !isset($_POST['totalChunks']) || !isset($_POST['fileName']) || !isset($_POST['fileSize'])) {
        throw new Exception("Missing required parameters");
    }

    // Get parameters
    $uploadId = $_POST['uploadId'];
    $chunkIndex = (int) $_POST['chunkIndex'];
    $totalChunks = (int) $_POST['totalChunks'];
    $fileName = $_POST['fileName'];
    $fileSize = (int) $_POST['fileSize'];
    $isFinalChunk = isset($_POST['finalChunk']) && $_POST['finalChunk'] === '1';

    // Define the temp directory for chunks
    $tempDir = __DIR__ . '/temp_chunks/' . $user_its . '_' . $uploadId;
    ensureDirectoryExists($tempDir);

    // Define the chunk file path
    $chunkPath = $tempDir . '/chunk_' . $chunkIndex;

    // Save the chunk
    if ($_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error uploading chunk: " . $_FILES['chunk']['error']);
    }

    if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkPath)) {
        throw new Exception("Failed to move uploaded chunk");
    }

    // Check if all chunks have been uploaded
    if ($isFinalChunk) {
        // Check if we have all the chunks
        $allChunksPresent = true;
        for ($i = 0; $i < $totalChunks; $i++) {
            if (!file_exists($tempDir . '/chunk_' . $i)) {
                $allChunksPresent = false;
                break;
            }
        }

        if ($allChunksPresent) {
            // Create the final file - extract file extension
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            
            // Verify it's a PDF
            if (strtolower($fileExt) !== 'pdf') {
                throw new Exception("Only PDF files are allowed");
            }

            // Generate final filename with user ITS ID
            $finalFileName = $user_its . '_closure_report_' . time() . '.' . $fileExt;
            $finalFilePath = __DIR__ . '/user_report_uploads/' . $finalFileName;
            ensureDirectoryExists(__DIR__ . '/user_report_uploads/');

            // Create the final file
            $finalFile = fopen($finalFilePath, 'wb');
            if (!$finalFile) {
                throw new Exception("Could not open file for writing");
            }

            // Combine chunks
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkContent = file_get_contents($tempDir . '/chunk_' . $i);
                if ($chunkContent === false) {
                    fclose($finalFile);
                    throw new Exception("Failed to read chunk " . $i);
                }
                
                if (fwrite($finalFile, $chunkContent) === false) {
                    fclose($finalFile);
                    throw new Exception("Failed to write chunk " . $i . " to final file");
                }
            }
            fclose($finalFile);

            // Verify file size as a basic integrity check
            if (filesize($finalFilePath) != $fileSize) {
                unlink($finalFilePath);
                throw new Exception("File size mismatch. Expected {$fileSize} bytes but got " . filesize($finalFilePath) . " bytes");
            }

            // Update database record
            // First check if user already has a report
            $checkQuery = "SELECT * FROM `closure_report` WHERE `its_id` = '$user_its'";
            $checkResult = mysqli_query($mysqli, $checkQuery);
            $existingReport = mysqli_fetch_assoc($checkResult);

            if ($existingReport) {
                // Update existing record
                $oldFile = $existingReport['upload_report'];
                $oldFilePath = __DIR__ . "/user_report_uploads/" . $oldFile;
                
                // Delete old file if it exists
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
                
                $query = "UPDATE `closure_report` SET `upload_report` = '$finalFileName' WHERE `its_id` = '$user_its'";
            } else {
                // Insert new record
                $query = "INSERT INTO `closure_report` (`its_id`, `upload_report`) VALUES ('$user_its', '$finalFileName')";
            }

            $result = mysqli_query($mysqli, $query);
            if (!$result) {
                unlink($finalFilePath);
                throw new Exception("Database error: " . mysqli_error($mysqli));
            }

            // Clean up temp chunks
            for ($i = 0; $i < $totalChunks; $i++) {
                if (file_exists($tempDir . '/chunk_' . $i)) {
                    unlink($tempDir . '/chunk_' . $i);
                }
            }
            rmdir($tempDir);
            
            // Set success response
            $response['success'] = true;
            $response['message'] = 'Upload completed successfully';
            $response['data'] = [
                'allComplete' => true,
                'fileName' => $finalFileName
            ];
        } else {
            // Not all chunks are present
            $response['success'] = true;
            $response['message'] = 'Chunk uploaded but not all chunks present yet';
            $response['data'] = [
                'chunkIndex' => $chunkIndex,
                'totalChunks' => $totalChunks,
                'allComplete' => false
            ];
        }
    } else {
        // Not the final chunk
        $response['success'] = true;
        $response['message'] = 'Chunk uploaded successfully';
        $response['data'] = [
            'chunkIndex' => $chunkIndex,
            'totalChunks' => $totalChunks,
            'allComplete' => false
        ];
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// Return the response
header('Content-Type: application/json');
echo json_encode($response);
exit;