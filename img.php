<?php
session_start();
require_once __DIR__ . '/conn.php';

// Only serve images to authenticated sessions
if (empty($_SESSION[USER_LOGGED_IN]) || $_SESSION[USER_LOGGED_IN] !== true) {
    http_response_code(403);
    exit;
}

$its_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($its_id <= 0) {
    http_response_code(400);
    exit;
}

$url = "https://www.talabulilm.com/mumin_images/{$its_id}.png";
$ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
$imageData = @file_get_contents($url, false, $ctx);

if ($imageData === false || strlen($imageData) < 100) {
    header('Location: https://www.talabulilm.com/placeholder_image/placeholder.png');
    exit;
}

header('Content-Type: image/png');
header('Cache-Control: private, max-age=3600');
header('Content-Length: ' . strlen($imageData));
echo $imageData;
