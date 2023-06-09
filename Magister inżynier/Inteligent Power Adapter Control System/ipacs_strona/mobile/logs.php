<?php
    require_once 'session_login.php';
?>
<body>
    <div id="container">
        <!--Listwa 1 szczegóły-->
        <div class="panel">
            <div class="subPanelTop">
                <div style="float:left; margin-left: 10px; position: fixed;"><a href="<?php echo $_SERVER['HTTP_REFERER'].'#'.$_GET['adapter_id']; ?>" class="goBack" style="line-height: 20px">Powrót</a></div>
                Dziennik zmian stanów wszystkich gniazd 
            </div>
            <div class="subPanelDetails">
                <table class="logs" style="">
                    <tr>
                        <th class="logs">ID logu</th>
                        <th class="logs">adapter</th>
                        <th class="logs">gniazdo</th>
                        <th class="logs">stan gniazda</th>
                        <th class="logs">kto</th>
                        <th class="logs">czas</th>
                        <th class="logs">data</th>
                    </tr>
                        <?php
                            require_once "mysql_connect.php";

                            $link = @new mysqli($host, $db_user, $db_password, $db_name);
                            
                            // Check connection
                            if ($sensors_link->connect_errno!=0)
                            {
                                echo "Error: ".$sensors_link->connect_errno;
                            }
                            else
                            {
                                if($result = @$link->query('
                                SELECT log_id, adapters.adapter_room, adapters.adapter_location, sockets.socket_name, socket_logs.socket_state, last_changed, log_time, log_date
                                    FROM socket_logs
                                    INNER JOIN adapters ON socket_logs.adapter_id=adapters.adapter_id
                                    INNER JOIN sockets ON socket_logs.socket_id=sockets.socket_id
                                    ORDER by `log_id` DESC LIMIT 100
                                '))
                                {
                                    if($result->num_rows > 0)
                                    {
                                        while($row = $result->fetch_assoc())
                                        {
                                            echo '<tr class="logs">';
                                                echo '<td class="logs">'.$row['log_id'].'</td>';
                                                echo '<td class="logs">'.$row['adapter_room'].', '.$row['adapter_location'].'</td>';
                                                echo '<td class="logs">'.$row['socket_name'].'</td>';
                                                if ($row['socket_state'])
                                                    echo '<td class="logs">włączenie</td>';
                                                else
                                                    echo '<td class="logs">wyłączenie</td>';
                                                echo '<td class="logs">'.$row['last_changed'].'</td>';
                                                echo '<td class="logs">'.$row['log_time'].'</td>';
                                                echo '<td class="logs">'.$row['log_date'].'</td>';
                                            echo '</tr>';
                                        }
                                        $result->free();
                                    } 
                                    else
                                    {
                                        echo "No records matching your query were found.";
                                    }
                                } 
                                else
                                {
                                    echo "ERROR: Could not able to execute $link. ";
                                }

                            $link->close(); 
                        }
                        ?>
                </table>
            </div>
            
        </div>
        
    </div>
</body>
</html>