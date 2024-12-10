<?php
	$drConDir = dirname(dirname(__DIR__)) . '/DBConn/';
	require  $drConDir . 'libs/rb-mysql.php';
	require $drConDir . 'dbconn.php';

	$devices = R::find('devices', "stat=?", [1]);

	$timeout = 300; //таймаут после окончания которого устройство считается оффлайн

	$datetime = new DateTime();
	$datetime_s = $datetime->getTimestamp();
	$begin_date = $datetime_s - 24 * 60 * 60;

	foreach ($devices as $device) {
		$temp = R::findOne('temps', 'device_id = ?', [$device->id]);
		$set = R::findOne('sets', 'device_id = ?', [$device->id]);
		if (!isset($temp) or !isset($set)) continue;
		$delta_connect_time = $datetime_s - $temp->datetime;
		if ($delta_connect_time >= $timeout) continue;
		$stat = R::find('statistic', 'devid=?', [$device->id]);
		$maxDateStat = null;
		// Удаляем устаревшую статистику
		foreach ($stat as $devStat) {
			if ($devStat->datetime < $begin_date) {
				$s = R::findOne('statistic', 'id=?', [$devStat->id]);
				R::trash($s);
			} else {
				if (!$maxDateStat || $devStat->datetime > $maxDateStat->datetime) {
					$maxDateStat = $devStat;
				}
			}
		}
		if (!$maxDateStat || $datetime_s - $maxDateStat->datetime >= $set['s23'] * 60) {
			$newStat = R::dispense('statistic');
			$newStat->devid = $device->id;
			$newStat->datetime = $datetime_s;
			$newStat->s2 = $set->s2;
			$newStat->t10 = $temp->t10;
			$newStat->t2 = $temp->t2;
			R::store($newStat);
		}
	}

	echo 1;


