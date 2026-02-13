<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require_once('conn.php');

// if user is not logged in then check cookie, if found then log him in
if (!isset($_SESSION[USER_LOGGED_IN])) {
  if (isset($_COOKIE[USER_ITS])) {
    // verify the password
    $ver_code = md5($_COOKIE[USER_ITS] . 'tlbilm@112345678+515253');
    if ($_COOKIE['ver'] == $ver_code) {
      $_SESSION[USER_LOGGED_IN] = TRUE;
      $_SESSION[USER_ID] = $_COOKIE[USER_ID];
      $_SESSION[USER_ITS] = $_COOKIE[USER_ITS];
    }
  }
}

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== TRUE) {
    header('location:https://www.talabulilm.com/send_2_onelogin.php?r=' . base64_encode(MODULE_PATH));
    exit();
}

$user_its = (int) $_SESSION[USER_ITS];

$only_admin_ITS = array('30305142', '30474444', '60412444', '78652093', '50474277', '30325584', '78652354', '60406245', '30372088', '40473195', '30320852', '30326300', '30325825',
                '30375555', '30310749', '30310055', '78652046', );
// $admin_its = array('50476733', '40444857', '20372359', '30419712');
$query = "SELECT its_id FROM users_admin";
$result=mysqli_query($mysqli,$query);
$adminData = $result->fetch_all(MYSQLI_ASSOC);
$admin_its = array_column($adminData, 'its_id');

if(in_array($user_its, $admin_its)) {
  $is_admin =true;
} else{
  $is_admin =false;

  // Check if its exsists in user table, or else show mnessage
  $query = "SELECT 'Exists' AS Result
FROM (
    SELECT `its_id` FROM `users_mamureen` WHERE `its_id` = '$user_its'
    UNION
    SELECT `its_id` FROM `users_admin` WHERE `its_id` = '$user_its'
) AS combined
LIMIT 1;";
  $result=mysqli_query($mysqli,$query);
  $row = $result->fetch_all(MYSQLI_ASSOC);

  if(empty($row)){
  ?>
    <h1>Sorry ITS ID (<?=$user_its?>) is not valid for this module.</h1>
    <h1>Click here to login with your ITS <a href="<?='https://www.talabulilm.com/send_2_onelogin.php?r=' . 'base64_encode(MODULE_PATH)'?>">LOG IN</a></h1>
  <?php
    exit();
  }
}