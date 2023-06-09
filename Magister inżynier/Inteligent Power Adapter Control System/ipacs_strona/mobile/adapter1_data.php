<?php
    /* Attempt MySQL server connection. Assuming you are running MySQL
    server with default setting (user 'root' with no password) */
    $a1_data_link = mysqli_connect("localhost", "pi", "pi2018pi", "Home_control_and_survey");
    
    // Check connection
    if($a1_data_link == false)
    {
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }
    
    // Attempt select query execution
    $a1_data_sql = 'SELECT * FROM adapters WHERE adapter_id=1';
    if($result = mysqli_query($a1_data_link, $a1_data_sql))
    {
        if(mysqli_num_rows($result) > 0)
        {
            while($row = mysqli_fetch_array($result))
            {
                $a1_room = $row['adapter_room'];
                $a1_location = $row['adapter_location'];
                $a1_state = $row['adapter_state'];
            }
            // Close result set
            mysqli_free_result($result);
        } 
        else
        {
            echo "No records matching your query were found.";
        }
    } 
    else
    {
        echo "ERROR: Could not able to execute $a1_data_sql. " . mysqli_error($a1_data_link);
    }

    
    $a1_data_sql2 = 'SELECT * FROM sockets WHERE adapter_id=1';
    {
        if($result = mysqli_query($a1_data_link, $a1_data_sql2))
        {
            if(mysqli_num_rows($result) > 0)
            {
                $i = 0;
                while($row = mysqli_fetch_array($result))
                {
                    $a1_socketNames[$i] = $row['socket_name'];
                    $a1_socketStates[$i] = $row['socket_state'];
                    $i++;
                }
                // Close result set
                mysqli_free_result($result);
            } 
            else
            {
                echo "No records matching your query were found.";
            }
        } 
        else
        {
            echo "ERROR: Could not able to execute $a1_data_sql2. " . mysqli_error($a1_data_link);
        }
    }
    // Close connection
    mysqli_close($a1_data_link);
?>