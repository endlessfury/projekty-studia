<?php
    $cookie_name = "refresh_timeout";

    if(!isset($_COOKIE[$cookie_name]))
    {
        //echo "Cookie named '" . $cookie_name . "' is not set!";
        $cookie_value = "60";
        setcookie($cookie_name, $cookie_value, time() + (86400 * 365), "/"); // 86400 = 1 day
    }

    $page = $_SERVER['PHP_SELF'];
    $sec = $_COOKIE[$cookie_name];

	include 'session_login.php';
?>
<head>
    <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">    
</head>
<body>
    <div id="container">
        <!--MENU-->
        <div class="panel">
            <div class="subPanelTop">
                Praca dyplomowa magisterska pt.<br>
                <font size="6">SYSTEM INTELIGENTNEGO STEROWANIA LISTWAMI ZASILANIA<br></font>
                <a class="goBack" href="info.php" style="font-size: 17px;line-height: 20px">Informacje</a>
                <a class="goBack" href="all_tasks.php" style="font-size: 17px;line-height: 20px">Podgląd zadań automatycznych</a>
                <a class="goBack" href="config.php" style="font-size: 17px;line-height: 20px">Panel konfiguracyjny</a>
                <a class="goBack" href="logout.php" style="font-size: 17px;line-height: 20px">Wyloguj się <font color="#7c7c7c"><?php if ($_SESSION['logged'] == true) echo $_SESSION['user_login']; ?></font></a>
            </div>
        </div>
        <!--Get info about adapters-->
        <?php include 'adapter_data.php'; ?>
        <!--Make a loop for adapters-->
        <?php foreach($hcas_adapters as $adapters) { ?>
        <!--Creating layout-->
        <div class="panel" id="<?php echo $adapters["adapter_id"]; ?>">
            <!--Top panel for adapter name-->
            <div class="subPanelTop">
                <?php
                    echo '<div class="idListwy">Listwa nr '.$adapters["adapter_id"].'</div>';
                    echo '<div><b>POKÓJ</b>: <p class="adapterName">'.$adapters["adapter_room"].'</p>, <b>MIEJSCE</b>: <p class="adapterName">'.$adapters["adapter_location"].'</p></div>';
                    if ($adapters['adapter_beacon'] and $adapters['adapter_state'])
                    {
                        if ($adapters['adapter_connection'] == '1')
                        {
                            echo '<div class="connectionInfo">SŁABE POŁĄCZENIE</div>';
                        }
                        
                ?>
            </div>
            <div class="subpanels">
                <!--Panel devided for 3 subpanels-->
                <div class="subpanel">
                    <!--First subpanel for buttons-->
                    <!--Get the info about sockets-->
                    <center>Stan gniazd listwy<br></center>
                   <?php include 'sockets.php'; ?>

                </div>
                <!--<div class="subpanel">
                    Wykres temperatury z ostatnich 24h<br>
                    <?php include 'main_graph.php' ?>
                </div>-->
                <div class="subpanel">
                    Dostępne opcje listwy<br>
                    <center><div class="subPanelButtons" style="margin-bottom: 90px;">
                        <a href="socket_logs.php?adapter_id=<?php echo $adapters['adapter_id']; ?>" class="taskManager" style="margin-top: 10px;">Rejestr gniazd</a>
                        <a href="tasks.php?adapter_id=<?php echo $adapters['adapter_id']; ?>" class="taskManager" style="margin-top: 10px;">Zadania automatyczne</a><br>
                        <div class="activeTasks">
                            Liczba aktywnych zadań: 
                            <?php echo '<font color="#0d5e86">'.$adapters['adapter_tasks'].'</font>'; ?> 
                        </div>
                    </div></center>
                    <center>Stan gniazd:  
                    <?php
                        if ($adapters['adapter_website_control'] == '1')
                            echo '<font color="#0d5e86">sterowalne</font>';
                        else echo '<font color="red">zablokowane</font>';
                    ?>
                    </center>
                </div>
                <div class="subpanel">
                    <center>Dane z czujników<br><br></center>
                <table border=0 width="100%">
                    <tr>
                        <?php
                            if ($adapters['temp_sensor_state'])
                            {
                                echo '<td><b>Aktualna temperatura: ';
                                if ($adapters['temp_sensor_data'] >= 30) echo '<font color="red">';
                                else echo '<font color="#0d5e86">';
                                echo round($adapters['temp_sensor_data'],0).'℃</font></b></td>';
                            }
                            else
                                echo '<td><b><font color="red">Czujnik temperatury wyłączony</font></b></td>';
                        ?>
                    </tr> 
                    <tr>
                        <?php
                            if ($adapters['light_sensor_state'])
                                echo '<td><b>Aktualna jasność: <font color="#0d5e86">'.round($adapters['light_sensor_data'],0).'%</font></b></td>';
                            else
                                echo '<td><b><font color="red">Czujnik światła wyłączony</font></b></td>';
                        ?>
                    </tr>
                    <tr>
                        <td><br>Ostatnie 24 godziny:<br></td>
                    </tr>
                    <tr>
                        <td>Maksymalna temperatura: <font color="#0d5e86"><?php echo round(max($data_main_graph),0); ?>℃</font></td>
                    </tr>
                    <tr>
                        <td>Minimalna temperatura: <font color="#0d5e86"><?php echo round(min($data_main_graph),0); ?>℃</font></td>
                    </tr>
                    <tr>
                        <td>Średnia temperatura: <font color="#0d5e86"><?php echo round($avarage,1); ?>℃</font></td>
                    </tr>
                </table>
                <a href="details.php?adapter_id=<?php echo $adapters["adapter_id"]; ?>" class="showMore" style="margin-top: 20px;">Wizualizacja danych</a>
                <!--Clear the arrays-->
                <?php
                    unset($data_main_graph);
                    unset($time_main_graph);
                    unset($avarageTable);
                    unset($avarage);
                ?>
                
                    
                </div>
            </div> <!--subpanels end-->
            <?php
                }
                else
                {
                    if ($adapters['adapter_beacon'] == '0')
                    {
                        echo '<font color="red"><b>LISTWA NIEOBSŁUGIWANA</b></font><br><div class="hintText" style="margin-top: 0;">Aby obsłużyć listwę należy włączyć odpytywanie listwy z panelu konfiguracyjnego</div>';
                    }
                    else if ($adapters['adapter_state'] == '0')
                    {
                        echo '<font color="red"><b>OBSLUGA LISTWY WSTRZYMANA Z POWODU BRAKU ODPOWIEDZI</b></font>';
                        echo '<div class="connectionInfo_off">';
                        if ($_SESSION['user_permission'] > 0) echo '<div class="hintText" style="margin-top: 0;">Listwa będzie ponownie obsługiwana po powrocie połączenia</div>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
            ?>
        </div>
        <?php } ?>
        
        <!--<div class="panel">
            <div class="subPanelTop">
                Niniejsza strona służy do sterowania systemem. Poznaj<a href="author.php" class="configLink">autora projektu</a>!
            </div>
        </div>-->
    </div>

  

</body>
</html>
