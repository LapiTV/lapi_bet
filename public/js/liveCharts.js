$(function() {
    var chart = Highcharts.chart('graphHighchart', {
        title: {
            text: 'Réponse'
        },

        xAxis: {},

        series: [{
            type: 'column',
            colorByPoint: true,
            showInLegend: false
        }]

    });

    var betId = $('#graphHighchart').data('betId');

    function updateChart() {
        $.get('/ajax/bet/' + betId, {}, function(data) {
            chart.update({
                xAxis: {
                    categories: data.key
                },
                series: [{
                    data: data.series
                }]
            });
        });
    }

    updateChart();
    setInterval(updateChart, 5000);
});