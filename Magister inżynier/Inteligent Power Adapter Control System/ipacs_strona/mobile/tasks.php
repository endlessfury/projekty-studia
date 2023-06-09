<?php
    require_once "mysql_connect.php";
    require_once 'session_login.php';
    if ($_SESSION['user_permission'] == 0) header('Location: '.'index.php');

    $link = @new mysqli($host, $db_user, $db_password, $db_name);
    
    // Check connection
    if ($link->connect_errno!=0)
    {
        echo "Error: ".$link->connect_errno;
    }
    else
    {
        if($result = @$link->query('
        SELECT task_time_controlled, task_time_on, task_time_off, task_active, task_cycle,task_id, sockets.socket_name, sensors.sensor_name, sensors.sensor_type, task_type, task_condition, task_state, task_date, task_time, task_user, sensors.adapter_id, task_error, sockets.socket_id, sockets.socket_task_control
            FROM socket_tasks 
            INNER JOIN sensors 
            	ON socket_tasks.sensor_id=sensors.sensor_id AND sensors.adapter_id = '.$_GET['adapter_id'].'
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
                    $data[$i]['task_active'] = $row['task_active'];
                    $data[$i]['task_time_controlled'] = $row['task_time_controlled'];
                    $data[$i]['task_time_on'] = $row['task_time_on'];
                    $data[$i]['task_time_off'] = $row['task_time_off'];
                    $i++;
                }
                $result->free();
            }
        } 
        else
        {
            echo "ERROR: Could not able to execute $link. ";
        }

        if($result = @$link->query('SELECT * FROM sockets WHERE `adapter_id`='.$_GET['adapter_id']))
        {
            if($result->num_rows > 0)
            {
                $i = 0;
                while($row = $result->fetch_assoc())
                {
                    $sockets[$i]['socket_id'] = $row["socket_id"];
                    $sockets[$i]['socket_name'] = $row["socket_name"];
                    $sockets[$i]['socket_state'] = $row["socket_state"];
                    $sockets[$i]['socket_task_control'] = $row["socket_task_control"];
                    $i++;
                }
                $result->free();
            } 
            else
            {
               echo "Error: No sockets";;
            }
        }
        else
        {
            echo "Error: result ".$adapters['adapter_id'];
        }

    $link->close(); 
}
?>
<body>
    <div id="container">
        <!--Listwa 1 szczegóły-->
        <div class="panel">
            <div class="subPanelTop">
            <div style="float:left; margin-left: 120px; position: fixed;"><a href="index.php#<?php echo $_GET['adapter_id']; ?>" class="goBack" style="line-height: 20px">Powrót</a></div><br>
                Dodaj zadanie automatyczne do listwy
            </div>
            <?php if ($_SESSION['user_permission'] > 0)
            echo '
            <div class="subPanelDetails" style="padding: 5px;">
                <form name="add" method="post" action="task_add.php?adapter_id='.$_GET["adapter_id"].'">
                    <div class="tasksDiv">Typ zadania: <br>
                    <select name="tasks" id="taskSelect" style="margin-top: 5px;width: 270px;">
                        <option value="time_off">Wyłącz o wskazanej godzinie</option>
                        <option value="time_on">Włącz o wskazanej godzinie</option>
                        <option value="temp_up_off" id="temp_up_off" hide>Wyłącz, gdy temperatura wzrośnie powyżej</option>
                        <option value="temp_up_on">Włącz, gdy temperatura wzrośnie powyżej</option>
                        <option value="temp_down_off">Wyłącz, gdy temperatura spadnie poniżej</option>
                        <option value="temp_down_on">Włącz, gdy temperatura spadnie poniżej</option>
                        <option value="light_up_off">Wyłącz, gdy natężenie swiatła wzrośnie powyżej</option>
                        <option value="light_up_on">Włącz, gdy natężenie swiatła wzrośnie powyżej</option>
                        <option value="light_down_off">Wyłącz, gdy natężenie swiatła spadnie poniżej</option>
                        <option value="light_down_on">Włącz, gdy natężenie swiatła spadnie poniżej</option>
                    </select></div><br>
                    <div class="tasksDiv">Warunek: <br>
                    <font size="2">
                    Przykłady poprawnych warunków:<br>
                    HH:mm (czasowe zadanie)<br>
                    XX (poziom natężenia światła w %)<br>
                    TT,tt lub TT (wartość temperatury)<br>
                    </font><br>
                    <input type="text" name="condition" class="taskCondition" id="condition">
                    </div><br>
                    <div class="tasksDiv">
                        <input type="checkbox" name="timeControlledTask"> Kontrola czasowa zadania<br>
                        <div id="pokazane_warunki" style="margin-top: 10px;">
                            <input type="text" size="11" class="changeName" name="timeOn" placeholder="początek HH:mm"/>
                            <input type="text" size="11" class="changeName" name="timeOff" placeholder="koniec HH:mm"/>
                        </div>
                    </div><br>
                    <div class="tasksDiv">Wybór gniazda:<br>
                    <select name="sockets">';
                            foreach($sockets as $socket)
                            {
                                echo '<option value="'.$socket["socket_id"].'">'.$socket["socket_name"].'</option>';
                            }
                        
                    echo '</select></div><br>
                    <div class="tasksDiv">Wybierz powtarzalność zadania:<br>
                    <input type="radio" name="repeatability" value="0" checked> jednorazowe<br>
                    <input type="radio" name="repeatability" value="1"> powtarzalne<br></div>
                    <br>
                    <center><button name="submit" class="panelButton">Dodaj zadanie</button></center>
                    <div class="hintText" style="float: right;margin-top: 5px;">
                        Zadanie zostanie automatycznie dodane do listwy poniżej.
                    </div>
                </form>
            </div>
            
            </div>';
        ?>
        <div class="panel" id="tasks">
            <div class="subPanelTop">
                Menadżer zadań listwy
            </div>
            <div class="subPanelDetailsTasks">
                <!--<table class="logs" style="font-size: 14px;">
                    <tr>
                        <th class="logs" width="30px">warunek</th>
                        <th class="logs" width="30px">powtarzalnść</th>
                        <th class="logs" width="50px">użytkownik</th>
                        <th class="logs" width="60px">czas i data</th>
                        
                    </tr>-->
                    <?php
                        if (!empty($data))
                        {
                            foreach($data as $row)
                            {
                                echo '<div class="subPanelConfigAdapter" id="id-'.$row['task_id'].'">
                                        <table class="logs" style="font-size: 14px;">
                                            <tr>
                                                <th class="logs" width="20%">ID</th>
                                                <th class="logs" width="10%">stan</th>
                                                <th class="logs" width="70%">gniazdo</th>
                                                <th class="logs" width="30px">powtarzalność</th>
                                            </tr><tr class="logs">';
                                echo '<td class="logs">'.$row['task_id'].'</td>';
                                if ($row['task_active'] == "1")
                                    echo '<td class="logs"><a href="task_activation.php?task_id='.$row['task_id'].'&inactive=1" class="task_active"></a></td>';
                                else if ($row['task_active'] == "0")
                                    echo '<td class="logs"><a href="task_activation.php?task_id='.$row['task_id'].'&active=1" class="task_inactive"></a></td>';
                                else
                                    echo '<td class="logs">'.$row['task_active'].'</td>';
                                echo '<td class="logs">'.$row['socket_name'].'</td>';
                                if ($row['task_cycle'] == 1)
                                    echo '<td class="logs">powtarzalne</td>';
                                else if ($row['task_cycle'] == 0)
                                    echo '<td class="logs">jednorazowe</td>';
                                echo '</tr></table>
                                <table class="logs" style="font-size: 14px;">
                                            <tr>
                                                <th class="logs" width="150px">typ zadania</th>
                                            </tr><tr class="logs">';
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
                                    echo '</tr></table>';
                                echo '<table class="logs" style="font-size: 14px;">
                                            <tr>
                                                <th class="logs" width="20%">warunek</th>
                                                <th class="logs" width="35%">użytkownik</th>
                                                <th class="logs" width="45%">czas i data</th>
                                            </tr><tr class="logs">';
                                    echo '<td class="logs">'.$row['task_condition'].'</td>';
                                echo '<td class="logs">'.$row['task_user'].'</td>';
                                echo '<td class="logs">'.$row['task_time'].', '.$row['task_date'].'</td>';
                                echo '</tr></table>';
                                echo '<table class="logs" style="font-size: 14px;">
                                            <tr>
                                                <th class="logs" width="20%">blokada</th>
                                                <th class="logs" width="40%">błąd</th>
                                                <th class="logs" width="40%"><img src="clock.png" width="20px" style="margin-top: 5px;"/></th>';
                                                if ($_SESSION['user_permission'] > 0) echo '<th class="logs" width="40%" align="right"></th>
                                            </tr><tr class="logs">';
                                if ($row['socket_task_control'] == 1)
                                    echo '<td class="logs"><a href="socket_lock.php?socket_id='.$row['socket_id'].'&lock=0&task_id='.$row['task_id'].'"><img src="padlock_unlocked.png" width="30px" height="30px" title="Odblokuj gniazdo"/></a></td>';
                                else
                                    echo '<td class="logs"><center><a href="socket_lock.php?socket_id='.$row['socket_id'].'&lock=1&task_id='.$row['task_id'].'"><img src="padlock_locked.png" width="30px" height="30px" title="Zablokuj gniazdo"/></a></center></td>';
                                echo '<td class="logs"><font color="red">'.$row['task_error'].'</font></td>';
                                if ($row['task_time_controlled'] == 1)
                                    echo '<td class="logs" style="font-size: 12px;">Początek: <i>'.$row['task_time_on'].'</i><br>Koniec: <i>'.$row['task_time_off'].'</i></td>';
                                else
                                    echo '<td class="logs"></td>';
                                if ($_SESSION['user_permission'] > 0) echo '<td class="logs" align="right"><a href="task_cancel.php?task_id='.$row['task_id'].'" class="goBack">Usuń</a></td>';
                                echo '</tr></table>';
                                echo '</div>';
                            } 
                            
                        }
                        else
                        {
                            echo '</table><br>Brak zadań';
                        }
                    ?>
                    <center><div class="hintText">
                        Gniazda po zdefiniowaniu zadania są automatycznie blokowane, aby je odblokować należy nadusić otwartą kłódkę.<br> <br>   
                        Aby zatrzymać wykonanie zadania należy nacisnąć zieloną/czerwoną kropkę przy kolumnie stan.<br><br>
                        <?php
                            echo '<a href="task_mgmt.php?adapter_id='.$_GET['adapter_id'].'&pause=1" class="configLink">Zatrzymaj aktywne zadania</a><br><br>';
                            echo '<a href="task_mgmt.php?adapter_id='.$_GET['adapter_id'].'&cancel=1" style="margin-top: 5px;" class="configLink">Usuń wszystkie zadania</a>';
                        ?>
                    </div></center>
            </div>
        </div>
    </div>
    <script>
        $(function(){
            $("#condition").hide();  // By default use jQuery to hide the second modal

            // We can use the change(); function to watch the state of the select box and run some conditional logic every time it's changes to hide or show the second select box
            $("#taskSelect").change(function(){
                if( $("temp_up_off").is(:selected) ){
                    $("#condition").show();
                } else {
                    $("#condition").hide();
                }
            });
        });
    </script>
</body>
</html>


