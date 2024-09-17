<?php
	require 'DBConn/libs/rb-mysql.php';
	require 'DBConn/dbconn.php';
require 'app/modules/send_notification.php';
//Отправляем тестовое уведомление
	$id = '066DFF50';
if ($_GET['s63'] != 0) {
    send_notifications($_GET['s63'], $id);
    echo('send');
} else {
    $current_notifications = R::find('notification', 'devid', [$id]);
    foreach ($current_notifications as $notification) {
        R::trash($notification);
        echo('delete');
    }
}
	echo($id);echo(' | '); echo( $_GET['s63']);