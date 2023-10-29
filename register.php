<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Регистрация</title>
	<link rel="stylesheet" href="styles.css" />
</head>
<body>
	<div class="form">
		<?php
		session_start();

		if (empty($_SESSION['auth'])) {//если пользователь ещё не авторизован

			if (!empty($_POST)) {//если форма отправлена

				//подключение к базе
				$link = mysqli_connect('localhost', 'root', '', 'test');
				mysqli_query($link, "SET NAMES 'utf8'");
	
				//проверка логина на дубль
				$query = "SELECT * FROM users WHERE login='" . $_POST['login'] . "'";
				$res = mysqli_query($link, $query) or die(mysqli_error($link));
				$user = mysqli_fetch_assoc($res);

				$diff = date_diff(date_create(date('Y-m-d', time())), date_create($_POST['birthdate']));

				if (empty($user) and $diff->invert and $diff->y>=18 and $diff->y<=65) {

					//если логин свободен и ДР прошёл валидациию - добавление пользователя в БД с хэшированием пароля
					$hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
					$values = "'" . $_POST['login'] . "', '" . $_POST['birthdate'] . "', '" . $hash . "'";

					$query ="INSERT INTO users (`login`, birthdate, `password`) VALUES (" . $values . ")";
					mysqli_query($link, $query) or die(mysqli_error($link));
					//echo '<p>Added user: ' . $_POST['login'] . '</p>';

					//авторизация и переадресация на главную
					$_SESSION['auth'] = true;
					$_SESSION['counter'] = 0;
					$_SESSION['login'] = $_POST['login'];
					header('Location: /');
					die();

				} else {

					if (!empty($user)) {//уже есть такой логин в БД
						echo '<p>This login is taken!</p>';
					} elseif (!$diff->invert) {//ДР не может быть позже текущей даты
						echo '<p>Kidding?</p>';
					} elseif ($diff->y < 18) {//не моложе 18 лет
						echo '<p>Too young!</p>';
					} elseif ($diff->y > 65) {//не старше 65 лет
						echo '<p>Too old!</p>';
					}

					//вывод формы с заполненными ранее данными, кроме пароля
					echo <<<EOT
					<form action="" method="POST">
						<label>Username:<br><input type="text" name="login" value="{$_POST['login']}" required></label><br>
						<label>Birthdate:<br><input type="date" name="birthdate" value="{$_POST['birthdate']}" required></label><br>
						<label>Password:<br><input type="password" name="password" required></label><br>
						<input type="submit" name="auth" value="Submit">
					</form>
					EOT;
				}

			} else {//если форма ещё не отправлена

				echo <<<EOT
				<form action="" method="POST">
					<label>Username:<br><input type="text" name="login" required></label><br>
					<label>Birthdate:<br><input type="date" name="birthdate" required></label><br>
					<label>Password:<br><input type="password" name="password" required></label><br>
					<input type="submit" name="auth" value="Submit">
				</form>
				EOT;
				
			}
		} else {//если авторизованный пользователь случайно попадает на страницу регистрации

			header('Location: /');
			die();
		
		}

		?>
	</div>
</body>
</html>