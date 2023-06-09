<?php
    require_once "mysql_connect.php";


    $adapter_data_link = @new mysqli($host, $db_user, $db_password, $db_name);
    
    // Check connection
    if ($adapter_data_link->connect_errno!=0)
    {
        //echo "Error: ".$adapter_data_link->connect_errno;
    }
    else
    {
        if($result = @$adapter_data_link->query('SELECT * FROM adapters WHERE `adapter_removed` = "0"'))
        {
            if($result->num_rows > 0)
            {
                $i = 0;
                while($row = $result->fetch_assoc())
                {
                    $hcas_adapters[$i]['adapter_id'] = $row["adapter_id"];
                    $hcas_adapters[$i]['adapter_connection'] = $row['adapter_connection'];
                    $hcas_adapters[$i]['adapter_room'] = $row["adapter_room"];
                    $hcas_adapters[$i]['adapter_location'] = $row["adapter_location"];
                    $hcas_adapters[$i]['adapter_state'] = $row["adapter_state"];
                    $hcas_adapters[$i]['adapter_website_control'] = $row["adapter_website_control"];
                    $hcas_adapters[$i]['adapter_powerLevel'] = $row["adapter_powerLevel"];
                    $hcas_adapters[$i]['adapter_waitNumber'] = $row["adapter_waitNumber"];
                    $hcas_adapters[$i]['adapter_channel'] = $row["adapter_channel"];
                    $hcas_adapters[$i]['adapter_error'] = $row["adapter_error"];
                    $hcas_adapters[$i]['adapter_weak_signal'] = $row["adapter_weak_signal"];
                    $hcas_adapters[$i]['adapter_beacon'] = $row["adapter_beacon"];

                    if($result2 = @$adapter_data_link->query('
                        SELECT sensor_type, sensor_data, sensor_state, sensor_name, sensor_data, sensor_data_date, sensor_data_time, sensor_id, packets_recieved, packets_sent
                        FROM adapters
                        INNER JOIN sensors ON sensors.adapter_id=adapters.adapter_id
                        INNER JOIN stats ON adapters.adapter_id=stats.adapter_id
                        WHERE `sensor_type` != "time" AND adapters.`adapter_id` = '.$row["adapter_id"].'
                        '))
                    {
                        if($result2->num_rows != 0)
                        {
                            while($row2 = $result2->fetch_assoc())
                            {
                                $hcas_adapters[$i]['packets_recieved'] = $row2["packets_recieved"];
                                $hcas_adapters[$i]['packets_sent'] = $row2["packets_sent"];
                                
                                if ($row2['sensor_type'] == "temperature")
                                {
                                    $hcas_adapters[$i]['temp_sensor_data'] = $row2["sensor_data"];
                                    $hcas_adapters[$i]['temp_sensor_state'] = $row2["sensor_state"];
                                    $hcas_adapters[$i]['temp_sensor_name'] = $row2["sensor_name"];
                                    $hcas_adapters[$i]['temp_sensor_data_date'] = $row2["sensor_data_date"];
                                    $hcas_adapters[$i]['temp_sensor_data_time'] = $row2["sensor_data_time"];
                                    $hcas_adapters[$i]['temp_sensor_id'] = $row2["sensor_id"];
                                }
                                else if ($row2['sensor_type'] == "light")
                                {
                                    $hcas_adapters[$i]['light_sensor_data'] = $row2["sensor_data"];
                                    $hcas_adapters[$i]['light_sensor_state'] = $row2["sensor_state"];
                                    $hcas_adapters[$i]['light_sensor_name'] = $row2["sensor_name"];
                                    $hcas_adapters[$i]['light_sensor_data_date'] = $row2["sensor_data_date"];
                                    $hcas_adapters[$i]['light_sensor_data_time'] = $row2["sensor_data_time"];
                                    $hcas_adapters[$i]['light_sensor_id'] = $row2["sensor_id"];
                                }
                            }
                            
                            // Close result set
                            
                        }
                        $result2->free();
                    }

                    if($result2 = @$adapter_data_link->query('
                        SELECT `task_id` 
                        FROM `socket_tasks` 
                        INNER JOIN adapters ON adapters.adapter_id=socket_tasks.adapter_id AND `socket_tasks`.`adapter_id`="'.$row["adapter_id"].'"
                        WHERE `task_state` = "1" AND `task_active` = "1"
                        '))
                    {
                        $hcas_adapters[$i]['adapter_tasks'] = $result2->num_rows;
                        $result2->free();
                    }

                    $i++;
                }
                
                // Close result set
                $result->free();
            }
        }
        // Close connection
        $adapter_data_link->close(); 
    }
?>

