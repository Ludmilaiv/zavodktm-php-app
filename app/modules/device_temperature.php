<?php

require 'DBConn/libs/rb-mysql.php';

$timeout = 300; // таймаут - 5 минут



R::setup('mysql:host=localhost;dbname=u0803985_climate', 'u0803985_user', 'Ludiky123');

//R::setup('mysql:host=localhost;dbname=u0803985_climate', 'root', '');



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

if (!isset($temp)) {               //  если параметры устройства ещё не приходили ни разу, то создаём для них поле в БД

    $temp = R::dispense('temps');

    $temp->device_id = $device->id;   // привязываемся к id таблицы устройств

}



for ($key = 1; $key < $temp_len; $key++) {    //перебираем массив данных и заносим их в БД

    $temp['t'.$key] = $data[$key];

}



$datetime = new DateTime();         // получаем дату и время в Unix-формате

$temp->datetime = $datetime->getTimestamp();



R::store($temp);



$response = "s0\x0D";      // далее формируем ответ устройству. 0-флаг успешной отправки

$set = R::findOne('sets', 'device_id = ?', [$device->id]);

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

