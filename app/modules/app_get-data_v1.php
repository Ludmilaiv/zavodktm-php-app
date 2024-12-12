<?php 
require 'DBConn/libs/rb-mysql.php';
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection()) {
  echo "err";
  exit;
}

if (!isset($_POST['id']) || !isset($_POST['userID']) || !isset($_POST['token'])) {
  echo "err1";
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
$sessionID = 0;
$datetime = new DateTime();         // получаем дату и время в Unix-формате
foreach ($sessions as $session) {
	if (password_verify($token, $session->token)) {
		$auth = true;
		$sessionID = $session->id;
		break;
	}
}
if (!$auth) {
	echo "err5";
	exit;
}

$session = R::findOne('sessions', 'id = ?', [$sessionID]);
$session->lastaccesstime = $datetime->getTimestamp();
R::store($session);
$timeout = 300; //таймаут после окончания которого устройство считается оффлайн

$user = R::findOne('users', 'id = ?', [$id]);
$device = R::findOne('devices', 'my_id = ?', [$_POST['id']]);
$user_dev = R::findOne('usersdevices', "my_device_id=? AND user_login = ?", [$_POST['id'], $user->login]);
$temp = R::findOne('temps', 'device_id = ?', [$device->id]);
$type = R::findOne('ddtypes', 'type_id = ?', [$device->type]);
$set = R::findOne('sets', 'device_id = ?', [$device->id]);
if (!isset($device) || !isset($user_dev)) {
	echo "err1";
	exit;
}
$name = $user_dev->device_name;

if (!isset($temp) or !isset($set)) {     //проверяем, есть ли данные, полученные от устройства
  $data_temp_json = json_encode(["temp"=>[-1], 'type'=>$type->type_id, 'name'=>$name, 'stat'=>$device->stat, 'time_online'=>0] );
} else {

  $datetime_ms = $datetime->getTimestamp();

  $delta_connect_time = $datetime_ms - $temp->datetime;  // сколько миллисекунд назад устройство в последний раз засылало данные

  if ($delta_connect_time >= $timeout) {   //если давно, то отправляем оффлайн
    $data_temp_json = json_encode(["temp"=>[-1], 'type'=>$type->type_id, 'name'=>$name, 'stat'=>$device->stat,'time_online'=>$temp->datetime]);
  } else {   //иначе получаем все данные устройства
    // если пришли настройки, то сохраняем их
    if (!empty($_POST['sets'])) {
	    // Удаляем старую стату при изменении периода сохранения
	    if (isset($_POST['sets']['s23']) && $_POST['sets']['s23'] != $set['s23']) {
		    $stat = R::find('statistic', 'devid=?', [$device->id]);
		    foreach ($stat as $devStat) {
			    $ds = R::findOne('statistic', 'id=?', [$devStat->id]);
			    R::trash($ds);
		    }
	    }
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
      'changed'=>$set->changed,
	    'stat'=>$device->stat
    ];
    $data_temp_json = json_encode($data);
  }
}

echo $data_temp_json;