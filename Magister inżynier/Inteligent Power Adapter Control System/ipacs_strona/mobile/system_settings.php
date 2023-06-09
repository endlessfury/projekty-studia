<?php
    require_once "mysql_connect.php";


    $link = @new mysqli($host, $db_user, $db_password, $db_name);
    
    // Check connection
    if ($link->connect_errno!=0)
    {
        //echo "Error: ".$link->connect_errno;
    }
    else
    {
        if($result = @$link->query('SELECT * FROM system_settings'))
        {
            if($result->num_rows > 0)
            {
                while($row = $result->fetch_assoc())
                {
                    if ($row['setting_name'] == "channel")
                        $channel = $row["setting_value"];
                    else if ($row['setting_name'] == "power")
                        $power = $row["setting_value"];
                }
                // Close result set
                $result->free();
            }
        }
        // Close connection
        $link->close(); 
    }
?>

