<?php
/**
 * Modernized Google Forms Webhook
 * Refactored for Senior standards & SaaS UI architecture
 */
require_once __DIR__ . '/../session.php';

// Webhooks usually return JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

/**
 * Handle Webhook Submission
 */
try {
    $googleFormId = $_POST['form_id'] ?? null;
    if (!$googleFormId) {
        throw new Exception('Missing form ID');
    }

    // Find internal form ID using PDO
    $form = $db->fetch("SELECT id FROM google_forms WHERE form_link LIKE ?", ["%$googleFormId%"]);
    if (!$form) {
        throw new Exception('Form configuration not found');
    }

    // Prepare payload
    $payload = $_POST;
    unset($payload['form_id'], $payload['form_name']);

    // Insert record
    $db->insert('form_submissions', [
        'form_id' => $form['id'],
        'user_its' => $user_its,
        'submission_data' => json_encode($payload),
        'added_ts' => date('Y-m-d H:i:s')
    ]);

    echo json_encode(['success' => true, 'message' => 'Data synchronized']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
