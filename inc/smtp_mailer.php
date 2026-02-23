<?php
// ============================================================
// SMTP Mailer — Gmail App Password, port 465 direct SSL
// ============================================================

// Credentials loaded from smtp_config.php (gitignored — never committed).
// Copy inc/smtp_config.example.php → inc/smtp_config.php to configure.
require_once __DIR__ . '/smtp_config.php';

// ------------------------------------------------------------
// sendSupportMail() — returns true on success, false on failure
// ------------------------------------------------------------
function sendSupportMail($to_email, $to_name, $subject, $html_body, &$error = '')
{
    if (empty(SUPPORT_SMTP_PASSWORD)) {
        $error = 'SMTP App Password not configured in inc/smtp_mailer.php';
        return false;
    }

    $host = SUPPORT_SMTP_HOST;
    $port = SUPPORT_SMTP_PORT;
    $user = SUPPORT_SMTP_USER;
    $pass = SUPPORT_SMTP_PASSWORD;
    $from = SUPPORT_FROM_NAME;

    // Direct SSL connection on port 465 (no STARTTLS needed)
    $ctx = stream_context_create(['ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ]]);

    $fp = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $ctx);
    if (! $fp) {
        $error = "Cannot connect to {$host}:{$port} — {$errstr} (errno {$errno})";
        return false;
    }
    stream_set_timeout($fp, 30);

    // Greeting
    $r = _smtpGet($fp);
    if (! _smtpOk($r, '220')) {$error = "Greeting: $r";
        fclose($fp);return false;}

    // EHLO
    _smtpPut($fp, 'EHLO localhost');
    _smtpMulti($fp);

    // AUTH LOGIN
    _smtpPut($fp, 'AUTH LOGIN');
    _smtpGet($fp); // 334 VXNlcm5hbWU6
    _smtpPut($fp, base64_encode($user));
    _smtpGet($fp); // 334 UGFzc3dvcmQ6
    _smtpPut($fp, base64_encode($pass));
    $r = _smtpGet($fp); // 235 or 535
    if (! _smtpOk($r, '235')) {$error = "Auth failed — check App Password: $r";
        fclose($fp);return false;}

    // MAIL FROM
    _smtpPut($fp, "MAIL FROM:<{$user}>");
    $r = _smtpGet($fp);
    if (! _smtpOk($r, '250')) {$error = "MAIL FROM: $r";
        fclose($fp);return false;}

    // RCPT TO
    _smtpPut($fp, "RCPT TO:<{$to_email}>");
    $r = _smtpGet($fp);
    if (! _smtpOk($r, '250')) {$error = "RCPT TO: $r";
        fclose($fp);return false;}

    // DATA
    _smtpPut($fp, 'DATA');
    $r = _smtpGet($fp);
    if (! _smtpOk($r, '354')) {$error = "DATA: $r";
        fclose($fp);return false;}

    $enc_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $body_b64    = chunk_split(base64_encode($html_body));

    $msg = "From: {$from} <{$user}>\r\n";
    $msg .= "To: {$to_name} <{$to_email}>\r\n";
    $msg .= "Date: " . date('r') . "\r\n";
    $msg .= "Subject: {$enc_subject}\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
    $msg .= "Content-Transfer-Encoding: base64\r\n";
    $msg .= "\r\n";
    $msg .= $body_b64;
    $msg .= "\r\n.\r\n";

    fwrite($fp, $msg);
    $r = _smtpGet($fp);

    _smtpPut($fp, 'QUIT');
    fclose($fp);

    if (! _smtpOk($r, '250')) {$error = "Message rejected: $r";return false;}
    return true;
}

// Internal helpers
function _smtpPut($fp, $cmd)
{fwrite($fp, $cmd . "\r\n");}
function _smtpGet($fp)
{return (string) fgets($fp, 512);}
function _smtpOk($line, $code)
{return strncmp(trim($line), $code, 3) === 0;}
function _smtpMulti($fp)
{while ($l = fgets($fp, 512)) {if (strlen($l) >= 4 && $l[3] === ' ') {
    break;
}
}}
