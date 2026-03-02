<?php
$host = 'localhost';
$db = 'talabulilm_mamureen';
$user = 'talabulilm_mamureen';
$password = 'M@mur352515';

$mysqli = mysqli_connect($host, $user, $password, $db);

// Check connection
if ($mysqli === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
$mysqli -> set_charset("utf8");

$corehost = 'localhost';
$coredb = 'talabulilm_core';
$coreuser = 'talabulilm_core';
$corepassword = 'core@515253';

$coremysqli = mysqli_connect($corehost, $coreuser, $corepassword, $coredb);

// Check connection
if ($coremysqli === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
$coremysqli -> set_charset("utf8");

define('ITS_KEY', '1qaz2w24sx');

define('USER_LOGGED_IN', 'user_logged_in');
define('USER_ID', 'user_id');
define('USER_ITS', 'user_its');
define('GROUP_ID', 'group_id');
define('ENABLE_WRITE_LOG', FALSE);
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
define('MODULE_PATH', 'https://www.talabulilm.com/mamureen/');
