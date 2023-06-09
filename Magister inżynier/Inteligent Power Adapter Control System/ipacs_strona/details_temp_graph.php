<?php
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
        WHERE sensors.sensor_type="temperature" AND sensors.adapter_id="'.$_GET['adapter_id'].'"
        '))
        {
            $row = $result->fetch_assoc();
            $tempSensorState = $row['sensor_state'];
            if ($tempSensorState == '0') echo '<font color="red"><center><b>Czujnik jest wyłączony!</b></font><br>';
        }
        /* znajdz ostatnie dane z sensora */
        if($result = @$link->query('
        SELECT sensor_data.sensor_id, sensor_data.data_date, sensors.sensor_type, sensor_data.data_id
        FROM sensor_data 
        INNER JOIN sensors ON sensors.sensor_id=sensor_data.sensor_id 
        WHERE sensors.sensor_type="temperature" AND sensors.adapter_id="'.$_GET['adapter_id'].'"
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
        /* nie wiem */
        if($result = @$link->query('SELECT * FROM `sensor_data` WHERE `sensor_id`='.$sensorID.' ORDER BY data_id DESC'))
        {
            if($result->num_rows > 0)
            {
                $i = 0;
                while($row = $result->fetch_assoc())
                {
                    if ($dates[$i-1] != $row["data_date"] && $i  < 15)
                    {
                        $dates[$i] = $row['data_date'];
                        $i++;
                    }
                    
                }

                $dates = array_reverse($dates, false);
                
                // Close result set
                $result->free();
            } 
            else
            {
               echo 'Nie mogę wykonać SQL!<br>';
            }
        }
        /* znajdz pierwszy wpis z daną datą */
            if($result = @$link->query('SELECT * FROM `sensor_data` WHERE `sensor_id`='.$sensorID.' AND `data_date`="'.($_POST["dates_start"]?$_POST["dates_start"]:$lastDate).'" ORDER BY `data_id` ASC'))
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
               echo 'Nie mogę wykonać SQL!<br>';
            }
        
        /* znajdz ostatni wpis z daną datą */
            if($result = @$link->query('SELECT * FROM `sensor_data` WHERE `sensor_id`='.$sensorID.' AND `data_date`="'.($_POST["dates_end"]?$_POST["dates_end"]:$lastDate).'" ORDER BY `data_id` DESC'))
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
               echo 'Nie mogę wykonać SQL!<br>';
            }

        /* znajdz wpisy pomiędzy danymi datami */
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
                /* jesli błąd to podano złe dane  i powtarzamy dla ostatniego dnia*/
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
                   echo 'Nie mogę wykonać SQL!<br>';
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
                   echo 'Nie mogę wykonać SQL!<br>';
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
                   echo 'Nie mogę wykonać SQL!<br>';
                }
            }
        }

        // Close connection
        $link->close();     
        
    
        $avarage = array_sum($data)/$i;
        
        $i = 0;
        foreach($data as $value)
        {
            $avarageTable[$i] = round($avarage,2);
            $dataTable[$i] = $time[$i].' - '.$date[$i];
            $i++;
        }
    }

?>
    <br><form action="<?php $_SERVER['HTTP_REFERER'] ?>" method="POST">
        <center>Okres: 
        od &nbsp; <select name="dates_start">
            <?php
            for ($i = count($dates)-1;$i >= 0;$i--)
            {
                if($dates[$i] == $_POST["dates_start"])
                    echo '<option value="'.$dates[$i].'" selected>'.$dates[$i].'</option>';
                else
                    echo '<option value="'.$dates[$i].'">'.$dates[$i].'</option>';
            }
                
            ?>
        </select>
        &nbsp;do &nbsp; <select name="dates_end">
            <?php
            for ($i = count($dates)-1;$i >= 0;$i--)
            {
                if($dates[$i] == $_POST["dates_end"])
                    echo '<option value="'.$dates[$i].'" selected>'.$dates[$i].'</option>';
                else
                    echo '<option value="'.$dates[$i].'">'.$dates[$i].'</option>';
            }
                
        
            ?>
        </select>
        &nbsp; <button name='submit' class="panelButton">WYRYSUJ</button>
    </form><br><br>
    <table border=0 width="90%">
        <tr>
            <td>Maksymalna temeperatura: <font color="#113e55"><?php echo round(max($data),2); ?>℃</font></td>
            <td>Minimalna temeperatura: <font color="#113e55"><?php echo round(min($data),2); ?>℃</font></td>
            <td>Średnia temeperatura: <font color="#113e55"><?php echo round($avarage,2); ?>℃</font></td>
        </tr>
    </table>
    <br></center>
    <div style="width:85%; height: 380px;">
		<canvas id="temp_details"></canvas>
	</div>
    <script>

        <?php
            function js_str($s)
            {
                return '"' . addcslashes($s, "\0..\37\"\\") . '"';
            }

            function js_array($array)
            {
                $temp = array_map('js_str', $array);
                return '[' . implode(',', $temp) . ']';
            }

            echo 'var temperatury = ', js_array($data), ';';
            echo 'var godziny = ', js_array($dataTable), ';';
            
            echo 'var minimalna_temperatura = ', floor(min($data)), ';';
            echo 'var maksymalna_temperatura = ', floor(max($data))+1, ';';
            
            echo 'var srednia = ', js_array($avarageTable), ';';
        ?>
    </script>

    <?php include 'details_temp_graph_settings.php'; ?>

<script>
    var ctx = document.getElementById("temp_details").getContext("2d");
    window.myLine = new Chart(ctx, config_temp_details);
</script>
