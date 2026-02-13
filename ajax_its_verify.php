<?php
require_once __DIR__ . '/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$its_id = isset($_POST['its_id']) ? (int) $_POST['its_id'] : 0;

if ($its_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ITS ID']);
    exit;
}

$user_its_cookie = $_COOKIE['user_its'];
$ver             = $_COOKIE['ver'];

$api_url = "https://www.talabulilm.com/api2022/core/user/getUserDetailsByItsID/$its_id";
$auth    = base64_encode("$user_its_cookie:$ver");
$headers = ["Authorization: Basic $auth"];

$ch = curl_init();
curl_setopt($ch, CURLOPT_RESOLVE, ['www.talabulilm.com:443:66.85.132.227']);
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || ! $response) {
    echo json_encode(['success' => false, 'message' => 'Could not verify ITS ID']);
    exit;
}

$data = json_decode($response, true);

if (! $data || empty($data['full_name_en'])) {
    echo json_encode(['success' => false, 'message' => 'ITS ID not found']);
    exit;
}

echo json_encode([
    'success'      => true,
    'its_id'       => $its_id,
    'full_name'    => $data['full_name_en'],
    'full_name_ar' => $data['full_name_ar'] ?? '',
    'gender'       => $data['gender'] ?? '',
    'dob'          => $data['dob'] ?? '',
    'jamaat'       => $data['jamaat'] ?? '',
    'jamiat'       => $data['jamiat'] ?? '',
    'mobile'       => $data['mobile'] ?? '',
    'email'        => $data['email'] ?? '',
    'photo_url'    => "https://www.talabulilm.com/mumin_images/$its_id.png",
]);
