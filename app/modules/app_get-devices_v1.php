<?php
require 'DBConn/libs/rb-mysql.php';
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection()) {
    echo "err0";
    exit;
}

if (!isset($_POST['userID']) || !isset($_POST['token'])) {
    echo "err1";
    exit;
}

$id = $_POST['userID'];
$token = $_POST['token'];

$datetime = new DateTime(); // получаем дату и время в Unix-формате

$sessions = R::find('sessions', 'userid = ?', [$id]);
if (empty($sessions)) {
    echo "err5";
    exit;
}
$auth = false;
$sessionID = 0;
foreach ($sessions as $session) {
    if (password_verify($token, $session->token)) {
        $auth = true;
        $sessionID = $session->id;
        break;
    }
}
if (!$auth) {
    echo "err5";
    exit;
}

$session = R::findOne('sessions', 'id = ?', [$sessionID]);
$session->lastaccesstime = $datetime->getTimestamp();
R::store($session);

//находим все устройства пользователя
$user = R::load('users', $id);
$dev = R::find('usersdevices', 'user_login = ?', [$user->login]);

$array_dev = array();

$timeout = 300; //таймаут после окончания которого устройство считается оффлайн

foreach ($dev as $i) {
    // Получаем идентификатор записи устройства в БД
    $device = R::findOne('devices', 'my_id = ?', [$i['my_device_id']]);
    // Получаем температуру по этому идентификатору
    $temps = R::findOne('temps', 'device_id = ?', [$device->id]);
    $sets = R::findOne('sets', 'device_id = ?', [$device->id]);
    $temp = -1000;
    $icon = "";
    if (!isset($temps) || !isset($sets)) {
        $temp = -3000;
    } else {
        $datetime_ms = $datetime->getTimestamp();
        $delta_connect_time = $datetime_ms - $temps->datetime;  // сколько миллисекунд назад устройство в последний раз засылало данные
        if ($delta_connect_time >= $timeout) {   //если давно, то отправляем оффлайн
            $temp = -3000;
        } else {
            $temp = $temps->t2;
            if ($temps->t1 >= 5) {
                $icon = "service-block";
            } elseif ($sets->s63 > 0) {
                $icon = "danger";
            } elseif ($temps->t2 == -1270 || $temps->t3 == -1270 || $temps->t4 == -1270 || $temps->t5 == -1270
              || $temps->t6 == -1270 || $temps->t7 == -1270 || $temps->t8 == -1270) {
                $icon = "warning";
            }
        }
    }
    $d = [
        "name" => $i['device_name'],
        "id" => $i['my_device_id'],
        "temp" => $temp,
        "icon" => $icon,
    ];
    array_push($array_dev, $d);
}

echo json_encode($array_dev);

?>
