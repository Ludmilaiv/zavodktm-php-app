<?php

require 'DBConn/libs/rb-mysql.php';
require 'app/modules/send_notification.php';
require 'DBConn/dbconn.php';

$timeout = 300;

if (!R::testConnection()) {   // Нет подключени к БД

    echo "s3\x0D";

    exit;

}

if (!isset($_GET['t'])) {   // Не пришли данные

    echo "s2\x0D";

    exit;

}

$data = explode(',', $_GET['t']);  // парсим данные

$device = R::findOne('devices', 'my_id = ?', [$data[0]]);

if (!isset($device)) {    // устройство не зарегистрировано в БД
    echo "s4\x0D";
    exit;
}



$type = R::findOne('ddtypes', 'type_id = ?', [$device->type]);  // ищем тип устройства



$temp_len = $type->temp_len;              // получаем длины массивов данных для этого устройства

$sets_len = $type->sets_len;



if (count($data) != $temp_len) {   // если длина не совпадает, то ошибка формата данных
    echo "s2\x0D";
    exit;
}

$temp = R::findOne('temps', 'device_id = ?', [$device->id]);

$set = R::findOne('sets', 'device_id = ?', [$device->id]);

if ($set['s63'] == 0) {       // Отправка оповещения на останов/запуск котла
	if ($data[1] == 0 && $temp['t1'] != 0) {
		send_notifications(17, $data[0]);
	}
	if ($data[2] > 950 && $temp['t2'] <= 950) {   // Уведомление о перегреве
		send_notifications(15, $data[0]);
	} else {                        // Уведомление о низкой температуре
		if ($set['s48'] > 0 && $set['s48'] < 255 && $data[2] < $set['s48'] * 10 && $temp['t2'] >= $set['s48'] * 10) {   // для новых контроллеров
			send_notifications(14, $data[0]);
		} else if ($set['s4'] > 0 && $set['s4'] < 255 && $data[2] < $set['s4'] * 10 && $temp['t2'] >= $set['s4'] * 10) { // для старых контроллеров
			send_notifications(14, $data[0]);
		}
	}
}

if (!isset($temp)) {               //  если параметры устройства ещё не приходили ни разу, то создаём для них поле в БД
	$temp = R::dispense('temps');
	$temp->device_id = $device->id;   // привязываемся к id таблицы устройств
}

if ($data[1] != 0 && $temp['t1'] == 0) {
	send_notifications(16, $data[0]);
}

for ($key = 1; $key < $temp_len; $key++) {    //перебираем массив данных и заносим их в БД
    $temp['t'.$key] = $data[$key];
}

$datetime = new DateTime();         // получаем дату и время в Unix-формате

$temp->datetime = $datetime->getTimestamp();

R::store($temp);


$response = "s0\x0D";      // далее формируем ответ устройству. 0-флаг успешной отправки

if (isset($set)) {

    if ($set->changed == 1) {       // если в БД установлен флаг об изменении настроек

        if ($temp->datetime - $set->changed_datetime > $timeout) {  // если таймаут изменения настроек вышел

            $set->changed = 0;                         // то сбрасываем флаг

            R::store($set);

        } else {

            $response = "s1";

            for ($key = 1; $key < $sets_len; $key++) {    // перебираем настройки из БД

                $response = $response.'&'.$set['s'.$key];     // и вносим их в ответ через разделитель &

            }

            $response = $response."\x0D";

        }

    }

}



echo $response;

