<?php

    require_once "mysql_connect.php";
    session_start();

    $polaczenie = @new mysqli($host, $db_user, $db_password, $db_name);

?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styl.css">
    <title>IPACS © PP 2019 Mobile</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="apple-touch-icon" sizes="57x57" href="/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>

<body>
        <div id="loginDiv">
            <div class="panel" style="margin-top: 0;">
                <div id="loginDivInner">        
                    <form action="logging.php" method="post">
                
                            Login: <br /> <input placeholder="login" class="logIn" type="text" name="username" style="width: 140px;margin-bottom: 5px;"/> <br />
                            Hasło: <br /> <input placeholder="hasło" class="logIn" type="password" name="userpass" style="width: 140px;" /> <br /><br />
                            <?php
                                if(isset($_SESSION['error']))	
                                {
                                    echo $_SESSION['error'];
                                    session_unset();
                                }
                            ?>
                            <input type="submit" class="changeName" value="Zaloguj się" />
                        
                        </form>
                </div>
            </div>
        </div>
</body>

</html>

<?php
    $mysqli->close();
?>