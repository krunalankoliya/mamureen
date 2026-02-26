<?php
/**
 * Configuration File
 * Centralizes all environment settings and constants.
 */

// Detect Base URL dynamically - Fixed for subdirectories
if (!defined('MODULE_PATH')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the script path and extract the project root directory
    $script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $parts = explode('/', trim($script_path, '/'));
    
    // We assume the project is in 'mamureen_new' or similar root-level folder
    // If we are in 'mamureen_new/admin', we want the path to be '/mamureen_new/'
    $root_dir = '';
    if (!empty($parts)) {
        // If the first part is our known root, use it. Otherwise, assume the first part is the root.
        if ($parts[0] === 'mamureen_new' || count($parts) == 1) {
            $root_dir = '/' . $parts[0] . '/';
        } else {
            // Logic for deeper nesting if needed, but for now we target the specific domain/folder
            if ($host === 'talabulilm.com' || $host === 'www.talabulilm.com') {
                $root_dir = '/mamureen_new/';
            } else {
                // Localhost or other: use the first directory as root
                $root_dir = '/' . $parts[0] . '/';
            }
        }
    } else {
        $root_dir = '/';
    }
    
    define('MODULE_PATH', $protocol . '://' . $host . $root_dir);
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'talabulilm_mamureen');
define('DB_USER', 'talabulilm_mamureen');
define('DB_PASS', 'M@mur352515');

define('CORE_DB_HOST', 'localhost');
define('CORE_DB_NAME', 'talabulilm_core');
define('CORE_DB_USER', 'talabulilm_core');
define('CORE_DB_PASS', 'core@515253');

// Security Keys
define('ITS_KEY', '1qaz2w24sx');
define('COOKIE_SECRET', 'adfefdddb86e4d5d2347e0dd5b560f0df37b2392ae19a05df4906e60158e7ebd');
define('COOKIE_SECRET_LEGACY', 'tlbilm@112345678+515253');

// Session Constants
define('USER_LOGGED_IN', 'user_logged_in');
define('USER_ID', 'user_id');
define('USER_ITS', 'user_its');
define('GROUP_ID', 'group_id');
define('MARHALA_ID', 'marhala_id');
define('IS_ADMIN', 'is_admin');
define('USER_ROLE', 'user_role');
define('TANZEEM_ID', 'tanzeem_id');
define('USER_TYPE', 'user_type');
define('JAMAT_NAME', 'jamat_name');
define('EDU_HUB', 'edu_hub');
define('IS_EDU_HUB', 'is_edu_hub');
define('SUCCESS_MESSAGE', 'success_message');
define('ERROR_MESSAGE', 'error_message');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting for Debugging (Disable in production if preferred)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
