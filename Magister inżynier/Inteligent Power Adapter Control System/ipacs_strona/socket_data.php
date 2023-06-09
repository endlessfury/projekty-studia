<?php
    require_once "mysql_connect.php";
    require_once 'session_login.php';


    $socket_data_link = @new mysqli($host, $db_user, $db_password, $db_name);
    
    // Check connection
    if ($socket_data_link->connect_errno!=0)
    {
        echo "Error: ".$socket_data_link->connect_errno;
    }
    else
    {
        if($result = @$socket_data_link->query('SELECT * FROM sockets WHERE `adapter_id`='.$adapters['adapter_id']))
        {
            if($result->num_rows > 0)
            {
                $i = 0;
                while($row = $result->fetch_assoc())
                {
                    $hcas_sockets[$adapters['adapter_id']][$i]['socket_id'] = $row["socket_id"];
                    $hcas_sockets[$adapters['adapter_id']][$i]['socket_name'] = $row["socket_name"];
                    $hcas_sockets[$adapters['adapter_id']][$i]['socket_state'] = $row["socket_state"];
                    $hcas_sockets[$adapters['adapter_id']][$i]['socket_task_control'] = $row["socket_task_control"];
                    $i++;
                }
                //for($j = 0;$j < count($hcas_sockets[$adapters['adapter_id']]);$j++)
                    //echo $hcas_sockets[$adapters['adapter_id']][$j]['socket_id'].', '.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].', '.$hcas_sockets[$adapters['adapter_id']][$j]['socket_state'].', '.$hcas_sockets[$adapters['adapter_id']][$j]['socket_control'].'<br>';
                
                
                // Close result set
                $result->free();
            } 
            else
            {
               echo "<center><br>Brak zdefiniowanych gniazd</center>";;
            }
        }

        // Close connection
        $socket_data_link->close(); 
    }
?>