<?php
require 'DBConn/libs/rb-mysql.php';
//связываемся с БД
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection())
{
  echo "err0";
	exit;
}

$to = $_POST['recovery'];
//проверяем, верно ли указан емэйл
$users  = R::find( 'users', 'email = ?', [$to]);
if (!isset($users) || count($users) === 0 ) {
  echo "err1";
	exit;
}

$links = array();

// перебираем всех пользователей, зарегистрированных на данный емаил
foreach($users as $user) {
    $login = $user->login;
    $id = $user->id;
    $recovery = R::findOne( 'recovery', 'userid = ?', [$id]);
    if (!$recovery) {
        $recovery = R::dispense('recovery');
    }
    //генерируем хеш и вставляем в ссылку на восстановление
    $hash = dechex(time()).md5(uniqid($to));
    $recovery->hash = password_hash($hash, PASSWORD_DEFAULT);
    $recovery->userid = $id;
    R::store($user);
    R::store($recovery);
    $link = 'https://' . $_SERVER['HTTP_HOST'] . '/recovery' . '?user=' . $id . '&hash=' . $hash;
    $links[] = "Для восстановления доступа к аккаунту <b>" .$login. "</b> перейдите по ссылке: <a href=" . $link . ">$link</a>";
}

//создаём и отправляем сообщение
$subject = 'Восстановление пароля';
$message = "<h3>Был получен запрос на восстановления доступа к Вашeму аккаунту</h3>";
if (count($links) > 1) {
   $message .= "<h4>На данный email зарегистрировано несколько аккаунтов</h4>"; 
}
$message .= "<p>" . join("<br/>", $links) . "</p>";
$message .= "Если Вы не запрашивали восстановление доступа, то проигнорируйте данное сообщение";
$headers  = "Content-type: text/html; charset=utf-8 \r\n"; 
$headers .= "From: biomatic <biomatic@zavodktm.ru>\r\n"; 
$headers .= "Reply-To: " . $to . "\r\n"; 

//Отправка сообщения
$curl = curl_init();
$res = mail($to, $subject, $message, $headers); 

if (!$res) {
	echo "err2";
} else {
	echo "200";
}

?>