<div class="socketsRefresh">
    <?php include 'socket_data.php' ?>
    <?php
        if ($adapters["adapter_website_control"] == 1)
        {
            for($j = 0;$j < count($hcas_sockets[$adapters['adapter_id']]);$j++)
            {
                if ($hcas_sockets[$adapters['adapter_id']][$j]['socket_state'] == '1')
                {
                    if ($hcas_sockets[$adapters['adapter_id']][$j]['socket_task_control'] == '1')
                    {
                        echo '<div class="subPanelButtons"><a href="#'.$adapters["adapter_id"].'" title="Gniazdo sterowane poprzez menadżer zadań" class="panelButtonGreen">'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</a></div>';
                    }
                    else
                    {
                        if ($_SESSION['user_permission'] >= 1) echo '<div class="subPanelButtons"><a href="socket.php?adapter_id='.$adapters['adapter_id'].'&socket_id='.$hcas_sockets[$adapters['adapter_id']][$j]['socket_id'].'&socket_state=0" class="panelButtonGreen">'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</a></div>';
                        else echo '<div class="subPanelButtons"><a href="#'.$adapters['adapter_id'].'" class="panelButtonGreen">'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</a></div>';
                    }
                }

                else
                {
                    if ($hcas_sockets[$adapters['adapter_id']][$j]['socket_task_control'] == '1')
                    {
                        echo '<div class="subPanelButtons"><a href="#'.$adapters["adapter_id"].'" title="Gniazdo sterowane poprzez menadżer zadań" class="panelButtonRed">'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</a></div>';
                    }
                    else
                    {
                        if ($_SESSION['user_permission'] >= 1) echo '<div class="subPanelButtons"><a href="socket.php?adapter_id='.$adapters['adapter_id'].'&socket_id='.$hcas_sockets[$adapters['adapter_id']][$j]['socket_id'].'&socket_state=1" class="panelButtonRed">'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</a></div>';
                        else echo '<div class="subPanelButtons"><a href="#'.$adapters['adapter_id'].'" class="panelButtonRed">'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</a></div>';
                    }
                }
            }
        }
        else
        {
            for($j = 0;$j < count($hcas_sockets[$adapters['adapter_id']]);$j++)
            {
                echo '<div title="Gniazdo sterowane ręcznie" class="subPanelButtons"><a href="#'.$adapters["adapter_id"].'" class="panelButtonRed">'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</a><a href="#" class="settings"></a></div>';
            }
        }
    ?>
    
    <div class="hintText">Kliknij na nazwę gniazda, aby zmienić jego stan</div><br>
    <!--<div class="subPanelButtons">
        <a href="tasks.php?adapter_id=<?php echo $adapters['adapter_id']; ?>" class="taskManager" style="margin: 20px 30px 20px 30px;">Menadżer zadań</a>
        <a href="socket_logs.php?adapter_id=<?php echo $adapters['adapter_id']; ?>" class="taskManager" style="margin: 0 35px 0;">Dziennik zmian</a>
    </div>-->
    
</div>