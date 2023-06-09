
    <script>
        

    var config_light_details = {
        type: 'line',
        data: {
            labels: light_times,
            datasets: [{
                data: light_data,
                borderColor: [
                    '#19a6c9'
                ],
                borderWidth: 1,
                backgroundColor: "rgb(214, 211, 13)",
                fill: false,
                lineTension: 0,
                cubicInterpolationMode: 'linear',
                borderWidth: 3
                
            },
            {
                data: avarage,
                borderColor: [
                    'black'
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
                        fontSize: 17,
                        fontColor: 'black',
                        min: 0,
                        max: light_maximal_data,
                        stepSize: 20,
                    },
                    gridLines: {
                        display: true ,
                        color: "grey"
                    },
                }],
                xAxes: [{
                    ticks: {
                        fontSize: 17,
                        fontColor: 'black',
                        autoSkip: true,
                        autoSkipPadding: 10,
                        maxTicksLimit: 8,
                        display: true
                    },
                    gridLines: {
                        display: false ,
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