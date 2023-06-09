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
        if (isset($_GET["adapter_id"]) and isset($_GET["socket_id"]) and isset($_GET["socket_state"]))
        {
            $result = @$link->query('UPDATE `sockets` SET `socket_state` = '.$_GET["socket_state"].' WHERE `sockets`.`socket_id` = '.$_GET["socket_id"].' AND `adapter_id` = '.$_GET["adapter_id"]);
            mysqli_free_result($result);
            $result = @$link->query('INSERT INTO `socket_logs`(`log_id`, `adapter_id`, `socket_id`, `socket_state`, `last_changed`, `log_time`, `log_date`) VALUES ("","'.$_GET["adapter_id"].'","'.$_GET["socket_id"].'","'.$_GET["socket_state"].'","'.$_SESSION["user_login"].'","'.date('H:i').'","'.date('d-m-Y').'")');
            mysqli_free_result($result);
            $url = $_SERVER['HTTP_REFERER'].'#'.$_GET["adapter_id"];
            header('Location: '.$url);
        }
    }

    $link->close();
    
?>