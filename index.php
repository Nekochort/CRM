<?php

session_start();
$log = $_SESSION["login"];
$uid = $_SESSION["id"];

function get_connection(){
  $host = 'localhost';
  $db   = 'u0941340_nss';
  $user = 'u0941340_nss';
  $pass = 'rH9dM1zK1u';
  $charset = 'utf8';

  $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
  $conn = new PDO($dsn, $user, $pass);
  return $conn;
}

function save_task($type, $task, $id){
  $conn = get_connection();
  if($id){
    $sql = "UPDATE kaban_board SET `task`=? WHERE id=?"; // create sql
    $query = $conn->prepare($sql); // prepare
    $query->execute([$task, $id]); // execute
    return $id;
  }else{
    $sql = "INSERT INTO kaban_board(`task`,`type`) VALUES (?,?)"; // create sql
    $query = $conn->prepare($sql); // prepare
    $query->execute([$task,$type]); // execute
    return $conn->lastInsertId();
  }
}
$role_check="SELECT role_id FROM crm_users WHERE username = '$log'";
$conn = get_connection();
$checkrole = $conn->prepare($role_check);
$checkrole ->execute();
$res = $checkrole->fetch(PDO::FETCH_BOTH);
$role = $res['role_id'];

function move_task($id, $position){
  $conn = get_connection();
  $sql = "UPDATE kaban_board SET `type`=? WHERE id=?"; // create sql
  $query = $conn->prepare($sql); // prepare
  $query->execute([$position,$id]); // execute
}

function get_tasks($type){
  $results = [];
  try {
    $conn = get_connection();
    $query = $conn->prepare("SELECT * from kaban_board WHERE type=? order by id desc");
    $query->execute([$type]);
    $results = $query->fetchAll();
  }catch (Exception $e){

  }
  return $results;
}

function get_task($id){
  $results = [];
  try {
    $conn = get_connection();
    $query = $conn->prepare("SELECT name, date_of_addiction from kaban_board WHERE id=?");
    $query->execute([$id]);
    $results = $query->fetchAll();
    $results = $results[0];
  }catch (Exception $e){

  }
  return $results;
}


function show_tile($taskObject, $type=""){
  $baseUrl = $_SERVER["PHP_SELF"]."?shift&id=".$taskObject["id"]."&type=";
  $editUrl = $_SERVER["PHP_SELF"] . "?edit&id=".$taskObject["id"]."&type=". $type;
  $deleteUrl = $_SERVER["PHP_SELF"] . "?delete&id=".$taskObject["id"];
  // var_dump($taskObject);
  $o = '<span class="board">'.$taskObject["task"]."<br>".date( "d.m.Y H:i:s", strtotime($taskObject["date_of_addition"])).'
      <hr>
      <span>
        <a href="'.$baseUrl.'backlog">Неприоритетные</a> |
        <a href="'.$baseUrl.'pending">Ожидание</a> |
        <a href="'.$baseUrl.'progress">В процессе</a> |
        <a href="'.$baseUrl.'completed">Готово</a> |
      </span>
      <a href="'.$editUrl.'">Изменить</a> | <a href="'.$deleteUrl.'">Удалить</a>
      </span>';
  return $o;
}

function show_tile_for_reader($taskObject, $type=""){
  $baseUrl = $_SERVER["PHP_SELF"]."?shift&id=".$taskObject["id"]."&type=";
  $editUrl = $_SERVER["PHP_SELF"] . "?edit&id=".$taskObject["id"]."&type=". $type;
  $deleteUrl = $_SERVER["PHP_SELF"] . "?delete&id=".$taskObject["id"];
  $o = '<span class="board">'.$taskObject["task"];
  return $o;
}

function get_active_value($type, $content){
  $currentType = isset($_GET['type']) ? $_GET['type']:  null;
  if($currentType == $type){
    return $content;
  }
  return "";
}


$activeId = "";
$activeTask = "";


if(isset($_GET['shift'])){
  $id = isset($_GET['id']) ? $_GET['id'] : null;
  $type = isset($_GET['type']) ? $_GET['type'] : null;
  if($id){
    move_task($id, $type);
    header("Location: ". $_SERVER['PHP_SELF']);
    exit();
  }else{
    // redirect take no action.
    header("Location: ". $_SERVER['PHP_SELF']);
  }
}

if(isset($_GET['edit'])){
  $id = isset($_GET['id']) ? $_GET['id'] : null;
  $activeId = $id;
  $type = isset($_GET['type']) ? $_GET['type'] : null;
  if($id){
    $taskObject = get_task($id);
    $activeTask = @$taskObject["task"];
    $add_log_edit = $conn->prepare("INSERT INTO `edit_logs_crm`(`user_id`) VALUES ('$uid')");
    $add_log_edit -> execute();
  }
  
}

if(isset($_GET['delete'])){
  $id = isset($_GET['id']) ? $_GET['id'] : null;
  if($id){
    try {
      $conn = get_connection();
      $query = $conn->prepare("DELETE from kaban_board WHERE id=?");
      $query->execute([$id]);
      header("Location: ". $_SERVER['PHP_SELF']);
    }catch (Exception $e){

    }
  }
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

  $backlog = "";
  $pending = "";
  $progress = "";
  $completed = "";
  $taskId = isset($_POST['task']) ? $_POST['task'] : null;

  if(isset($_POST['save-backlog'])){
    $backlog = isset($_POST['backlog']) ? $_POST['backlog'] : null;
    save_task('backlog', $backlog, $activeId);
    $add_log = $conn->prepare("INSERT INTO `add_log_crm`(`user_id`) VALUES ('$uid')");
    $add_log -> execute();

  }else if(isset($_POST['save-pending'])){
    $pending = isset($_POST['pending']) ? $_POST['pending'] : null;
    save_task('pending', $pending, $activeId);
    $add_log = $conn->prepare("INSERT INTO `add_log_crm`(`user_id`) VALUES ('$uid')");
    $add_log -> execute();
  }else if(isset($_POST['save-progress'])){
    $progress = isset($_POST['progress']) ? $_POST['progress'] : null;
    save_task('progress', $progress, $activeId);
    $add_log = $conn->prepare("INSERT INTO `add_log_crm`(`user_id`) VALUES ('$uid')");
    $add_log -> execute();
  }else if(isset($_POST['save-completed'])){
    $completed = isset($_POST['completed']) ? $_POST['completed'] : null;
    save_task('completed', $completed, $activeId);
    $add_log = $conn->prepare("INSERT INTO `add_log_crm`(`user_id`) VALUES ('$uid')");
    $add_log -> execute();
  }
  if($activeId){
    header("Location: ". $_SERVER['PHP_SELF']);
  }

}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>CRM</title>
</head>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&display=swap');
</style>
<style>
   @font-face {
    src: url('https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&display=swap'); /* Путь к файлу со шрифтом */
   }
   h1, h2, h3 {
    font-family: 'Manrope', sans-serif;
   }
  </style>
<link rel="stylesheet" href="crm.css">
<body>

<h1 style="text-align: center; color: white">CRM-система by Nekochort</h1>
<h2 style="text-align: center; color: white"><?php echo("Добро пожаловать, $log!")?></h2>
<h3 style="text-align: center; "><a href="https://dev.rea.hrel.ru/NSS/crm_system/login.php" style="color: white"><?php session_abort(); ?> Выйти из аккаунта</a></h3>
<div class="bottom" style="margin-left: 150px;">
  <form method="post">
    <input type="hidden" value="<?php echo $activeId;?>" name="task"/>
  <div class="board-column">
    <h3>Неприоритетные задачи</h3>
    <div class="board-form">
    <?php if($role == 1){?>
            <input value='<?php echo get_active_value('backlog', $activeTask);?>' type='text' name='backlog' style='height: 30px; width: 70%' autocomplete='off'/>
            <button type='submit' name='save-backlog'>Сохранить</button>
    <?php }?>
    </div>
    <div class="board-items">
      <?php foreach (get_tasks('backlog') as $task):?>
          <?php if($role == 1){
            echo show_tile($task,'backlog');
          }
          elseif($role == 2){
            echo show_tile_for_reader($task,'backlog');
          }?>
      <?php endforeach;?>
    </div>
  </div>

  <div class="board-column">
    <h3>Задачи в ожидании</h3>
    <div class="board-form">
    <?php if($role == 1){?>
            <input value='<?php echo get_active_value('pending', $activeTask);?>' type='text' name='pending' style='height: 30px; width: 70%' autocomplete='off'/>
            <button type='submit' name='save-pending'>Сохранить</button>
    <?php }?>
    </div>
    <div class="board-items">
      <?php foreach (get_tasks('pending') as $task):?>
        <?php if($role == 1){
            echo show_tile($task,'pending');
          }
          elseif($role == 2){
            echo show_tile_for_reader($task,'pending');
          }?>
      <?php endforeach;?>
    </div>
  </div>

  <div class="board-column">
    <h3>Задачи в процессе</h3>
    <div class="board-form">
    <?php if($role == 1){?>
            <input value="<?php echo get_active_value("progress", $activeTask);?>" type="text" name="progress" style="height: 30px; width: 70%" autocomplete="off"/>
            <button type="submit" name="save-progress"  >Сохранить</button>
          <?php }?>
    </div>
    <div class="board-items">
      <?php foreach (get_tasks('progress') as $task):?>
        <?php if($role == 1){
            echo show_tile($task,'progress');
          }
          elseif($role == 2){
            echo show_tile_for_reader($task,'progress');
          }?>
      <?php endforeach;?>
    </div>
  </div>

  <div class="board-column">
    <h3>Выполненные задачи</h3>
    <div class="board-form">
    <?php if($role == 1){?>
            <input value="<?php echo get_active_value("completed", $activeTask);?>" type="text" name="completed" style="height: 30px; width: 70%" autocomplete="off"/>
            <button type="submit" name="save-completed">Сохранить</button>
          <?php }?>
    </div>
    <div class="board-items">
      <?php foreach (get_tasks('completed') as $task):?>
        <?php if($role == 1){
            echo show_tile($task,'completed');
          }
          elseif($role == 2){
            echo show_tile_for_reader($task,'completed');
          }?>
      <?php endforeach;?>
    </div>
  </div>
  </form>
</div>

</body>
</html>