<?php 
require 'DBConn/libs/rb-mysql.php';
require 'DBConn/dbconn.php';
require 'app/modules/send_notification.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!isset($_POST['userID']) || !isset($_POST['token']) || !isset($_POST['sets']) || empty($_POST['sets'])) {
    echo "err";
    exit;
}

$id = $_POST['userID'];
$token = $_POST['token'];

$sessions = R::find('sessions', 'userid = ?', [$id]);
if (empty($sessions)) {
    echo "err5";
    exit;
}
$auth = false;
foreach ($sessions as $session) {
    if (password_verify($token, $session->token)) {
        $auth = true;
        break;
    }
}
if (!$auth) {
    echo "err5";
    exit;
}

$device = R::findOne('devices', 'my_id = ?', [$_POST['sets']['id']]);
$set = R::findOne('sets', 'device_id = ?', [$device->id]);

foreach ($_POST['sets'] as $key => $val) {
  if ($key != 'id') {
    $set[$key] = $val;
  }
}

$datetime = new DateTime();         // получаем дату и время в Unix-формате

$set->changed_datetime = $datetime->getTimestamp();

$set->changed = 1;   // устанавливаем флаг об изменении настроек

R::store($set);

if ($set['s63'] != 0) {
    send_notifications($set['s63'], $_POST['sets']['id']);
} else {
    $current_notifications = R::find('notification', 'devid = ?', [$_POST['sets']['id']]);
    foreach ($current_notifications as $notification) {
        R::trash($notification);
    }
}

echo 1;