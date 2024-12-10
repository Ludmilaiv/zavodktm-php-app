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

	$device = R::findOne('devices', 'my_id = ?', [$_POST['id']]);
	$users_dev = R::find('usersdevices', "my_device_id = ?", [$_POST['id']]);
	$temp = R::findOne('temps', 'device_id = ?', [$device->id]);
	$type = R::findOne('ddtypes', 'type_id = ?', [$device->type]);

	$users = array();
	foreach ($users_dev as $user_dev) {
		$user = R::findOne('users', 'login = ?', [$user_dev->user_login]);
		if (!isset($user)) continue;
		$users[] = array('login'=>$user->login, 'email'=>$user->email);
	}

	$data = array('id'=>$device->id, 'my_id'=>$device->my_id, 'users'=>$users, 'type' => $device->type, 'temp' => [-1], 'time_online'=>0);

	if (!isset($temp)) {     //проверяем, есть ли данные, полученные от устройства
		$data["temp"] = [-1];
		$data["time_online"] = 0;
	} else {
		$datetime_ms = $datetime->getTimestamp();

		$delta_connect_time = $datetime_ms - $temp->datetime;  // сколько миллисекунд назад устройство в последний раз засылало данные

		if ($delta_connect_time >= $timeout) {   //если давно, то отправляем оффлайн
			$data["temp"] = [-1];
			$data["time_online"] = $temp->datetime;
		} else {
			$data["temp"] = [$temp["t1"], $temp["t2"]];
		}
	}

	$data_json = json_encode($data);
	echo $data_json;



