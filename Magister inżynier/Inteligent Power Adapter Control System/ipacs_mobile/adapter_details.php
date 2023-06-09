<?php
    require_once 'session_login.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="styl.css">
    <script src="Chart.bundle.js"></script>
	<script src="utils.js"></script>
</head>
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