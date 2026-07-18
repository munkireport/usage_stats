var format_usage_stats_thermal_pressure = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text().trim();
    var thermalLower = colvar.toLowerCase();
    var labelClass = 'label-default';  // Default: Unknown = grey
    
    if (thermalLower === 'nominal') {
        labelClass = 'label-success';  // Nominal = green
    } else if (thermalLower === 'moderate') {
        labelClass = 'label-info';     // Moderate = blue
    } else if (thermalLower === 'heavy') {
        labelClass = 'label-warning';  // Heavy = yellow
    } else if (thermalLower === 'critical') {
        labelClass = 'label-danger';   // Critical = red
    }
    
    col.html('<span class="label ' + labelClass + '">' + colvar + '</span>')
}

var format_usage_stats_byte_rate = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar > 0){
        col.text(fileSize(parseFloat(colvar), 2)+'/s')
    } else {
        col.text("")
    }
}

var format_usage_stats_byte_size = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar > 0){
        col.text(fileSize(parseFloat(colvar), 2))
    } else {
        col.text("")
    }
}

var format_usage_stats_rate = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar > 0){
        col.text(parseFloat(colvar).toFixed(2)+'/s')
    } else {
        col.text("")
    }
}

var format_usage_stats_watts = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar > 0){
        col.text(parseFloat(colvar).toFixed(2)+' Watts')
    } else {
        col.text("")
    }
}

var format_usage_stats_mhz = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar > 0){
        col.text(((parseFloat(colvar)/1000000).toFixed(2))+'Mhz')
    } else {
        col.text("")
    }
}

var format_usage_stats_ghz = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar > 0){
        col.text(((parseFloat(colvar)/1000000000).toFixed(2))+'Ghz')
    } else {
        col.text("")
    }
}

var format_usage_stats_ratio = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar > 0){
        col.text((parseFloat(colvar*100)).toFixed(2)+'%')
    } else {
        col.text("")
    }
}

var format_usage_stats_cpu_idle = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar !== "" && !isNaN(parseFloat(colvar))){
        var value = parseFloat(colvar);
        var percent = value.toFixed(1);
        var labelClass = value > 70 ? 'label-success' :  // High idle (good) = green
                        value > 30 ? 'label-warning' :   // Medium idle = yellow
                        'label-danger';                   // Low idle (busy) = red
        col.html('<span class="label ' + labelClass + '">' + percent + '%</span>')
    }
}

var format_usage_stats_cpu_usage_from_idle = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar !== "" && !isNaN(parseFloat(colvar))){
        var idleValue = parseFloat(colvar);
        var usageValue = 100 - idleValue;  // Calculate usage from idle
        var percent = usageValue.toFixed(1);
        var labelClass = usageValue >= 70 ? 'label-danger' :   // High usage (>= 70%) = red
                        usageValue >= 30 ? 'label-warning' :    // Medium usage (>= 30%) = yellow
                        'label-success';                         // Low usage (< 30%) = green
        col.html('<span class="label ' + labelClass + '">' + percent + '%</span>')
    }
}

var format_usage_stats_cpu_usage = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar !== "" && !isNaN(parseFloat(colvar))){
        var value = parseFloat(colvar);
        var percent = value.toFixed(1);
        var labelClass = value >= 70 ? 'label-danger' :   // High usage (>= 70%) = red
                        value >= 30 ? 'label-warning' :    // Medium usage (>= 30%) = yellow
                        'label-success';                    // Low usage (< 30%) = green
        col.html('<span class="label ' + labelClass + '">' + percent + '%</span>')
    }
}

var format_usage_stats_gpu_busy = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    if (colvar !== "" && !isNaN(parseFloat(colvar))){
        var value = parseFloat(colvar);
        var percent = (value * 100).toFixed(1);  // GPU busy is a ratio (0-1)
        var percentValue = value * 100;
        var labelClass = percentValue >= 70 ? 'label-danger' :   // High usage (>= 70%) = red
                        percentValue >= 30 ? 'label-warning' :    // Medium usage (>= 30%) = yellow
                        'label-success';                           // Low usage (< 30%) = green
        col.html('<span class="label ' + labelClass + '">' + percent + '%</span>')
    }
}