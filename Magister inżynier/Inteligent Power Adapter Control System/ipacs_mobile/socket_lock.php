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
        if (isset($_GET["socket_id"]) and isset($_GET["lock"]))
        {
            $result = @$link->query('UPDATE `sockets` SET `socket_task_control` = "'.$_GET["lock"].'" WHERE `sockets`.`socket_id` = "'.$_GET["socket_id"].'"');
            mysqli_free_result($result);
        }
        
        $url = $_SERVER['HTTP_REFERER'].'#id-'.$_GET["task_id"];
        header('Location: '.$url);
    }

    $link->close();
    
?>