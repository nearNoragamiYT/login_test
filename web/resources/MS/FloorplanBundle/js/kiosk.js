var exChartDataByDay = null;
var exChartDataByDayViews = null;
var exChartData = null;
var exData = null;
var resizeTimer = 500;
var karray = [];

/*Charts and something more*/
$(window).resize(function () {
    window.clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
        drawKioskChart(exChartDataByDay, exChartDataByDayViews);
    }, 500);
});

$(document).ready(function () {
    //initTooltips();
    initReport();
    drawModules();
    initRangeBox();
    initDatePickers();
    initInfoBox();
    initDates();

    var rowtitle = ["", textosGenerales.msapi_total_clicks, textosGenerales.msapi_total_views, textosGenerales.msapi_total_searches, textosGenerales.lb_kioskos_instalados];
    var kcolumns = [textosGenerales.lb_kioskos, textosGenerales.msapi_date, textosGenerales.msapi_clicks, textosGenerales.msapi_views, textosGenerales.msapi_searches];


    $('.export-kiosk').click(function () {
        var repotitle = $(".event-name").text() + " - " + textosGenerales.lb_kiosko_estadistica;
        performExport(karray, repotitle, kcolumns, -1);
    });

    $(".update").click(function () {
        $("#loader").slideDown('2000');
        var period = $('#from').val() + " - " + $('#to').val();
        $(".from-to").text(period);
        $(".close-box").trigger("click");
        loadData();
    });
//    $(".help").click(function () {
//        showHelp();
//    });
    //loadData();
});


function loadData() {
    from = $("#from").val();
    to = $("#to").val();

    $.ajax({                   
        type: 'POST',
        url: url_kiosk_details,
        dataType: 'json',
        data: {
            ini: from,
            end: to,
            idEdition: idEdicion
        },
        success: function (response) {

            if (response.status) {
                karray = [];
                drawKioskChart(response.data.chart.clicks, response.data.chart.views);
                drawKioskDetails(response.data.kioskos);
            } else {
                emptyKiosk();
            }
            //counter++;
            //checkAjax();
        }
    });
}

function drawKioskChart(chartData, chartDataViews) {
    // Create the data table.
    exChartDataByDay = chartData;
    exChartDataByDayViews = chartDataViews;
    var data = new google.visualization.DataTable();
    data.addColumn('date', textosGenerales.msapi_date);
    data.addColumn('number', '');
    data.addColumn('number', textosGenerales.msapi_clicks);
    data.addColumn('number', textosGenerales.msapi_views);
    rows = new Array();
    chartViewsIndex = 0;
    totalOfViews = 0;
    for (var i in chartData) {
        chartViewsAmount = 0;
        chartAmount = chartData[i].amount;
        dat = chartData[i].month + "/" + chartData[i].day + "/" + chartData[i].year;
        if (typeof chartDataViews[chartViewsIndex] !== "undefined") {
            dat2 = chartDataViews[chartViewsIndex].month + "/" + chartDataViews[chartViewsIndex].day + "/" + chartDataViews[chartViewsIndex].year
            if (dat2 == dat) {
                chartAmount -= chartDataViews[chartViewsIndex].amount;
                chartViewsAmount = chartDataViews[chartViewsIndex].amount;
                chartViewsIndex++;
            }
        }
        totalOfViews += parseInt(chartViewsAmount);
        rows[i] = new Array(new Date(dat), parseInt(chartAmount), parseInt(chartAmount), parseInt(chartViewsAmount));
    }
    data.addRows(rows);
    $("#totalInformationViews").html(totalOfViews);
    // Set chart options
    width = ($("#kMainChart").width());
    var options = {
        curveType: "function",
        'title': textosGenerales.msapi_userInteraction,
        'width': width,
        'height': 300,
        'pointSize': 5,
        legend: 'top',
        'colors': ['#FFFFFF', '#1A99AA', '#F76464'],
        hAxes: {
            0: {logScale: false, title: textosGenerales.msapi_date, textStyle: {color: '#000000', fontSize: 12}, showTextEvery: 1}
            //, format:'MMM d'}
        },
        vAxes: {
            0: {logScale: false, title: textosGenerales.msapi_clicks, textStyle: {color: '#1A99AA'}, minValue: 0},
            1: {logScale: false, title: textosGenerales.msapi_views, textStyle: {color: '#F76464'}, minValue: 0}
        },
        series: {
            0: {targetAxisIndex: 0},
            1: {targetAxisIndex: 0, color: '#1A99AA', visibleInLegend: true, pointSize: 5, minValue: 0},
            2: {targetAxisIndex: 1, color: '#F76464', visibleInLegend: true, pointSize: 5, minValue: 0}
        },
        'backgroundColor': "transparent",
        hAxis: {format: 'yyyy/MM/dd'}//formato para la fecha

    };
    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.AreaChart(document.getElementById("kChart"));
    chart.draw(data, options);
}

function initDates() {

    $.ajax({                   
        type: 'POST',
        url: url_kiosk_dates,
        dataType: 'json',
        data: {
            idEdition: idEdicion
        },
        success: function (response) {

            if (response.status) {
                setDates(response.data);
            } else {
                emptyKiosk();
            }
        }
    });
}

function setDates(data) {
    $("#loader").slideDown('2000');
    $('#from').datepicker('update', data.ini);
    $('#to').datepicker('update', data.end);

    var period = $('#from').val() + " - " + $('#to').val();
    $(".from-to").text(period);
    loadData();

}

function initInfoBox() {
    createBox(textosGenerales.msapi_total_clicks, "bg-aqua click", "glyphicon-hand-up");
    createBox(textosGenerales.msapi_total_views, "bg-green view", "glyphicon-eye-open");
    createBox(textosGenerales.msapi_total_searches, "bg-yellow search", "glyphicon-search");
    createBox(textosGenerales.lb_kioskos_instalados, "bg-red kiosk", "glyphicon-blackboard");
}

function createBox(text, bg, icon) {
    var cont = $("#kTotal");
    var div = jQuery('<div/>', {"class": "col-lg-3 col-xs-6"}).appendTo(cont);
    var box = jQuery('<div/>', {'class': 'small-box ' + bg}).appendTo(div);
    var inn = jQuery('<div/>', {'class': 'inner ' + bg}).appendTo(box);
    jQuery('<h3/>', {'text': 0}).appendTo(inn);
    jQuery('<p/>', {'text': text}).appendTo(inn);
    inn = jQuery('<div/>', {'class': 'icon'}).appendTo(box);
    jQuery('<li/>', {'class': 'glyphicon ' + icon}).appendTo(inn);
    jQuery('<div/>', {'class': 'small-box-footer'}).appendTo(box);
}

function drawKioskDetails(data) {
    var label = textosGenerales.lb_kiosko_numero;
    var tc = 0, tv = 0, ts = 0, tk = 0;
    var cont = $(".panel-kiosk");
    cont.empty();
    $.each(data, function (i, value) {

        var div = jQuery('<div/>', {"class": "col-lg-6 col-md-6 col-sm-12 col-xs-12", "style": "padding: 10px;"}).appendTo(cont);
        var body = jQuery('<div/>', {"class": "panel col-xs-12"}).appendTo(div);
        var box = jQuery('<div/>', {'class': 'kiosko-label'}).appendTo(body);
        jQuery('<h3/>', {'text': label.replace("%kiosko%", i)}).appendTo(box);
        var table = jQuery('<table/>', {'class': 'col-xs-12 table-bordered', "style": "text-align:center;"}).appendTo(body);
        var totc = 0, totv = 0, tots = 0;
        var tr = jQuery('<tr/>', {"style": "color: #fff; background: #f39c12;"}).appendTo(table);
        jQuery('<td/>', {'text': textosGenerales.msapi_date}).appendTo(tr);
        jQuery('<td/>', {'text': textosGenerales.msapi_clicks}).appendTo(tr);
        jQuery('<td/>', {'text': textosGenerales.msapi_views}).appendTo(tr);
        jQuery('<td/>', {'text': textosGenerales.msapi_searches}).appendTo(tr);
        $.each(value, function (j, value2) {
            tr = jQuery('<tr/>').appendTo(table);
            jQuery('<td/>', {'text': value2.date}).appendTo(tr);
            jQuery('<td/>', {'text': value2.data['clicks']}).appendTo(tr);
            jQuery('<td/>', {'text': value2.data['views']}).appendTo(tr);
            jQuery('<td/>', {'text': value2.data['searches']}).appendTo(tr);
            totc += value2.data['clicks'];
            totv += value2.data['views'];
            tots += value2.data['searches'];
            var rows = [i, value2.date, value2.data['clicks'], value2.data['views'], value2.data['searches']];
            karray.push(rows);
        });
        tc += totc;
        tv += totv;
        ts += tots;
        tk++;
        tr = jQuery('<tr/>', {"style": "background: #E0DFDF;"}).appendTo(table);
        jQuery('<td/>', {'text': textosGenerales.total_label}).appendTo(tr);
        jQuery('<td/>', {'text': totc}).appendTo(tr);
        jQuery('<td/>', {'text': totv}).appendTo(tr);
        jQuery('<td/>', {'text': tots}).appendTo(tr);
        var rows = ["", textosGenerales.total_label, totc, totv, tots];
        karray.push(rows);
    });

    var rowqty = ["", tc, tv, ts, tk];
    karray.push(emptyrow(5), rowtitle, rowqty);
    $(".click .inner h3").text(tc);
    $(".view .inner h3").text(tv);
    $(".search .inner h3").text(ts);
    $(".kiosk .inner h3").text(tk);
    $("#loader").slideUp('2000');
}

function emptyKiosk() {
    $("#kEmpty").empty();
    var novisitors = "<span class='glyphicon glyphicon-signal'></span> " + textosGenerales.sin_datos;
    var d = jQuery('<div/>', {'style': 'margin: 0 auto; margin-top: 40px; margin-bottom: 40px; text-align: center;'}).appendTo("#kEmpty");
    jQuery('<p/>', {'html': novisitors, 'style': 'font-size: 25px; color: #B3B0B0;'}).appendTo(d);
    $("#loader").slideUp('2000');
}

function initReport() {

    var container = $(".user-body");

    var option = [{class: 'export-kiosk', text: textosGenerales.exportar + " " + textosGenerales.lb_kiosko_estadistica, click: ''}];

    $.each(option, function (key, val) {
        var opt = jQuery('<div/>',
                {
                    class: "col-xs-6 text-center " + val.class,
                    style: 'border: 1px solid #ccc; margin-bottom: 4px;'
                }
        ).appendTo(container);

        jQuery('<a/>',
                {
                    "href": "#",
                    text: val.text,
                    "onclick": val.click
                }
        ).appendTo(opt);

    });

}