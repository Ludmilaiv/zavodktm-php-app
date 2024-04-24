<?php
require 'DBConn/libs/rb-mysql.php';
//связываемся с БД
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection())
//если не связались с бд, то ошибка
{
	echo "err0";
	exit;
}

// если скрипт запустили без данных, то выдаём ошибку
if (!isset($_POST["dev_id"])) {
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