<?php
require 'DBConn/libs/rb-mysql.php';
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection())
{
	echo "err0";
	exit;
}

// если скрипт запустили без данных, то выдаём ошибку
if (!isset($_POST["dev_id"]) || !isset($_POST["user_id"]) || !isset($_POST["token"])) {
	echo "err0";
	exit;
}

$session = R::findOne('sessions', 'userid = ?', [$_POST['user_id']]);
if (!isset($session) || password_verify($_POST["token"], $session->token)) {
    echo "err0";
    exit;
}

$dev_id = $_POST["dev_id"];

$user_dev  = R::findOne( 'usersdevices', 'my_device_id = ?', [$dev_id]);

$dev_name = "";
if (isset($user_dev)) 
{
	R::trash($user_dev);
  $dev_name = $user_dev["device_name"];
} else {
	//если устройство по id не найдено, то ошибка
	echo "err0";
	exit;
}

//проверяем успешность удаления

$user_dev  = R::findOne( 'usersdevices', 'my_device_id = ?', [$dev_id]);
if (isset($user_dev)) 
{
	echo "err0";
}
else echo "1";

?>