<?php
    require_once "mysql_connect.php";
    require_once 'session_login.php';

    $link = @new mysqli($host, $db_user, $db_password, $db_name);
    
    // Check connection
    if ($link->connect_errno!=0)
    {
        echo "Error: ".$link->connect_errno;
    }
    else
    {
        if($result = @$link->query('
        SELECT *
            FROM socket_tasks 
            INNER JOIN sensors 
                ON socket_tasks.sensor_id=sensors.sensor_id
            INNER JOIN adapters 
            	ON socket_tasks.adapter_id=adapters.adapter_id
            INNER JOIN sockets 
            	ON socket_tasks.socket_id=sockets.socket_id 
            WHERE `task_state`=1 ORDER by `task_id` DESC
        '))
        {
            if($result->num_rows > 0)
            {
                $i = 0;
                while($row = $result->fetch_assoc())
                {
                    $data[$i]['task_id'] = $row['task_id'];
                    $data[$i]['socket_name'] = $row['socket_name'];
                    $data[$i]['task_condition'] = $row['task_condition'];
                    $data[$i]['task_cycle'] = $row['task_cycle'];
                    $data[$i]['sensor_name'] = $row['sensor_name'];
                    $data[$i]['sensor_type'] = $row['sensor_type'];
                    $data[$i]['task_user'] = $row['task_user'];
                    $data[$i]['task_time'] = $row['task_time'];
                    $data[$i]['task_date'] = $row['task_date'];
                    $data[$i]['task_id'] = $row['task_id'];
                    $data[$i]['task_type'] = $row['task_type'];
                    $data[$i]['task_error'] = $row['task_error'];
                    $data[$i]['socket_id'] = $row['socket_id'];
                    $data[$i]['socket_task_control'] = $row['socket_task_control'];
                    $data[$i]['adapter_room'] = $row['adapter_room'];
                    $data[$i]['adapter_location'] = $row['adapter_location'];
                    $data[$i]['adapter_id'] = $row['adapter_id'];
                    $data[$i]['task_cancel'] = $row['task_cancel'];
                    $data[$i]['task_error'] = $row['task_error'];
                    $i++;
                }
                $result->free();
            }
            if($result = @$link->query('
        SELECT *
            FROM socket_tasks 
            INNER JOIN sensors 
                ON socket_tasks.sensor_id=sensors.sensor_id
            INNER JOIN adapters 
            	ON socket_tasks.adapter_id=adapters.adapter_id
            INNER JOIN sockets 
            	ON socket_tasks.socket_id=sockets.socket_id 
            WHERE `task_state`=0 ORDER by `task_id` DESC LIMIT 10
        '))
            {
                if($result->num_rows > 0)
                {
                    $i = 0;
                    while($row = $result->fetch_assoc())
                    {
                        $data2[$i]['task_id'] = $row['task_id'];
                        $data2[$i]['socket_name'] = $row['socket_name'];
                        $data2[$i]['task_condition'] = $row['task_condition'];
                        $data2[$i]['task_cycle'] = $row['task_cycle'];
                        $data2[$i]['sensor_name'] = $row['sensor_name'];
                        $data2[$i]['sensor_type'] = $row['sensor_type'];
                        $data2[$i]['task_user'] = $row['task_user'];
                        $data2[$i]['task_time'] = $row['task_time'];
                        $data2[$i]['task_date'] = $row['task_date'];
                        $data2[$i]['task_id'] = $row['task_id'];
                        $data2[$i]['task_type'] = $row['task_type'];
                        $data2[$i]['task_error'] = $row['task_error'];
                        $data2[$i]['socket_id'] = $row['socket_id'];
                        $data2[$i]['socket_task_control'] = $row['socket_task_control'];
                        $data2[$i]['adapter_room'] = $row['adapter_room'];
                        $data2[$i]['adapter_location'] = $row['adapter_location'];
                        $data2[$i]['adapter_id'] = $row['adapter_id'];
                        $data2[$i]['task_cancel'] = $row['task_cancel'];
                        $data2[$i]['task_error'] = $row['task_error'];
                        $i++;
                    }
                    $result->free();
                }
            }
        } 
        else
        {
            echo "ERROR: Could not able to execute $link. ";
        }

    $link->close(); 
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="styl.css">
    <script src="Chart.bundle.js"></script>
	<script src="utils.js"></script>
</head>
<body>
    <div id="container">
        <div class="panel" id="tasks">
            <div class="subPanelTop">
            <div style="float:left; margin-left: 10px; position: fixed;"><a href="<?php echo 'index.php'.'#'.$_GET['adapter_id']; ?>" class="goBack" style="line-height: 20px">Powrót</a></div>
                Uruchomione zadania
            </div>
            <div class="subPanelDetails">
                <table class="logs">
                    <tr>
                        <th class="logs" width="20px">ID</th>
                        <th class="logs" width="50px">adapter</th>
                        <th class="logs" width="100px">gniazdo</th>
                        <th class="logs" width="150px">typ zadania</th>
                        <th class="logs" width="30px">warunek</th>
                        <th class="logs" width="20px">cykliczne</th>
                        <th class="logs">użytkownik</th>
                        <th class="logs">czas</th>
                        <th class="logs" width="60px">data</th>
                        <th class="logs" width="160px">informacja
                        <th class="logs">komentarz</th>
                    </tr>
                    <?php
                        if (!empty($data))
                        {
                            foreach($data as $row)
                            {
                                echo '<tr class="logs">';
                                echo '<td class="logs">'.$row['task_id'].'</td>';
                                echo '<td class="logs"><a class="taskLink" href="tasks.php?adapter_id='.$row['adapter_id'].'#tasks" title="Kliknij, aby się przenieść">'.$row['adapter_room'].', '.$row['adapter_location'].'</a></td>';
                                echo '<td class="logs">'.$row['socket_name'].'</td>';
                                if ($row['task_type'] == "time_on")
                                    echo '<td class="logs">Włącz o wskazanej godzinie</td>';
                                else if ($row['task_type'] == "time_off")
                                    echo '<td class="logs">Wyłącz o wskazanej godzinie</td>';
                                else if ($row['task_type'] == "temp_up_off")
                                    echo '<td class="logs">Wyłącz, gdy temperatura wzrośnie powyżej</td>';
                                else if ($row['task_type'] == "temp_up_on")
                                    echo '<td class="logs">Włącz, gdy temperatura wzrośnie powyżej</td>';
                                else if ($row['task_type'] == "temp_down_off")
                                    echo '<td class="logs">Wyłącz, gdy temperatura spadnie poniżej</td>';
                                else if ($row['task_type'] == "temp_down_on")
                                    echo '<td class="logs">Włącz, gdy temperatura spadnie poniżej</td>';
                                else if ($row['task_type'] == "light_up_off")
                                echo '<td class="logs">Wyłącz, gdy natężenie swiatła wzrośnie powyżej</td>';
                                else if ($row['task_type'] == "light_up_on")
                                    echo '<td class="logs">Włącz, gdy natężenie swiatła wzrośnie powyżej</td>';
                                else if ($row['task_type'] == "light_up_on")
                                    echo '<td class="logs">Włącz, gdy natężenie swiatła przekroczy</td>';
                                else if ($row['task_type'] == "light_down_off")
                                    echo '<td class="logs">Wyłącz, gdy natężenie swiatła spadnie poniżej</td>';
                                else if ($row['task_type'] == "light_down_on")
                                    echo '<td class="logs">Włącz, gdy natężenie swiatła spadnie poniżej</td>';
                                echo '<td class="logs">'.$row['task_condition'].'</td>';
                                echo '<td class="logs">'.$row['task_cycle'].'</td>';
                                echo '<td class="logs">'.$row['task_user'].'</td>';
                                echo '<td class="logs">'.$row['task_time'].'</td>';
                                echo '<td class="logs">'.$row['task_date'].'</td>';
                                if (empty($row['task_cancel']))
                                    echo '<td class="logs"><i>brak</i></td>';
                                else
                                    echo '<td class="logs">'.$row['task_cancel'].'</td>';
                                if (empty($row['task_error']))
                                    echo '<td class="logs"><i>brak</i></td>';
                                else
                                    echo '<td class="logs">'.$row['task_error'].'</td>';
                                echo '</tr>';
                            } 
                            echo '</table>';
                        }
                        else
                        {
                            echo '</table><br>Brak zadań';
                        }
                    ?>
            </div>
            
        </div>
        <div class="panel" id="tasks">
            <div class="subPanelTop">
                Anulowane/zakończone zadania
            </div>
            <div class="subPanelDetails">
                <table class="logs">
                    <tr>
                        <th class="logs" width="20px">ID</th>
                        <th class="logs" width="50px">adapter</th>
                        <th class="logs" width="100px">gniazdo</th>
                        <th class="logs" width="150px">typ zadania</th>
                        <th class="logs" width="30px">warunek</th>
                        <th class="logs" width="20px">cykliczne</th>
                        <th class="logs">użytkownik</th>
                        <th class="logs">czas</th>
                        <th class="logs" width="60px">data</th>
                        <th class="logs" width="160px">informacja
                        <th class="logs">komentarz</th>
                    </tr>
                    <?php
                        if (!empty($data2))
                        {
                            foreach($data2 as $row)
                            {
                                echo '<tr class="logs">';
                                echo '<td class="logs">'.$row['task_id'].'</td>';
                                echo '<td class="logs">'.$row['adapter_room'].', '.$row['adapter_location'].'</td>';
                                echo '<td class="logs">'.$row['socket_name'].'</td>';
                                if ($row['task_type'] == "time_on")
                                    echo '<td class="logs">Włącz o wskazanej godzinie</td>';
                                else if ($row['task_type'] == "time_off")
                                    echo '<td class="logs">Wyłącz o wskazanej godzinie</td>';
                                else if ($row['task_type'] == "temp_up_off")
                                    echo '<td class="logs">Wyłącz, gdy temperatura wzrośnie powyżej</td>';
                                else if ($row['task_type'] == "temp_up_on")
                                    echo '<td class="logs">Włącz, gdy temperatura wzrośnie powyżej</td>';
                                else if ($row['task_type'] == "temp_down_off")
                                    echo '<td class="logs">Wyłącz, gdy temperatura spadnie poniżej</td>';
                                else if ($row['task_type'] == "temp_down_on")
                                    echo '<td class="logs">Włącz, gdy temperatura spadnie poniżej</td>';
                                else if ($row['task_type'] == "light_up_off")
                                echo '<td class="logs">Wyłącz, gdy natężenie swiatła wzrośnie powyżej</td>';
                                else if ($row['task_type'] == "light_up_on")
                                    echo '<td class="logs">Włącz, gdy natężenie swiatła wzrośnie powyżej</td>';
                                else if ($row['task_type'] == "light_up_on")
                                    echo '<td class="logs">Włącz, gdy natężenie swiatła przekroczy</td>';
                                else if ($row['task_type'] == "light_down_off")
                                    echo '<td class="logs">Wyłącz, gdy natężenie swiatła spadnie poniżej</td>';
                                else if ($row['task_type'] == "light_down_on")
                                    echo '<td class="logs">Włącz, gdy natężenie swiatła spadnie poniżej</td>';
                                echo '<td class="logs">'.$row['task_condition'].'</td>';
                                echo '<td class="logs">'.$row['task_cycle'].'</td>';
                                echo '<td class="logs">'.$row['task_user'].'</td>';
                                echo '<td class="logs">'.$row['task_time'].'</td>';
                                echo '<td class="logs">'.$row['task_date'].'</td>';
                                if (empty($row['task_cancel']))
                                    echo '<td class="logs"><i>brak</i></td>';
                                else
                                    echo '<td class="logs">'.$row['task_cancel'].'</td>';
                                if (empty($row['task_error']))
                                    echo '<td class="logs"><i>brak</i></td>';
                                else
                                    echo '<td class="logs">'.$row['task_error'].'</td>';
                                echo '</tr>';
                            } 
                            echo '</table>';
                        }
                        else
                        {
                            echo '</table><br>Brak zadań';
                        }
                    ?>
            </div>
            
        </div>
        
    </div>
</body>
</html>