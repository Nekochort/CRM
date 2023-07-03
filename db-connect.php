<?php

include_once("config.php");

$conn = mysqli_connect($DB["host"], $DB["login"], $DB["password"], $DB["name"]);

mysqli_query($conn, "SET NAMES '" . $DB['charset'] . "_unicode_ci';");
mysqli_query($conn, "SET CHRACTER SET '" . $DB['charset'] . "_unicode_ci';");
mysqli_query($conn, "SET timezone = '" . TIME_ZONE . "';");
mysqli_query($conn, "SET group_concat_max_len = 9999999;");

if(mysqli_connect_errno()){
    echo ajax_echo(
        "Ошибка!",
        // заголовок
        "Нет ГЕТ параметра token", // описание ответа 
        true,
        "ERROR",
        null
    );
   
    exit();
}

if(!$conn->set_charset($DB['charset'])){
    echo ajax_echo(
        "Ошибка!",
        // заголовок
        "Нет ГЕТ параметра token",  
        true,
        "ERROR",
        null
    );
   

exit();
}