<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/myapp/favicon.png" type="image/png">
    <title>Подтверждение аккаунта</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
        }

        .wraper {
            background: #430415;
            background: linear-gradient(180deg, #430415, #2c030d 61%, #0d0101);
            height: 100vh;
            padding: 2vh 4vw .5vh;
            text-align: center;
            color: #fff;
            font-family: Arial, sans-serif;
            font-size: 18px;
        }

        input {
            display: block;
            margin: 10px auto;
            color: #fff;
            background-color: #222;
            border: 1px solid #fff;
            border-radius: 3px;
            font-size: 18px;
            padding: 5px;
        }

        input::placeholder {
            color: #aaa;
        }

        button[type="submit"] {
            display: block;
            margin: 10px auto;
            color: #fff;
            background-color: #430415;
            border: 1px solid #fff;
            border-radius: 3px;
            padding: 5px;
            cursor: pointer;
            font-size: 18px;
        }

        #message {
            color: #ffd18f;
        }
    </style>
</head>
<body>
<div class='wraper'>
    <h1>Восстановление пароля</h1>
    <? if (!$_GET['hash'] || !$_GET['user']) { ?>
    <p>Страница не актуальна</p></div>
</body>
<? exit;
};
require 'DBConn/libs/rb-mysql.php';
require 'DBConn/dbconn.php';

if (!R::testConnection()) {
    echo "<p> Что-то пошло не так </p></div></body>";
    exit;
}

$hash = $_GET['hash'];
$id = $_GET['user'];

$confirm = R::findOne('confirms', 'userid = ?', [$id]);
$user = R::findOne('users', 'id = ?', [$id]);
if (!isset($confirm) || !isset($user)) { ?>
    <p>Страница не актуальна</p></div></body>
    <?
    exit;
} else {
    if (!password_verify($hash, $confirm->hash)) {
        ?>
        <p>Страница не актуальна</p></div></body>

        <? exit;
    } else {
        $user->not_confirm = false;
        $confirm->hash = null;
        R::store($confirm);
        R::store($user);
        ?>
        <p>Адрес электронной почты пользователя <b><?= $user->login ?></b> успешно подтверждён.
            Вернитесь в приложение для продолжения</p></div></body>
<? exit; }} ?>

</html>