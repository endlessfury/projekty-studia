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
        <div id="loginDiv">
        <form action="user.php" method="POST">
            Podaj nazwe uzytkownika:<br>
            <input type="text" name="username" class="logIn" maxlength="10" style="margin-top:5px;margin-bottom:5px;"><br>
            Podaj hasło użytkownika:<br>
            <input type="password" name="password" class="logIn" style="margin-top:5px;margin-bottom:5px;"><br>
            Powtórz hasło:<br>
            <input type="password" name="password_2" class="logIn" style="margin-top:5px;margin-bottom:5px;"><br>
            <p  style="margin-top:5px;margin-bottom:5px;">Typ konta:</p>
            <select name="permissions" class="permissions">
                <option value="0">gość</option>
                <option value="1" selected>użytkownik</option>
            </select>
            <br><br>
            <input type="submit" name="addUser" value="Załóż konto" class="changeName">
        </form>
            
        
    </div>
</body>

</html>

<?php
    $mysqli->close();
?>