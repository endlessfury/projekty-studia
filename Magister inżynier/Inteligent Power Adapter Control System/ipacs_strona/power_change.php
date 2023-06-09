<?php
    require_once 'session_login.php';
    require_once "mysql_connect.php";

    $link = @new mysqli($host, $db_user, $db_password, $db_name);

    if ($link->connect_errno!=0)
    {
        echo "Error: ".$link->connect_errno;
    }
    else
    {
        if (isset($_GET["adapter_id"]) and isset($_POST["powerLevel"]))
        {
            $result = @$link->query('INSERT INTO `job_list`(`job_id`, `job_type`, `job_setting`, `job_setting2`) VALUES ("","powerLevelChange","'.$_GET["adapter_id"].'","'.$_POST["powerLevel"].'")');
            $result = @$link->query('UPDATE stats SET `packets_recieved` = 0, `packets_sent` = 0, `last_recieved_time` = "", `last_recieved_date` = ""');
            mysqli_free_result($result);
        }
        $url = $_SERVER['HTTP_REFERER'].'#'.$_GET["adapter_id"];
        header('Location: '.$url);
    }

    $link->close();
    
?>