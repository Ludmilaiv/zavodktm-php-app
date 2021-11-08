<?php
require 'DBConn/libs/rb-mysql.php';
//связываемся с БД
require 'DBConn/dbconn.php';

$_POST = json_decode(file_get_contents('php://input'), true);

if (!R::testConnection())
//если не связались с бд, то выводим в сессию ошибку и перезагружаем страничку
{
  echo "err0";
	exit;
}

$to = $_POST['recovery'];
//проверяем, верно ли указан емэйл
$user  = R::findOne( 'users', 'email = ?', [$to]);
if (!isset($user)) {
  echo "err1";
	exit;
}

$login = $user->login;

//создаём и отправляем сообщение
$subject = 'Восстановление пароля';
$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
$new_pas = substr(str_shuffle($permitted_chars), 5, 7);
$message = "<h3>Данные для доступа к Вашему аккаунту:</h3>";
$message .= "<b>Логин:</b> ".$login."<br>";
$message .= "<b>Новый пароль:</b> ".$new_pas."<br>";
$message .= "Этот пароль сгенерирован системой. Вы можете сменить его после входа в личный кабинет<br>";

//Отправка сообщения
$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://rapidprod-sendgrid-v1.p.rapidapi.com/mail/send",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => "{\r
    \"personalizations\": [\r
        {\r
            \"to\": [\r
                {\r
                    \"email\": \"".$to."\"\r
                }\r
            ],\r
            \"subject\": \"".$subject."\"\r
        }\r
    ],\r
    \"from\": {\r
        \"email\": \"zavodktm<help@zavodktm.ru>\"\r
    },\r
    \"content\": [\r
        {\r
            \"type\": \"text/html\",\r
            \"value\": \"".$message."\"\r
        }\r
    ]\r
}",
	CURLOPT_HTTPHEADER => [
		"content-type: application/json",
		"x-rapidapi-host: rapidprod-sendgrid-v1.p.rapidapi.com",
		"x-rapidapi-key: 5c701f46camsh42b00ea2ac3661ep117609jsn2d27bc357506"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	echo "err2";
} else {
  $user->password = $new_pas;
  R::store($user);
	echo "200";
}

?>