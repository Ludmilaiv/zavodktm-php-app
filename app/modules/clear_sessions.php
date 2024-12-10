<?php
	$drConDir = dirname(dirname(__DIR__)) . '/DBConn/';
	require  $drConDir . 'libs/rb-mysql.php';
	require $drConDir . 'dbconn.php';

	$datetime = new DateTime();
	$timeout = 604800;

	echo '<h1>Remove sessions</h1>';

	for ($i = 1; $i<1000; $i++) {
		$sessions = R::find('sessions', 'userid = ?', [$i]);
		if (count($sessions) < 20) continue;
		foreach ($sessions as $session) {
			$datetime_s = $datetime->getTimestamp();
			$delta_access_time = $datetime_s - $session->lastaccesstime;
			if ($delta_access_time > $timeout or $datetime == 0) {
				$sessionOld = R::findOne('sessions', 'id = ?', [$session->id]);
				echo $sessionOld->userid . '<br>';
				R::trash($sessionOld);
			}
		}
	}



