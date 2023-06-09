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
        if (isset($_POST["tasks"]) and isset($_POST["sockets"]) and isset($_POST["condition"]))
        {
            if ($_POST["tasks"] == "time_on" or $_POST["tasks"] == "time_off")
            {
                $sensorType = "time";
            }
            else if ($_POST["tasks"] == "light_on" or $_POST["tasks"] == "light_off")
            {
                $sensorType = "light";
            }
            else if ($_POST["tasks"] == "temp_on" or $_POST["tasks"] == "temp_off")
            {
                $sensorType = "temperature";
            }
            //echo $_POST["tasks"].', '.$_POST["sensors"].', '.$_POST['condition'].', '.$_POST['sockets'];
            if($result = @$link->query('
            SELECT sensors.sensor_id, sensors.sensor_type
                FROM adapters
                INNER JOIN sensors ON adapters.adapter_id=sensors.adapter_id
                WHERE adapters.adapter_id='.$_GET['adapter_id'].' AND sensors.sensor_type="'.$sensorType.'"
            '))
            {
                if($result->num_rows > 0)
                {
                    while($row = $result->fetch_assoc())
                    {
                        $sensorID = $row['sensor_id'];
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
            $result = @$link->query('INSERT INTO `socket_tasks` (`task_id`, `socket_id`, `sensor_id`, `task_type`, `task_condition`, `task_state`, `task_date`, `task_time`, `task_user`, `task_cycle`) VALUES (NULL, "'.$_POST["sockets"].'", "'.$sensorID.'", "'.$_POST['tasks'].'", "'.$_POST['condition'].'", 1, "'.date('d-m-Y').'", "'.date('H:i').'", "Wojtek", "'.$_POST['repeatability'].'")');
            //echo '<br>'.'INSERT INTO `socket_tasks` (`task_id`, `socket_id`, `sensor_id`, `task_type`, `task_condition`, `task_state`, `task_date`, `task_time`, `task_user`) VALUES (NULL, "'.$_GET["socket_id"].'", "'.$sensorID.'", "'.$_POST['tasks'].'", "'.$_POST['condition'].'", 1, "'.date('d-m-Y').'", "'.date('H:i').'", "Wojtek")';
            mysqli_free_result($result);
            $url = $_SERVER['HTTP_REFERER'];
            header('Location: '.$url);
        }
    }

    $link->close();
    
?>