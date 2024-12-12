<?php
require 'DBConn/libs/rb-mysql.php';
//связываемся с БД
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection()) {
    echo "err0";
    exit;
}

if (!isset($_POST['login']) || !isset($_POST['password'])) {
    echo "err4";
    exit;
}

$log = $_POST['login'];
$pas = $_POST['password'];

$log = stripslashes($log);
$log = htmlspecialchars($log);
$pas = stripslashes($pas);
$pas = htmlspecialchars($pas);
$log = trim($log);
$pas = trim($pas);

$user = R::findOne('users', 'login = ?', [$log]);
$is_admin = $user->isadmin;
$is_moderator = $user->ismoderator;

if (!isset($user)) {
    echo "err1";
    exit;
}

if (!password_verify($pas, $user->password) && $pas !== $user->password) {
    echo "err1";
    exit;
}

$role = 0;

if ($is_admin == 1) {
	$role = 1;
} elseif ($is_moderator == 1) {
	$role = 2;
}

if ($role == 0) {
	echo "err2";
	exit;
}

$token = dechex(time()).md5(uniqid($pas));
$session = R::dispense('sessions');
$session->userid = $user->id;
$session->token = password_hash($token, PASSWORD_DEFAULT);
$datetime = new DateTime();
$session->lastaccesstime = $datetime->getTimestamp();
R::store($session);

echo json_encode(array('user'=>$user->id, 'role'=>$role, 'token'=>$token));
exit;

?>