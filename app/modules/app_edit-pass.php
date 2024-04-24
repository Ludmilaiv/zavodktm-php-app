<?php
require 'DBConn/libs/rb-mysql.php';
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection())
{
	echo "err0";
	exit;
}

if (!isset($_POST["password"]) || !isset($_POST["hash"])) {
	echo "err0";
	exit;
}

$new_pass = password_hash($_POST["password"], PASSWORD_DEFAULT);
$hash = $_POST["hash"];
$id = $_POST["id"];

$user  = R::findOne( 'users', 'id = ?', [$id]);
$session = R::findOne( 'sessions', 'userid = ?', [$id]);

if (isset($user) && isset($session) && password_verify($hash, $session->hash))
{
    $user->password = $new_pass;
    // сбрасываем хеш, чтобы нельзя было использовать ссылку второй раз
    $session->hash = null;
	R::store($user);
    R::store($session);
} else {
	echo "err0";
	exit;
}

//проверяем успешность

$session = R::findOne( 'sessions', 'userid = ?', [$id]);
if ($session->hash)
{
	echo "err0";
}
else echo "1";

?>