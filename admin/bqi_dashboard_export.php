<?php
ob_start();
require_once __DIR__ . '/../session.php';
ob_clean();

if (!$is_admin) {
    http_response_code(403);
    exit('Access denied.');
}

$type = $_GET['type'] ?? '';

$exports = [
    'zakereen_parties' => [
        'filename' => 'zakereen_parties',
        'query'    => "SELECT * FROM `bqi_zakereen_parties` WHERE `is_active` = 1 ORDER BY `id` ASC",
    ],
    'zakereen_farzando' => [
        'filename' => 'zakereen_farzando',
        'query'    => "SELECT * FROM `bqi_zakereen_farzando` ORDER BY `id` ASC",
    ],
    'zakereen_training' => [
        'filename' => 'zakereen_training_sessions',
        'query'    => "SELECT * FROM `bqi_training_sessions` WHERE `program_type` LIKE '%Zakereen Farzando Training%' ORDER BY `id` ASC",
    ],
    'social_media_baramij' => [
        'filename' => 'social_media_baramij',
        'query'    => "SELECT * FROM `bqi_training_sessions` WHERE `program_type` LIKE '%Social Media Awareness%' ORDER BY `id` ASC",
    ],
    'social_media_counseling' => [
        'filename' => 'social_media_one_on_one',
        'query'    => "SELECT * FROM `bqi_individual_tafheem` WHERE `type` LIKE '%Social Media Awareness%' ORDER BY `id` ASC",
    ],
    'shaadi_baramij' => [
        'filename' => 'shaadi_layak_baramij',
        'query'    => "SELECT * FROM `bqi_training_sessions` WHERE `program_type` LIKE '%Shaadi Tafheem%' ORDER BY `id` ASC",
    ],
    'shaadi_tafheem' => [
        'filename' => 'shaadi_individual_tafheem',
        'query'    => "SELECT * FROM `bqi_individual_tafheem` WHERE `type` LIKE '%Shaadi Tafheem%' ORDER BY `id` ASC",
    ],
    'challenges' => [
        'filename' => 'challenges_solutions',
        'query'    => "SELECT * FROM `bqi_challenges_solutions` ORDER BY `id` ASC",
    ],
    'noteworthy' => [
        'filename' => 'noteworthy_experiences',
        'query'    => "SELECT * FROM `bqi_noteworthy_experiences` ORDER BY `id` ASC",
    ],
    'khidmat' => [
        'filename' => 'khidmat_preparations',
        'query'    => "SELECT * FROM `bqi_khidmat_preparations` ORDER BY `id` ASC",
    ],
];

if (!isset($exports[$type])) {
    http_response_code(400);
    exit('Invalid export type.');
}

$export   = $exports[$type];
$result   = mysqli_query($mysqli, $export['query']);
$rows     = $result->fetch_all(MYSQLI_ASSOC);
$filename = $export['filename'] . '_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');

$out = fopen('php://output', 'w');

if (!empty($rows)) {
    // Write header row
    fputcsv($out, array_keys($rows[0]));
    // Write data rows
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
} else {
    fputcsv($out, ['No data found']);
}

fclose($out);
exit;
