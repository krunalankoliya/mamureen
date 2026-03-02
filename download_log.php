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

if (! $download) {
    header('Location: ' . MODULE_PATH . 'resources.php');
    exit;
}

$file_path = __DIR__ . '/uploads/' . $download['file_name'];

if (! file_exists($file_path)) {
    header('Location: ' . MODULE_PATH . 'resources.php');
    exit;
}

// Log the download
$its_id = (int) $_SESSION[USER_ITS];
mysqli_query($mysqli, "INSERT INTO `download_log` (`its_id`, `download_id`, `download_date`) VALUES ($its_id, $download_id, NOW())");

// Stream file as forced download
$mime = mime_content_type($file_path) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($download['file_name']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
readfile($file_path);
exit;
