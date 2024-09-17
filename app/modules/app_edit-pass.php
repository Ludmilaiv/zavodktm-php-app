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
$recovery = R::findOne( 'recovery', 'userid = ?', [$id]);

if (isset($user) && isset($recovery) && password_verify($hash, $recovery->hash))
{
    $user->password = $new_pass;
    // сбрасываем хеш, чтобы нельзя было использовать ссылку второй раз
    $recovery->hash = null;
	R::store($user);
    R::store($recovery);
} else {
	echo "err0";
	exit;
}

//проверяем успешность

$recovery = R::findOne( 'recovery', 'userid = ?', [$id]);
if ($recovery->hash)
{
	echo "err0";
}
else echo "1";

?>