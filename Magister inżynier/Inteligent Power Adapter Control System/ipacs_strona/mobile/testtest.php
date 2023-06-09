<div class="subpanels">
                <!--Panel devided for 3 subpanels-->
                <div class="subpanel">
                    <!--First subpanel for buttons-->
                    <!--Get the info about sockets-->
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
                                            echo '<div class="subPanelButtons"><a href="socket.php?adapter_id='.$adapters['adapter_id'].'&socket_id='.$hcas_sockets[$adapters['adapter_id']][$j]['socket_id'].'&socket_state=0" class="panelButtonGreen">'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</a></div>';
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
                                            echo '<div class="subPanelButtons"><a href="socket.php?adapter_id='.$adapters['adapter_id'].'&socket_id='.$hcas_sockets[$adapters['adapter_id']][$j]['socket_id'].'&socket_state=1" class="panelButtonRed">'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</a></div>';
                                        }
                                    }
                                }
                            }
                            else
                            {
                                for($j = 0;$j < count($hcas_sockets[$adapters['adapter_id']]);$j++)
                                {
                                    echo '<div class="subPanelButtons"><a href="#'.$adapters["adapter_id"].'" class="panelButtonRed">'.$hcas_sockets[$adapters['adapter_id']][$j]['socket_name'].'</a><a href="#" class="settings"></a></div>';
                                }
                            }
                        ?>
                        <div class="subPanelButtons"><a href="tasks.php?adapter_id=<?php echo $adapters['adapter_id']; ?>" class="taskManager" style="margin: 40px 30px 20px 30px;">Menadżer zadań</a>
                        <a href="socket_logs.php?adapter_id=<?php echo $adapters['adapter_id']; ?>" class="taskManager" style="margin: 0 35px 0;">Dziennik zmian</a></div>

                </div>
                <div class="subpanel">
                    Wykres temperatury z ostatnich 24h<br>
                    <?php include 'main_graph.php' ?>
                </div>
                <div class="subpanel"><br>
                <table border=0 width="100%">
                    <tr>
                        <?php
                            if ($adapters['temp_sensor_state'])
                                echo '<td><b>Aktualna temperatura: <font color="white">'.round($adapters['temp_sensor_data'],2).' ℃</font></b></td>';
                            else
                                echo '<td><b><font color="red">Czujnik temperatury wyłączony</font></b></td>';
                        ?>
                    </tr>
                    <tr>
                        <td>Maksymalna temperatura: <font color="white"><?php echo round(max($data_main_graph),2); ?> ℃</font></td>
                    </tr>
                    <tr>
                        <td>Minimalna temperatura: <font color="white"><?php echo round(min($data_main_graph),2); ?> ℃</font></td>
                    </tr>
                    <tr>
                        <td>Średnia temperatura: <font color="white"><?php echo round($avarage,2); ?> ℃</font></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <?php
                            if ($adapters['light_sensor_state'])
                                echo '<td><b>Jasność w pokoju: <font color="white">'.round($adapters['light_sensor_data'],2).' %</font></b></td>';
                            else
                                echo '<td><b><font color="red">Czujnik światła wyłączony</font></b></td>';
                        ?>
                    </tr>
                </table>
                <!--Clear the arrays-->
                <?php
                    unset($data_main_graph);
                    unset($time_main_graph);
                    unset($avarageTable);
                    unset($avarage);
                ?>
                <br><br>
                    Stan gniazd: <br> <font color="white">sterowane przez stronę</font>
                    <?php

                    ?>
                    <br><br>
                    <a href="details.php?adapter_id=<?php echo $adapters["adapter_id"]; ?>" class="showMore">Pokaż więcej</a>
                </div>
            </div>