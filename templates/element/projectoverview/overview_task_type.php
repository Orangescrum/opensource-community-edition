<script type="text/javascript">
    $(document).ready(function(){
        var dat = <?php echo $task_type; ?>;
        if (dat.total_cnt) {
            $('#tot_tsx_typ_cnt').text(dat.total_cnt);
        }
        if (dat.status == 'success' && parseInt(dat.total_cnt) > 0) {
            chart = new Highcharts.Chart({
                credits: {
                    enabled: false
                },
                chart: {
                    renderTo: 'task_status_pie',
                    type: 'pie'
                },
                title: {
                    text: ''
                },
                yAxis: {
                    title: {
                        text: ''
                    }
                },
                plotOptions: {
                    pie: {
                        shadow: false
                    }
                },
                tooltip: {
                    formatter: function() {
                        return '<b>' + this.point.name + '</b>: ' + this.y;
                    }
                },
                series: [{
                    name: 'Browsers',
                    data: dat.data,
                    size: '120%',
                    innerSize: '70%',
                    showInLegend: true,
                    marker: {
                        symbol: "circle",
                        radius: 4
                    },
                    dataLabels: {
                        enabled: false
                    }
                }],
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: 0,
                    y: 20,
                    borderWidth: 0,
                    labelFormatter: function() {
                        return this.name + ' - ' + this.y + '';
                    }
                },
            });
        } else {
            $('#task_status_pie').html('<img src="/img/sample/dashboard/task_types_pie.jpg" style="width:98%;">');
        }
    });
</script>
