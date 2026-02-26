<?php
/**
 * Modern Download Logger & Streamer
 */
require_once __DIR__ . '/session.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('resources.php');

// Fetch Resource
$res = $db->fetch("SELECT * FROM my_downloads WHERE id = ?", [$id]);
if (!$res) redirect('resources.php');

$path = __DIR__ . '/uploads/' . $res['file_name'];
if (!file_exists($path)) {
    die("Error: File not found on server.");
}

// Log Activity
$db->insert('download_log', [
    'its_id' => $user_its,
    'download_id' => $id,
    'download_date' => date('Y-m-d H:i:s')
]);

// Stream File
$mime = mime_content_type($path) ?: 'application/octet-stream';
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($res['file_name']) . '"');
header('Content-Length: ' . filesize($path));
header('Pragma: public');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

readfile($path);
exit;
