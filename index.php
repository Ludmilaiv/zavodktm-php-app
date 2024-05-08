<?php
$path_with_get = $_SERVER['REQUEST_URI'];
$path_with_get_array = explode("?", $path_with_get);
$path = $path_with_get_array[0];

switch ($path) {

    // Для устройств

    case "/temp": // Сюда отсылать массив температур в согласованном порядке
        require_once("./app/modules/device_temperature.php");
        break;
    case "/temp.php":
        require_once("./app/modules/device_temperature.php");
        break;
    case "/set":   // Сюда аналогично отсылать массив настроек
        require_once("./app/modules/device_settings.php");
        break;
    case "/set.php":
        require_once("./app/modules/device_settings.php");
        break;
    case "/done":  // Сюда отсылать отчёт об успешно полученных настройках
        require_once("./app/modules/device_done.php");
        break;
    case "/done.php":
        require_once("./app/modules/device_done.php");
        break;

    // Для приложения

    case "/add-device":   // Добавление устройства
        require_once("./app/modules/app_add-device.php");
        break;
    case "/add-device-v1":
        require_once("./app/modules/app_add-device_v1.php");
        break;
    case "/del-device":  // Удаление устройства
        require_once("./app/modules/app_del-device.php");
        break;
    case "/del-device-v1":
        require_once("./app/modules/app_del-device_v1.php");
        break;
    case "/edit-device": // Изменение имени устройства
        require_once("./app/modules/app_edit-device.php");
        break;
    case "/edit-device-v1": // Изменение имени устройства
        require_once("./app/modules/app_edit-device_v1.php");
        break;
    case "/get-data": // Получение приложением температур и настроек из БД
        require_once("./app/modules/app_get-data.php");
        break;
    case "/get-data-v1":
        require_once("./app/modules/app_get-data_v1.php");
        break;
    case "/get-devices": // Получение списка всех устройств пользователя
        require_once("./app/modules/app_get-devices.php");
        break;
    case "/get-devices-v1":
        require_once("./app/modules/app_get-devices_v1.php");
        break;
    case "/recovery-pass": // Восстановление пароля
        require_once("./app/modules/app_recovery-pass.php");
        break;
    case "/recovery": // Страница восстановление пароля
        require_once("./app/templates/recovery-pass.php");
        break;
    case "/edit-pass": // Изменение пароля
        require_once("./app/modules/app_edit-pass.php");
        break;
    case "/reg":   // Регистрация и авторизация
        require_once("./app/modules/app_reg.php");
        break;
    case "/reg-v1":
        require_once("./app/modules/app_reg_v1.php");
        break;
    case "/set-data":  // Отправка настроек приложением
        require_once("./app/modules/app_set-data.php");
        break;
    case "/set-data-v1":
        require_once("./app/modules/app_set-data_v1.php");
        break;
    case "/mail-confirm": // Подтверждение эл. почты
        require_once("./app/modules/app_mail-confirm.php");
        break;
    case "/confirm": // Страница подтверждения эл. почты
        require_once("./app/templates/mail-confirm.php");
        break;
    case "/get-confirm": // Проверить, подтверждён ли адрес эл. почты
        require_once("./app/modules/app_get-confirm.php");
        break;
//    case "/get-notifications":
//      require_once("./app/modules/app_notifications.php");
//      break;
//    case "/push-subscribe":
//      require_once("./app/modules/app_push_subscribe.php");
//      break;
//    case "/push-unsubscribe":
//      require_once("./app/modules/app_push_unsubscribe.php");
//      break;
//    case "/tg-subscribe":
//      require_once("./app/modules/app_tg_subscribe.php");
//      break;
//    case "/tg-unsubscribe":
//      require_once("./app/modules/app_tg_unsubscribe.php");
//      break;
    default:
        header("Location: http://zavodktm.ru/myapp");
        exit;
        break;

}

?>