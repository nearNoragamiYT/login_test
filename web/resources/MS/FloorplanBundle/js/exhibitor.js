var from = null;
var to = null;
var lastFrom = null;
var lastTo = null;
var exChartDataByDay = null;
var exChartDataByDayViews = null;
var exChartData = null;
var exData = null;
var vData = null;
var resizeTimer = 500;
var totalTour = null;
var totalRetrieval = null;
var productarray = [];
var clicksarray = [];
var enjoyhint_script_data = [];
var event = {};
var exName = '';

/*Charts and something more*/
$(window).resize(function () {
    window.clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
        drawExChart(exChartDataByDay, exChartDataByDayViews);
    }, 500);
});

$(document).ready(function () {
    show_loader_wrapper();
    if ($("#from").val() == '') {
        $("#from").val('2019/07/01');
        $("#ini").val('2019/07/01');
    }
    if ($("#to").val() == '') {
        var current = getFormatedDate();
        $("#to").val(current);
        $("#fin").val(current);
    }
    initVisitorTable();
    initReport();
    //initVisitorTable();    
    //initDataTables();
    loadData();

    $(".export-products").click(function () {
        var repotitle = evName + " " + $("#exName").text() + " - List of Products Directory";
        performExport(productarray, repotitle, prcolumns, 0);
    });

    $(".export-clicks").click(function () {
        event.preventDefault();
        var my_t = [];
        var my_arr = [];
        var ccolumns = [];

        clicksarray = [];
        my_t = $("#tableClickDetails thead").find("tr");

        $(my_t).each(function () {
            var row = [];
            var tr = $(this).find("th span");

            $(tr).each(function () {
                ccolumns.push($(this).text());
            });

            var tr2 = $(this).find("th a");

            $(tr2).each(function () {
                row.push($(this).attr('data-tooltip'));
            });
            clicksarray.push(row);
        });

        my_arr = $("#tableClickDetails_body").find("tr");

        $(my_arr).each(function () {
            var row = [];
            var tr = $(this).find("td");

            $(tr).each(function () {
                row.push($(this).text());
            });
            clicksarray.push(row);
        });

        my_arr = $("#exInformation").find(".col");

        var row2 = [];
        var row3 = [];
        var cont = 0;
        $(my_arr).each(function () {
            row2.push($(this).find("span").text());
            row2.push($(this).find("i").text());
            row3.push($(this).find("a").attr("data-tooltip"));
            if (cont == 0) {
                row2.push("");
                row3.push("");
                row3.push("");
                cont++;
            } else {
                row3.push("");
            }
        });

        clicksarray.push(emptyrow(5));
        clicksarray.push(emptyrow(5));
        clicksarray.push(row2);
        clicksarray.push(row3);

        my_arr = $("#clickDetails .card-panel");

        var row2 = [];
        var row3 = [];
        $(my_arr).each(function () {
            row2.push($(this).find("p").text());
            row2.push($(this).find("h3").text());
            row3.push($(this).attr("data-tooltip"));
            row3.push("");
        });
        row2.push("");
        row3.push("");
        clicksarray.push(emptyrow(5));
        clicksarray.push(emptyrow(5));
        clicksarray.push(row2);
        clicksarray.push(row3);
        var repotitle = evName + " " + $("#exName").text() + " - Interactions with the user";
        performExport(clicksarray, repotitle, ccolumns, -1);
    });

    $(".update").click(function () {
        show_loader_wrapper();
        loadData();
    });

    $("#to").change(function () {
        if ($("#to").val() == '') {
            var current = getFormatedDate();
            $("#to").val(current);
        }
    });

    $("#from").change(function () {
        if ($("#from").val() == '') {
            $("#from").val('2019/07/01');
        }
    });
});

function drawExChart(chartData, chartDataViews) {
    // Create the data table.
    exChartDataByDay = chartData;
    exChartDataByDayViews = chartDataViews;
    var data = new google.visualization.DataTable();
    data.addColumn('date', ms_lang.ms_textoFecha);
    data.addColumn('number', '');
    data.addColumn('number', ms_lang.ms_textoClics);
    data.addColumn('number', ms_lang.ms_textoVistas);
    rows = new Array();
    chartViewsIndex = 0;
    totalOfViews = 0;
    for (var i in chartData) {
        chartViewsAmount = 0;
        chartClicksAmount = chartData[i].amount;
        dat = chartData[i].month + "/" + chartData[i].day + "/" + chartData[i].year;
        if (typeof chartDataViews[chartViewsIndex] !== "undefined") {
            dat2 = chartDataViews[chartViewsIndex].month + "/" + chartDataViews[chartViewsIndex].day + "/" + chartDataViews[chartViewsIndex].year
            if (dat2 == dat) {
                chartClicksAmount = chartData[chartViewsIndex].amount;
                chartViewsAmount = chartDataViews[chartViewsIndex].amount;
                chartViewsIndex++;
            }
        }
        totalOfViews += parseInt(chartViewsAmount);
        rows[i] = new Array(new Date(dat), parseInt(chartClicksAmount), parseInt(chartClicksAmount), parseInt(chartViewsAmount));
    }
    data.addRows(rows);
    $("#totalInformationViews").html(commasNumber(totalOfViews, true));
    // Set chart options
    width = ($("#exMainChart").width());
    var options = {
        curveType: "function",
        'title': ms_lang.ms_tituloGrafica,
        'width': width,
        'height': 300,
        'pointSize': 5,
        legend: 'top',
        'colors': ['#FFFFFF', '#1A99AA', '#F76464'],
        hAxes: {
            0: {logScale: false, title: ms_lang.ms_textoFecha, textStyle: {color: '#000000', fontSize: 12}, showTextEvery: 1}
            //, format:'MMM d'}
        },
        vAxes: {
            0: {logScale: false, title: ms_lang.ms_textoClics, textStyle: {color: '#1A99AA'}, minValue: 0},
            1: {logScale: false, title: ms_lang.ms_textoVistas, textStyle: {color: '#F76464'}, minValue: 0}
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
    var chart = new google.visualization.AreaChart(document.getElementById("exChart"));
    chart.draw(data, options);
}

function loadData() {
    loadExhibitorData();
    loadProductDir();
    setTimeout(function () {
        hide_loader_wrapper();
    }, 10000);
}

function commasNumber(x, ceros = false) {
    var type = typeof x;
    x = type === 'number' ? x.toFixed(2) : parseFloat(x).toFixed(2);
    x = x.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    x = x.replace(/\B(?=(\d{6})+(?!\d))/g, ",");
    if (ceros) {
        x = x.replace(/\.00/g, "");
    }
    return x;
}

function loadExhibitorData() {
    from = $("#from").val();
    to = $("#to").val();
    lastFrom = from;
    lastTo = to;
    $.ajax({
        type: 'POST',
        url: url_graph_ed_ex_details,
        dataType: 'json',
        data: {
            ini: from,
            end: to,
            idExhibitor: idExpositor
        },
        success: function (response) {
            if (response.status) {
                exData = response.data[0];
                createClicksDetails(response.data);
                loadExhibitorDataByDay();
            } else {
                /* no se pudieron cargar los detalles*/
            }
            //counter++;
            //checkAjax();
        }
    });
}

function getObjetoText(str) {
    switch (str) {
        case "booth":
            return ms_lang.ms_textoStands;
        case "product":
            return ms_lang.ms_textoProductos;
        case "video":
            return ms_lang.ms_textoVideos;
        case "webpage":
            return ms_lang.ms_textoPaginaWeb;
        case "location":
            return ms_lang.ms_textoDireccion;
        case "list":
            return ms_lang.ms_textoInformacionGeneral;
        case "tour":
            return "Tour";
        case "product directory":
            return "Directorio de Productos";
        case "retrieval":
            return "Lecturas";
        case "views":
            return getLanTextBySelector("#views");
        case "all":
            return "Todos";
    }
}

function createClicksDetails(data) {
    var table = $("#tableClickDetails_body");
    $("#tableClickDetails_body").html("");
    $.each(data[0], function (key, value) {
        if (key !== "views" && key != "idExpositor" && key != "Nombre" && key != "upgrade" && key != ("PaqueteES") && value > 0 && key != "tour" && key != "retrieval" && key != "Upgrade") //si el objeto es "vistas" asignarlo a un objeto y no en la tabla
        {
            var row = jQuery('<tr/>', {}).appendTo(table);
            jQuery('<td/>', {html: getObjetoText(key)}).appendTo(row);
            var td = jQuery('<td/>', {'class': ''}).appendTo(row);
            jQuery('<a/>', {'class': 'btn btn-table blue', html: value}).appendTo(td);
            var td = jQuery('<td/>', {'class': 'click_details_c'}).appendTo(row);
            jQuery('<a/>', {id: 'objeto_' + key, 'class': 'btn btn-table green', html: ''}).appendTo(td);
            var td = jQuery('<td/>', {'class': 'click_details_c'}).appendTo(row);
            jQuery('<a/>', {id: 'a_objeto_' + key, 'class': 'btn btn-table orange', html: ''}).appendTo(td);
            var td = jQuery('<td/>', {'class': 'click_details_c'}).appendTo(row);
            jQuery('<a/>', {id: 'b_objeto_' + key, 'class': 'btn btn-table red', "data-idObjeto": key, html: ''}).appendTo(td).click(function () {
                $("#op2").click();
                if ($("#objectAmoun_" + $(this).attr("data-idObjeto")).attr("class").indexOf("active") < 0)
                    $("#objectAmoun_" + $(this).attr("data-idObjeto")).click();
            });
        }
    });
    $('#totalPeopleTour').text(parseInt(data[0].tour) > 0 ? commasNumber(data[0].tour, true) : 0);
    $('#totalRetrieval').text(parseInt(data[0].retrieval) > 0 ? commasNumber(data[0].retrieval, true) : 0);

}

function loadExhibitorDataByDay() {
    from = $("#from").val();
    to = $("#to").val();
    lastFrom = from;
    lastTo = to;
    $.ajax({
        type: 'POST',
        url: url_graph_ed_ex_details_day,
        dataType: 'json',
        data: {
            ini: from,
            end: to,
            idExhibitor: idExpositor
        },
        success: function (response) {

            if (response.status) {
                drawExChart(response.data.chart, response.data.viewsChart);
                updateRelevantInformation(response.data);
                setTimeout(function () {
                    hide_loader_wrapper();
                }, 10000);
            } else {
                /* no se pudieron cargar los detalles*/

                setTimeout(function () {
                    hide_loader_wrapper();
                }, 10000);
            }
            //counter++;
            //checkAjax();
        }
    });
}
function updateRelevantInformation(data) {
    $("#exName").html(ms_lang.ms_textoExpositor + ": " + exData.Nombre);
    exName = exData.Nombre;
    document.title = evName + " - " + exName;
    var package = (packages.hasOwnProperty(exData.upgrade)) ? "<span class='glyphicon glyphicon-star-empty' style='color:#f39c12'></span> " + packages[exData.upgrade]['Paquete' + lang.toUpperCase()] : '';
    $("#package").html(package);
    $('#totalPeopleReached').text(data.totalUniqueVisitors > 0 ? commasNumber(data.totalUniqueVisitors, true) : 0);
    totalTour = data.uniqueTour;

    for (var i in data.uniqueVisitors) {//asignar valores a los filtros de la parte inferior

        $("#objeto_" + data.uniqueVisitors[i].key).html(data.uniqueVisitors[i].amount);
        $("#a_objeto_" + data.uniqueVisitors[i].key).html(data.uniqueVisitors[i].anonymous);
        $("#b_objeto_" + data.uniqueVisitors[i].key).html(data.uniqueVisitors[i].registered);
        $("#objectAmoun_" + data.uniqueVisitors[i].key).html("(" + data.uniqueVisitors[i].registered + ")");
    }
    //$("#objectAmoun_0").html("(" + data.uniqueTour[0].cont + ")");
    setTimeout(function () {
        hide_loader_wrapper();
    }, 10000);
}

function loadDates() {
    $("#loader").slideDown('2000');
    var ini = $("#ini").val().replace(/-/g, "/");
    var fin = $("#fin").val().replace(/-/g, "/");
    var current = getFormatedDate();
    if (Date.parse(fin) > Date.parse(current)) {
        fin = current;
    }

    ini = (ini === '0000/00/00' || ini === 'NULL') ? '2015/01/01' : ini;
    fin = (fin === '0000/00/00' || fin === 'NULL') ? current : fin;
    $('#from').datepicker('update', ini);
    $('#to').datepicker('update', fin);
    var period = $('#from').val() + " - " + $('#to').val();
    $(".from-to").text(period);
    loadData();
}


function loadProductDir() {
    clearProduct();

//    from = $("#from").val();
//    to = $("#to").val();
//    $.ajax({
//        type: 'POST',
//        url: url_graph_ed_ex_products,
//        dataType: 'json',
//        data: {
//            ini: from,
//            end: to,
//            idExpositor: idExpositor
//        },
//        success: function (response) {
//            if (response.status) {
//                (jQuery.isEmptyObject(response.data)) ? clearProduct() : drawProductDetails(response.data);
//            } else {
//                clearProduct();
//            }
//        }
//    });

}

function drawProductDetails(prData) {
    clearTable("#main_productsTab");
    $(".tabc").css("display", "none");
    $(".export-products").css("display", "none");
    productarray = [];

    for (var i in prData) {
        var amount = prData[i].cantidad;
        var name = prData[i].Producto;
        var ca = prData[i].Categoria;
        var rows = [name, ca, amount];
        productarray.push(rows);
    }
    if (!jQuery.isEmptyObject(productarray)) {
        AddSearchTerm('#main_productsTab', productarray);
        $(".tabc").css("display", "block");
        $(".export-products").css("display", "block");
    }
}

function clearProduct() {
    $(".tabc").css("display", "none");
    $("#button-products").remove();
    clearTable("#main_productsTab");
}
function clearVisitorTours() {
    $(".tabd").css("display", "none");
    clearTable("#main_visitorsTab");
}


function initDataTables() {

    var options = {
        responsive: true,
        paging: true,
        language: {
            "url": url_lang
        },
        aaSorting: [],
        bAutoWidth: false,
        order: [[2, "desc"]]
    };
    $('#main_productsTab').DataTable(options);
    $('#main_visitorsTab').DataTable(options);
}

function updateRetrievalInfo(totalRetrieval) {
    $(".totalretrieval").html(totalRetrieval); //#objectAmoun_00, #totalRetrieval
}

function initReport() {

    var container = $("#user-export");

    var option = [
        {
            class: ' export-clicks',
            li: 'button-clicks',
            tooltip: ms_lang.ms_exportarInteraccion,
            click: '',
            color: 'red',
            text: '<i class="material-icons">person</i>'
        },
        {
            class: ' export-products',
            li: 'button-products',
            tooltip: "Export list of Products directory",
            click: '',
            color: 'yellow darken-3',
            text: '<i class="material-icons">subject</i>'
        },
        {
            class: ' printchart',
            li: 'button-printchart',
            tooltip: ms_lang.ms_exportarGrafica,
            click: 'event.preventDefault();printChart("exMainChart");',
            color: 'green',
            text: '<i class="material-icons">insert_chart</i>'
        }
    ];

    $.each(option, function (key, val) {
        var opt = jQuery('<li/>', {
            id: val.li
        }).appendTo(container);

        jQuery('<a/>',
                {
                    "href": "#",
                    class: "btn-floating tooltipped " + val.color + val.class,
                    "data-position": "bottom",
                    "data-delay": "50",
                    "data-tooltip": val.tooltip,
                    html: val.text,
                    "onclick": val.click
                }
        ).appendTo(opt);
        $('.tooltipped').tooltip({delay: 50});
    });

}

function isEmpty(str) {
    str = (str === '' || str === null) ? '' : str;
    return str;
}

var visitorTable;
function initVisitorTable() {
    init_table({
        "table_name": "visitor-table",
        "wrapper": "cover-visitor-table",
        "columns": visitor_table_columns,
        "column_categories": visitor_table_column_categories,
        "text_datatable": url_lang,
        "custom_filters": true,
        "server_side": true,
        "cache_data": true,
        "cache_pages": 10,
        "url_get_data": url_visitor_get_to_dt,
        "export_data": true,
        "url_export_data": url_export_general_data,
        "callback_init": callbackVisitorTable,
        "row_column_id": 'Nombre',
        "edit_rows": false,
        "lang": lang,
    });

}

function callbackVisitorTable($data_table) {
    visitorTable = $data_table;
    // LENGTH - Inline-Form control
    var length_sel = visitorTable.closest('.dataTables_wrapper').find('div[id$=_length] select');
    length_sel.addClass('form-control input-sm');
}
