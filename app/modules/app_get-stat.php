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

	$user = R::load('users', $id);

	$session = R::findOne('sessions', 'id = ?', [$sessionID]);
	$session->lastaccesstime = $datetime->getTimestamp();
	R::store($session);

	$device = R::findOne('devices', 'my_id = ?', [$_POST['id']]);
	$user_dev = R::findOne('usersdevices', "my_device_id=? AND user_login = ?", [$_POST['id'], $user->login]);

	if (!isset($user_dev)) {
		echo "err6";
		exit;
	}

	$set = R::findOne('sets', 'device_id = ?', [$device->id]);
	$stat = R::find('statistic', 'devid = ?', [$device->id]);

	$data = array('lt' => 24, 'dt' => 0, 'stat' => array());

	if (isset($set)) {
		$data['dt'] = $set->s23;
		$data['stat'] = $stat;
	}

	echo json_encode($data);

