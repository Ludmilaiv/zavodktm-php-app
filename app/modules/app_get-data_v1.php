<?php 
require 'DBConn/libs/rb-mysql.php';
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection()) {
  echo "err";
  exit;
}

if (!isset($_POST['id']) || !isset($_POST['userID']) || !isset($_POST['token'])) {
  echo "err";
  exit;
}

$id = $_POST['userID'];
$token = $_POST['token'];

$session = R::findOne('sessions', 'userid = ?', [$id]);
if (!isset($session) || password_verify($token, $session->token)) {
    echo "err";
    exit;
}

$timeout = 20; //таймаут после окончания которого устройство считается оффлайн

$device = R::findOne('devices', 'my_id = ?', [$_POST['id']]);
$user_dev = R::findOne('usersdevices', "my_device_id=?", [$_POST['id']]);
$temp = R::findOne('temps', 'device_id = ?', [$device->id]);
$type = R::findOne('ddtypes', 'type_id = ?', [$device->type]);
$set = R::findOne('sets', 'device_id = ?', [$device->id]);
$name = $user_dev->device_name;

if (!isset($temp) or !isset($set)) {     //проверяем, есть ли данные, полученные от устройства
  $data_temp_json = json_encode(["temp"=>[-1]]); 
} else {
  $datetime = new DateTime();         // получаем дату и время в Unix-формате
  $datetime_ms = $datetime->getTimestamp();

  $delta_connect_time = $datetime_ms - $temp->datetime;  // сколько миллисекунд назад устройство в последний раз засылало данные

  if ($delta_connect_time >= $timeout) {   //если давно, то отправляем оффлайн
    $data_temp_json = json_encode(["temp"=>[-1], 'name'=>$name]);
  } else {   //иначе получаем все данные устройства
    // если пришли настройки, то сохраняем их
    if (!empty($_POST['sets'])) {
      foreach ($_POST['sets'] as $key => $val) {
        $set[$key] = $val;
      }
      $set->changed_datetime = $datetime->getTimestamp();
      $set->changed = 1;   // устанавливаем флаг об изменении настроек
      R::store($set);
      $set = R::findOne('sets', 'device_id = ?', [$device->id]);
    }
    $data_temp = array();   
    $data_set = array();
    for($i=1; $i<$type->temp_len; $i++) {
      array_push($data_temp, $temp['t'.$i]);      
    }
    for($i=1; $i<$type->sets_len; $i++) {
      array_push($data_set, $set['s'.$i]);      
    }
    $data = [
      'temp'=>$data_temp,
      'set'=>$data_set,
      'name'=>$name,
      'type'=>$type->type_id,
      'changed'=>$set->changed
    ];
    $data_temp_json = json_encode($data);
  }
}

echo $data_temp_json;