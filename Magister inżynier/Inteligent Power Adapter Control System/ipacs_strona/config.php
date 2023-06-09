<?php
    require_once 'session_login.php';
    require_once 'mysql_connect.php';
    require_once 'system_settings.php';

    $cookie_name = "refresh_timeout";

    if(!isset($_COOKIE[$cookie_name]))
    {
        //echo "Cookie named '" . $cookie_name . "' is not set!";
        $cookie_value = "60";
        setcookie($cookie_name, $cookie_value, time() + (86400 * 365), "/"); // 86400 = 1 day
    }

    $page = $_SERVER['PHP_SELF'];
    $sec = $_COOKIE[$cookie_name];
    
    $link = @new mysqli($host, $db_user, $db_password, $db_name);
?>
<head>
    <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">    
</head>
<?php
    $cookie_name = "refresh_timeout";

    if(!isset($_COOKIE[$cookie_name])) 
    {
        //echo "Cookie named '" . $cookie_name . "' is not set!";
        $cookie_value = "5";
        setcookie($cookie_name, $cookie_value, time() + (86400 * 365), "/"); // 86400 = 1 day
    }
    
    $page = $_SERVER['PHP_SELF'];
    $sec = $_COOKIE[$cookie_name];

    include 'session_login.php';
    
    

?>
<body>
    <div id="container">
        <!--Listwa 1 szczegóły-->
        <div class="panel" id="configMain">
            <div class="subPanelTop" style="margin-bottom: 30px;">
                <div style="float:left; margin-left: 10px; position: fixed;"><a href="index.php" class="goBack" style="line-height: 20px">Powrót</a></div>
                Aktualna data i czas systemu: <b><?php echo date('d-m-Y').", ".date('H:i:s'); ?></b>
            </div>
            <div class="subPanelTop">
                Stan systemu
            </div>
            <div class="subPanelConfig" id="<?php echo $adapters['adapter_id']; ?>">
                <table class="configMain">
                    <tr class="configMain">
                        <th class="configMain">adapter</th>
                        <th class="configMain">czujniki</th>
                        <th class="configMain">gniazda</th>
                    </tr>
                    <?php include 'adapter_data.php'; ?> <!-- POBRANIE DANYCH Z BAZY -->
                    <?php foreach($hcas_adapters as $adapters) { ?>
                    <tr class="configMain">
                        <td class="configMain" id="<?php echo $adapters['adapter_id'] ?>"> 
                            <?php 
                                if ($adapters['adapter_beacon'] == 1) 
                                {
                                    if ($adapters['adapter_state'] == 0) 
                                        echo '<font color="red">Listwa nieobsługiwana</font><br><br>'; 
                                    else 
                                    {
                                        if ($adapters['adapter_website_control'] == 1)
                                            echo '<font color="#1aa142">Listwa obsługiwana</font><br><br>';
                                        else
                                            echo '<font color="#1aa142">Listwa obsługiwana częsciowo</font><br><br>';
                                    }
                                }
                                else if ($adapters['adapter_beacon'] == 0) 
                                {
                                    echo '<font color="red">Listwa nieobsługiwana</font><br><br>'; 
                                }
                                echo 'Status gniazd: ';
                                if ($adapters['adapter_website_control'] == 1)
                                {
                                    echo '<font color="#0d5e86">sterowalne</font><br><br>';
                                }
                                else
                                {
                                    echo '<font color="red">zablokowane ręcznie</font><br><br>';
                                }
                                echo 'ID listwy: '.$adapters['adapter_id'].'<br>Pokój: <i>'.$adapters['adapter_room'].'</i><br>Miejsce: <i>'.$adapters['adapter_location'].'</i><br><br>';
                                if ($adapters['adapter_state'] == 0)
                                {
                                    if ($adapters['adapter_connection'] == '3')
                                    {
                                        echo 'Stan połączenia: <font color="blue">zresetowane</font><br>';
                                    }
                                    else if ($adapters['adapter_connection'] == '2')
                                    {
                                        echo 'Stan połączenia: <font color="#1aa142">dobre</font><br>';
                                    }
                                    else if ($adapters['adapter_connection'] == '1')
                                    {
                                        echo 'Stan połączenia: <font color="orange">słabe</font><br>';
                                    }
                                    else if ($adapters['adapter_connection'] == '0')
                                    {
                                        echo 'Stan połączenia: <font color="red">brak odpowiedzi</font><br>';
                                    }
                                    if ($_SESSION['user_permission'] >= 0) echo 'Odpytywanie: ';
                                    if ($_SESSION['user_permission'] >= 0) if($adapters['adapter_beacon'] == '1')  echo 'włączone';
                                    if ($_SESSION['user_permission'] >= 0) if($adapters['adapter_beacon'] == '0')    echo 'wyłączone';
                                    if ($_SESSION['user_permission'] >= 0) echo '<br>';
                                    if ($_SESSION['user_permission'] == 2) echo 'Statystyka: '.round(((int)$adapters['packets_recieved']/(int)$adapters['packets_sent']),2,PHP_ROUND_HALF_EVEN).'<br>';
                                    if ($_SESSION['user_permission'] == 2) echo 'Moc nadawcza listwy: '.$adapters['adapter_powerLevel'].'<br>';
                                    if ($_SESSION['user_permission'] > 0) echo 'Próg słabego połączenia: '.$adapters['adapter_weak_signal'].'<br>';
                                    if ($_SESSION['user_permission'] == 2) echo 'Liczba prób połączenia: '.$adapters['adapter_waitNumber'].'';
                                    if ($_SESSION['user_permission'] == 1) echo 'Liczba prób połączenia: '.((int)$adapters['adapter_waitNumber']/3).' s';
                                     
                                    
                                }
                                else
                                {
                                    
                                
                                    if ($adapters['adapter_connection'] == '3')
                                    {
                                        echo 'Stan połączenia: <font color="blue">zresetowane</font><br>';
                                    }
                                    else if ($adapters['adapter_connection'] == '2')
                                    {
                                        echo 'Stan połączenia: <font color="#1aa142">dobre</font><br>';
                                    }
                                    else if ($adapters['adapter_connection'] == '1')
                                    {
                                        echo 'Stan połączenia: <font color="orange">słabe</font><br>';
                                    }
                                    else if ($adapters['adapter_connection'] == '0')
                                    {
                                        echo 'Stan połączenia: <font color="red">brak odpowiedzi </font><br>';
                                    }
                                    if ($_SESSION['user_permission'] >= 0) echo 'Odpytywanie: ';
                                    if ($_SESSION['user_permission'] >= 0) if($adapters['adapter_beacon'] == '1')  echo 'włączone';
                                    if ($_SESSION['user_permission'] >= 0) if($adapters['adapter_beacon'] == '0')  echo 'wyłączone';
                                    if ($_SESSION['user_permission'] >= 0) echo '<br>';
                                    if ($_SESSION['user_permission'] == 2) echo 'Statystyka: '.round(((int)$adapters['packets_recieved']/(int)$adapters['packets_sent']),2,PHP_ROUND_HALF_EVEN).'<br>';
                                    if ($_SESSION['user_permission'] == 2) echo 'Moc nadawcza listwy: '.$adapters['adapter_powerLevel'].'<br>';
                                    if ($_SESSION['user_permission'] > 0) echo 'Próg słabego połączenia: '.$adapters['adapter_weak_signal'].'<br>';
                                    if ($_SESSION['user_permission'] == 2) echo 'Liczba prób połączenia: '.$adapters['adapter_waitNumber'].'';
                                    if ($_SESSION['user_permission'] == 1) echo 'Czas prób połączenia: '.((int)$adapters['adapter_waitNumber']/3).' s';
                                }
                                if ($_SESSION['user_permission'] > 0) 
                                {
                                    if ($adapters['adapter_beacon'] == '1')
                                        echo '<br><br>Dostępne opcje dla listwy:<br>&nbsp&nbsp•&nbsp<a href="state_change.php?adapter_id='.$adapters['adapter_id'].'&adapter_beacon=0" class="configLink">Wyłącz odpytywanie listwy</a><br>';
                                    else
                                        echo '<br><br>Dostępne opcje dla listwy:<br>&nbsp&nbsp•&nbsp<a href="state_change.php?adapter_id='.$adapters['adapter_id'].'&adapter_beacon=1" class="configLink">Włącz odpytywanie listwy</a><br>';
                                }
                                if ($_SESSION['user_permission'] > 0) echo '&nbsp&nbsp•&nbsp<a href="task_mgmt.php?adapter_id='.$adapters['adapter_id'].'&cancel=1" class="configLink">Anuluj aktywne zadania</a><br>';
                                if ($_SESSION['user_permission'] > 0) echo '&nbsp&nbsp•&nbsp<a href="task_mgmt.php?adapter_id='.$adapters['adapter_id'].'&pause=1" class="configLink">Zatrzymaj aktywne zadania</a><br>';
                                if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a href="clear_errors.php?adapter_id='.$adapters['adapter_id'].'" class="configLink">Wyczyść błędy menadżera</a><br>';
                                if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a class="configLinkRemove">Dezaktywuj listwę</a>';
                                if ($_SESSION['user_permission'] > 0) echo '<div class="RemoveConfirm">Czy potwierdzasz usunięcie?
                                                                            <form action="remove.php?adapter_id='.$adapters['adapter_id'].'" method="POST">
                                                                            <input type="submit" name="removeButton" value="potwierdzam" class="changeName" style="margin: 5px;"/></form></div>';
                                if ($_SESSION['user_permission'] > 0) echo '&nbsp&nbsp•&nbsp<a class="configLinkChangeName">Zmień nazwę</a><br>';
                                if ($_SESSION['user_permission'] > 0) echo '<div class="changeNameInputs"><br>
                                                                            <form action="change_name.php?adapter_id='.$adapters['adapter_id'].'" method="POST">
                                                                            <input type="text" size="15" class="changeName" name="nowyPokoj" placeholder="'.$adapters['adapter_room'].'"/><br>
                                                                            <input type="text" style="margin-top:5px;" size="15" class="changeName" name="nowaLokacja" placeholder="'.$adapters['adapter_location'].'"/><br>
                                                                            <input type="submit" name="changeNameButton" value="Zmień" class="changeName" style="margin: 10px 0 0 22px;"/>
                                                                            </form></div>';
                                if ($_SESSION['user_permission'] > 0 && $adapters['adapter_connection'] == '0' || $_SESSION['user_permission'] > 1) echo '<br>Zarządzenie połączeniem:<br>';
                                if ($adapters['adapter_connection'] == '0')
                                {
                                    if ($_SESSION['user_permission'] > 0) echo '&nbsp&nbsp•&nbsp<a href="reset_connection.php?adapter_id='.$adapters['adapter_id'].'" class="configLink">Resetuj połączenie</a><br>';
                                }
                                if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a class="configWaitNumber">Ustaw liczbę prób połączenia</a><br>';
                                if ($_SESSION['user_permission'] == 2) echo '<div class="configWaitNumberForm"> 
                                                                            <form action="change_wait_number.php?adapter_id='.$adapters['adapter_id'].'" method="POST">
                                                                            <input type="text" size="3" max="3" name="waitNumber" class="changeName" placeholder="'.$adapters['adapter_waitNumber'].'" style="margin: 0;"/><br>
                                                                            <input type="submit" name="addButton" value="zmień" class="changeName"/>
                                                                            </form></div>';
                                
                                if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a class="configWeakSignalIndicator">Ustaw próg słabego sygnału</a><br>';
                                if ($_SESSION['user_permission'] == 2) echo '<div class="configWeakSignalIndicatorForm"> 
                                                                            <form action="change_weak_signal.php?adapter_id='.$adapters['adapter_id'].'" method="POST">
                                                                            <input type="text" size="3" max="3" name="weakSignal" class="changeName" placeholder="'.$adapters['adapter_weak_signal'].'" style="margin: 0;"/><br>
                                                                            <input type="submit" name="addButton" value="zmień" class="changeName"/>
                                                                            </form></div>';
                                if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a class="configPower">Zmień moc listwy</a><br>';
                                if ($_SESSION['user_permission'] == 2) {
                                                                            echo '<div class="configPowerForm"> 
                                                                            <form action="power_change.php?adapter_id='.$adapters['adapter_id'].'" method="POST">
                                                                            <select name="powerLevel" class="changePower">';
                                                                            if ($adapters['adapter_powerLevel'] == "MIN")
                                                                                echo '<option value="MIN" selected>Minimalna</option>';
                                                                            else
                                                                                echo '<option value="MIN">Minimalna</option>';
                                                                            if ($adapters['adapter_powerLevel'] == "LOW")
                                                                                echo '<option value="LOW" selected>Niska</option>';
                                                                            else
                                                                                echo '<option value="LOW">Niska</option>';
                                                                            if ($adapters['adapter_powerLevel'] == "HIGH")
                                                                                echo '<option value="HIGH" selected>Wysoka</option>';
                                                                            else
                                                                                echo '<option value="HIGH">Wysoka</option>';
                                                                            if ($adapters['adapter_powerLevel'] == "MAX")
                                                                                echo '<option value="MAX" selected>Maksymalna</option>';
                                                                            else
                                                                                echo '<option value="MAX">Maksymalna</option>';
                                                                            echo '</select><br>
                                                                            <input type="submit" name="addButton" value="zmień" class="changeName"/>
                                                                            </form></div>';
                                                                        }
                                
                            ?>
                        </td>
                        <td class="configMain"> 
                            <table class="configSub">
                                <?php 
                                    echo '<tr class="configSub"><td class="configSub">';
                                    if (empty($adapters['temp_sensor_id']))
                                    {
                                        echo 'Błąd: brak czujnika temperatury<br>';
                                        echo '&nbsp&nbsp•&nbsp<a class="configLinkAdd">Dodaj sensor</a><br>';
                                        echo '<div class="configAdd"><form action="add_new.php?adapter_id='.$adapters['adapter_id'].'&sensor=1" method="POST">';
                                        echo '<input type="text" size="15" class="changeName" name="sensorName" placeholder="nazwa sensora" style="margin-left: 0px;"/><br>';
                                        echo '<input type="text" size="15" class="changeName" name="sensorType" placeholder="type sensora" value="temperature" style="margin-left: 0px;display:none;"/>';
                                        echo '<input type="submit" name="addButton" value="dodaj" class="changeName"/>';
                                        echo '</form></div>';
                                    }
                                    else
                                    {
                                        if ($adapters['temp_sensor_state'] == 0)
                                        {
                                            echo '<font color="red">Czujnik temperatury wyłączony</font><br><br>';echo 'Sensor ID: '.$adapters['temp_sensor_id'].'<br><i>'.$adapters['temp_sensor_name'].'</i><br>Dane: '.round($adapters['temp_sensor_data'],2).'℃<br>Czas i data: '.$adapters['temp_sensor_data_time'].', '.$adapters['temp_sensor_data_date'].'<br><br>'; 
                                            if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a href="state_change.php?sensor_id='.$adapters['temp_sensor_id'].'&sensor_state=1" class="configLink">Włącz sensor</a><br>';
                                        }
                                        else if ($adapters['temp_sensor_state'] == 1)
                                        {
                                            echo '<font color="#1aa142">Czujnik temperatury włączony</font><br>';
                                            echo 'Sensor ID: '.$adapters['temp_sensor_id'].'<br><i>'.$adapters['temp_sensor_name'].'</i><br>Dane: '.round($adapters['temp_sensor_data'],2).'℃<br>Czas i data: '.$adapters['temp_sensor_data_time'].', '.$adapters['temp_sensor_data_date'].'<br>'; 
                                            if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a href="state_change.php?sensor_id='.$adapters['temp_sensor_id'].'&sensor_state=0" class="configLink">Wyłącz sensor</a><br>';
                                        }
                                        if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a class="configLinkRemove">Usuń sensor</a>';
                                        if ($_SESSION['user_permission'] == 2) echo '<div class="RemoveConfirm">Czy potwierdzasz usunięcie?<form action="remove.php?anchor_id='.$adapters['adapter_id'].'&sensor_id='.$adapters['temp_sensor_id'].'" method="POST"><input type="submit" name="removeButton" value="potwierdzam" class="changeName" style="margin: 5px;"/></form></div>';
                                        if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a class="configLinkChangeName">Zmień nazwę</a><br>';
                                        if ($_SESSION['user_permission'] == 2) echo '<div class="changeNameInputs"><form action="change_name.php?sensor_id='.$adapters['temp_sensor_id'].'&adapter_id='.$adapters['adapter_id'].'" method="POST"><input type="text" size="15" class="changeName" name="nowaNazwa" placeholder="'.$adapters['temp_sensor_name'].'"/><input type="submit" name="changeNameButton" value="Zmień" class="changeName" style="margin-left:10px;"/></form></div>';
                                        
                                    }
                                    echo '</td></tr>';
                                    echo '<tr class="configSub"><td class="configSub">';
                                    
                                    if (empty($adapters['light_sensor_id']))
                                    {
                                        echo 'Error: no light sensor<br>';
                                        echo '&nbsp&nbsp•&nbsp<a class="configLinkAdd">Dodaj sensor</a><br>';
                                        echo '<div class="configAdd"><form action="add_new.php?adapter_id='.$adapters['adapter_id'].'&sensor=1" method="POST">';
                                        echo '<input type="text" size="15" class="changeName" name="sensorName" placeholder="nazwa sensora" style="margin-left: 0px;"/><br>';
                                        echo '<input type="text" size="15" class="changeName" name="sensorType" placeholder="type sensora" value="light" style="margin-left: 0px;display:none;"/>';
                                        echo '<input type="submit" name="addButton" value="dodaj" class="changeName"/>';
                                        echo '</form></div>';
                                    }
                                    else
                                    {
                                        if ($adapters['light_sensor_state'] == 0)
                                        {
                                            echo '<font color="red">Czujnik światła wyłączony</font><br><br>';
                                            echo 'Sensor ID: '.$adapters['light_sensor_id'].'<br><i>'.$adapters['light_sensor_name'].'</i><br>Dane: '.round($adapters['light_sensor_data'],1).'%<br>Czas i data: '.$adapters['light_sensor_data_time'].', '.$adapters['light_sensor_data_date'].'<br>'; 
                                            if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a href="state_change.php?sensor_id='.$adapters['light_sensor_id'].'&sensor_state=1" class="configLink">Włącz sensor</a><br>';
                                        }
                                        else if ($adapters['light_sensor_state'] == 1)
                                        {
                                            echo '<font color="#1aa142">Czujnik światła włączony</font><br>';
                                            echo 'Sensor ID: '.$adapters['light_sensor_id'].'<br><i>'.$adapters['light_sensor_name'].'</i><br>Dane: '.round($adapters['light_sensor_data'],1).'%<br>Czas i data: '.$adapters['light_sensor_data_time'].', '.$adapters['light_sensor_data_date'].'<br>'; 
                                            if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a href="state_change.php?sensor_id='.$adapters['light_sensor_id'].'&sensor_state=0" class="configLink">Wyłącz sensor</a><br>';
                                        }
                                        
                                        if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a class="configLinkRemove">Usuń sensor</a>';
                                        if ($_SESSION['user_permission'] == 2) echo '<div class="RemoveConfirm">Czy potwierdzasz usunięcie?<form action="remove.php?anchor_id='.$adapters['adapter_id'].'&sensor_id='.$adapters['light_sensor_id'].'" method="POST"><input type="submit" name="removeButton" value="potwierdzam" class="changeName" style="margin: 5px;"/></form></div>';
                                        if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a class="configLinkChangeName">Zmień nazwę</a><br>';
                                        if ($_SESSION['user_permission'] == 2) echo '<div class="changeNameInputs"><form action="change_name.php?sensor_id='.$adapters['light_sensor_id'].'&adapter_id='.$adapters['adapter_id'].'" method="POST"><input type="text" size="15" class="changeName" name="nowaNazwa" placeholder="'.$adapters['light_sensor_name'].'"/><input type="submit" name="changeNameButton" value="Zmień" class="changeName" style="margin-left:10px;"/></form></div>';
                                    }
                                    echo '</td></tr>';
                                ?>
                            </table>
                        </td>
                        <td class="configMain"> 
                        <?php include 'socket_data.php'; ?>
                            <table class="configSub">
                                <?php 
                                    for($j = 0;$j < count($hcas_sockets[$adapters['adapter_id']]);$j++)
                                    {
                                        echo '<tr class="configSub"><td class="configSub">';
                                        echo 'Socket ID: '.$hcas_sockets[$adapters['adapter_id']][$j]['socket_id'].'<br><i>'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</i><br>'; 
                                        if ($hcas_sockets[$adapters['adapter_id']][$j]['socket_state'] == 0)
                                        {
                                            echo '<font color="red">Gniazdo wyłączone</font><br>';
                                        }
                                        else
                                        {
                                            echo '<font color="#1aa142">Gniazdo włączone</font><br>';
                                        }
                                        if ($hcas_sockets[$adapters['adapter_id']][$j]['socket_task_control'] == 1)
                                        {
                                            echo 'Kontrola przez menadżer zadań<br>';
                                        }
                                        else
                                        {
                                            echo 'Gniazdo sterowane ze strony<br>';
                                        }
                                        
                                        if ($_SESSION['user_permission'] == 2) echo '&nbsp&nbsp•&nbsp<a class="configLinkRemove">Usuń gniazdo</a>';
                                        if ($_SESSION['user_permission'] == 2) echo '<div class="RemoveConfirm">Czy potwierdzasz usunięcie?<form action="remove.php?anchor_id='.$adapters['adapter_id'].'&socket_id='.$hcas_sockets[$adapters['adapter_id']][$j]['socket_id'].'" method="POST"><input type="submit" name="removeButton" value="potwierdzam" class="changeName" style="margin: 5px;"/></form></div>';
                                        if ($_SESSION['user_permission'] > 0) echo '&nbsp&nbsp•&nbsp<a class="configLinkChangeNameSockets">Zmień nazwę</a><br>';
                                        if ($_SESSION['user_permission'] > 0) echo '<div class="changeNameInputsSockets"><form action="change_name.php?socket_id='.$hcas_sockets[$adapters['adapter_id']][$j]['socket_id'].'&adapter_id='.$adapters['adapter_id'].'" method="POST"><input type="text" size="15" class="changeName" name="nowaNazwa" placeholder="'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'"/><input type="submit" name="changeNameButton" value="Zmień" class="changeName" style="margin-left:10px;"/></form></div>';
                                        echo '</td></tr>';
                                    }
                                    if (count($hcas_sockets[$adapters['adapter_id']]) < 4)
                                    {
                                        echo '<tr class="configSub"><td class="configSub">';
                                        if ($_SESSION['user_permission'] == 2)echo '&nbsp&nbsp•&nbsp<a class="configLinkAdd">Dodaj gniazdo</a><br>';
                                        echo '<div class="configAdd"><form action="add_new.php?adapter_id='.$adapters['adapter_id'].'&socket=1" method="POST">';
                                        echo '<input type="text" size="15" class="changeName" name="socketName" placeholder="nazwa gniazda" style="margin-left: 0px;"/><br>';
                                        echo '<input type="submit" name="addButton" value="dodaj" class="changeName"/>';
                                        echo '</form></div></td></tr>';
                                    }
                                ?>
                            </table>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if ($_SESSION['user_permission'] == 2) { ?>
                    <tr class="configMain">
                        <td class="configMain">
                            &nbsp&nbsp•&nbsp<a class="configLinkAdd">Dodaj listwę</a><br>
                            <div class="configAdd"><form action="add_new.php?adapter_id=<?php echo $adapters['adapter_id']; ?>&adapter=1" method="POST">
                                <input type="text" size="15" class="changeName" name="adapterRoom" placeholder="pokój" style="margin-left: 0px;"/><br>
                                <input type="text" size="15" class="changeName" name="adapterLocation" placeholder="miejsce" style="margin-left: 0px; margin-top: 5px;"/><br>
                                <input type="submit" name="addButton" value="dodaj" class="changeName"/></form>
                            </div>
                        </td>
                        <td class="configMain">
                        </td>
                        <td class="configMain">
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
            
            </div>
            <?php if ($_SESSION['user_permission'] == 2) 
            {
                echo '<div class="panel">
                    <div class="subPanelTop">
                        <center>
                            <a class="goBack" href="/phpmyadmin" style="line-height: 20px;margin-bottom: 3px;">PhpMyAdmin</a>
                            <a class="goBack" href="system_reboot.php" style="line-height: 20px">Restart systemu</a>
                            <a href="config_logs.php" class="goBack" style="line-height: 20px">Ostatnie zmiany konfiguracji</a>
                            <a class="goBack" href="logs.php" style="font-size: 17px;line-height: 20px">Dziennik wszystkich zmian</a>
                        </center>
                    </div>
                </div>';
            }
            ?>
            <?php if ($_SESSION['user_permission'] >= 1) 
            {
                echo '
                <div class="panel" id="settings">
                    <div class="subPanelTop">
                        Ustawienia stacji matki
                    </div>
                    <div class="subpanels" style="height: 180px;">
                        <div class="subpanelConfig_2" style="height:100%;font-size:15px;">
                            <center><p style="margin-top:5px;">Zmień godziny i daty</p></center>
                            <div style="margin:20px 0 0 50px;float: left;">
                                <form action="change_settings.php" method="POST">
                                    Podaj nową godzinę:<br>
                                    <input type="text" name="newTime" class="configInput" style="margin-top:5px;margin-bottom:5px;" placeholder="HH:mm:ss"><br>
                                    <input name="timeSubmit" type="submit" value="Zmień godzinę" class="changeName">
                                </form>
                            </div>
                            <div style="margin:20px 0 0 10px;float: left;">
                                <form action="change_settings.php" method="POST">
                                    Podaj nową datę:<br>
                                    <input type="text" name="newDate" class="configInput" style="margin-top:5px;margin-bottom:5px;" placeholder="RRRR-MM-DD"><br>
                                    <input name="dateSubmit" type="submit" value="Zmień datę" class="changeName">
                                </form>
                            </div>
                        </div>
                        <div class="subpanelConfig_2" style="margin-left: 10px;height:100%">
                            <center><p style="margin-top:5px;">Zmień mocy nadajnika</p></center>
                            <div style="margin:30px 0 0 50px;font-size:17px;font-size:15px;">
                                <form action="change_settings.php" method="POST">
                                    Wybierz moc nadawczą:<br>
                                    <select name="power" class="changePower" style="margin-top:10px;margin-bottom:5px;">';
                                    if ($power == "MIN")
                                        echo '<option value="MIN" selected>Minimalna</option>';
                                    else
                                        echo '<option value="MIN">Minimalna</option>';
                                    if ($power == "LOW")
                                        echo '<option value="LOW" selected>Niska</option>';
                                    else
                                        echo '<option value="LOW">Niska</option>';
                                    if ($power == "HIGH")
                                        echo '<option value="HIGH" selected>Wysoka</option>';
                                    else
                                        echo '<option value="HIGH">Wysoka</option>';
                                    if ($power == "MAX")
                                        echo '<<option value="MAX" selected>Maksymalna</option>';
                                    else
                                        echo '<option value="MAX">Maksymalna</option>';
                                    echo '</select><br>
                                    <input type="submit" name="powerSubmit" value="Zmień moc" class="changeName" style="">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>';
            }
            ?>
            <?php if ($_SESSION['user_permission'] == 2) 
            {
                echo '<div class="panel" id="userMgmt">
                    <div class="subPanelTop">
                        Ustawienia użytkowników
                    </div>
                    <div class="subpanels" style="">
                        <div class="subpanelConfig_2" style="min-height: 370px;";>
                            <center><p style="margin-top:5px;">Dodawanie użytkownika</p></center>
                            <div style="margin:30px 0 0 50px;font-size:15px;">';
                            if(isset($_SESSION['error']))	
                            {
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                            }
                            echo '<form action="user.php" method="POST">
                                    Podaj nazwe uzytkownika:<br>
                                    <input type="text" name="username" placeholder="login" class="configInput" maxlength="10" style="margin-top:5px;margin-bottom:5px;"><br>
                                    Podaj hasło użytkownika:<br>
                                    <input type="password" name="password" placeholder="hasło" class="configInput" style="margin-top:5px;margin-bottom:5px;"><br>
                                    Powtórz hasło:<br>
                                    <input type="password" name="password_2" placeholder="hasło" class="configInput" style="margin-top:5px;margin-bottom:5px;"><br>
                                    <p  style="margin-top:5px;margin-bottom:5px;">Wybierz typ konta:</p>
                                    <select name="permissions" class="permissions">
                                        <option value="0">gość</option>
                                        <option value="1" selected>użytkownik</option>
                                        <option value="2">administrator</option>
                                    </select>
                                    <br><br>
                                    <input type="submit" name="addUser" value="Dodaj użytkownika" class="changeName">
                                    <input type="submit" name="changePassword" value="Zmień hasło" class="changeName" style="margin-left: 5px;">
                                </form>
                            </div>
                        </div>
                        <div class="subpanelConfig_2" style="margin-left: 10px;height:370px;">
                            <center><p style="margin-top:5px;">Zmień uprawnień</p></center>
                            <div style="margin:30px 0 0 50px;font-size:15px;">';
                            if(isset($_SESSION['info']))	
                            {
                                echo $_SESSION['info'];
                                unset($_SESSION['info']);
                            }
                            echo '<form action="user.php" method="POST">';
                                    echo 'Wybierz użytkownika:<br>
                                    <select name="username" class="permissions" style="margin-top: 5px;">';
                                    if($result = @$link->query('SELECT `user_login`, `user_permission`, `user_blocked`, `user_last_login` FROM users'))
                                    {
                                        if($result->num_rows > 0)
                                        {
                                            while($row = $result->fetch_assoc())
                                            {
                                                echo '<option value="'.$row["user_login"].'">'.$row["user_login"].' &emsp;[';
                                                if ($row["user_permission"] == 0)
                                                    echo 'gość';
                                                else if ($row["user_permission"] == 1)
                                                    echo 'użytkownik';
                                                else if ($row["user_permission"] == 2)
                                                    echo 'admin';
                                                if ($row["user_blocked"] == 1)
                                                    echo ', zablokowany';
                                                echo ', '.$row['user_last_login'].']</option>';
                                            }
                                        }
                                    }
                                    $result->free();
                                    echo '</select><br>';
                                    echo '<p  style="margin-top:5px;margin-bottom:5px;">Wybierz typ konta:</p>
                                    <select name="permissions" class="permissions">
                                        <option value="0">gość</option>
                                        <option value="1" selected>użytkownik</option>
                                        <option value="2">administrator</option>
                                    </select>
                                    <br><br>
                                    <input type="submit" name="changePermission" value="Zmień uprawnienia i odblokuj" class="changeName">
                                    <input type="submit" name="blockUser" value="Zablokuj użytkownika" class="changeName" style="margin-left: 5px;">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>'; 
            }
            ?>
            <div class="panel" id="settings">
                <div class="subPanelTop">
                    Ustawienia konta
                </div>
                <div class="subpanels" style="height: 230px;">
                    <div class="subpanelConfig_2" style="height:100%";>
                        <center><p style="margin-top:5px;">Zmień hasła</p></center>
                        <div style="margin:30px 0 0 50px;font-size:15px;">
                            <form action="change_password.php" method="POST">
                                Podaj nowe hasło:<br>
                                <input type="password" name="newPassword" class="configInput" placeholder="hasło" style="margin-top:5px;margin-bottom:5px;"><br>
                                Powtórz nowe hasło:<br>
                                <input type="password" name="newPassword_2" class="configInput" placeholder="hasło" style="margin-top:5px;margin-bottom:5px;"><br>
                                <input type="submit" value="Zmień haslo" class="changeName">
                            </form>
                        </div>
                    </div>
                    <div class="subpanelConfig_2" style="margin-left: 10px;height:100%;">
                        <center><p style="margin-top:5px;">Zmień czasu odświeżania strony</p></center>
                        <div style="margin:30px 0 0 50px;font-size:15px;">
                            <form action="refresh.php" method="POST">
                                Domyślne odświeżanie wynosi: 60 sekund <br>
                                Aktualny czas odświeżania: <?php echo $_COOKIE['refresh_timeout']; ?> sekund<br><br>
                                Podaj czas odświeżania strony głównej i konfiguracji:<br>
                                <input type="text" name="refresh_timeout" maxlength="4" size="4" class="configInput" placeholder="<?php echo $_COOKIE['refresh_timeout']; ?>" style="margin-top:5px;margin-bottom:5px;">&nbsp<br>
                                <input type="submit" value="Zmień czas" name="refreshSubmit"  class="changeName">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
           
        </div>
        
    </div>

    <script>
        a = document.getElementsByClassName("configLinkChangeName").length;
        for(i = 0; i < a; i++) 
        {
            if(document.getElementsByClassName("configLinkChangeName")[i])
            {
                let icon = document.getElementsByClassName("configLinkChangeName")[i];
                let x = i;
                icon.addEventListener('click',function()
                {
                    var l = document.getElementsByClassName("changeNameInputs")[x];
                    if(l.classList.contains('active'))
                    {
                        l.classList.remove('active');
                    }
                    else
                    {
                        l.classList.add('active');
                    }
                });
                        
            }
        }

        a = document.getElementsByClassName("configLinkChangeNameSockets").length;
        for(i = 0; i < a; i++) 
        {
            if(document.getElementsByClassName("configLinkChangeNameSockets")[i])
            {
                let icon = document.getElementsByClassName("configLinkChangeNameSockets")[i];
                let x = i;
                icon.addEventListener('click',function()
                {
                    var l = document.getElementsByClassName("changeNameInputsSockets")[x];
                    if(l.classList.contains('active'))
                    {
                        l.classList.remove('active');
                    }
                    else
                    {
                        l.classList.add('active');
                    }
                });
                        
            }
        }

        a = document.getElementsByClassName("configLinkRemove").length;
        for(i = 0; i < a; i++) 
        {
            if(document.getElementsByClassName("configLinkRemove")[i])
            {
                let icon = document.getElementsByClassName("configLinkRemove")[i];
                let x = i;
                icon.addEventListener('click',function()
                {
                    var l = document.getElementsByClassName("RemoveConfirm")[x];
                    if(l.classList.contains('active'))
                    {
                        l.classList.remove('active');
                    }
                    else
                    {
                        l.classList.add('active');
                    }
                });
                        
            }
        }

        a = document.getElementsByClassName("configLinkAdd").length;
        for(i = 0; i < a; i++) 
        {
            if(document.getElementsByClassName("configLinkAdd")[i])
            {
                let icon = document.getElementsByClassName("configLinkAdd")[i];
                let x = i;
                icon.addEventListener('click',function()
                {
                    var l = document.getElementsByClassName("configAdd")[x];
                    if(l.classList.contains('active'))
                    {
                        l.classList.remove('active');
                    }
                    else
                    {
                        l.classList.add('active');
                    }
                });
                        
            }
        }

        a = document.getElementsByClassName("configPower").length;
        for(i = 0; i < a; i++) 
        {
            if(document.getElementsByClassName("configPower")[i])
            {
                let icon = document.getElementsByClassName("configPower")[i];
                let x = i;
                icon.addEventListener('click',function()
                {
                    var l = document.getElementsByClassName("configPowerForm")[x];
                    if(l.classList.contains('active'))
                    {
                        l.classList.remove('active');
                    }
                    else
                    {
                        l.classList.add('active');
                    }
                });
                        
            }
        }

        a = document.getElementsByClassName("configWaitNumber").length;
        for(i = 0; i < a; i++) 
        {
            if(document.getElementsByClassName("configWaitNumber")[i])
            {
                let icon = document.getElementsByClassName("configWaitNumber")[i];
                let x = i;
                icon.addEventListener('click',function()
                {
                    var l = document.getElementsByClassName("configWaitNumberForm")[x];
                    if(l.classList.contains('active'))
                    {
                        l.classList.remove('active');
                    }
                    else
                    {
                        l.classList.add('active');
                    }
                });
                        
            }
        }

        a = document.getElementsByClassName("configWeakSignalIndicator").length;
        for(i = 0; i < a; i++) 
        {
            if(document.getElementsByClassName("configWeakSignalIndicator")[i])
            {
                let icon = document.getElementsByClassName("configWeakSignalIndicator")[i];
                let x = i;
                icon.addEventListener('click',function()
                {
                    var l = document.getElementsByClassName("configWeakSignalIndicatorForm")[x];
                    if(l.classList.contains('active'))
                    {
                        l.classList.remove('active');
                    }
                    else
                    {
                        l.classList.add('active');
                    }
                });
                        
            }
        }
    </script>
</body>
</html>