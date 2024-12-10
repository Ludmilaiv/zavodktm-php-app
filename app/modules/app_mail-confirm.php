<?php

	require 'DBConn/libs/rb-mysql.php';
//связываемся с БД
require 'DBConn/dbconn.php';

require 'vendors/PHPMailer/src/PHPMailer.php';
require 'vendors/PHPMailer/src/SMTP.php';
require 'vendors/PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;

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
$from = "Biomatic <info@zavodktm.ru>";

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
$messageLinkText = "Для подтверждения электронной почты к аккаунту " .$login. " перейдите по ссылке: ". $link;
$subject = 'Подтверждение электронной почты';

//создаём и отправляем сообщение
$mail = new PHPMailer();
$mail->CharSet = 'UTF-8';
$mail->setFrom('info@zavodktm.ru', 'Biomatic');
$mail->addAddress($to);
$mail->Subject = $subject;

$message = "<html><h3>Был получен запрос на подтверждение электронной почты Вашего аккаунта</h3>";
$message .= "<p>" . $messageLink . "</p>";
$message .= "Если Вы не запрашивали подтверждение электронной почты, то проигнорируйте данное сообщение";
$message .= "</html>";

$messageText = "Был получен запрос на подтверждение электронной почты Вашего аккаунта\n";
$messageText .= $messageLinkText;
$messageText .= "\nЕсли Вы не запрашивали подтверждение электронной почты, то проигнорируйте данное сообщение\n";

$mail->msgHTML($message);
$mail->AltBody = $messageText;

// Настройка DKIM подписи
$mail->DKIM_domain = 'zavodktm.ru';
$mail->DKIM_private = 'DBConn/mail.private';
$mail->DKIM_selector = 'mail';

$res = $mail->send();
if (!$res) {
	echo "err2";
} else {
	echo json_encode(array('email' => $to));
}

?>