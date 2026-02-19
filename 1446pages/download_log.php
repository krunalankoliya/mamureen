<?php
require_once(__DIR__ . '/session.php');

// Check if user is logged in
if (!isset($_SESSION[USER_ITS])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

// Get download ID from URL
$download_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($download_id <= 0) {
    // Invalid download ID
    header("Location: resources.php");
    exit;
}

// Get file information using your preferred query style
$query = "SELECT * FROM `my_downloads` WHERE `id` = $download_id";
$result = mysqli_query($mysqli, $query);
$download = $result->fetch_assoc();

if (!$download) {
    // File not found
    header("Location: resources.php");
    exit;
}

// Log the download
$its_id = (int)$_SESSION[USER_ITS];
$log_query = "INSERT INTO `download_log` (`its_id`, `download_id`, `download_date`) VALUES ($its_id, $download_id, NOW())";
mysqli_query($mysqli, $log_query);

// Redirect to the actual file
$file_path = MODULE_PATH . 'uploads/' . $download['file_name'];
header("Location: $file_path");
exit;
