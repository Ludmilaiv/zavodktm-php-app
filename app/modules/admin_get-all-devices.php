<?php
	require 'DBConn/libs/rb-mysql.php';
	require 'DBConn/dbconn.php';

	$_POST = json_decode(file_get_contents('php://input'), true);

	if (!R::testConnection()) {
		echo "err0";
		exit;
	}

	if (!isset($_POST['userID']) || !isset($_POST['token']) || !isset($_POST['page'])) {
		echo "err1";
		exit;
	}

	$id = $_POST['userID'];
	$token = $_POST['token'];
	$page = $_POST['page'];

	$datetime = new DateTime(); // получаем дату и время в Unix-формате

	$sessions = R::find('sessions', 'userid = ?', [$id]);
	if (empty($sessions)) {
		echo "err5";
		exit;
	}
	$auth = false;
	$sessionID = 0;
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

	$user = R::load('users', $id);
	if (!$user || $user->isadmin == 0) {
		echo "err1";
		exit;
	}

	$session = R::findOne('sessions', 'id = ?', [$sessionID]);

	$session->lastaccesstime = $datetime->getTimestamp();
	R::store($session);

	$timeout = 300;
	$limit = 20; // по сколько на странице
	$all_devices = R::findAll('devices', 'ORDER BY id DESC');
	$obj_dev = array_slice($all_devices,(($page-1)*$limit),$limit,true);
	$arr_dev = array();
	foreach ($obj_dev as $key => &$dev) {
		$temps = R::findOne('temps', 'device_id = ?', [$dev->id]);
		$sets = R::findOne('sets', 'device_id = ?', [$dev->id]);
		$temp = -1000;
		$icon = "";
		if (!isset($temps) || !isset($sets)) {
			$temp = -3000;
		} else {
			$datetime_ms = $datetime->getTimestamp();
			$delta_connect_time = $datetime_ms - $temps->datetime;  // сколько миллисекунд назад устройство в последний раз засылало данные
			if ($delta_connect_time >= $timeout) {   //если давно, то отправляем оффлайн
				$temp = -3000;
			} else {
				$temp = $temps->t2;
				if ($temps->t1 >= 5) {
					$icon = "service-block";
				} elseif ($sets->s63 > 0) {
					$icon = "danger";
				} elseif ($temps->t2 == -1270 || $temps->t3 == -1270 || $temps->t4 == -1270 || $temps->t5 == -1270
				  || $temps->t6 == -1270 || $temps->t7 == -1270 || $temps->t8 == -1270) {
					$icon = "warning";
				}
			}
		}
		$dev["temp"] = $temp;
		$dev["icon"] = $icon;
		$dev['id'] = $key;
		$arr_dev[] = $dev;
	}
	$count = R::count('devices');
	$totalPages = ceil($count/$limit);

	echo json_encode(['devs' => $arr_dev, 'count' => $totalPages]);




