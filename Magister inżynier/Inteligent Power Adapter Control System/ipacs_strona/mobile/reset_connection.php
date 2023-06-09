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
        if ($_GET["fast_restart"] == 1 and isset($_GET["adapter_id"]))
        {
            $result = @$link->query('UPDATE `adapters` SET `adapter_connection` = "3" WHERE `adapter_id` = '.$_GET["adapter_id"]);
            mysqli_free_result($result);
            $url = 'state_change.php?adapter_state=1&adapter_id='.$_GET["adapter_id"];
            header('Location: '.$url);
        }
        else if (isset($_GET["adapter_id"]))
        {
            $result = @$link->query('UPDATE `adapters` SET `adapter_connection` = "3" WHERE `adapter_id` = '.$_GET["adapter_id"]);
            mysqli_free_result($result);
            $url = $_SERVER['HTTP_REFERER'].'#'.$_GET["adapter_id"];
            header('Location: '.$url);
        }
    }

    $link->close();
    
?>