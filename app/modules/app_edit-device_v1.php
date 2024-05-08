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
if (!isset($_POST["dev_id"]) || !isset($_POST["new_name"]) || !isset($_POST["user_id"]) || !isset($_POST["token"])) {
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
$new_name = $_POST["new_name"];

//обрабатываем данные
$new_name = stripslashes($new_name);
$new_name = htmlspecialchars($new_name);
$new_name = trim($new_name);

$user_dev  = R::findOne( 'usersdevices', 'my_device_id = ?', [$dev_id]);

if (isset($user_dev)) 
{
    $user_dev->device_name = $new_name;
		R::store($user_dev);
} else {
	//если устройство по id не найдено, то ошибка
	echo "err0";
	exit;
}

//проверяем успешность переименования

$user_dev  = R::findOne( 'usersdevices', 'my_device_id = ? AND device_name = ?', [$dev_id, $new_name]);
if (!isset($user_dev)) 
{
	echo "err0";
}
else echo "1";

?>