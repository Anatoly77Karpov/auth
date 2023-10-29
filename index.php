<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Авторизация</title>
	<link rel="stylesheet" href="styles.css" />
</head>
<body>
<?php
	session_start();

	if (!empty($_SESSION['auth'])) {//если пользователь авторизован

		//отображается логин, счётчик и кнопки +1 и Exit
		echo <<<EOT
		<table>
			<tr><td align="right">
				<span style="font-size:20px;">login: </span>
				<span style="font-size:20px; color:blue;">{$_SESSION['login']}</span>
			</td></tr>

			<tr><td align="center"><div style="font-size:144px;">{$_SESSION['counter']}</div></td></tr>

			<tr><td>
				<div class="form">
					<form action="" method="POST">
						<button type="submit" name="plus" value="1" style="font-size:24px;"> +1 </button>
						<button type="submit" name="exit" value="1" style="font-size:24px;"> Exit </button>
					</form>
				</div>
			</td></tr>
		</table>
		EOT;

		//увеличение +1 счётчика и переадресация
		if (!empty($_POST['plus'])) {
			$_SESSION['counter']++;
			header('Location: /');
			die();
		}

		//выход из авторизации и переадресация на исходную страницу с формой
		if (!empty($_POST['exit'])) {
			$_SESSION['auth'] = null;
			header('Location: /');
			die();
		}

	} else {//если пользователь не авторизован

		if (!empty($_POST['login'])) {//если была заполнена и отправлена форма

			$link = mysqli_connect('localhost', 'root', '', 'test');
			mysqli_query($link, "SET NAMES 'utf8'");

			//запрос в БД с указанным логином
			$query = "SELECT * FROM users WHERE login='" . $_POST['login'] . "'";
			$res = mysqli_query($link, $query) or die(mysqli_error($link));
			$user = mysqli_fetch_assoc($res);

			if (empty($user) or !password_verify($_POST['password'], $user['password'])) {

				//если пользователь с таким логином не найден или пароль не верен
				echo '<p>Invalid data, try again!</p>' . '<br>';
				unset($_POST);

				//отображаем форму авторизации
				echo <<<EOT
				<div class="form">
					<form action="" method="POST">
						<input type="text" name="login" placeholder="Type in username" required><br>
						<input type="password" name="password" placeholder="Type in password" required><br>
						<input type="submit" name="auth" value="Enter">
					</form>
					<a href="register.php"><button>Register</button></a>
				</div>
				EOT;

			} else {//если указанные логин и пароль верны

				//сохранение пометки об авторизации и логин в сессии
				$_SESSION['auth'] = true;
				$_SESSION['counter'] = 0;
				$_SESSION['login'] = $_POST['login'];
				header('Location: /');
				die();

			}
		} else {//если форма не была заполнена

			echo <<<EOT
			<table>
			<tr><td>
				<form action="" method="POST">
					<input type="text" name="login" placeholder="Type in username" required><br>
					<input type="password" name="password" placeholder="Type in password" required><br>
					<input type="submit" name="auth" value="Enter">
				</form>
			</td></tr>
			<tr><td align="right"><a href="register.php"><button>Register</button></a></td></tr>
			</table>
			EOT;

		}
	}
?>
</body>
</html>