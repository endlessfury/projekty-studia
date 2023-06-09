<?php
    require_once 'session_login.php';
?>

<body>
    
<div style="float:left; margin-left: 150px; position: fixed; margin-top: 200px;"><a href="index.php" class="goBack" style="line-height: 20px">Powrót</a></div>
<div id="loginDiv">
            <div class="panel" style="margin-top: 0;">
                <div id="loginDivInner">        
                <form action="change_settings.php" method="post">
                <font size="3">Potwiedź restart hasłem do konta:</font><br>
                <input placeholder="hasło" class="logIn" type="password" name="userpass" style="margin-top: 10px;"/> <br /><br />
                <?php
                    if(isset($_SESSION['error']))	
                    {
                        echo $_SESSION['error'];
                        session_unset();
                    }
                ?>
                <input type="submit" class="changeName" value="Wykonaj restart" name="restart"/>
            
            </form>
            </div>
            </div>
    </div>
</body>

</html>

<?php
    $mysqli->close();
?>