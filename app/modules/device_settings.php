<?php

require 'DBConn/libs/rb-mysql.php';
require 'app/modules/send_notification.php';
require 'DBConn/dbconn.php';

$timeout = 300; // таймаут - 5 минут



//R::setup('mysql:host=localhost;dbname=u0803985_climate', 'u0803985_user', 'Ludiky123');

//R::setup('mysql:host=localhost;dbname=u0803985_climate', 'root', '');



if (!R::testConnection()) {   // Нет подключения к БД

    echo "s3\x0D";

    exit;

}



if (!isset($_GET['s'])) {   // Не пришли настройки

    echo "s2\x0D";

    exit;

}



$data = explode(',', $_GET['s']);  // парсим настройки



$device = R::findOne('devices', 'my_id = ?', [$data[0]]);



if (!isset($device)) {    // устройство не зарегистрировано в БД

    echo "s4\x0D";

    exit;

}



$type = R::findOne('ddtypes', 'type_id = ?', [$device->type]);   // ищем тип устройства

$sets_len = $type->sets_len;                            //получаем длину массива данных для этого типа



if (count($data) != $sets_len) {   // если длина не совпадает, то ошибка формата данных

    echo "s2\x0D";

    exit;

}



$set = R::findOne('sets', 'device_id = ?', [$device->id]);

if (!isset($set)) {               //  если настройки устройства ещё не приходили ни разу, то создаём для них поле в БД

    $set = R::dispense('sets');

    $set->device_id = $device->id;   // привязываемся к id таблицы устройств

}

// Корректировка данных для старого контроллера, у которого в настройках сохранены ошибочные данные
if ($data[63] > 20) {
	$data[63] = 0;
}

// Удаляем старую стату при изменении периода сохранения
if ($data[23] != $set['s23']) {
	$stat = R::find('statistic', 'devid=?', [$device->id]);
	foreach ($stat as $devStat) {
		$ds = R::findOne('statistic', 'id=?', [$devStat->id]);
		R::trash($ds);
	}
}

for ($key = 1; $key < $sets_len; $key++) {    //перебираем массив настроек и заносим их в БД

    $set['s'.$key] = $data[$key];

}



$datetime = new DateTime();         // получаем дату и время в Unix-формате

$set->datetime = $datetime->getTimestamp();

$set->changed = 0;   // сбрасываем флаг об изменении настроек



R::store($set);

if ($set['s63'] != 0) {
    send_notifications($set['s63'], $data[0]);
}


echo "s0\x0D"; // отвечаем, что всё норм

