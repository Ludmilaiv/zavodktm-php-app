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
if (!isset($_POST["user_id"]) or !isset($_POST["subscription"])) {
	echo "err0";
	exit;
}

$user_id = $_POST["user_id"];
$endpoint = json_encode($_POST["subscription"]["endpoint"]);

$user_subscriptions  = R::find( 'pushsubscriptions', 'endpoint = ?', [$endpoint]);
foreach ($user_subscriptions as $user_subscription) {
	R::trash($user_subscription);
}


//проверяем успешность удаления

$user_subscription  = R::findOne( 'pushsubscriptions', 'endpoint = ?', [$endpoint]);
if (isset($user_subscription)) 
{
	echo "err0";
}
else echo "1";
