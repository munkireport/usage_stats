<div class="col-lg-4">
    <h4><i class="fa fa-bar-chart"></i> <span data-i18n="usage_stats.usage_stats"></span><a data-toggle="tab" title="Usage Stats" class="btn btn-xs pull-right" href="#usage_stats-tab" aria-expanded="false"><i class="fa fa-arrow-right"></i></a></h4>
    <table id="usage_stats_detail-data" class="table"></table>
</div>

<script>
$(document).on('appReady', function () {
    window.getUsageStatsDetailData = window.getUsageStatsDetailData || function() {
        if (window.mrUsageStatsDetailData) {
            return $.Deferred().resolve(window.mrUsageStatsDetailData).promise();
        }

        if (!window.mrUsageStatsDetailPromise) {
            window.mrUsageStatsDetailPromise = $.getJSON(appUrl + '/module/usage_stats/get_data/' + serialNumber)
                .done(function(data) {
                    window.mrUsageStatsDetailData = data;
                })
                .fail(function() {
                    window.mrUsageStatsDetailPromise = null;
                });
        }

        return window.mrUsageStatsDetailPromise;
    };

    window.getUsageStatsDetailData().done(function (data) {

        var table = $('#usage_stats_detail-data');
        table.empty();

        // Handle missing data gracefully
        if (!data || $.isEmptyObject(data) || !data.timestamp) {
            table.append(
                $('<tbody>').append(
                    $('<tr>').append(
                        $('<td>')
                            .attr('colspan', 2)
                            .addClass('text-muted')
                            .text(i18n.t('no_data'))
                    )
                )
            );
            return;
        }

        var tbody = $('<tbody>');

        // Thermal Pressure with colored label
        if (data.thermal_pressure) {
            var thermalClass = 'label-default'; // Default: Unknown = grey
            var thermalLower = data.thermal_pressure.toLowerCase();
            if (thermalLower === 'nominal') {
                thermalClass = 'label-success';  // Nominal = green
            } else if (thermalLower === 'moderate') {
                thermalClass = 'label-info';     // Moderate = blue
            } else if (thermalLower === 'heavy') {
                thermalClass = 'label-warning';  // Heavy = yellow
            } else if (thermalLower === 'critical') {
                thermalClass = 'label-danger';   // Critical = red
            }
            tbody.append(
                $('<tr>')
                    .append($('<th>').text(i18n.t('usage_stats.thermal_pressure')))
                    .append($('<td>').html('<span class="label ' + thermalClass + '">' + data.thermal_pressure + '</span>'))
            );
        }

        // Last Updated (timestamp)
        if (data.timestamp) {
            var timestampHtml = '<span title="' + moment((+data.timestamp) * 1000).format('llll') + '">' + moment((+data.timestamp) * 1000).fromNow() + '</span>';
            tbody.append(
                $('<tr>')
                    .append($('<th>').text(i18n.t('usage_stats.timestamp')))
                    .append($('<td>').html(timestampHtml))
            );
        }

        // CPU Usage - calculate from cpu_idle or show cpu_user + cpu_sys with colored label
        if (data.cpu_idle !== null && data.cpu_idle !== undefined && data.cpu_idle !== '') {
            var cpuUsageValue = 100 - parseFloat(data.cpu_idle);
            var cpuUsage = cpuUsageValue.toFixed(0) + '%';
            // Color coding: green < 50%, yellow 50-80%, red >= 80%
            var cpuClass = 'label-success';
            if (cpuUsageValue >= 80) {
                cpuClass = 'label-danger';
            } else if (cpuUsageValue >= 50) {
                cpuClass = 'label-warning';
            }
            tbody.append(
                $('<tr>')
                    .append($('<th>').text(i18n.t('usage_stats.cpu')))
                    .append($('<td>').html('<span class="label ' + cpuClass + '">' + cpuUsage + '</span>'))
            );
        }

        // Load Average
        if (data.load_avg) {
            tbody.append(
                $('<tr>')
                    .append($('<th>').text(i18n.t('usage_stats.load_avg')))
                    .append($('<td>').text(data.load_avg))
            );
        }

        // GPU Usage with colored label
        if (data.gpu_busy !== null && data.gpu_busy !== undefined && data.gpu_busy !== '' && data.gpu_busy !== 0) {
            var gpuUsageValue = data.gpu_busy * 100;
            var gpuUsage = gpuUsageValue.toFixed(0) + '%';
            // Color coding: green < 50%, yellow 50-80%, red >= 80%
            var gpuClass = 'label-success';
            if (gpuUsageValue >= 80) {
                gpuClass = 'label-danger';
            } else if (gpuUsageValue >= 50) {
                gpuClass = 'label-warning';
            }
            tbody.append(
                $('<tr>')
                    .append($('<th>').text(i18n.t('usage_stats.gpu_busy')))
                    .append($('<td>').html('<span class="label ' + gpuClass + '">' + gpuUsage + '</span>'))
            );
        }

        // Network - combine inbound and outbound rates
        if ((data.ibyte_rate !== null && data.ibyte_rate !== undefined && data.ibyte_rate !== '') ||
            (data.obyte_rate !== null && data.obyte_rate !== undefined && data.obyte_rate !== '')) {
            var inRate = data.ibyte_rate ? fileSize(data.ibyte_rate, 1) + '/s' : '0 B/s';
            var outRate = data.obyte_rate ? fileSize(data.obyte_rate, 1) + '/s' : '0 B/s';
            var networkHtml = '<i class="fa fa-arrow-down text-success"></i> ' + inRate + ' / <i class="fa fa-arrow-up text-primary"></i> ' + outRate;
            tbody.append(
                $('<tr>')
                    .append($('<th>').text(i18n.t('usage_stats.network_actvity')))
                    .append($('<td>').html(networkHtml))
            );
        }

        // Disk - combine read and write rates
        if ((data.rbytes_per_s !== null && data.rbytes_per_s !== undefined && data.rbytes_per_s !== '') ||
            (data.wbytes_per_s !== null && data.wbytes_per_s !== undefined && data.wbytes_per_s !== '')) {
            var readRate = data.rbytes_per_s ? fileSize(data.rbytes_per_s, 1) + '/s' : '0 B/s';
            var writeRate = data.wbytes_per_s ? fileSize(data.wbytes_per_s, 1) + '/s' : '0 B/s';
            var diskHtml = '<i class="fa fa-arrow-down text-success"></i> ' + readRate + ' / <i class="fa fa-arrow-up text-primary"></i> ' + writeRate;
            tbody.append(
                $('<tr>')
                    .append($('<th>').text(i18n.t('usage_stats.disk_activity')))
                    .append($('<td>').html(diskHtml))
            );
        }

        table.append(tbody);
    });

});
</script>
