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
        if (isset($_GET["task_id"]))
        {
            $result = @$link->query('UPDATE `socket_tasks` SET `task_state` = "0", `task_cancel` = "Anulowano: '.date('H:i').', '.date('d-m-Y').'" WHERE `socket_tasks`.`task_id` = '.$_GET['task_id']);
            //echo '<br>'.'UPDATE `socket_tasks` SET `task_state` = "0", `task_cancel` = "'.date('H:i').','.date('d-m-Y').'" WHERE `socket_tasks`.`task_id` = '.$_GET['task_id'];

            $result = @$link->query('SELECT * FROM `socket_tasks` WHERE `task_id` = "'.$_GET["task_id"].'"');
            $row = $result->fetch_assoc();
            $socketID = $row['socket_id'];
            $result->free();

            $result = @$link->query('UPDATE `sockets` SET `socket_task_control` = "0" WHERE `sockets`.`socket_id` = "'.$socketID.'"');

            $url = $_SERVER['HTTP_REFERER']."#tasks";
            header('Location: '.$url);
        }
    }

    $link->close();
    
?>