<?php
    require_once 'session_login.php';
?>
<body>
    <div id="container">
        <!--Listwa 1 szczegóły-->
        <div class="panel">
            <div class="subPanelTop">
                <div style="float:left; margin-left: 10px; position: fixed;"><a href="<?php echo $_SERVER['HTTP_REFERER'].'#'.$_GET['adapter_id']; ?>" class="goBack" style="line-height: 20px">Powrót</a></div>
                Ostatnie zmiany konfiguracji 
            </div>
            <div class="subPanelDetails">
                <table class="logs" style="">
                    <tr>
                        <th class="logs">ID zmiany</th>
                        <th class="logs">typ zmiany</th>
                        <th class="logs">ustawienie 1</th>
                        <th class="logs">ustawienie 2</th>
                        <th class="logs">ustawienie 3</th>
                        <th class="logs">czas</th>
                        <th class="logs">data</th>
                        <th class="logs">aktywność</th>
                        <th class="logs">komentarz</th>
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
                                if($result = @$link->query('SELECT * FROM job_list ORDER BY job_id DESC LIMIT 50'))
                                {
                                    if($result->num_rows > 0)
                                    {
                                        while($row = $result->fetch_assoc())
                                        {
                                            echo '<tr class="logs">';
                                                echo '<td class="logs">'.$row['job_id'].'</td>';
                                                echo '<td class="logs">'.$row['job_type'].'</td>';
                                                echo '<td class="logs">'.$row['job_setting'].'</td>';
                                                echo '<td class="logs">'.$row['job_setting2'].'</td>';
                                                echo '<td class="logs">'.$row['job_setting3'].'</td>';
                                                echo '<td class="logs">'.$row['job_time'].'</td>';
                                                echo '<td class="logs">'.$row['job_date'].'</td>';
                                                echo '<td class="logs">'.$row['job_active'].'</td>';
                                                echo '<td class="logs">'.$row['job_comment'].'</td>';
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