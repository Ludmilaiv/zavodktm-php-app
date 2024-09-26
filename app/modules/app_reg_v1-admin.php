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

if (!isset($user)) {
    echo "err1";
    exit;
}

if (!password_verify($pas, $user->password) && $pas !== $user->password) {
    echo "err1";
    exit;
}

if ($is_admin == 0) {
	echo "err2";
	exit;
}

$token = dechex(time()).md5(uniqid($pas));
$session = R::dispense('sessions');
$session->userid = $user->id;
$session->token = password_hash($token, PASSWORD_DEFAULT);
R::store($session);

echo json_encode(array('user'=>$user->id, 'token'=>$token));
exit;

?>