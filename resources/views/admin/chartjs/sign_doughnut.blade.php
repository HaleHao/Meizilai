<canvas id="sign_doughnut" style="width: 100%;"></canvas>
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
        type: 'doughnut',
        data: {
            datasets: [{
                data:{!! $data !!},
                backgroundColor: [
                    window.chartColors.red,
                    window.chartColors.green,
                ],
                label: 'Dataset 1'
            }],
            labels: [
                '未签到',
                '已签到',
            ]
        },
        options: {
            responsive: true,
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: '今日签到人数'
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    };

    var ctx = document.getElementById('sign_doughnut').getContext('2d');
    new Chart(ctx, config);
});
</script>