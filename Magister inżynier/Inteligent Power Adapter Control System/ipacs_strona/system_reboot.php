<?php

    require_once "mysql_connect.php";
    require_once 'session_login.php';

?> 

<body>
    
    <div style="float:left; margin-left: 200px; position: fixed; margin-top: 200px;"><a href="index.php" class="goBack" style="line-height: 20px">Powrót</a></div>
    <div id="loginDiv" style="position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);overflow: hidden;background-color: none;color: black;font-size: 18px;  width: 244px;">
        <div class="panel" style="margin-top: 0; width: 240px;">
            <div id="loginDivInner" style="background-color: #f5f5f5;    margin: 0px;padding: 30px;border-radius: 5px;width: 156px; height: 130px;">    
                <form action="change_settings.php" method="post">
                    Potwiedź restart hasłem do konta:<br>
                    <input placeholder="hasło" class="logIn" type="password" name="userpass" /> <br /><br />
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