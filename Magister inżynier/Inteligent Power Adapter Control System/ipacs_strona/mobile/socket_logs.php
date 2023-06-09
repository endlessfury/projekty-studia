<?php
    require_once 'session_login.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styl.css">
    <title>SISLZ © PP 2019</title>
</head>
<body>
    <div id="container">
        <!--Logi gniazd adaptera-->
        <div class="panel">
            <div class="subPanelTop">
            <div style="float:left; margin-left: 120px; position: fixed;"><a href="index.php#<?php echo $_GET['adapter_id']; ?>" class="goBack" style="line-height: 20px">Powrót</a></div><br>
                Dziennik zmian stanów gniazd
            </div>
            <div class="subPanelDetails"> 
                <?php include 'adapter_logs.php'; ?>
            </div>
        </div>
    </div>
</body>
</html>