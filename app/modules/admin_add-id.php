<?php
	require 'DBConn/libs/rb-mysql.php';
	require 'DBConn/dbconn.php';

	$_POST = json_decode(file_get_contents('php://input'), true);

	// Функция генерации капчи
	function generate_code()
	{
		$chars = 'abdefhknrstyz23456789'; // Задаем символы, используемые в капче. Разделитель использовать не надо.
		$length = rand(4, 7); // Задаем длину капчи, в нашем случае - от 4 до 7
		$numChars = strlen($chars); // Узнаем, сколько у нас задано символов
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, rand(1, $numChars) - 1, 1);
		} // Генерируем код

		// Перемешиваем, на всякий случай
		$array_mix = preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);
		srand ((float)microtime()*1000000);
		shuffle ($array_mix);
		// Возвращаем полученный код
		return implode("", $array_mix);
	}

	function img_code($code) // $code - код нашей капчи, который мы укажем при вызове функции
	{
		// Отправляем браузеру Header'ы
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", 10000) . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Content-Type:image/png");
		$img_dir = "https://zavodktm.ru/captcha/";
		$linenum = rand(2, 4); // Количество линий
		$img_arr = array(
		  "1.png", "2.png"
		);
		// Шрифты для капчи. Задавать можно сколько угодно, они будут выбираться случайно
		$font_arr = array();
		$font_arr[0]["fname"] = "droidsans.ttf";	// Имя шрифта
		$font_arr[0]["size"] = rand(20, 30);				// Размер в pt
		// Генерируем "подстилку" для капчи со случайным фоном
		$n = rand(0,sizeof($font_arr)-1);
		$img_fn = $img_arr[rand(0, sizeof($img_arr)-1)];
		$im = imagecreatefrompng ($img_dir . $img_fn);
		//$im =  imageCreateTrueColor(200, 70);
		// Рисуем линии на подстилке
		for ($i=0; $i<$linenum; $i++)
		{
			$color = imagecolorallocate($im, rand(0, 150), rand(0, 100), rand(0, 150)); // Случайный цвет c изображения
			imageline($im, rand(0, 20), rand(1, 50), rand(150, 180), rand(1, 50), $color);
		}
		$color = imagecolorallocate($im, rand(0, 200), 0, rand(0, 200)); // Опять случайный цвет. Уже для текста.

		// Накладываем текст капчи
		$x = 0;
		for($i = 0; $i < strlen($code); $i++) {
			$x+=15;
			$letter=substr($code, $i, 1);
			putenv('GDFONTPATH=' . realpath('.'));
			imagefttext ($im, $font_arr[$n]["size"], rand(2, 4), $x, rand(50, 55), $color, "droidsans", $letter);
		}

		// Опять линии, уже сверху текста
		for ($i=0; $i<$linenum; $i++)
		{
			$color = imagecolorallocate($im, rand(0, 255), rand(0, 200), rand(0, 255));
			imageline($im, rand(0, 20), rand(1, 50), rand(150, 180), rand(1, 50), $color);
		}
		// Возвращаем получившееся изображение
		ImagePNG ($im);
		ImageDestroy ($im);
	}

	if (!R::testConnection()) {
		echo "err0";
		exit;
	}

	if (!isset($_POST['user_id']) || !isset($_POST['token']) || !isset($_POST['dev_id']) || !isset($_POST['type'])) {
		echo "err1";
		exit;
	}

	$id = $_POST['user_id'];
	$token = $_POST['token'];
	$page = $_POST['page'];
	$dev_id = strtoupper($_POST["dev_id"]);

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

	$user = R::load('users', $id);
	if (!$user || $user->isadmin == 0) {
		echo "err";
		exit;
	}

	$session = R::findOne('sessions', 'id = ?', [$sessionID]);

	$session->lastaccesstime = $datetime->getTimestamp();
	R::store($session);

	//проверяем не существует ли уже такое устройство
	$dev  = R::findOne( 'devices', 'my_id = ?', [$dev_id]);
	if (isset($dev))
	{
		echo "err1";
		exit;
	}

	if (isset($_POST['getcaptcha'])) {
		$captcha = generate_code();
		$user['capcha'] = $captcha;
		R::store($user);
		img_code($captcha);
		exit;
	}

	if ($user['capcha_time']
	  && $datetime->getTimestamp() - $user['capcha_time'] < 60 * 60
	  && $user['capcha_count'] >= 3) {
		if (!isset($_POST['captcha']) || $_POST['captcha'] != $user->capcha) {
			echo "captcha";
			exit;
		}
	}

	$device = R::dispense('devices');
	$device->my_id = $dev_id;
	$device->type = $_POST['type'];
	R::store($device);

	$device  = R::findOne( 'devices', 'my_id = ?', [$dev_id]);
	if (!isset($device))
	{
		echo "err0";
		exit;
	}

	if (!$user['capcha_time'] || $datetime->getTimestamp() - $user['capcha_time'] >= 60 * 60)
	{
		$user['capcha_count'] = 0;
		$user['capcha_time'] = $datetime->getTimestamp();
	} elseif (!isset($user['capcha_count']))
	{
		$user['capcha_count'] = 0;
	}
	$user['capcha_count'] = $user['capcha_count'] + 1;
	$user['capcha'] = null;
	R::store($user);

	echo "1";