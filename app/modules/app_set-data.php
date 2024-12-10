<?php 
require 'DBConn/libs/rb-mysql.php';
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!isset($_POST['id'])) {
	echo 'err';
	exit;
}

$device = R::findOne('devices', 'my_id = ?', [$_POST['id']]);

if (!isset($device)) {
	echo 'err';
	exit;
}

$set = R::findOne('sets', 'device_id = ?', [$device->id]);

if (!isset($set)) {
	echo 'err';
	exit;
}

foreach ($_POST as $key => $val) {
  if ($key != 'id') {
    $set[$key] = $val;
  }
}

$datetime = new DateTime();         // получаем дату и время в Unix-формате

$set->changed_datetime = $datetime->getTimestamp();

$set->changed = 1;   // устанавливаем флаг об изменении настроек

R::store($set);

echo 1;