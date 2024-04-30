<?php 
require 'DBConn/libs/rb-mysql.php';
//связываемся с БД
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection())
{
    echo "err0";
        exit;
}

if (!isset($_POST['userID']))
{
    echo "err1";
    exit;
}

$id = $_POST['userID'];

//находим все устройства пользователя
$user = R::load('users', $id);
$dev  = R::find('usersdevices', 'user_login = ?', [$user->login]);

$array_dev = array();

$timeout = 300; //таймаут после окончания которого устройство считается оффлайн

foreach($dev as $i){
  // Получаем идентификатор записи устройства в БД
 $device = R::findOne('devices', 'my_id = ?', [$i['my_device_id']]);
 // Получаем температуру по этому идентификатору
 $temps = R::findOne('temps', 'device_id = ?', [$device->id]);
 $sets = R::findOne('sets', 'device_id = ?', [$device->id]);
 $temp = -1000;
 if (!isset($temps)||!isset($sets)) {
   $temp = -3000;
 } else {
  $datetime = new DateTime(); // получаем дату и время в Unix-формате
  $datetime_ms = $datetime->getTimestamp();
  $delta_connect_time = $datetime_ms - $temps->datetime;  // сколько миллисекунд назад устройство в последний раз засылало данные
  if ($delta_connect_time >= $timeout) {   //если давно, то отправляем оффлайн
    $temp = -3000; 
  } else {
    $temp = $temps->t2;
  }
 }
  $d = [
    "name" => $i['device_name'],
    "id" => $i['my_device_id'],
    "temp" => $temp
  ];
  array_push($array_dev, $d);
}

echo json_encode($array_dev);

?>
