<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="/myapp/favicon.png" type="image/png">
        <title>Восстановление пароля</title>
        <style>
            body,html {
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
        <? if (!$_GET['hash']) { ?>
            <p>Страница не актуальна</p>
        <? };
        require 'DBConn/libs/rb-mysql.php';
        require 'DBConn/dbconn.php';
    
        if (!R::testConnection())
        {
          echo "<p> Что-то пошло не так </p></div></body>";
          exit;
        }
        
        $hash = $_GET['hash'];
    
        $user  = R::findOne( 'users', 'hash = ?', [$hash]);
        if (!isset($user)) { ?>
          <p>Страница не актуальна</p></div></body>
        <? 
            exit;
        }; ?>
        
        <form id="password-form">
            <label>
            Введите новый пароль
            </label>
            <input type="password" name=password id="password" placeholder="Новый пароль" required>
            <input type="password" id="password_1" placeholder="Повторите пароль" required>
            <span id='message'></span>
            <button type="submit">Сохранить пароль</button>
        </form>
    </div>
</body>

<script>
    const password = document.getElementById('password');
    const password1 = document.getElementById('password_1');
    const form = document.getElementById('password-form');
    const message = document.getElementById('message');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (password.value.length < 6) {
            message.innerHTML = 'Пароль слишком короткий';
            return;
        }
        if (password.value !== password1.value) {
            message.innerHTML = 'Введённые пароли не совпадают';
            return;
        }
        const body = JSON.stringify({
            password: password.value,
            hash: "<?= $hash ?>"
        });
        
        message.innerHTML = 'Подождите...'
        
        let response = await fetch('/edit-pass', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json;charset=utf-8'
          },
          body
        });
        
        const result = await response.json();
        if (result == 1) {
            form.innerHTML = 'Пароль для <b><?= $user->login ?></b> успешно изменён.<br/>Можете вернуться в приложение и войти под новым паролем';
        } else {
            message.innerHTML = 'Что-то пошло не так. Попробуйте позже';
        }
    })
    
    document.addEventListener('input', function(e) {
        message.innerHTML = '';
    })
    
</script>
</html>