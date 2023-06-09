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
        echo $_POST["addButton"].', '.$_POST["sensorName"].', '.$_POST["sensorType"].', '.$_GET["adapter_id"];
        if (isset($_POST["addButton"]) and isset($_GET["adapter_id"]) and isset($_GET["socket"]) and isset($_POST["socketName"]))
        {
            $result = @$link->query('INSERT INTO `sockets` (`socket_id`, `adapter_id`, `socket_name`, `socket_state`, `socket_task_control`, `socket_task_assigned`) VALUES (NULL, "'.$_GET["adapter_id"].'", "'.$_POST["socketName"].'", "0", "0", "0")');
            
            $url = $_SERVER['HTTP_REFERER'].'#'.$_GET["adapter_id"];
        }
        else if (isset($_POST["addButton"]) and isset($_GET["adapter_id"]) and isset($_GET["sensor"]) and isset($_POST["sensorName"]) and isset($_POST["sensorType"]))
        {
            $result = @$link->query('INSERT INTO `sensors` (`sensor_id`, `sensor_name`, `sensor_type`, `adapter_id`, `sensor_data`, `sensor_data_date`, `sensor_data_time`, `sensor_state`) VALUES (NULL, "'.$_POST["sensorName"].'", "'.$_POST["sensorType"].'", "'.$_GET["adapter_id"].'", "0", "0", "0", "0")');
            
            $url = $_SERVER['HTTP_REFERER'].'#'.$_GET["adapter_id"];
        }
        else if (isset($_POST["addButton"]) and isset($_GET["adapter_id"]) and isset($_GET["adapter"]) and isset($_POST["adapterRoom"]) and isset($_POST["adapterLocation"]))
        {
            $result = @$link->query('INSERT INTO `adapters` (`adapter_id`, `adapter_room`, `adapter_location`, `adapter_state`, `adapter_powerLevel`, `adapter_website_control`) VALUES (NULL, "'.$_POST["adapterRoom"].'", "'.$_POST["adapterLocation"].'", "0", "", "1")');
            $result = @$link->query('SELECT `adapter_id` FROM `adapters` ORDER BY `adapter_id` DESC LIMIT 1');
            $row = $result->fetch_assoc();
            $result->free();
            $result = @$link->query('INSERT INTO `sensors` (`sensor_id`, `sensor_name`, `sensor_type`, `adapter_id`, `sensor_data`, `sensor_data_date`, `sensor_data_time`, `sensor_state`) VALUES (NULL, "time", "time", "'.$row['adapter_id'].'", "0", "0", "0", "1")');
            
            $url = $_SERVER['HTTP_REFERER'].'#'.$row['adapter_id'];
        }

        header('Location: '.$url);
        
    }

    $link->close();
    
?>