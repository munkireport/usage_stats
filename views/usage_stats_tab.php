<h2 data-i18n="usage_stats.usage_stats"></h2>
<div id="usage_stats-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

<script>
$(document).on('appReady', function(e, lang) {

    // Get directory_service data
    $.getJSON( appUrl + '/module/usage_stats/get_data/' + serialNumber, function( d ) {
        if( ! d.timestamp){
            $('#usage_stats-msg').text(i18n.t('no_data'));
        }
        else{
            // Hide
            $('#usage_stats-msg').text('');
            $('#usage_stats-view').removeClass('hide');

            var boot_rows = '';
            var network_rows = '';
            var disk_rows = '';
            var processor_rows = '';
            var gpu_rows = '';
            var backlight_rows = '';
            var cluster_rows = '';

            // Process each key in the JSON array
            for (var prop in d){
                // Do nothing for nulls to blank them
                if (d[prop] !== 0 && d[prop] == '' || d[prop] == null){
                    boot_rows = boot_rows

                } else if (prop == 'thermal_pressure' || prop == 'kern_bootargs'){
                    boot_rows = boot_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                } else if (prop == 'timestamp'){
                    boot_rows = boot_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td><span title=" '+moment((+d[prop])*1000).format('llll')+'">'+moment((+d[prop])*1000).fromNow()+'</span></td></tr>';

                } else if (prop == 'keyboard_backlight'){
                    backlight_rows = backlight_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'%</td></tr>';
                } else if (prop == 'backlight'){
                    backlight_rows = backlight_rows + '<tr><th>'+i18n.t('usage_stats.lcd_backlight')+'</th><td>'+((d[prop]/d['backlight_max'])*100).toFixed(2)+'%</td></tr>';

                } else if (prop == 'rbytes_diff' || prop == 'wbytes_diff'){
                    disk_rows = disk_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+fileSize(d[prop], 2)+'</td></tr>';
                } else if (prop == 'rbytes_per_s' || prop == 'wbytes_per_s'){
                    disk_rows = disk_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+fileSize(d[prop], 2)+'/s</td></tr>';
                } else if (prop == 'rops_per_s' || prop == 'wops_per_s'){
                    disk_rows = disk_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'/s</td></tr>';
                } else if (prop == 'rops_diff' || prop == 'wops_diff'){
                    disk_rows = disk_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                
                } else if (prop == 'ibytes' || prop == 'obytes'){
                    network_rows = network_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+fileSize(d[prop], 2)+'</td></tr>';
                } else if (prop == 'ibyte_rate' || prop == 'obyte_rate'){
                    network_rows = network_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+fileSize(d[prop], 2)+'/s</td></tr>';
                } else if (prop == 'ipacket_rate' || prop == 'opacket_rate'){
                    network_rows = network_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'/s</td></tr>';
                } else if (prop == 'ipackets' || prop == 'opackets'){
                    network_rows = network_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'</td></tr>';

                } else if (prop == 'gpu_name'){
                    gpu_rows = gpu_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                } else if (prop == 'gpu_freq_hz'){
                    gpu_rows = gpu_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]/1000000).toFixed(2)+'Mhz</td></tr>';
                } else if (prop == 'gpu_freq_ratio' || prop == 'gpu_busy'){
                    gpu_rows = gpu_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]*100).toFixed(2)+'%</td></tr>';
                
                } else if (prop == 'freq_ratio'){
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]*100).toFixed(2)+'%</td></tr>';
                } else if (prop == 'freq_hz'){
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]/1000000000).toFixed(2)+'Ghz</td></tr>';
                } else if (prop == 'package_joules'){
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]*1).toFixed(2)+' Joules</td></tr>';
                } else if (prop == 'package_watts'){
                    processor_rows = processor_rows + '<tr><th>'+i18n.t('usage_stats.'+prop)+'</th><td>'+(d[prop]*1).toFixed(2)+' Watts</td></tr>';
                
                } else if (prop == 'clusters'){
                    // Process clusters table into fancy table
                    var clusters_data = JSON.parse(d[prop]);

                    for (cluster_entry in clusters_data){
                        if (cluster_entry.includes('_active')){

                            cluster_active = clusters_data[cluster_entry]
                            cluster_name = cluster_entry.split("_")[0].replace("Cluster", "Cluster ")
                            cluster_mhz = clusters_data[cluster_entry.replace("_active", "_freq_Mhz")]

                            // Mhz or Ghz
                            if (cluster_mhz >= 1000){
                                cluster_rows = cluster_rows + '<tr><th>'+cluster_name+'</th><td>'+(cluster_mhz/1000).toFixed(2)+'Ghz<br>'+cluster_active+'% '+i18n.t('usage_stats.average_load')+'</td></tr>';
                            } else {
                                cluster_rows = cluster_rows + '<tr><th>'+cluster_name+'</th><td>'+cluster_mhz+'Mhz<br>'+cluster_active+'% '+i18n.t('usage_stats.average_load')+'</td></tr>';
                            }
                        }
                    }
                }
            };

            // Only show boot table if data exists
            if ( boot_rows !== ""){
                $('#usage_stats-tab')
                    .append($('<div style="max-width:450px;">')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(boot_rows))));;
            }

            // Only show network table if data exists
            if ( network_rows !== ""){
                $('#usage_stats-tab')
                    .append($('<h4>')
                        .append($('<i>')
                            .addClass('fa fa-wifi'))
                        .append(' '+i18n.t('usage_stats.network_actvity')))
                    .append($('<div style="max-width:450px;">')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(network_rows))));
            }

            // Only show disk table if data exists
            if ( disk_rows !== ""){
                $('#usage_stats-tab')
                    .append($('<h4>')
                        .append($('<i>')
                            .addClass('fa fa-hdd-o'))
                        .append(' '+i18n.t('usage_stats.disk_activity')))
                    .append($('<div style="max-width:450px;">')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(disk_rows))));
            }

            // Only show processor table if data exists
            if ( processor_rows !== ""){
                $('#usage_stats-tab')
                    .append($('<h4>')
                        .append($('<i>')
                            .addClass('fa fa-microchip'))
                        .append(' '+i18n.t('usage_stats.processor_usage')))
                    .append($('<div style="max-width:450px;">')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(processor_rows))));
            }

            // Only show cluster table if data exists
            if ( cluster_rows !== ""){
                $('#usage_stats-tab')
                    .append($('<h4>')
                        .append($('<i>')
                            .addClass('fa fa-cubes'))
                        .append(' '+i18n.t('usage_stats.cluster_usage')))
                    .append($('<div style="max-width:450px;">')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(cluster_rows))));
            }

            // Only show gpu table if data exists
            if ( gpu_rows !== ""){
                $('#usage_stats-tab')
                    .append($('<h4>')
                        .append($('<i>')
                            .addClass('fa fa-television'))
                        .append(' '+i18n.t('usage_stats.gpu_usage')))
                    .append($('<div style="max-width:450px;">')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(gpu_rows))));
            }

            // Only show backlight table if data exists
            if ( backlight_rows !== ""){
                $('#usage_stats-tab')
                    .append($('<h4>')
                        .append($('<i>')
                            .addClass('fa fa-sun-o'))
                        .append(' '+i18n.t('usage_stats.backlights')))
                    .append($('<div style="max-width:450px;">')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(backlight_rows))));
            }
        }
    });
});
</script>
