<?php
require 'DBConn/libs/rb-mysql.php';
//связываемся с БД
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection())
{
  echo "err0";
	exit;
}

$userId = $_POST['user_id'];
$user = R::findOne( 'users', 'id = ?', [$userId]);
if (!isset($user)) {
  echo "err1";
	exit;
}

$confirm = !($user -> not_confirm);

echo $confirm;

?>