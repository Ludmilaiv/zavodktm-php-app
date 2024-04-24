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
if (!isset($_POST["user_id"]) or !isset($_POST["user_tg"])) {
	echo "err0";
	exit;
}

$user_id = $_POST["user_id"];
$user_tg = $_POST["user_tg"];

$user_subscription  = R::findOne( 'tgsubscriptions', 'user_tg = ?', [$user_tg]);
if (isset($user_subscription)) {
    echo "err1";
    exit;
}

//добавляем в БД
$subscriptions = R::dispense('tgsubscriptions');
$subscriptions->userid = $user_id;
$subscriptions->user_tg = $user_tg;
R::store($subscriptions);
//Проверим успешность добавления

$user_subscription  = R::findOne( 'tgsubscriptions', 'userid = ? AND user_tg = ?', [$user_id, $user_tg]);
if (!isset($user_subscription)) 
{
	echo "err0";
}
else echo "1";