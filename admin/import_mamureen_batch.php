<?php
ob_start();
require_once __DIR__ . '/../session.php';
ob_clean();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ── Step 1: Upload & validate CSV, TRUNCATE table, save temp file ──────────
if ($action === 'upload') {

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
        exit;
    }

    // MIME check
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($_FILES['csv_file']['tmp_name']);
    if (!in_array($mimeType, ['text/plain', 'text/csv', 'application/csv', 'application/octet-stream'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid file format. Please upload a CSV file.']);
        exit;
    }

    // Header validation
    $expectedHeaders = [
        'its_id', 'fullname', 'fullname_ar', 'avs_idara', 'email_id', 'mobile',
        'mauze_type_name', 'khidmat_type_name', 'miqaat_name', 'kg_age',
        'miqaat_mauze', 'zone', 'miqaat_india_foreign', 'miqaat_jamiat',
        'miqaat_ilaqa', 'farmaan_seen', 'jamea_degree', 'jamea_std', 'is_musaid'
    ];

    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
    $header = fgetcsv($handle);
    fclose($handle);

    if (!$header || count($header) < count($expectedHeaders)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSV header. Please download the sample CSV for reference.']);
        exit;
    }

    $headerLower = array_map('strtolower', array_map('trim', $header));
    foreach ($expectedHeaders as $eh) {
        if (!in_array(strtolower($eh), $headerLower)) {
            echo json_encode(['success' => false, 'message' => "Missing required column: $eh"]);
            exit;
        }
    }

    // Count data rows
    $handle   = fopen($_FILES['csv_file']['tmp_name'], 'r');
    fgetcsv($handle); // skip header
    $totalRows = 0;
    while (fgetcsv($handle) !== false) {
        $totalRows++;
    }
    fclose($handle);

    if ($totalRows === 0) {
        echo json_encode(['success' => false, 'message' => 'No data rows found in the CSV file.']);
        exit;
    }

    // Save temp file
    $key      = preg_replace('/[^a-zA-Z0-9_]/', '', uniqid('mam_', true));
    $tempDir  = sys_get_temp_dir();
    $tempPath = $tempDir . DIRECTORY_SEPARATOR . $key . '.csv';
    move_uploaded_file($_FILES['csv_file']['tmp_name'], $tempPath);

    // Truncate table before starting
    mysqli_query($mysqli, 'TRUNCATE TABLE users_mamureen');

    echo json_encode(['success' => true, 'key' => $key, 'total' => $totalRows]);
    exit;
}

// ── Step 2: Process one batch of 10 rows ──────────────────────────────────
if ($action === 'batch') {

    $key      = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['key'] ?? '');
    $offset   = max(0, (int)($_POST['offset'] ?? 0));
    $batchSize = 10;

    $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $key . '.csv';

    if (empty($key) || !file_exists($tempPath)) {
        echo json_encode(['success' => false, 'message' => 'Session expired or file not found. Please re-upload.']);
        exit;
    }

    $handle = fopen($tempPath, 'r');
    fgetcsv($handle); // skip header row

    // Skip to current offset
    for ($i = 0; $i < $offset; $i++) {
        if (fgetcsv($handle) === false) {
            fclose($handle);
            unlink($tempPath);
            echo json_encode(['success' => true, 'inserted' => 0, 'errors' => [], 'offset' => $offset, 'done' => true]);
            exit;
        }
    }

    $stmt = $mysqli->prepare(
        'INSERT INTO users_mamureen (
            its_id, fullname, fullname_ar, avs_idara, email_id, mobile,
            mauze_type_name, khidmat_type_name, miqaat_name, kg_age,
            miqaat_mauze, zone, miqaat_india_foreign, miqaat_jamiat,
            miqaat_ilaqa, farmaan_seen, jamea_degree, jamea_std, is_musaid
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $inserted    = 0;
    $errors      = [];
    $processed   = 0;
    $lineNumber  = $offset + 2; // 1-indexed + header
    $eof         = false;

    while ($processed < $batchSize) {
        $data = fgetcsv($handle);

        if ($data === false) {
            $eof = true;
            break;
        }

        if (count($data) < 19) {
            $errors[] = "Line $lineNumber: incomplete data (expected 19 columns).";
            $processed++;
            $lineNumber++;
            continue;
        }

        if (empty(trim($data[0]))) {
            $errors[] = "Line $lineNumber: empty ITS ID — row skipped.";
            $processed++;
            $lineNumber++;
            continue;
        }

        $stmt->bind_param(
            'sssssssssssssssssss',
            $data[0], $data[1], $data[2], $data[3], $data[4],
            $data[5], $data[6], $data[7], $data[8], $data[9],
            $data[10], $data[11], $data[12], $data[13], $data[14],
            $data[15], $data[16], $data[17], $data[18]
        );

        if ($stmt->execute()) {
            $inserted++;
        } else {
            $errors[] = "Line $lineNumber: " . $stmt->error;
        }

        $processed++;
        $lineNumber++;
    }

    fclose($handle);

    $done      = $eof || ($processed < $batchSize);
    $newOffset = $offset + $processed;

    if ($done) {
        unlink($tempPath); // clean up temp file
    }

    echo json_encode([
        'success'  => true,
        'inserted' => $inserted,
        'errors'   => $errors,
        'offset'   => $newOffset,
        'done'     => $done,
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
