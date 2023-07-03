<?php
session_start();
$title="Форма авторизации"; // название формы
$connpath = '/var/www/u0941340/data/www/dev.rea.hrel.ru/NSS/To-do-list/app1/conn.php';
require $connpath; // подключаем файл для соединения с БД
// Создаем переменную для сбора данных от пользователя по методу POST
$data = $_POST;
$log = null;


// Пользователь нажимает на кнопку "Авторизоваться" и код начинает выполняться
if(isset($data['do_login'])) {
	$login = $data['login'];
	$pass = md5($data['password']);
 // Проводим поиск пользователей в таблице
    $query = "SELECT * FROM `crm_users` WHERE username = '$login'";
    $result = $connection->query($query);
	$user_id = $result -> fetch_array();
	$user_id = $user_id['id'];
	$_SESSION['id'] = $user_id;
	$passcheck = "SELECT * FROM `passwords_crm` WHERE password = '$pass'";
	$checkpass = $connection->query($passcheck);
    if($result -> num_rows > 0 AND $checkpass-> num_rows > 0){
		$_SESSION['auth'] = true;
		$_SESSION["login"] = $login;
		$log = $_SESSION["login"];
		$connection->query("INSERT INTO `login_logs_crm`(`user_id`) VALUES ('$user_id')");
		header('Location: https://dev.rea.hrel.ru/NSS/crm_system/index.php');
    }
	else{
		echo "Неверный логин или пароль!";
	}
}
?>
<?php require 'header.php'; ?>

<div class="container mt-4">
		<div class="row">
			<div class="col">
		<!-- Форма авторизации -->
		<h2>Форма авторизации</h2>
		<form action="login.php" method="post">
			<input type="text" class="form-control" name="login" id="login" placeholder="Введите логин" required><br>
			<input type="password" class="form-control" name="password" id="pass" placeholder="Введите пароль" required><br>
			<button class="btn btn-success" name="do_login" type="submit">Авторизоваться</button>
		</form>
		<br>
		<p>Если вы еще не зарегистрированы, тогда нажмите <a href="signup.php">здесь</a>.</p>
			</div>
		</div>
	</div>
