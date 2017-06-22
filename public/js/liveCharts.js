$(function() {
    var graphDiv = $('#graphHighchart');

    var betId = graphDiv.data('betId');
    var betTitle = graphDiv.data('betTitle');

    var chart = Highcharts.chart('graphHighchart', {
        title: {
            text: betTitle
        },

        xAxis: {
            title: {
                enabled: true,
                text: 'Nombre de vote'
            }
        },

        yAxis: {
            title: {
                enabled: true,
                text: 'Réponses'
            }
        },

        exporting: { enabled: false },

        series: [{
            type: 'column',
            colorByPoint: true,
            showInLegend: false
        }]

    });

    function updateChart() {
        $.get('/ajax/bet/' + betId, {}, function(data) {
            chart.update({
                xAxis: {
                    categories: data.key
                },
                series: [{
                    name: 'Réponses',
                    data: data.series
                }]
            });
        });
    }

    updateChart();
    setInterval(updateChart, 5000);
});