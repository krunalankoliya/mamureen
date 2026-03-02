<?php
// Quick SMTP test — DELETE THIS FILE after testing
require_once __DIR__ . '/session.php';
if (!$is_admin) { die('Admin only'); }

require_once __DIR__ . '/inc/smtp_mailer.php';

$test_to = $_GET['email'] ?? '';
if (empty($test_to)) {
    echo '<form>Send test email to: <input name="email" placeholder="target@example.com" style="width:260px"> <button>Send</button></form>';
    exit;
}

$error = '';
$ok = sendSupportMail(
    $test_to,
    'Test Recipient',
    'SMTP Test — Mamureen',
    '<h2>Test email from Mamureen Support</h2><p>If you received this, SMTP is working correctly.</p>',
    $error
);

echo $ok
    ? '<p style="color:green;font-size:18px">✔ Email sent successfully to ' . htmlspecialchars($test_to) . '</p>'
    : '<p style="color:red;font-size:18px">✘ Failed: ' . htmlspecialchars($error) . '</p>';
