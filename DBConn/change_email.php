<?php
require 'libs/rb-mysql.php';
//связываемся с БД
require 'dbconn.php';

if (!R::testConnection())
//если не связались с бд, то выводим в сессию ошибку и перезагружаем страничку
{
	echo "err1";
	exit;
}
$log = $_POST["login"];
$user  = R::findOne( 'users', 'login = ?', [$log]);
//обрабатываем данные
$mail = $_POST['email'];
$mail = stripslashes($mail);
$mail = htmlspecialchars($mail);
$mail = trim($mail);
//загружаем в БД
$user->email = $mail;
R::store($user);
//проверяем успешность загрузки и выводим новое значение
$user  = R::findOne( 'users', 'login = ?', [$log]);
if ($user->email == $mail)
	{echo $mail;}
else echo "err2";
?>
