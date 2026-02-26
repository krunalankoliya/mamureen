<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/Database.php';
require_once __DIR__ . '/inc/ApiService.php';

/**
 * Global Database Instance (Modern OOP)
 */
$db = Database::getInstance();

// Expose underlying PDO object for legacy code
$pdo = $db->pdo;

/**
 * Legacy MySQLi Connection (For backward compatibility during migration)
 */
$mysqli = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
$mysqli->set_charset("utf8mb4");

/**
 * Core Database Handle
 */
$core_dsn = "mysql:host=" . CORE_DB_HOST . ";dbname=" . CORE_DB_NAME . ";charset=utf8mb4";
try {
    $core_pdo = new PDO($core_dsn, CORE_DB_USER, CORE_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    // Graceful fail for core DB
    $core_pdo = null;
}

$coremysqli = mysqli_connect(CORE_DB_HOST, CORE_DB_USER, CORE_DB_PASS, CORE_DB_NAME);

/**
 * Global Helper Functions
 */

if (!function_exists('user_photo_url')) {
    function user_photo_url($its_id) {
        return MODULE_PATH . 'img.php?id=' . (int) $its_id;
    }
}

if (!function_exists('redirect')) {
    function redirect($path) {
        header("Location: " . MODULE_PATH . ltrim($path, '/'));
        exit;
    }
}

if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}
