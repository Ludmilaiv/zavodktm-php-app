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

$to = $_POST['recovery'];
//проверяем, верно ли указан емэйл
$users  = R::find( 'users', 'email = ?', [$to]);
if (!isset($users) || count($users) === 0 ) {
  echo "err1";
	exit;
}

$links = array();
$linksText = array();

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
	$linksText[] = "Для восстановления доступа к аккаунту " .$login. " перейдите по ссылке: " . $link;
}

$from = "Biomatic <info@zavodktm.ru>";
$subject = 'Восстановление пароля';
$boundary = uniqid('np');

//создаём и отправляем сообщение
$mail = new PHPMailer();
$mail->CharSet = 'UTF-8';
$mail->setFrom('info@zavodktm.ru', 'Biomatic');
$mail->addAddress($to);
$mail->Subject = $subject;

$messageText = "Был получен запрос на восстановления доступа к Вашeму аккаунту\n";
if (count($links) > 1) {
	$messageText .= "На данный email зарегистрировано несколько аккаунтов\n";
}
$messageText .= "\n" . join("\n", $linksText) . "\n";
$messageText .= "Если Вы не запрашивали восстановление доступа, то проигнорируйте данное сообщение";

$message = "<html><h3>Был получен запрос на восстановления доступа к Вашeму аккаунту</h3>";
if (count($links) > 1) {
   $message .= "<h4>На данный email зарегистрировано несколько аккаунтов</h4>"; 
}
$message .= "<p>" . join("<br/>", $links) . "</p>";
$message .= "Если Вы не запрашивали восстановление доступа, то проигнорируйте данное сообщение";
$message .= "</html>";

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
	echo "200";
}

?>