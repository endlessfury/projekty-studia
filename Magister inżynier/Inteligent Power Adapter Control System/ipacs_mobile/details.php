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
    <script src="Chart.bundle.js"></script>
	<script src="utils.js"></script>
</head>
<body>
    <div id="container">
        <!--Wykres temperatury-->
        <div class="panel">
            <div class="subPanelTop">
            <div style="float:left; margin-left: 120px; position: fixed;"><a href="index.php" class="goBack" style="line-height: 20px">Powrót</a></div><br>
                Okresowy wykres temperatury
            </div>
            <div class="subPanelDetails">
                <?php include 'details_temp_graph.php'; ?>
            </div>
        </div>
         <!--Wykres natężenie światla-->
         <div class="panel" id="light">
            <div class="subPanelTop">
                Okresowy wykres natężenia światła
            </div>
            <div class="subPanelDetails">
                <?php include 'details_light_graph.php'; ?>
            </div>
        </div>
        
    </div>
</body>
</html>