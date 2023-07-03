<?php 
$title="Форма регистрации";
require 'header.php';
require "db-connect.php";
// Создаем переменную для сбора данных от пользователя по методу POST
$data = $_POST;


// Пользователь нажимает на кнопку "Зарегистрировать" и код начинает выполняться
if(isset($data['do_signup'])) {
	$login = $data['login'];
	$pass = md5($data['password']);

        // Регистрируем
        // Создаем массив для сбора ошибок
	$errors = array();

	// Проводим проверки
        // trim — удаляет пробелы (или другие символы) из начала и конца строки
	if(trim($data['login']) == '') {

		$errors[] = "Введите логин!";
	}

	if($data['password'] == '') {

		$errors[] = "Введите пароль!";
	}

	if($data['password_2'] != $data['password']) {

		$errors[] = "Повторный пароль введен не верно!";
	}
         // функция mb_strlen - получает длину строки
        // Если логин будет меньше 5 символов и больше 90, то выйдет ошибка
	if(mb_strlen($data['login']) < 5 || mb_strlen($data['login']) > 90) {

	    $errors[] = "Недопустимая длина логина";

    }

    if (mb_strlen($data['password']) < 2 || mb_strlen($data['password']) > 8){
	
	    $errors[] = "Недопустимая длина пароля (от 2 до 8 символов)";

    }

	if(empty($errors)) {
        $query = "INSERT INTO `crm_users` (username) VALUES ('$login')";
		$add_login = $conn->query($query);

		$get_id = "SELECT `id` FROM `crm_users` WHERE `username` = '$login'";
		$get_id = $conn->query($get_id);
		$fetchid = $get_id->fetch_array();
		$id = $fetchid['id'];
		
		$add_pass = "INSERT INTO `passwords_crm`(`user_id`, `password`) VALUES ('$id','$pass')";
		if($conn -> query($add_pass)){
			echo '<div style="color: green; ">Вы успешно зарегистрированы! Можно <a href="login.php">авторизоваться</a>.</div><hr>';
		} else{
			echo "Ошибка: ". $conn->error;
		}

	} else {
                // array_shift() извлекает первое значение массива array и возвращает его, сокращая размер array на один элемент. 
		echo '<div style="color: red; ">' . array_shift($errors). '</div><hr>';
	}
}
?>

<div class="container mt-4">
		<div class="row">
			<div class="col">
	   <!-- Форма регистрации -->
		<h2>Форма регистрации</h2>
		<form action="signup.php" method="post">
			<input type="text" class="form-control" name="login" id="login" placeholder="Введите логин"><br>
			<input type="password" class="form-control" name="password" id="password" placeholder="Введите пароль"><br>
			<input type="password" class="form-control" name="password_2" id="password_2" placeholder="Повторите пароль"><br>
			<button class="btn btn-success" name="do_signup" type="submit">Зарегистрировать</button>
		</form>
		<br>
		<p>Если вы зарегистрированы, тогда нажмите <a href="login.php">здесь</a>.</p>
			</div>
		</div>
	</div>