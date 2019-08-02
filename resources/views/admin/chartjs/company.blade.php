<canvas id="line" style="width: 100%;"></canvas>
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
                label: '收益额',
                backgroundColor: window.chartColors.red,
                borderColor: window.chartColors.red,
                data: {!! $sum !!},
                fill: false,
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: '公司近七日收益'
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
                        labelString: '收益额'
                    }
                }]
            }
        }
    };

    var ctx = document.getElementById('line').getContext('2d');
    new Chart(ctx, config);
});
</script>