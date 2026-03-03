<?php
ob_start();
require_once __DIR__ . '/session.php';
ob_clean();

$download_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($download_id <= 0) {
    header('Location: ' . MODULE_PATH . 'resources.php');
    exit;
}

$result   = mysqli_query($mysqli, "SELECT * FROM `my_downloads` WHERE `id` = $download_id");
$download = $result ? $result->fetch_assoc() : null;

if (!$download) {
    header('Location: ' . MODULE_PATH . 'resources.php');
    exit;
}

$file_path = __DIR__ . '/uploads/' . $download['file_name'];

if (!file_exists($file_path)) {
    header('Location: ' . MODULE_PATH . 'resources.php');
    exit;
}

// Stream inline so the browser opens/displays it instead of downloading
$mime = mime_content_type($file_path) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($download['file_name']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache');
readfile($file_path);
exit;
