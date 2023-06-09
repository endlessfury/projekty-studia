<?php
    require_once 'session_login.php';
?>
    <script>

    var config_graph_main = {
        type: 'line',
        data: {
            labels: main_time<?php echo $adapters['adapter_id']; ?>,
            datasets: [{
                data: main_data<?php echo $adapters['adapter_id']; ?>,
                borderColor: [
                    'rgb(25, 166, 201)'
                ],
                borderWidth: 1,
                backgroundColor: "rgb(214, 211, 13)",
                fill: false,
                lineTension: 0,
                cubicInterpolationMode: 'linear',
                borderWidth: 3
                
            },
            {
                data: srednia<?php echo $adapters['adapter_id']; ?>,
                borderColor: [
                    'darkgrey'
                ],
                borderWidth: 1,
                backgroundColor: "white",
                fill: false,
                lineTension: 0,
                cubicInterpolationMode: 'linear',
                borderWidth: 3
                
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {display: false},
            layout: {
                padding: {
                    left: 0,
                    right: 0,
                    top: 0,
                    bottom: 0
                }
            },
            hover: {
					mode: 'nearest',
					intersect: false
				},
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        fontSize: 15,
                        fontColor: 'black',
                        min: minimal_data<?php echo $adapters['adapter_id']; ?>,
                        max: maximal_data<?php echo $adapters['adapter_id']; ?>,
                        stepSize: 1
                    },
                    gridLines: {
                        display: true ,
                        color: "grey"
                    },
                }],
                xAxes: [{
                    ticks: {
                        fontSize: 15,
                        fontColor: 'black',
                        autoSkip: true,
                        autoSkipPadding: 10,
                        maxTicksLimit: 8,
                        display: true
                    },
                    gridLines: {
                        display: true ,
                        color: "grey"
                    },
                }]
            },
            elements: { 
                point: { radius: 0 } 
                },
            tooltips: 
            {
                callbacks: 
                {
                label: function(tooltipItem) 
                {
                        return tooltipItem.yLabel;
                    }
                }
            }
        }
    };
    </script>