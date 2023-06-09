<?php
    require_once "mysql_connect.php";

    require_once 'session_login.php';


    $sensors_link = @new mysqli($host, $db_user, $db_password, $db_name);
    
    if ($sensors_link->connect_errno!=0)
    {
        echo "Error: ".$sensors_link->connect_errno;
    }
    else
    {
        if($result = @$sensors_link->query('
        SELECT sensor_data.data_time, sensor_data.sensor_data
        FROM sensor_data
        INNER JOIN sensors ON sensors.sensor_id=sensor_data.sensor_id AND sensor_type = "temperature"
        INNER JOIN adapters ON sensors.adapter_id=adapters.adapter_id
        WHERE adapters.adapter_id='.$adapters['adapter_id'].' ORDER by `data_id` DESC LIMIT 97
        '))
        {
            if($result->num_rows > 0)
            {
                $i = 0;
                while($row = $result->fetch_assoc())
                {
                    $data_main_graph[$i] = round($row['sensor_data'],2);
                    $time_main_graph[$i] = $row['data_time'];
                    $i++;
                }
                // Close result set
                $result->free();

                $avarage = array_sum($data_main_graph)/$i;
                
                $i = 0;
                foreach($data_main_graph as $value)
                {
                    $avarageTable[$i] = round($avarage,2);
                    $i++;
                }

                ?>
                    <div style="width:390px; height: 315px;margin-top: 30px;">
                        <canvas id="graph_main<?php echo $adapters['adapter_id']; ?>"></canvas>
                    </div>
                    <script>
                        <?php 
                        
                        require_once "graph_js.php"; 
                            echo 'var main_data'.$adapters['adapter_id'].' = ', js_array($data_main_graph), ';';
                            echo 'console.log( main_data'.$adapters['adapter_id'].' );';
                            echo 'var main_time'.$adapters['adapter_id'].' = ', js_array($time_main_graph), ';';
                            
                            echo 'var minimal_data'.$adapters['adapter_id'].' = ', floor(min($data_main_graph)), ';';
                            echo 'var maximal_data'.$adapters['adapter_id'].' = ', floor(max($data_main_graph)) + 1, ';';
                            
                            echo 'main_data'.$adapters['adapter_id'].'.reverse();';
                            echo 'main_time'.$adapters['adapter_id'].'.reverse();';

                            echo 'var srednia'.$adapters['adapter_id'].' = ', js_array($avarageTable), ';';
                            ?>
                    </script>

                    <?php include 'main_graph_settings.php'; ?>

                    <script>
                        waitInterval = setInterval(function (e) 
                        {
                            //document.getElementsByClassName("panel");
                            
                            //var ctx = document.getElementById('graph_main<?php echo $adapters['adapter_id']; ?>').getContext('2d');
                            //window.myLine = new Chart(ctx, config_graph_main);

                        
                        }, 3000);

                        var ctx = document.getElementById('graph_main<?php echo $adapters['adapter_id']; ?>').getContext('2d');
                        window.myLine = new Chart(ctx, config_graph_main);
                    </script>
                <?php
            } 
            else
            {
                echo "Brak danych";
            }
        }
        // Close connection
        $sensors_link->close(); 
    }

    ?>

        

    
