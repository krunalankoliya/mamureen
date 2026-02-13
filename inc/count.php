<?php
require_once(__DIR__ . '/../session.php');

$query = "SELECT its_id FROM users_mamureen WHERE its_id = $user_its UNION SELECT its_id FROM users_admin WHERE its_id = $user_its";
$result = mysqli_query($mysqli, $query);
$rowdata = $result->fetch_assoc();

function getCounts($table, $where, $mysqli)
{
  $query = "SELECT COUNT(*) `count` FROM `$table` $where";
  $result = mysqli_query($mysqli, $query);
  $res = $result->fetch_assoc();

  return ($res && $res['count']) ? (int) $res['count'] : 0;
}
?>
