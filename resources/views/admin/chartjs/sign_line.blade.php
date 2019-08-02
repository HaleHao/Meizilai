<canvas id="sign_line" style="width: 100%;"></canvas>
<script>
$(function () {

    function randomScalingFactor() {
        return Math.floor(Math.random() * 100)
    }

    window.chartColors = {
        red: 'rgb(255, 99, 132)',
        orange: 'rgb(255, 159, 64)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(75, 192, 192)',
        blue: 'rgb(54, 162, 235)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(201, 203, 207)'
    };

    var config = {
        type: 'line',
        data: {
            labels: {!! $date !!},
            datasets: [{
                label: '签到',
                backgroundColor: window.chartColors.green,
                borderColor: window.chartColors.green,
                data: {!! $day_num !!},
                fill: false,
            },{
                label: '未签到',
                backgroundColor: window.chartColors.red,
                borderColor: window.chartColors.red,
                data: {!! $no_num !!},
                fill: false,
            }
            ]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: '近七日打卡人数'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: '日期'
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: '人数'
                    }
                }]
            }
        }
    };

    var ctx = document.getElementById('sign_line').getContext('2d');
    new Chart(ctx, config);
});
</script>