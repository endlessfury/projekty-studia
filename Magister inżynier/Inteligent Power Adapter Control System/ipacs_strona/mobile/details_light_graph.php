<?php
    unset($data_main_graph);
    unset($time_main_graph);
    unset($avarageTable);
    unset($avarage);
    unset($data);
    unset($dataTable);


    require_once "mysql_connect.php";

    $link = @new mysqli($host, $db_user, $db_password, $db_name);
    
    // Check connection
    if ($link->connect_errno!=0)
    {
        echo "Error: ".$link->connect_errno;
    }
    else
    {
        // sprawdz czy czujnik jest włączony
        if($result = @$link->query('
        SELECT sensor_state
        FROM sensors 
        INNER JOIN adapters ON adapters.adapter_id=sensors.adapter_id 
        WHERE sensors.sensor_type="light" AND sensors.adapter_id="'.$_GET['adapter_id'].'"
        '))
        {
            $row = $result->fetch_assoc();
            $lightSensorState = $row['sensor_state'];
            if ($lightSensorState == '0') echo '<font color="red"><center><b>Czujnik jest wyłączony!</b></font><br>';
        }
        // znajdz ostatnie dane z sensora 
        if($result = @$link->query('
        SELECT sensor_data.sensor_id, sensor_data.data_date, sensors.sensor_type, sensor_data.data_id
        FROM sensor_data 
        INNER JOIN sensors ON sensors.sensor_id=sensor_data.sensor_id 
        WHERE sensors.sensor_type="light" AND sensors.adapter_id="'.$_GET['adapter_id'].'"
        ORDER BY `sensor_data`.`data_id` ASC
        '))
        {
            if($result->num_rows > 0)
            {
                while($row = $result->fetch_assoc())
                {
                    $sensorID=$row['sensor_id'];
                    $lastDate=$row['data_date'];
                }
                    
                $result->free();
            } 
            else
            {
               echo 'W listwie brak czujnika!<br>';
            }
        }
        else
        {
            echo 'Error: Nie mogę pobrać danych o sensorze i ostatnie daty<br>';
        }
        // wybierz wszyskie daty
        if($result = @$link->query('SELECT * FROM `sensor_data` WHERE `sensor_id`='.$sensorID.' ORDER BY data_id DESC'))
        {
            if($result->num_rows > 0)
            {
                $i = 0;
                while($row = $result->fetch_assoc())
                {
                    if ($light_dates[$i-1] != $row["data_date"] && $i  < 15)
                    {
                        $light_dates[$i] = $row['data_date'];
                        $i++;
                    }
                    
                }

                $light_dates = array_reverse($light_dates, false);
                
                // Close result set
                $result->free();
            } 
            else
            {
               echo 'Nie mogę wykonać SQL1!<br>';
            }
        }
        // znajdz pierwszy wpis z daną datą
            if($result = @$link->query('SELECT * FROM `sensor_data` WHERE `sensor_id`='.$sensorID.' AND `data_date`="'.($_POST["light_dates_start"]?$_POST["light_dates_start"]:$lastDate).'" ORDER BY `data_id` ASC'))
            {
                if($result->num_rows > 0)
                {
                    while($row = $result->fetch_assoc())
                    {
                        $firstID = $row["data_id"];
                        break;
                    }
                    // Close result set
                    $result->free();
                } 
            }
            else
            {
               echo 'Nie mogę wykonać SQL2!<br>';
            }
        
        // znajdz ostatni wpis z daną datą 
            if($result = @$link->query('SELECT * FROM `sensor_data` WHERE `sensor_id`='.$sensorID.' AND `data_date`="'.($_POST["light_dates_end"]?$_POST["light_dates_end"]:$lastDate).'" ORDER BY `data_id` DESC'))
            {
                if($result->num_rows > 0)
                {
                    while($row = $result->fetch_assoc())
                    {
                        $lastID = $row["data_id"];
                        break;
                    }
                    // Close result set
                    $result->free();
                } 
            }
            else
            {
               echo 'Nie mogę wykonać SQL3!<br>';
            }

        // znajdz wpisy pomiędzy danymi datami 
        $lastID = $lastID + 1; // żeby było od 12 do 12 włącznie
        if($result = @$link->query('SELECT * FROM `sensor_data` WHERE `sensor_id`='.$sensorID.' AND `data_id`>='.$firstID.' AND `data_id`<='.$lastID.' ORDER BY `data_id` ASC'))
        {
            if($result->num_rows > 0)
            {
                $i = 0;
                while($row = $result->fetch_assoc())
                {
                    $data[$i] = $row['sensor_data'];
                    $date[$i] = $row['data_date'];
                    $time[$i] = $row['data_time'];
                    $i++;
                }
                // Close result set
                $result->free();
            } 
            else
            {
                // jesli błąd to podano złe dane  i powtarzamy dla ostatniego dnia
                echo '<font color="red"><center><b>Podano złe dane!</b></font><br>';
                $result = @$link->query('SELECT * FROM `sensor_data` WHERE `sensor_id`="1" AND `data_date`="'.$lastDate.'" ORDER BY `data_id` ASC');
                if($result->num_rows > 0)
                {
                    while($row = $result->fetch_assoc())
                    {
                        $firstID = $row["data_id"];
                        break;
                    }
                    // Close result set
                    $result->free();
                } 
                else
                {
                   echo $lastDate.'Nie mogę wykonać SQL4!<br>';
                }
                $result = @$link->query('SELECT * FROM `sensor_data` WHERE `sensor_id`='.$sensorID.' AND `data_date`="'.$lastDate.'" ORDER BY `data_id` DESC');
                if($result->num_rows > 0)
                {
                    while($row = $result->fetch_assoc())
                    {
                        $lastID = $row["data_id"];
                        break;
                    }
                    // Close result set
                    $result->free();
                }  
                else
                {
                   echo 'Nie mogę wykonać SQL5!<br>';
                }
                $result = @$link->query('SELECT * FROM `sensor_data` WHERE `sensor_id`='.$sensorID.' AND `data_id`>='.$firstID.' AND `data_id`<='.$lastID.' ORDER BY `data_id` ASC');
                if($result->num_rows > 0)
                {
                    $i = 0;
                    while($row = $result->fetch_assoc())
                    {
                        $data[$i] = $row['sensor_data'];
                        $date[$i] = $row['data_date'];
                        $time[$i] = $row['data_time'];
                        $i++;
                    }
                    // Close result set
                    $result->free();
                } 
                else
                {
                   echo 'Nie mogę wykonać SQL6!<br>';
                }
            }
        }

        // Close connection
        $link->close();     
        
    
        $light_avarage = array_sum($data)/$i;
        
        $i = 0;
        foreach($data as $value)
        {
            $light_avarageTable[$i] = round($light_avarage,2);
            $dataTable[$i] = $time[$i].' - '.$date[$i];
            $i++;
        }
    }

?>
    <br><form action="<?php $_SERVER['HTTP_REFERER'] ?>#light" method="POST">
        <center>Okres: 
       <table border=0>
            <tr>
                <td>
                    od &nbsp; <select name="light_dates_start">
                        <?php
                        for ($i = count($light_dates)-1;$i >= 0;$i--)
                        {
                            if($light_dates[$i] == $_POST["light_dates_start"])
                                echo '<option value="'.$light_dates[$i].'" selected>'.$light_dates[$i].'</option>';
                            else
                                echo '<option value="'.$light_dates[$i].'">'.$light_dates[$i].'</option>';
                        }
                            
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    do &nbsp; <select name="light_dates_end">
                        <?php
                        for ($i = count($light_dates)-1;$i >= 0;$i--)
                        {
                            if($light_dates[$i] == $_POST["light_dates_end"])
                                echo '<option value="'.$light_dates[$i].'" selected>'.$light_dates[$i].'</option>';
                            else
                                echo '<option value="'.$light_dates[$i].'">'.$light_dates[$i].'</option>';
                        }
                            
                    
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        &nbsp; <button name='submit' class="panelButton" style="margin-top:10px;">WYRYSUJ</button>
    </form><br><br>
    <table border=0 width="90%">
        <tr>
            <td>Maksymalne natężenie: <font color="#113e55"><?php echo round(max($data),2); ?>%</font></td>
        </tr><tr>
            <td>Minimalne natężenie: <font color="#113e55"><?php echo round(min($data),2); ?>%</font></td>
        </tr><tr>
            <td>Średnie natężenie: <font color="#113e55"><?php echo round($light_avarage,2); ?>%</font></td>
        </tr>
    </table>
    <br></center>
    <div style="width:85%; height: 380px;">
		<canvas id="light_details"></canvas>
	</div>
    <script>

        <?php
            

            echo 'var light_data = ', js_array($data), ';';
            echo 'var light_times = ', js_array($dataTable), ';';
            
            echo 'var light_minimal_data = ', floor(min($data)), ';';
            echo 'var light_maximal_data = ', floor(max($data)/10+1)*10, ';';
            
            echo 'var avarage = ', js_array($light_avarageTable), ';';
        ?>
    </script>

    <?php include 'details_light_graph_settings.php'; ?>

<script>
    var ctx = document.getElementById("light_details").getContext("2d");
    window.myLine = new Chart(ctx, config_light_details);
</script>
