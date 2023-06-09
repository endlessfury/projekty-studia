<?php

    require_once "mysql_connect.php";
    session_start();

    $polaczenie = @new mysqli($host, $db_user, $db_password, $db_name);

?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<link rel="stylesheet" href="styl.css">
</head>

<body>
    <div style="float:left; margin-left: 200px; position: fixed; margin-top: 200px;"><a href="index.php" class="goBack" style="line-height: 20px">Powrót</a></div>
    <div id="loginDiv" style="background-color: white; width: 450px;">
        Autorem projektu jest inż. Wojciech Olszewski<br><br>
        <img src="avatar.png" width="100%" height="100%"/><br>
        <center><a href="http://facebook.com/wojtusolszewski" style="background-image:url('fb.png');width: 100px; height: 50px;display: block;"></a><a title="eriksonekxd@gmail.com" href="mailto:eriksonekxd@gmail.com" style="background-image:url('gmail.png');width: 50px; height: 50px;display: block;"></a></center>
    </div>
</body>

</html>

<?php
    $mysqli->close();
?>