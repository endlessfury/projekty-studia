<?php
    require_once "mysql_connect.php";
    require_once 'session_login.php';


    $link = @new mysqli($host, $db_user, $db_password, $db_name);

    if ($link->connect_errno!=0)
    {
        echo "Error: ".$link->connect_errno;
    }
    else
    {
        echo 'TASK_id:'.$_GET["task_id"].', active: '.isset($_GET["active"]).', inactive:'.isset($_GET["inactive"]);
        if (isset($_GET["task_id"]) && isset($_GET["active"]))
        {
            $result = @$link->query('UPDATE `socket_tasks` SET `task_active` = "1" WHERE `socket_tasks`.`task_id` = '.$_GET["task_id"]);
        }
        else if (isset($_GET["task_id"]) && isset($_GET["inactive"]))
        {
            $result = @$link->query('UPDATE `socket_tasks` SET `task_active` = "0" WHERE `socket_tasks`.`task_id` = '.$_GET["task_id"]);
        }

        $url = $_SERVER['HTTP_REFERER'].'#id-'.$_GET["task_id"];
        header('Location: '.$url);
    }

    $link->close();
    
?>