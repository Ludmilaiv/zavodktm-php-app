<?php
require 'DBConn/libs/rb-mysql.php';
//связываемся с БД
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection())
//если не связались с бд, то возвращаем ошибку
{
    echo "err0";
        exit;
}

if (!isset($_POST['login']) || !isset($_POST['password']) )
//если не пришли данные, то возвращаем ошибку
{
    echo "err4";
    exit;
}

$log = $_POST['login'];
$pas = $_POST['password'];

//обрабатываем логин и пароль чтобы привести их к виду, безопасному для БД. 
    $log = stripslashes($log);
    $log = htmlspecialchars($log);
		$pas = stripslashes($pas);
    $pas = htmlspecialchars($pas);
//удаляем лишние пробелы
    $log = trim($log);
    $pas = trim($pas);
//если информация пришла от панели авторизации (то есть, в ней нет дубликата пароля), то совершаем авторизацию
if (!isset($_POST['password2'])) 
{
	//отправляем поисковый запрос к бд
	$user  = R::findOne( 'users', 'login = ? AND password = ?', [$log, $pas]);

	if (!isset($user))
	{
		//если пароль или логин не верный, то помещаем отправляем ошибку 
        echo "err1";
        exit;
	}
}
else  
//если же данные пришли от формы регистрации, то переходим к регистрации
{
		//обрабатываем дополнительные данные
		$mail = $_POST['email'];
    $pas2 = $_POST['password2'];
		$mail = stripslashes($mail);
		$mail = htmlspecialchars($mail);
		$mail = trim($mail);
		//отправляем поисковый запрос к бд, чтобы выяснить, нет ли такого пользователя
		$user  = R::findOne( 'users', 'login = ?', [$log]);
		if (isset($user))
		{
        echo "err2";
				exit;
		}
		//Проверяем, совпадают ли введённые пароли
		if ($pas !== $pas2) {
				echo "err3";
				exit;
		}
		//укажем в какую таблицу будем записывать
    $users = R::dispense('users');
    //и запихиваем туда данные
    $users->login = $log;
    $users->password = $pas;
    $users->email = $mail;
    // Сохраняем объект в БД
	R::store($users);
	//Проверим успешность регистрации 
	$user  = R::findOne( 'users', 'login = ? AND password = ?', [$log, $pas]);
	if (!isset($user))
	{
		//если нового пользователя нет в БД, значит регистрация не прошла, посываем в сессию ошибку, перезагружаемся и прерываем скрипт
        echo "err0";
		exit;
	}
}
//Если скрипт сумел выполниться до этого места, то авторизация или регистрация прошли успешно. Осталось войти.
$user  = R::findOne( 'users', 'login = ?', [$log]);
echo $user->id;
exit;

?>