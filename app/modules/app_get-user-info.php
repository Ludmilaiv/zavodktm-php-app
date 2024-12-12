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

if (!isset($_POST['user_id']) || !isset($_POST['token'])) {
	echo "err1";
	exit;
}

$userId = $_POST['user_id'];
$token = $_POST['token'];

$user = R::findOne( 'users', 'id = ?', [$userId]);
if (!isset($user)) {
  echo "err1";
	exit;
}

$sessions = R::find('sessions', 'userid = ?', [$userId]);
if (empty($sessions)) {
	echo "err5";
	exit;
}
$auth = false;
$sessionID = 0;
$datetime = new DateTime();         // получаем дату и время в Unix-формате
foreach ($sessions as $session) {
	if (password_verify($token, $session->token)) {
		$auth = true;
		$sessionID = $session->id;
		break;
	}
}
if (!$auth) {
	echo "err5";
	exit;
}

$is_admin = $user->isadmin;
$is_moderator = $user->ismoderator;

$role = 0;
if ($is_admin == 1) {
	$role = 1;
} elseif ($is_moderator == 1) {
	$role = 2;
}

if ($role == 0) {
	echo "err6";
	exit;
}

$userData = array('login'=>$user->login, 'role'=>$role);

echo json_encode($userData);

?>