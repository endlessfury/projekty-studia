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
        echo $_POST["removeButton"].', '.$_GET["socket_id"];
        if (isset($_POST["removeButton"]) and isset($_GET["sensor_id"]))
        {
            $result = @$link->query('DELETE FROM `sensors` WHERE `sensors`.`sensor_id` = "'.$_GET["sensor_id"].'"');
        }
        else if (isset($_POST["removeButton"]) and isset($_GET["socket_id"]))
        {
            $result = @$link->query('DELETE FROM `sockets` WHERE `sockets`.`socket_id` = "'.$_GET["socket_id"].'"');
        }
        if (isset($_POST["removeButton"]) and isset($_GET["adapter_id"]))
        {
            //$result = @$link->query('DELETE FROM `adapters` WHERE `adapters`.`adapter_id` = "'.$_GET["adapter_id"].'"');
            if($result = @$link->query('
            SELECT sensors.sensor_id, sensors.sensor_type
                FROM adapters
                INNER JOIN sensors ON adapters.adapter_id=sensors.adapter_id
                WHERE adapters.adapter_id='.$_GET['adapter_id'].'
            '))
            {
                if($result->num_rows > 0)
                {
                    while($row = $result->fetch_assoc())
                    {
                        if ($row['sensor_type'] == "temperature")
                            $tempSensorID = $row['sensor_id'];
                        else if ($row['sensor_type'] == "light")
                            $lightSensorID = $row['sensor_id'];
                        else if ($row['sensor_type'] == "time")
                            $timeSensorID = $row['sensor_id'];
                    }
                    $result->free();
                } 
                else
                {
                    echo "Error: No sensors found.";
                }
            } 
            else
            {
                echo "ERROR: Could not able to execute $link. ";
            }

            $result = @$link->query('UPDATE `sensors` SET `sensor_state` = "0" WHERE `sensors`.`sensor_id` = "'.$tempSensorID.'"');
            $result = @$link->query('UPDATE `sensors` SET `sensor_state` = "0" WHERE `sensors`.`sensor_id` = "'.$lightSensorID.'"');
            $result = @$link->query('UPDATE `sensors` SET `sensor_state` = "0" WHERE `sensors`.`sensor_id` = "'.$timeSensorID.'"');
            $result = @$link->query('UPDATE `adapters` SET `adapter_removed` = "1", `adapter_state` = "0" WHERE `adapters`.`adapter_id` = "'.$_GET["adapter_id"].'"');
        }

        header('Location: active_task_cancel.php?adapter_id='.$_GET["adapter_id"]);
        
    }

    $link->close();
    
?>