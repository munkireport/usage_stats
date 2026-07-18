<div id="lister" style="font-size: large; float: right;">
    <a href="/show/listing/usage_stats/usage_stats" title="List">
        <i class="btn btn-default tab-btn fa fa-list-alt"></i>
    </a>
</div>
<h2><i class="fa fa-bar-chart"></i> <span data-i18n="usage_stats.usage_stats"></span></h2>
<div id="usage_stats-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

<script>
$(document).on('appReady', function(e, lang) {
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

    var i18nCache = {};
    var t = function(key) {
        if (i18nCache[key] === undefined) {
            i18nCache[key] = i18n.t(key);
        }
        return i18nCache[key];
    };
    var fileSizeCache = {};
    var fileSizeCached = function(value, precision) {
        var cacheKey = String(value) + '|' + String(precision);
        if (fileSizeCache[cacheKey] === undefined) {
            fileSizeCache[cacheKey] = fileSize(value, precision);
        }
        return fileSizeCache[cacheKey];
    };

    // Get directory_service data
    window.getUsageStatsDetailData().done(function( d ) {
        if( ! d.timestamp){
            $('#usage_stats-msg').text(i18n.t('no_data'));
        }
        else{
            // Hide
            $('#usage_stats-msg').text('');
            $('#usage_stats-view').removeClass('hide');

            // Update the tab badge count
            $('#usage_stats_processes-cnt').text("");

            var boot_rows = '';
            var network_rows = '';
            var disk_rows = '';
            var processor_rows = '';
            var gpu_rows = '';
            var backlight_rows = '';
            var cluster_rows = '';
            var processes_data = '';
            var network_combined_added = false;
            var disk_combined_added = false;

            // Process each key in the JSON array
            for (var prop in d){
                // Do nothing for nulls to blank them
                if ((d[prop] == '' || d[prop] == null || d[prop] == "none" || prop == '') && d[prop] != 0){
                    boot_rows = boot_rows

                } else if (prop == 'thermal_pressure'){
                    // Thermal Pressure with colored label
                    var thermalClass = 'label-default';  // Default: Unknown = grey
                    var thermalLower = d[prop].toLowerCase();
                    if (thermalLower === 'nominal') {
                        thermalClass = 'label-success';  // Nominal = green
                    } else if (thermalLower === 'moderate') {
                        thermalClass = 'label-info';     // Moderate = blue
                    } else if (thermalLower === 'heavy') {
                        thermalClass = 'label-warning';  // Heavy = yellow
                    } else if (thermalLower === 'critical') {
                        thermalClass = 'label-danger';   // Critical = red
                    }
                    boot_rows = boot_rows + '<tr><th>'+t('usage_stats.'+prop)+'</th><td><span class="label ' + thermalClass + '">'+d[prop]+'</span></td></tr>';
                } else if (prop == 'kern_bootargs'){
                    boot_rows = boot_rows + '<tr><th>'+t('usage_stats.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                } else if (prop == 'timestamp'){
                    var bootTimestamp = moment((+d[prop]) * 1000);
                    boot_rows = boot_rows + '<tr><th>'+t('usage_stats.'+prop)+'</th><td><span title=" '+bootTimestamp.format('llll')+'">'+bootTimestamp.fromNow()+'</span></td></tr>';

                } else if (prop == 'keyboard_backlight'){
                    backlight_rows = backlight_rows + '<tr><th>'+t('usage_stats.'+prop)+'</th><td>'+d[prop]+'%</td></tr>';
                } else if (prop == 'backlight'){
                    backlight_rows = backlight_rows + '<tr><th>'+t('usage_stats.lcd_backlight')+'</th><td>'+Math.round((d[prop]/d['backlight_max'])*100)+'%</td></tr>';

                } else if (prop == 'rbytes_diff' || prop == 'wbytes_diff'){
                    disk_rows = disk_rows + '<tr><th>'+t('usage_stats.'+prop)+'</th><td>'+fileSizeCached(d[prop], 2)+'</td></tr>';
                } else if (prop == 'rbytes_per_s'){
                    // Combined display for disk activity (like widget) - only add once when processing rbytes_per_s
                    if (!disk_combined_added && ((d['rbytes_per_s'] !== null && d['rbytes_per_s'] !== undefined && d['rbytes_per_s'] !== '') || (d['wbytes_per_s'] !== null && d['wbytes_per_s'] !== undefined && d['wbytes_per_s'] !== ''))) {
                        var readRate = d['rbytes_per_s'] ? fileSizeCached(d['rbytes_per_s'], 1) + '/s' : '0 B/s';
                        var writeRate = d['wbytes_per_s'] ? fileSizeCached(d['wbytes_per_s'], 1) + '/s' : '0 B/s';
                        disk_rows = disk_rows + '<tr><th>'+t('usage_stats.disk_activity')+'</th><td><i class="fa fa-arrow-down text-success"></i> ' + readRate + ' / <i class="fa fa-arrow-up text-primary"></i> ' + writeRate + '</td></tr>';
                        disk_combined_added = true;
                    }
                } else if (prop == 'wbytes_per_s'){
                    // Skip wbytes_per_s since it's already included in the combined row above
                } else if (prop == 'rops_per_s' || prop == 'wops_per_s'){
                    disk_rows = disk_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'/s</td></tr>';
                } else if (prop == 'rops_diff' || prop == 'wops_diff'){
                    disk_rows = disk_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                
                } else if (prop == 'ibytes' || prop == 'obytes'){
                    network_rows = network_rows + '<tr><th>'+t('usage_stats.'+prop)+'</th><td>'+fileSizeCached(d[prop], 2)+'</td></tr>';
                } else if (prop == 'ibyte_rate'){
                    // Combined display for network activity (like widget) - only add once when processing ibyte_rate
                    if (!network_combined_added && ((d['ibyte_rate'] !== null && d['ibyte_rate'] !== undefined && d['ibyte_rate'] !== '') || (d['obyte_rate'] !== null && d['obyte_rate'] !== undefined && d['obyte_rate'] !== ''))) {
                        var inRate = d['ibyte_rate'] ? fileSizeCached(d['ibyte_rate'], 1) + '/s' : '0 B/s';
                        var outRate = d['obyte_rate'] ? fileSizeCached(d['obyte_rate'], 1) + '/s' : '0 B/s';
                        network_rows = network_rows + '<tr><th>'+t('usage_stats.network_actvity')+'</th><td><i class="fa fa-arrow-down text-success"></i> ' + inRate + ' / <i class="fa fa-arrow-up text-primary"></i> ' + outRate + '</td></tr>';
                        network_combined_added = true;
                    }
                } else if (prop == 'obyte_rate'){
                    // Skip obyte_rate since it's already included in the combined row above
                } else if (prop == 'ipacket_rate' || prop == 'opacket_rate'){
                    network_rows = network_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'/s</td></tr>';
                } else if (prop == 'ipackets' || prop == 'opackets'){
                    network_rows = network_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'</td></tr>';

                } else if (prop == 'gpu_name'){
                    gpu_rows = gpu_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                } else if (prop == 'gpu_freq_hz'){
                    gpu_rows = gpu_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]/1000000).toFixed(2)+'Mhz</td></tr>';
                } else if (prop == 'gpu_freq_ratio'){
                    gpu_rows = gpu_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]*100).toFixed(2)+'%</td></tr>';
                } else if (prop == 'gpu_busy'){
                    // Format GPU usage with colored label
                    if (d[prop] !== null && d[prop] !== undefined && d[prop] !== '' && d[prop] !== 0) {
                        var gpuUsageValue = d[prop] * 100;
                        var gpuUsage = gpuUsageValue.toFixed(1) + '%';
                        // Color coding: >= 70% = red, >= 30% = yellow, < 30% = green
                        var gpuClass = 'label-success';
                        if (gpuUsageValue >= 70) {
                            gpuClass = 'label-danger';
                        } else if (gpuUsageValue >= 30) {
                            gpuClass = 'label-warning';
                        }
                        gpu_rows = gpu_rows + '<tr><th>'+i18n.t('usage_stats.gpu_busy')+'</th><td><span class="label ' + gpuClass + '">'+gpuUsage+'</span></td></tr>';
                    }
                
                } else if (prop == 'freq_ratio'){
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]*100).toFixed(2)+'%</td></tr>';
                } else if (prop == 'freq_hz'){
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]/1000000000).toFixed(2)+'Ghz</td></tr>';
                } else if (prop == 'package_joules'){
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]*1).toFixed(2)+' Joules</td></tr>';
                } else if (prop == 'package_watts'){
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]*1).toFixed(2)+' Watts</td></tr>';
                } else if (prop == 'cpu_sys'){
                    // CPU System with colored label
                    var cpuSysValue = parseFloat(d[prop]);
                    var cpuSys = cpuSysValue.toFixed(1) + '%';
                    // Color coding: >= 70% = red, >= 30% = yellow, < 30% = green
                    var cpuSysClass = 'label-success';
                    if (cpuSysValue >= 70) {
                        cpuSysClass = 'label-danger';
                    } else if (cpuSysValue >= 30) {
                        cpuSysClass = 'label-warning';
                    }
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.cpu_sys')+'</th><td><span class="label ' + cpuSysClass + '">'+cpuSys+'</span></td></tr>';
                } else if (prop == 'cpu_user'){
                    // CPU User with colored label
                    var cpuUserValue = parseFloat(d[prop]);
                    var cpuUser = cpuUserValue.toFixed(1) + '%';
                    // Color coding: >= 70% = red, >= 30% = yellow, < 30% = green
                    var cpuUserClass = 'label-success';
                    if (cpuUserValue >= 70) {
                        cpuUserClass = 'label-danger';
                    } else if (cpuUserValue >= 30) {
                        cpuUserClass = 'label-warning';
                    }
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.cpu_user')+'</th><td><span class="label ' + cpuUserClass + '">'+cpuUser+'</span></td></tr>';
                } else if (prop == 'load_avg'){
                    // Load Average - display as plain text
                    var loadAvgStr = d[prop].toString();
                    var loadAvgParts = loadAvgStr.split(',');
                    var loadAvg1min = parseFloat(loadAvgParts[0].trim());
                    var loadAvg5min = loadAvgParts.length > 1 ? parseFloat(loadAvgParts[1].trim()) : null;
                    var loadAvg15min = loadAvgParts.length > 2 ? parseFloat(loadAvgParts[2].trim()) : null;
                    
                    // Display all three values (1min / 5min / 15min)
                    var loadAvgHtml = loadAvg1min.toFixed(2);
                    if (loadAvg5min !== null) {
                        loadAvgHtml += ' / ' + loadAvg5min.toFixed(2);
                    }
                    if (loadAvg15min !== null) {
                        loadAvgHtml += ' / ' + loadAvg15min.toFixed(2);
                    }
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.load_avg')+'</th><td>'+loadAvgHtml+'</td></tr>';
                } else if (prop == 'cpu_idle'){
                    // Format CPU usage (calculate from cpu_idle) with colored label
                    if (d[prop] !== null && d[prop] !== undefined && d[prop] !== '') {
                        var cpuUsageValue = 100 - parseFloat(d[prop]);
                        var cpuUsage = cpuUsageValue.toFixed(1) + '%';
                        // Color coding: >= 70% = red, >= 30% = yellow, < 30% = green
                        var cpuClass = 'label-success';
                        if (cpuUsageValue >= 70) {
                            cpuClass = 'label-danger';
                        } else if (cpuUsageValue >= 30) {
                            cpuClass = 'label-warning';
                        }
                        processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.cpu')+'</th><td><span class="label ' + cpuClass + '">'+cpuUsage+'</span></td></tr>';
                        // Update the tab badge count
                        $('#usage_stats_processes-cnt').text(cpuUsage.replace('.0%', '%'));
                    }

                } else if (prop == 'processes'){
                    processes_data = d[prop];

                } else if (prop == 'clusters'){
                    // Process clusters table into fancy table
                    var clusters_data = JSON.parse(d[prop]);

                    var formatClusterName = function(clusterKey) {
                        var spaced = clusterKey.replace("Cluster", "Cluster ");
                        if (clusterKey.indexOf('S-Cluster') === 0) {
                            return spaced.replace('S-Cluster', t('usage_stats.super_cluster'));
                        }
                        if (clusterKey.indexOf('P-Cluster') === 0) {
                            return spaced.replace('P-Cluster', t('usage_stats.performance_cluster'));
                        }
                        if (clusterKey.indexOf('E-Cluster') === 0) {
                            return spaced.replace('E-Cluster', t('usage_stats.efficiency_cluster'));
                        }
                        return spaced;
                    };

                    for (cluster_entry in clusters_data){
                        if (cluster_entry.includes('_active')){

                            cluster_active = clusters_data[cluster_entry]
                            cluster_name = formatClusterName(cluster_entry.split("_")[0])
                            cluster_mhz = clusters_data[cluster_entry.replace("_active", "_freq_Mhz")]

                            // Check if core is asleep (0Mhz)
                            var isAsleep = !cluster_mhz || cluster_mhz === 0;

                            // Color coding for cluster load: grey if asleep, >= 70% = red, >= 30% = yellow, < 30% = green
                            var clusterClass = 'label-default';
                            if (!isAsleep) {
                                clusterClass = 'label-success';
                                if (cluster_active >= 70) {
                                    clusterClass = 'label-danger';
                                } else if (cluster_active >= 30) {
                                    clusterClass = 'label-warning';
                                }
                            }
                            
                            var clusterLoadHtml;
                            if (isAsleep) {
                                clusterLoadHtml = '<span class="label ' + clusterClass + '">Asleep</span>';
                            } else {
                                clusterLoadHtml = '<span class="label ' + clusterClass + '">' + cluster_active + '%</span>';
                            }

                            // Mhz or Ghz - put freq and % on one line with spacing
                            var freqText;
                            if (isAsleep) {
                                freqText = '0Mhz';
                            } else if (cluster_mhz >= 1000) {
                                freqText = (cluster_mhz/1000).toFixed(2) + 'Ghz';
                            } else {
                                freqText = cluster_mhz + 'Mhz';
                            }
                            cluster_rows = cluster_rows + '<tr><th>'+cluster_name+'</th><td>'+freqText + ' &nbsp; ' + clusterLoadHtml+'</td></tr>';
                        }
                    }
                }
            };

            // Create responsive dashboard layout with Bootstrap grid (3 columns, like other tabs/widgets)
            var dashboardContainer = $('<div>').addClass('row');
            
            // Helper — match ethernet / filevault / firmware / battery markup
            function createTableColumn(title, icon, rows) {
                var col = $('<div>').addClass('col-lg-4 col-md-6 col-sm-12').css({'margin-bottom': '20px'});

                if (title) {
                    col.append($('<h4>')
                        .append($('<i>').addClass('fa ' + icon))
                        .append(' ' + title));
                }

                col.append($('<div style="max-width:550px;">')
                    .append($('<table>')
                        .addClass('table table-striped table-condensed')
                        .append($('<tbody>').append(rows))));

                return col;
            }
            
            // Collect all sections for 3-column layout
            var sections = [];
            
            if (boot_rows !== "") {
                sections.push({rows: boot_rows, title: t('usage_stats.general'), icon: 'fa-info-circle'});
            }
            if (network_rows !== "") {
                sections.push({rows: network_rows, title: t('usage_stats.network_actvity'), icon: 'fa-wifi'});
            }
            if (disk_rows !== "") {
                sections.push({rows: disk_rows, title: t('usage_stats.disk_activity'), icon: 'fa-hdd-o'});
            }
            if (processor_rows !== "") {
                sections.push({rows: processor_rows, title: t('usage_stats.processor_usage'), icon: 'fa-microchip'});
            }
            if (gpu_rows !== "") {
                sections.push({rows: gpu_rows, title: t('usage_stats.gpu_usage'), icon: 'fa-television'});
            }
            if (backlight_rows !== "") {
                sections.push({rows: backlight_rows, title: t('usage_stats.backlights'), icon: 'fa-sun-o'});
            }
            
            // Append all columns directly to the row - Bootstrap will handle wrapping automatically
            // col-lg-4 means 3 columns per row on large screens, col-md-6 means 2 columns on medium
            for (var i = 0; i < sections.length; i++) {
                dashboardContainer.append(createTableColumn(sections[i].title, sections[i].icon, sections[i].rows));
            }

            // Clusters section (same width as other sections, will wrap naturally)
            if (cluster_rows !== "") {
                dashboardContainer.append(createTableColumn(t('usage_stats.cluster_usage'), 'fa-cubes', cluster_rows));
            }

            // Append dashboard container to tab
            if (dashboardContainer.children().length > 0) {
                $('#usage_stats-tab').append(dashboardContainer);
            }

            // Only show and sort processes table if data exists
            if ( processes_data !== ""){

                // Hide loading message
                $('#processes-msg').text('');

                // Match filevault / ethernet — plain table, no table-responsive
                var processesTable = $('<table>')
                        .addClass('table table-striped table-condensed')
                        .attr('id', 'processes-table')
                        .append($('<thead>')
                            .append($('<tr>')
                                .append($('<th>').attr('data-colname', 'usage_stats.pid').attr('data-i18n', 'usage_stats.pid').text(t('usage_stats.pid')))
                                .append($('<th>').attr('data-colname', 'usage_stats.proc').attr('data-i18n', 'usage_stats.proc').text(t('usage_stats.proc')))
                                .append($('<th>').attr('data-colname', 'usage_stats.usr').attr('data-i18n', 'usage_stats.usr').text(t('usage_stats.usr')))
                                .append($('<th>').attr('data-colname', 'usage_stats.cpu').attr('data-i18n', 'usage_stats.cpu').text(t('usage_stats.cpu')))
                                .append($('<th>').attr('data-colname', 'usage_stats.memp').attr('data-i18n', 'usage_stats.memp').text(t('usage_stats.memp')))
                                .append($('<th>').attr('data-colname', 'usage_stats.mem').attr('data-i18n', 'usage_stats.mem').text(t('usage_stats.mem')))
                                .append($('<th>').attr('data-colname', 'usage_stats.path').attr('data-i18n', 'usage_stats.path').text(t('usage_stats.path')))))
                        .append($('<tbody>')
                            .append($('<tr>')
                                .append($('<td>')
                                    .attr('data-i18n', 'listing.loading')
                                    .attr('colspan', 7)
                                    .addClass('dataTables_empty')
                                    .text(i18n.t('listing.loading')))));
                
                $('#processes').append(processesTable);

                    // Process rules json for processing in the fancy table
                    var table_data = JSON.parse(processes_data);

                    $('#processes-table').DataTable({

                        data: table_data,
                        order: [[3,'asc']],
                        autoWidth: false,
                        columns: [
                            { data: 'pid' },
                            { data: 'proc' },
                            { data: 'usr' },
                            { data: 'cpu' },
                            { data: 'memp' },
                            { data: 'mem' },
                            { data: 'path' }
                        ],
                        createdRow: function( nRow, aData, iDataIndex ) {

                            var colvar=$('td:eq(3)', nRow).html();
                            $('td:eq(3)', nRow).text(colvar+"%")

                            var colvar=$('td:eq(4)', nRow).html();
                            $('td:eq(4)', nRow).text(colvar+"%")
                        }
                });
            } else {
                $('#processes-msg').text(i18n.t('no_data'));
            }
        }
    });
});
</script>
