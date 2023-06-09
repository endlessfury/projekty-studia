<?php

    require_once 'session_login.php';

    $cookie_name = "refresh_timeout";
    if (isset($_POST['refresh_timeout']))
    {
        $cookie_value = $_POST['refresh_timeout'];
        setcookie($cookie_name, $cookie_value, time() + (86400 * 365), "/"); // 86400 = 1 day
        //echo "we are good";
    }

    $url = $_SERVER['HTTP_REFERER']."#settings";
    header('Location: '.$url);
?>