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
        if (isset($_GET["adapter_id"]) and isset($_GET["adapter_state"]))
        {
            $result = @$link->query('UPDATE `adapters` SET `adapter_state` = '.$_GET["adapter_state"].' WHERE `adapters`.`adapter_id` = '.$_GET["adapter_id"]);
            mysqli_free_result($result);
        }
        if (isset($_GET["sensor_id"]) and isset($_GET["sensor_state"]))
        {
            $result = @$link->query('UPDATE `sensors` SET `sensor_state` = '.$_GET["sensor_state"].' WHERE `sensors`.`sensor_id` = '.$_GET["sensor_id"]);
            mysqli_free_result($result);
        }
        $url = $_SERVER['HTTP_REFERER'].'#configMain';
        header('Location: '.$url);
    }

    $link->close();
    
?>