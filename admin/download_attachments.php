<?php
require_once __DIR__ . '/../session.php';

// Get parameters
$record_id = isset($_GET['record_id']) ? (int) $_GET['record_id'] : 0;
$module    = isset($_GET['module']) ? $_GET['module'] : '';

if (! $record_id || ! $module) {
    http_response_code(400);
    exit('Invalid parameters');
}

// Validate module name (prevent directory traversal)
if (! in_array($module, ['maaraz', 'training_sessions', 'closure_report', 'challenges', 'noteworthy_experiences', 'important_contacts'])) {
    http_response_code(403);
    exit('Invalid module');
}

// Fetch attachments for this record
$query = "SELECT * FROM `bqi_file_attachments`
          WHERE module = '" . $mysqli->real_escape_string($module) . "'
          AND record_id = " . $record_id;
$result = $mysqli->query($query);

if (! $result || $result->num_rows === 0) {
    http_response_code(404);
    exit('No attachments found');
}

$files     = [];
$base_path = __DIR__ . '/../user_uploads/';

while ($row = $result->fetch_assoc()) {
    $file_path = $base_path . $row['file_path'];
    if (file_exists($file_path)) {
        $files[] = [
            'path' => $file_path,
            'name' => $row['file_name'],
        ];
    }
}

if (empty($files)) {
    http_response_code(404);
    exit('No valid files found');
}

// If only one file, download directly
if (count($files) === 1) {
    $file = $files[0];
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file['name']) . '"');
    header('Content-Length: ' . filesize($file['path']));
    readfile($file['path']);
    exit;
}

// Multiple files - create zip
$zip      = new ZipArchive();
$zip_file = sys_get_temp_dir() . '/' . $module . '_' . $record_id . '_' . time() . '.zip';

if ($zip->open($zip_file, ZipArchive::CREATE) !== true) {
    http_response_code(500);
    exit('Could not create zip file');
}

foreach ($files as $file) {
    $zip->addFile($file['path'], basename($file['name']));
}
$zip->close();

// Download zip
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $module . '_' . $record_id . '.zip"');
header('Content-Length: ' . filesize($zip_file));
readfile($zip_file);

// Clean up
unlink($zip_file);
exit;
