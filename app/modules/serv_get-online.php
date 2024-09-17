<?php
	require 'DBConn/libs/rb-mysql.php';
//связываемся с БД
	require 'DBConn/dbconn.php';
	require 'app/modules/send_notification.php';

	if (!R::testConnection()) {
		echo "err0";
		exit;
	}

	$timeout = 300; //таймаут после окончания которого устройство считается оффлайн

	$devicesTable = R::findAll('temps');
	$devices = array();
	foreach ($devicesTable as $temp) {
		$datetime = new DateTime();         // получаем дату и время в Unix-формате
		$datetime_ms = $datetime->getTimestamp();
		$delta_connect_time = $datetime_ms - $temp['datetime'];  // сколько миллисекунд назад устройство в последний раз засылало данные
		$dev_online = R::findOne('devicesonline', 'devid = ?', [$temp->device_id]);

		if ($delta_connect_time >= $timeout) {
			if ($dev_online) {
				$device = R::findOne('devices', 'id = ?', [$temp->device_id]);
				send_notifications(19, $device['my_id']);
				R::trash($dev_online);
			}
		} else {
			if (!$dev_online) {
				$dev_online = R::dispense('devicesonline');
				$dev_online->devid = $temp->device_id;
				$device = R::findOne('devices', 'id = ?', [$temp->device_id]);
				send_notifications(18, $device['my_id']);
				R::store($dev_online);
			}
		}
	}

	echo 'ok';



