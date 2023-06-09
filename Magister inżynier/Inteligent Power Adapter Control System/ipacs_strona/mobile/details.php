<?php
    require_once 'session_login.php';
?>
<body>
    <div id="container">
        <!--Wykres temperatury-->
        <div class="panel">
            <div class="subPanelTop">
            <div style="float:left; margin-left: 120px; position: fixed;"><a href="index.php#<?php echo $_GET['adapter_id']; ?>" class="goBack" style="line-height: 20px">Powrót</a></div><br>
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