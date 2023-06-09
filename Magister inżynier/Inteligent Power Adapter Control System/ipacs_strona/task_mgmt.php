<?php
    require_once 'session_login.php';
    require_once "mysql_connect.php";

    $link = @new mysqli($host, $db_user, $db_password, $db_name);

    if ($link->connect_errno!=0)
    {
        echo "Error: ".$link->connect_errno;
    }
    else
    {
        if (isset($_GET["adapter_id"]) && isset($_GET["cancel"]))
        {
            //
            //echo '<br>'.'UPDATE `socket_tasks` SET `task_state` = "0", `task_cancel` = "'.date('H:i').','.date('d-m-Y').'" WHERE `socket_tasks`.`task_id` = '.$_GET['task_id'];

            $result = @$link->query('SELECT socket_id FROM `socket_tasks` WHERE `task_state` = "1" AND `adapter_id` = "'.$_GET["adapter_id"].'"');
            while($row = $result->fetch_assoc())
            {
                $socketID[] = $row['socket_id'];
            }
            $result->free();

            foreach($socketID as $socket)
            {
                $result = @$link->query('UPDATE `sockets` SET `socket_task_control` = "0" WHERE `sockets`.`socket_id` = "'.$socket.'"');
            }

            $result = @$link->query('UPDATE `socket_tasks` SET `task_state` = "0", `task_cancel` = "Anulowano aktywne: '.date('H:i').', '.date('d-m-Y').'" WHERE `task_state` = "1" AND `socket_tasks`.`adapter_id` = '.$_GET['adapter_id']);

            $url = $_SERVER['HTTP_REFERER']."#tasks";
            header('Location: '.$url);
        }
        else if (isset($_GET["adapter_id"]) && isset($_GET["pause"]))
        {
            //
            //echo '<br>'.'UPDATE `socket_tasks` SET `task_state` = "0", `task_cancel` = "'.date('H:i').','.date('d-m-Y').'" WHERE `socket_tasks`.`task_id` = '.$_GET['task_id'];

            $result = @$link->query('SELECT socket_id FROM `socket_tasks` WHERE `task_state` = "1" AND `task_active`="1" AND `adapter_id` = "'.$_GET["adapter_id"].'"');
            while($row = $result->fetch_assoc())
            {
                $socketID[] = $row['socket_id'];
            }
            $result->free();

            foreach($socketID as $socket)
            {
                $result = @$link->query('UPDATE `sockets` SET `socket_task_control` = "0" WHERE `sockets`.`socket_id` = "'.$socket.'"');
            }

            $result = @$link->query('UPDATE `socket_tasks` SET `task_active` = "0", `task_cancel` = "Zatrzymano aktywne zadanie: '.date('H:i').', '.date('d-m-Y').'" WHERE `task_state` = "1" AND `task_active`="1" AND `socket_tasks`.`adapter_id` = '.$_GET['adapter_id']);

            $url = $_SERVER['HTTP_REFERER']."#tasks";
            header('Location: '.$url);
        }
    }

    $link->close();
    
?>