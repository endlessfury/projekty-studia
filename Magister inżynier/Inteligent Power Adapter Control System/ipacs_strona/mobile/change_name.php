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
        //echo $_POST["nowaNazwa"].', '$_POST["nowyPokoj"].', '$_POST["nowaLokacja"].', '.$_GET["socket_id"].', '.$_GET["sensor_id"].', '.$_GET["adapter_id"];
        if (isset($_POST["nowaNazwa"]) and isset($_POST["changeNameButton"]) and isset($_GET["socket_id"]))
        {
            $result = @$link->query('UPDATE `sockets` SET `socket_name` = "'.$_POST["nowaNazwa"].'" WHERE `sockets`.`socket_id` = "'.$_GET["socket_id"].'"');
        }
        else if (isset($_POST["nowaNazwa"]) and isset($_POST["changeNameButton"]) and isset($_GET["sensor_id"]))
        {
            $result = @$link->query('UPDATE `sensors` SET `sensor_name` = "'.$_POST["nowaNazwa"].'" WHERE `sensors`.`sensor_id` = "'.$_GET["sensor_id"].'"');
        }
        else if (isset($_POST["nowyPokoj"]) and isset($_POST["nowaLokacja"]) and isset($_POST["changeNameButton"]) and isset($_GET["adapter_id"]))
        {
            $result = @$link->query('UPDATE `adapters` SET `adapter_location` = "'.$_POST["nowaLokacja"].'", `adapter_room` = "'.$_POST["nowyPokoj"].'" WHERE `adapters`.`adapter_id` = "'.$_GET["adapter_id"].'"');
        }

        $url = $_SERVER['HTTP_REFERER']."#".$_GET["adapter_id"];
        header('Location: '.$url);
        
    }

    $link->close();
    
?>