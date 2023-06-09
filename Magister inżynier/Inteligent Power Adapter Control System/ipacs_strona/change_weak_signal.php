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
        if (isset($_GET["adapter_id"]) and isset($_POST["weakSignal"]))
        {
            $result = @$link->query('UPDATE `adapters` SET `adapter_weak_signal` = "'.$_POST["weakSignal"].'" WHERE `adapters`.`adapter_id` = "'.$_GET["adapter_id"].'"');
            $result = @$link->query('INSERT INTO `job_list`(`job_id`, `job_type`, `job_setting`, `job_setting2`, `job_active`, `job_comment`, `job_time`, `job_date`) VALUES ("","weakSignalLevelChange","'.$_GET["adapter_id"].'","'.$_POST["weakSignal"].'", "0" , "Job finished", "'.date('H:i').'", "'.date('d-m-Y').'")');
            mysqli_free_result($result);
        }
        $url = $_SERVER['HTTP_REFERER'].'#'.$_GET["adapter_id"];
        header('Location: '.$url);
    }

    $link->close();
    
?>