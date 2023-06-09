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
        if (isset($_POST["power"]) and isset($_POST["powerSubmit"]))
        {
            $result = @$link->query('UPDATE `system_settings` SET `setting_value` = "'.$_POST["power"].'" WHERE `system_settings`.`setting_id` = 2');
            $result = @$link->query('INSERT INTO `job_list`(`job_id`, `job_type`, `job_setting`,`job_time`,`job_date`) VALUES ("","motherPowerLevel","'.$_POST["power"].'","'.date('H:i').'","'.date('d-m-Y').'")');
        }
        else if (isset($_POST["channel"]) and isset($_POST["channelSubmit"]))
        {
            $result = @$link->query('UPDATE `system_settings` SET `setting_value` = "'.$_POST["channel"].'" WHERE `system_settings`.`setting_id` = 1');
            $result = @$link->query('INSERT INTO `job_list`(`job_id`, `job_type`, `job_setting`,`job_time`,`job_date`) VALUES ("","channelChange","'.$_POST["channel"].'","'.date('H:i').'","'.date('d-m-Y').'")');
        }
        else if (isset($_POST["newTime"]) and isset($_POST["timeSubmit"]))
        {
            $result = @$link->query('INSERT INTO `job_list`(`job_id`, `job_type`, `job_setting`,`job_time`,`job_date`) VALUES ("","timeChange","'.$_POST["newTime"].'","'.date('H:i').'","'.date('d-m-Y').'")');
        }
        else if (isset($_POST["newDate"]) and isset($_POST["dateSubmit"]))
        {
            $result = @$link->query('INSERT INTO `job_list`(`job_id`, `job_type`, `job_setting`,`job_time`,`job_date`) VALUES ("","dateChange","'.$_POST["newDate"].'","'.date('H:i').'","'.date('d-m-Y').'")');
        }
        else if (isset($_POST["userpass"]) and isset($_POST["restart"]))
        {
            $haslo = hash( 'sha256', $_POST['userpass']);
            $login = $_SESSION['user_login'];
            if ($result = @$link->query(
                sprintf('SELECT * FROM users WHERE `user_login`="%s" AND `user_password`="%s" AND `user_new`="0" AND `user_blocked`="0"',
                mysqli_real_escape_string($link,$login),
                mysqli_real_escape_string($link,$haslo))))
                {
                    $result = @$link->query('INSERT INTO `job_list`(`job_id`, `job_type`, `job_setting`,`job_time`,`job_date`) VALUES ("","motherRestart","'."now".'","'.date('H:i').'","'.date('d-m-Y').'")');
                    $url = 'config.php';
                }
                
        }
        $url = $_SERVER['HTTP_REFERER'].'#settings';
        
        $link->close();
        header('Location: '.$url);
    }

    
?>