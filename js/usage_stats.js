var format_usage_stats_thermal_pressure = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    colvar = colvar == 'Heavy' ? '<span class="label label-danger">Heavy</span>' :
    colvar = colvar == 'Moderate' ? '<span class="label label-warning">Moderate</span>' :
    (colvar === 'Nominal' ? '<span class="label label-success">Nominal</span>' : 
    colvar = colvar)
    col.html(colvar)
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