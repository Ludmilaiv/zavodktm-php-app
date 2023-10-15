<?php
require 'DBConn/libs/rb-mysql.php';
$timeout = 300000; // таймаут - 5 минут

R::setup('mysql:host=localhost;dbname=u0803985_climate', 'u0803985_user', 'Ludiky123');
//R::setup('mysql:host=localhost;dbname=u0803985_climate', 'root', '');

if (!R::testConnection()) {   // Нет подключени к БД
    echo "s3\x0D";
    exit;
}

if (!isset($_GET['d'])) {   // Не пришли данные
    echo "s2\x0D";
    exit;
}

$data = explode(',', $_GET['d']);  // парсим настройки

if (count($data) != 2) {   // если длина не совпадает, то ошибка формата данных
    echo "s2\x0D";
    exit;
}

$device = R::findOne('devices', 'my_id = ?', [$data[0]]);

if (!isset($device)) {    // устройство не зарегистрировано в БД
    echo "s4\x0D";
    exit;
}
  
$set = R::findOne('sets', 'device_id = ?', [$device->id]);

if ($data[1] == 0) {
    $set->changed = 0;   // если настройки успешно приняты устройством, то сбрасываем флаг об изменении настроек
    R::store($set);
    echo "s0\x0D"; // отвечаем, что всё норм
    exit;
}

// если данные не приняты, то засылаем их повторно

$type = R::findOne('ddtypes', 'type_id = ?', [$device->type]);  // определяем тип устройства
$sets_len = $type->sets_len;          // получаем длину массива данных для этого типа

$datetime = new DateTime();         // получаем дату и время в Unix-формате
$datetime_set = $datetime->getTimestamp();

if ($set->changed == 1) {       // если в БД всё ещё установлен флаг об изменении настроек
  if ($datetime_set - $set->changed_datetime > $timeout) {  // если таймаут изменения настроек вышел
      $set->changed = 0;                         // то сбрасываем флаг
      R::store($set);
      echo "s0\x0D"; // шлём устройству, что всё норм, чтоб не дёргалось, так как изменения уже не актуальны
  } else {
      $response = "s1";
      for ($key = 1; $key < $sets_len; $key++) {    // перебираем настройки из БД
          $response = $response.'&'.$set['s'.$key];     // и вносим их в ответ через разделитель &
      }
      echo $response."\x0D";
  }
}
