<?php
ob_start();
require_once(__DIR__ . '/session.php');
ob_clean();

header('Content-Type: application/json');

$type = trim(mysqli_real_escape_string($mysqli, $_GET['type'] ?? ''));

if (empty($type)) {
    echo json_encode([]);
    exit;
}

$result = mysqli_query($mysqli,
    "SELECT `id`, `reason_name` FROM `bqi_tafheem_reasons`
     WHERE `is_active` = 1 AND `type` = '$type'
     ORDER BY `sort_order`, `reason_name`"
);

echo json_encode($result ? $result->fetch_all(MYSQLI_ASSOC) : []);
