$(function() {
    var graphDiv = $('#graphHighchart');

    var betId = graphDiv.data('betId');
    var betTitle = graphDiv.data('betTitle');
    var liveUpdate = graphDiv.data('liveUpdate');

    var chart = Highcharts.chart('graphHighchart', {
        title: {
            text: betTitle
        },

        xAxis: {
            title: {
                enabled: true,
                text: 'Réponses'
            }
        },

        yAxis: {
            title: {
                enabled: true,
                text: 'Nombre de vote'
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
    if(liveUpdate) {
        setInterval(updateChart, 5000);
    }
});
