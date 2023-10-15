<?php
require 'DBConn/libs/rb-mysql.php';
//связываемся с БД
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection())
//если не связались с бд, то выводим в сессию ошибку и перезагружаем страничку
{
	echo "err0";
	exit;
}

// если скрипт запустили без данных, то выдаём ошибку
if (!isset($_POST["dev_id"]) or !isset($_POST["dev_name"]) or !isset($_POST["user_id"])) {
	echo "err0";
	exit;
}

$dev_id = $_POST["dev_id"];
$dev_name = $_POST["dev_name"];


//проверяем существует ли такое устройство
$dev  = R::findOne( 'devices', 'my_id = ?', [$dev_id]);
if (!isset($dev)) 
{
	echo "err1";
	exit;
}

$user = R::load('users', $_POST['user_id']);

$log = $user->login;

//проверяем, не добавлено ли устройство этим пользователем
$user_dev  = R::findOne( 'usersdevices', 'user_login = ? AND my_device_id = ?', [$log, $dev_id]);
if (isset($user_dev)) 
{
	echo "err2";
	exit;
}

//не добавлено ли оно другим пользователем
$user_dev  = R::findOne( 'usersdevices', 'my_device_id = ?', [$dev_id]);
if (isset($user_dev)) 
{
	echo "err3";
	exit;
}

//обрабатываем данные
$name = $_POST['dev_name'];
$name = stripslashes($name);
$name = htmlspecialchars($name);
$name = trim($name);

//не используется ли этим пользователем устройство с таким-же именем
$user_dev  = R::findOne( 'usersdevices', 'user_login = ? AND device_name = ?', [$log, $name]);
if (isset($user_dev)) 
{
	echo "err4";
	exit;
}

//добавляем в БД
$users_dev = R::dispense('usersdevices');
$users_dev->user_login = $log;
$users_dev->my_device_id = $dev_id;
$users_dev->device_name = $name;
R::store($users_dev);
//Проверим успешность добавления

$user_dev  = R::findOne( 'usersdevices', 'user_login = ? AND my_device_id = ?', [$log, $dev_id]);
if (!isset($user_dev)) 
{
	echo "err0";
}
else echo "1";
