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

$new_pass = $_POST["password"];
$hash = $_POST["hash"];

$user  = R::findOne( 'users', 'hash = ?', [$hash]);

if (isset($user)) 
{
    $user->password = $new_pass;
    // сбрасываем хеш, чтобы нельзя было использовать ссылку второй раз
    $user->hash = null;
	R::store($user);
} else {
	echo "err0";
	exit;
}

//проверяем успешность

$user = R::findOne( 'users', 'hash = ?', [$hash]);
if (isset($user)) 
{
	echo "err0";
}
else echo "1";

?>