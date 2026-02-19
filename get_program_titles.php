<?php
ob_start();
require_once(__DIR__ . '/session.php');
ob_clean(); // discard any output session.php may have produced

header('Content-Type: application/json');

$program_type = trim(mysqli_real_escape_string($mysqli, $_GET['program_type'] ?? ''));

if (empty($program_type)) {
    echo json_encode([]);
    exit;
}

$result = mysqli_query($mysqli,
    "SELECT `id`, `title_name` FROM `bqi_program_titles`
     WHERE `is_active` = 1 AND `program_type` = '$program_type'
     ORDER BY `sort_order`, `title_name`"
);

if (!$result) {
    echo json_encode([]);
    exit;
}

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
