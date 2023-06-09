<?php
    require_once 'session_login.php';
?>
<body>
    <div id="container">
        <!--Wykres temperatury-->
        <div class="panel">
            <div class="subPanelTop">
                <div style="float:left; margin-left: 10px; position: fixed;"><a href="<?php echo 'index.php#'.$_GET['adapter_id']; ?>" class="showMore" style="line-height: 20px">Powrót</a></div>
                Okresowy wykres temperatury
            </div>
            <div class="subPanelDetails">
                <?php include 'details_graph.php'; ?>
            </div>
        </div>
        
        
    </div>
</body>
</html>