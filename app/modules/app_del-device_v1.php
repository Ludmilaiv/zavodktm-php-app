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

$sessions = R::find('sessions', 'userid = ?', [$_POST['user_id']]);
if (empty($sessions)) {
    echo "err5";
    exit;
}
$auth = false;
foreach ($sessions as $session) {
    if (password_verify($_POST["token"], $session->token)) {
        $auth = true;
        break;
    }
}
if (!$auth) {
    echo "err5";
    exit;
}

$dev_id = $_POST["dev_id"];

$user = R::findOne('users', 'id = ?', [$_POST["user_id"]]);

$user_dev  = R::findOne( 'usersdevices', 'my_device_id = ? AND user_login = ?', [$dev_id, $user->login]);

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

$user_dev  = R::findOne( 'usersdevices', 'my_device_id = ? AND user_login = ?', [$dev_id, $user->login]);
if (isset($user_dev)) 
{
	echo "err0";
}
else echo "1";

?>