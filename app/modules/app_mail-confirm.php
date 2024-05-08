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

$userId = $_POST['user_id'];
$user  = R::findOne( 'users', 'id = ?', [$userId]);
if (!isset($user)) {
  echo "err1";
	exit;
}

$to = $user -> email;
$login = $user->login;

$confirm = R::findOne( 'confirms', 'userid = ?', [$userId]);
if (!$confirm) {
    $confirm = R::dispense('confirms');
}
$hash = dechex(time()).md5(uniqid($to));
$confirm->hash = password_hash($hash, PASSWORD_DEFAULT);
$confirm->userid = $userId;
R::store($confirm);
$link = 'https://' . $_SERVER['HTTP_HOST'] . '/confirm' . '?user=' . $userId . '&hash=' . $hash;
$messageLink = "Для подтверждения электронной почты к аккаунту <b>" .$login. "</b> перейдите по ссылке: <a href=" . $link . ">$link</a>";

//создаём и отправляем сообщение
$subject = 'Подтверждение электронной почты';
$message = "<h3>Был получен запрос на подтверждение электронной почты Вашего аккаунта</h3>";

$message .= "<p>" . $messageLink . "</p>";
$message .= "Если Вы не запрашивали подтверждение электронной почты, то проигнорируйте данное сообщение";
$headers  = "Content-type: text/html; charset=utf-8 \r\n"; 
$headers .= "From: biomatic <biomatic@zavodktm.ru>\r\n"; 
$headers .= "Reply-To: " . $to . "\r\n"; 

//Отправка сообщения
$curl = curl_init();
$res = mail($to, $subject, $message, $headers); 

if (!$res) {
	echo "err2";
} else {
	echo json_encode(array('email' => $to));
}

?>