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
$keys = json_encode($_POST["subscription"]["keys"]);
$endpoint = json_encode($_POST["subscription"]["endpoint"]);

// Проверяем, не включены ли уже уведомления для этого пользователя на эту конечную точку
$user_subscription  = R::findOne( 'pushsubscriptions', 'userid = ? AND endpoint = ?', [$user_id, $endpoint]);
if (!isset($user_subscription)) {
	//добавляем в БД
	$subscriptions = R::dispense('pushsubscriptions');
	$subscriptions->userid = $user_id;
	$subscriptions->keys = $keys;
	$subscriptions->endpoint = $endpoint;
	R::store($subscriptions);
}

//Проверим успешность добавления

$user_subscription  = R::findOne( 'pushsubscriptions', 'userid = ? AND endpoint = ?', [$user_id, $endpoint]);
if (!isset($user_subscription)) 
{
	echo "err0";
}
else echo "1";