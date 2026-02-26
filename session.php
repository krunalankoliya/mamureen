<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/conn.php';

/**
 * Handle Session and Cookie Authentication
 */
if (!isset($_SESSION[USER_LOGGED_IN])) {
    if (isset($_COOKIE[USER_ITS])) {
        $cookieUserId = $_COOKIE[USER_ITS];
        $verNew = $_COOKIE['ver256'] ?? '';
        $verLegacy = $_COOKIE['ver'] ?? '';
        $cookieValid = false;

        // Modern SHA-256 Validation
        if ($verNew !== '' && COOKIE_SECRET !== '') {
            $expectedSha256 = hash_hmac('sha256', (string) $cookieUserId, COOKIE_SECRET);
            if (hash_equals($expectedSha256, $verNew)) {
                $cookieValid = true;
            }
        }

        // Legacy MD5 Validation
        if (!$cookieValid && $verLegacy !== '' && COOKIE_SECRET_LEGACY !== '') {
            $expectedMd5 = md5($cookieUserId . COOKIE_SECRET_LEGACY);
            if (hash_equals($expectedMd5, $verLegacy)) {
                $cookieValid = true;
            }
        }

        if ($cookieValid) {
            $_SESSION[USER_LOGGED_IN] = true;
            $_SESSION[USER_ID] = $_COOKIE[USER_ID];
            $_SESSION[USER_ITS] = $_COOKIE[USER_ITS];
        }
    }
}

// Redirect to OneLogin if not authenticated
if (!isset($_SESSION[USER_LOGGED_IN]) || $_SESSION[USER_LOGGED_IN] !== true) {
    $redirectUrl = "https://www.talabulilm.com/send_2_onelogin.php?r=" . base64_encode(MODULE_PATH);
    header("Location: $redirectUrl");
    exit();
}

$user_its = (int) $_SESSION[USER_ITS];

/**
 * Authorization Checks
 */

// 1. Admin Check
$stmt = $pdo->prepare("SELECT its_id FROM users_admin WHERE its_id = ?");
$stmt->execute([$user_its]);
$isAdminUser = $stmt->fetch();
$is_admin = (bool) $isAdminUser;

// 2. Sub Admin Check
$is_sub_admin = false;
try {
    $stmt = $pdo->prepare("SELECT its_id FROM users_sub_admin WHERE its_id = ?");
    $stmt->execute([$user_its]);
    $is_sub_admin = (bool) $stmt->fetch();
} catch (Exception $e) {
    // Table might not exist
}

/**
 * Validation: Ensure user exists in allowed tables
 */
if (!$is_admin) {
    $stmt = $pdo->prepare("
        SELECT 1 FROM (
            SELECT its_id FROM users_mamureen WHERE its_id = ?
            UNION
            SELECT its_id FROM users_admin WHERE its_id = ?
            UNION
            SELECT its_id FROM users_sub_admin WHERE its_id = ?
        ) AS combined LIMIT 1
    ");
    $stmt->execute([$user_its, $user_its, $user_its]);
    
    if (!$stmt->fetch()) {
        ?>
        <div style="text-align: center; padding: 50px; font-family: sans-serif;">
            <h1>Access Denied</h1>
            <p>ITS ID (<?php echo htmlspecialchars($user_its) ?>) is not authorized for this module.</p>
            <a href="https://www.its52.com/Login.aspx?OneLogin=MHB&tlbre=<?php echo base64_encode(MODULE_PATH) ?>" 
               style="padding: 10px 20px; background: #4154f1; color: white; text-decoration: none; border-radius: 5px;">
               Log in with ITS
            </a>
        </div>
        <?php
        exit();
    }
}
