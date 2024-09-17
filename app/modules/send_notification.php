<?php
//	require 'DBConn/libs/rb-mysql.php';
//	require 'DBConn/dbconn.php';

function send_notifications($type, $id)
{
    // $current_notification = R::findOne('notification', 'devid = ? AND error_value = ?', [$id, $type]);
    //if ($current_notification) return;

//    $current_notification = R::dispense('notification');
//    $current_notification->devid = $id;
//    $current_notification->error_value = $type;
//    R::store($current_notification);

    $title = "❗️ ВНИМАНИЕ!";
    $message = "";

    if ($type == 1) {
        $message = "Работа котла \"{name}\" прервана. Неисправен датчик подачи.";
    } else if ($type == 2) {
        $message = "Работа котла \"{name}\" прервана. Заклинивание шнека.";
    } else if ($type == 3) {
        $message = "Котёл \"{name}\" угас. Низкая температура подачи.";
    } else if ($type == 4) {
        $message = "Котёл \"{name}\" угас. Низкая температура дымовых газов.";
    } else if ($type == 5) {
        $message = "Работа котла \"{name}\" прервана. Перегрев.";
    } else if ($type == 6) {
        $message = "Работа котла \"{name}\" прервана. Неисправно силовое реле.";
    } else if ($type == 19) {
	    $title = "Пропала связь с котлом!";
		$message = "Контроллер \"{name}\" не в сети.";
    } else if ($type == 18) {
	    $title = "Связь восстановлена.";
		$message = "Контроллер \"{name}\" снова в сети.";
    }
	if ($message == "") {
		echo "message is empty";
		exit;
	}
    $users_dev = R::find('usersdevices', "UPPER(my_device_id)=UPPER(?)", [$id]);
    $notifications = array();
    foreach ($users_dev as $user_dev) {
        $login = $user_dev->user_login;
        $user = R::findOne('users', 'login = ?', [$login]);
        $push_data = R::find('pushsubscriptions', 'userid = ?', [$user->id]);
        foreach ($push_data as $push_item) {
            $notification = array(
                'keys' => json_decode($push_item->keys),
                'endpoint' => json_decode($push_item->endpoint),
                'data' => $push_item->userid,
                'title' => $title,
                'message' => str_replace("{name}", $user_dev->device_name ,$message),
                'type' => 'web-push'
            );
            $notifications[] = $notification;
        }
        $tg_data = R::find('tgsubscriptions', 'userid = ?', [$user->id]);
        foreach ($tg_data as $tg_item) {
            $notification = array(
                'chatid' => $tg_item->user_tg,
                'title' => $title,
                'message' => str_replace("{name}", $user_dev->device_name ,$message),
                'type' => 'telegram'
            );
            $notifications[] = $notification;
        }
    }
    $myCurl = curl_init();
    curl_setopt_array($myCurl, array(
        CURLOPT_URL => 'https://biomatic24.ru/push/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(array('notifications'=>json_encode($notifications)))
    ));
    $result = curl_exec($myCurl);
    print_r($result);
    curl_close($myCurl);
}
