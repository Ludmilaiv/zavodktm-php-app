<?php
	require 'DBConn/libs/rb-mysql.php';
	require 'DBConn/dbconn.php';

	$_POST = json_decode(file_get_contents('php://input'), true);

	if (!R::testConnection()) {
		echo "err0";
		exit;
	}

	if (!isset($_POST['user_id']) || !isset($_POST['password']) || !isset($_POST['dev_id'])) {
		echo "err4";
		exit;
	}

	$userId = $_POST['user_id'];
	$pas = $_POST['password'];
	$id = $_POST['dev_id'];

	$user = R::findOne('users', 'id = ?', [$userId]);
	if (!password_verify($pas, $user->password) && $pas !== $user->password) {
		echo "err1";
		exit;
	}

	$device = R::findOne('devices', 'id = ?', [$id]);

	$users_dev  = R::find( 'usersdevices', 'my_device_id = ?', [$device->my_id]);
	foreach ($users_dev as $user_dev) {
		$del = R::findOne('usersdevices', 'id = ?', [$user_dev->id]);
		R::trash($del);
	}

	$set = R::findOne('sets', 'device_id = ?', [$id]);
	if (isset($set)) R::trash($set);

	$temp = R::findOne('temps', 'device_id = ?', [$id]);
	if (isset($temp)) R::trash($temp);

	R::trash($device);

	echo 1;
