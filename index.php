<?php
  $path_with_get = $_SERVER['REQUEST_URI'];
  $path_with_get_array = explode("?", $path_with_get);
  $path = $path_with_get_array[0];

  switch ($path) {

    // Переадресация на сайт

    case "/":
      header("Location: https://kotelktm.ru/");
      exit;
      break;

    // Андрею для новых устройств
    
    case "/temp": // Сюда отсылать массив температур в порядке, согдасованном с сеструхой
      require_once("./app/modules/device_temperature.php");
      break;
    case "/set":   // Сюда аналогично отсылать массив настроек
      require_once("./app/modules/device_settings.php");
      break;
    case "/done":  // Сюда отсылать отчёт об успешно полученных настройках
      require_once("./app/modules/device_done.php");
      break;

    // Для приложения

    case "/add-device":   // Добавление устройства
      require_once("./app/modules/app_add-device.php");
      break;
    case "/del-device":  // Удаление устройства
      require_once("./app/modules/app_del-device.php");
      break;
    case "/edit-device": // Изменение имени устройства
      require_once("./app/modules/app_edit-device.php");  
      break;
    case "/get-data": // Получение приложением температур и настроек из БД
      require_once("./app/modules/app_get-data.php");
      break;
    case "/get-devices": // Получение списка всех устройств пользователя
      require_once("./app/modules/app_get-devices.php");
      break;
    case "/recovery-pass": // Восстановление пароля
      require_once("./app/modules/app_recovery-pass.php");
      break;
    case "/reg":   // Регистрация
      require_once("./app/modules/app_reg.php");
      break;
    case "/set-data":  // Отправка настроек приложением
      require_once("./app/modules/app_set-data.php");
      break;

    // Это временная поддержка для старых контроллеров и приложений
    default: 
      header("Location: http://old.zavodktm.ru".$path_with_get);
      exit;
      break;
    
}

?>