<?php
require 'DBConn/libs/rb-mysql.php';
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection()) {
    echo "err0";
    exit;
}

if (isset($_POST['logout']) && isset($_POST['id'])) {  // Выход из аккаунта
	$sessions = R::find('sessions', 'userid = ?', [$_POST['id']]);
	$sessionId = 0;
	foreach ($sessions as $session) {
		if (password_verify($_POST['logout'], $session['token'])) {
			$sessionId = $session['id'];
			break;
		}
	}
	if ($sessionId != 0) {
		$session = R::findOne('sessions', 'id = ?', [$sessionId]);
		R::trash($session);
		echo 1;
		exit;
	}
}

if (!isset($_POST['login']) || !isset($_POST['password'])) {
    echo "err4";
    exit;
}

$log = $_POST['login'];
$pas = $_POST['password'];

$log = stripslashes($log);
$log = htmlspecialchars($log);
$pas = stripslashes($pas);
$pas = htmlspecialchars($pas);
$log = trim($log);
$pas = trim($pas);
//если информация пришла от панели авторизации (то есть, в ней нет дубликата пароля), то совершаем авторизацию
if (!isset($_POST['password2'])) {
    $user = R::findOne('users', 'login = ?', [$log]);

    if (!isset($user)) {
        echo "err1";
        exit;
    }

    if (!password_verify($pas, $user->password) && $pas !== $user->password) {
        echo "err1";
        exit;
    }
} else //если же данные пришли от формы регистрации, то переходим к регистрации
{
    $mail = $_POST['email'];
    $pas2 = $_POST['password2'];
    $mail = stripslashes($mail);
    $mail = htmlspecialchars($mail);
    $mail = trim($mail);
    $user = R::findOne('users', 'login = ?', [$log]);
    if (isset($user)) {
        echo "err2";
        exit;
    }

    if ($pas !== $pas2) {
        echo "err3";
        exit;
    }
    $user = R::dispense('users');
    $user->login = $log;
    $user->password = password_hash($pas, PASSWORD_DEFAULT);;
    $user->email = $mail;
    $user->not_confirm = true;
    R::store($user);
    //Проверим успешность регистрации
    $user = R::findOne('users', 'login = ?', [$log]);
    if (!isset($user)) {
        echo "err0";
        exit;
    }
}
//Если скрипт сумел выполниться до этого места, то авторизация или регистрация прошли успешно. Осталось войти.

$user = R::findOne('users', 'login = ?', [$log]);

$token = dechex(time()).md5(uniqid($pas));
$session = R::dispense('sessions');
$session->userid = $user->id;
$session->token = password_hash($token, PASSWORD_DEFAULT);
$datetime = new DateTime();
$session->lastaccesstime = $datetime->getTimestamp();
R::store($session);

echo json_encode(array('user'=>$user->id, 'token'=>$token));
exit;

?>