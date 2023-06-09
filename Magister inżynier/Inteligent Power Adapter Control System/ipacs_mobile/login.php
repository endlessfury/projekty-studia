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