<?php
    require_once "mysql_connect.php";
    require_once 'session_login.php';


    $link = @new mysqli($host, $db_user, $db_password, $db_name);

    if ($link->connect_errno!=0)
    {
        echo "Error: ".$link->connect_errno;
    }
    else
    {
        if (isset($_GET["adapter_id"]))
        {
            
            //echo '<br>'.'UPDATE `socket_tasks` SET `task_state` = "0", `task_cancel` = "'.date('H:i').','.date('d-m-Y').'" WHERE `socket_tasks`.`task_id` = '.$_GET['task_id'];
            echo 'SELECT * FROM `socket_tasks` WHERE `adapter_id` = "'.$_GET["adapter_id"].'" AND `task_error` <> "" AND `task_state` = 1'.'<br>';
            if ($result = @$link->query('SELECT * FROM `socket_tasks` WHERE `adapter_id` = "'.$_GET["adapter_id"].'" AND `task_error` <> "" AND `task_state` = 1'))
            {
                if($result->num_rows > 0)
                {
                    echo $result->num_rows.'<br>';
                    while($row = $result->fetch_assoc())
                    {
                        echo 'UPDATE `socket_tasks` SET `task_error` = "", `task_cancel` = "Wyczyszczono błędy: '.date('H:i').', '.date('d-m-Y').'" WHERE `socket_tasks`.`task_id` = '.$row['task_id'].'<br>';
                        @$link->query('UPDATE `socket_tasks` SET `task_error` = "", `task_cancel` = "Wyczyszczono błędy: '.date('H:i').', '.date('d-m-Y').'" WHERE `socket_tasks`.`task_id` = '.$row['task_id']);
                    }
                }
            }
            
            $result->free();

            $url = $_SERVER['HTTP_REFERER']."#".$_GET["adapter_id"];
            header('Location: '.$url);
        }
    }

    $link->close();
    
?>