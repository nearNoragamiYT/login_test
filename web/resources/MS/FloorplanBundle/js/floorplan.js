var from = null;
var to = null;
var lastFrom = null;
var lastTo = null;
var cData = new Array();
var sData = new Array();
var totalDays = 0;
var global_exData = [];
var mtData = [];
var lrData = [];
var package = '';
var exarray = [];
var exarrayexport = [];
var repotitle;
var earcharray = [];
var categoryarray = [];
var productarray = [];
var summaryarray = [];
var resizeTimer = null;
var currentExId = null;

var event = {};

enjoyhint_script_data = [];



var excolumns = ["Nombre", "Paquete Contratado", "Recorrido", "Lecturas de visitantes", "Cantidad de clics"];
var cacolumns = ["Categoria", "Cantidad"];
var secolumns = ["Texto buscado", "Cantidad"];
var prcolumns = ["Producto", "Categoria", "Expositor", "Cantidad de clics"];

var counter = 0;
(function () {
    initReport();
    initDataTables();
    loadDates();
    $(window).resize(function () {
        drawMainChart(cData["drawMainChart"], false);
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
        $("#ini").val($("#to").val());
    });

    $("#from").change(function () {
        if ($("#from").val() == '') {
            $("#from").val('2019/07/01');
        }
        $("#fin").val($("#from").val());
    });

    $('.toc-wrapper').pushpin();

    $(".export-exhibitors").click(function () {
        event.preventDefault();
        repotitle = evName + " - Lista de Expositores";
        //repotitle = $("#idEdition option:selected").text().replace('-', '') + " - Listado de Expositores";
        var featured= $('#featured-filter').prop('checked') ? true:false;        
        var tour= $('#mytour-filter').prop('checked') ? true:false;
        var retrieval = $('#retrieval-filter').prop('checked') ? true:false;
        repotitle += featured===true || tour===true || retrieval===true ?' con ':'';
        repotitle += featured===true ?'Paquete ':'';
        repotitle += tour===true ?'Recorridos ':'';
        repotitle += retrieval===true ?'Lecturas ':'';
        performExportExPa(exarrayexport, repotitle, excolumns, 1,featured,tour,retrieval);
    });

    $(".export-categories").click(function () {
        event.preventDefault();
        repotitle = evName + " - Listado de Categorias";
        //repotitle = $("#idEdition option:selected").text().replace('-', '') + " - Listado de Categorias";
        performExport(categoriesarray, repotitle, cacolumns, 0);
    });

    $(".export-searches").click(function () {
        event.preventDefault();
        repotitle = evName + " - Listado de Busquedas";
        //repotitle = $("#idEdition option:selected").text().replace('-', '') + " - Listado de Busquedas";
        performExport(searcharray, repotitle, secolumns, 0);
    });

    $(".export-products").click(function () {
        event.preventDefault();
        repotitle = evName + " - Lista de Productos";
        //repotitle = $("#idEdition option:selected").text().replace('-', '') + " - Lista de Productos";
        performExport(productarray, repotitle, prcolumns, 0);
    });

    $(".export-summary").click(function () {
        event.preventDefault();
        var my_arr = [];
        summaryarray = [];
        my_arr = $("#top_su").find(".top_row");

        $(my_arr).each(function () {
            summaryarray.push([$(this).find(".top_row_text").text(), $(this).find(".top_row_value").text()]);
        });
        repotitle = evName + " - Sumario";
        //repotitle = $("#idEdition option:selected").text().replace('-', '') + " - Sumario";
        performExport(summaryarray, repotitle, ["Concepto", "Cantidad"], -1);
    });

})();
//carga de la información
function loadData() {
    show_loader_wrapper();
    if ($("#from").val() == '') {
        $("#from").val('2019/07/01');
        $("#fin").val('2019/07/01');
    }
    if ($("#to").val() == '') {
        var current = getFormatedDate();
        $("#to").val(current);
        $("#ini").val(current);
    }
    loadExDetails();
    loadAmountOfClick();
    loadProductDir();
    loadSearch();
    loadVisitor();
    setTimeout(function () {
        hide_loader_wrapper();
    }, 5000);
    //$.getJSON(url_graph_get_bookmark_refresh);
}

function commasNumber(x,ceros=false){
    var type = typeof x;
    x= type ==='number' ? x.toFixed(2):  parseFloat(x).toFixed(2);
    x=x.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    x=x.replace(/\B(?=(\d{6})+(?!\d))/g, ",");
    if(ceros){    
        x=x.replace(/\.00/g, "");
    }
    return x;
}

function isNewQuery() {
    if (lastFrom !== from || lastTo !== to)
        return true;
    return false;
}

//Grafica de vistas/clicks
function loadAmountOfClick(reload) {
    if (reload === undefined)
        reload = true;

    from = $("#from").val();
    to = $("#to").val();
    //Not necesary reaload data
    if (!isNewQuery()) {
        google.charts.setOnLoadCallback(function(){drawMainChart(cData["drawMainChart"], false)});
        return;
    }
    lastFrom = from;
    lastTo = to;

    $.ajax({
        type: 'POST',
        url: url_graph_get_clicks,
        dataType: 'json',
        data: {
            ini: from,
            end: to,
            type: "day"
        },
        success: function (response) {

            if (response.status) {
                google.charts.setOnLoadCallback(function(){drawMainChart(response.data.chart, reload)});
            } else {
                /* no se pudieron cargar los detalles*/
            }
            counter++;
            checkAjax();
        }
    });

}

//Usada por grafica vistas/clicks
function drawMainChart(chartData, reload) {
    // Create the data table.  
    exChartDataByDay = chartData;
    var data = new google.visualization.DataTable();
    data.addColumn('date', "Fecha");
    data.addColumn('number', "Clics");
    data.addColumn('number', "Vistas");
    var clicks = 0;
    rows = new Array();
    totalDays = 0;
    totalClicksSum = 0;
    chartViewsIndex = 0;
    totalOfViews = 0;

    for (var i in chartData) {
        dat = chartData[i].month + "/" + chartData[i].day + "/" + chartData[i].year;
        chartAmount = chartData[i].c_amount;
        chartViewsAmount = chartData[i].v_amount;
        totalClicksSum += parseInt(chartAmount);
        totalOfViews += parseInt(chartViewsAmount);
        totalDays++;
        clicks = parseInt(chartAmount);
        rows[i] = new Array(new Date(dat), clicks, parseInt(chartViewsAmount));
        i++;
    }
    data.addRows(rows);
    // Set chart options
    width = ($(".main_data").width() * 0.98);//no se restan los 240 px para que ocupe todo el ancho
    var options = {curveType: "function",
        'title': "Interacción de los visitantes",
        'width': width,
        'height': 300,
        'pointSize': 5,
        legend: 'top',
        'colors': ['#FFFFFF', '#1A99AA', '#F76464'],
        'backgroundColor': "#FFF",
        hAxes: {
            0: {logScale: false, title: "Fecha", textStyle: {color: '#000000', fontSize: 12}, showTextEvery: 1}
        },
        vAxes: {
            0: {logScale: false, title: "Clics", textStyle: {color: '#1A99AA'}, minValue: 0},
            1: {logScale: false, title: "Vistas", textStyle: {color: '#F76464'}, minValue: 0}

        },
        series: {
            0: {targetAxisIndex: 0, color: '#1A99AA', visibleInLegend: true, pointSize: 5, minValue: 0},
            1: {targetAxisIndex: 1, color: '#F76464', visibleInLegend: true, pointSize: 5, minValue: 0}
        },
        hAxis: {format: 'yyyy/MM/dd'}//formato para la fecha
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.AreaChart(document.getElementById('main_chart'));
    chart.draw(data, options);
    cData["drawMainChart"] = chartData;
    if (reload) {
        totalClicksSum !== 0 ? av_clicks = (totalClicksSum / totalDays).toFixed(2) : av_clicks = 0;
        totalOfViews !== 0 ? av_views = (totalOfViews / totalDays).toFixed(2) : av_views = 0;
        totalClicksSum = commasNumber(totalClicksSum);
        totalOfViews = commasNumber(totalOfViews);
        av_clicks = commasNumber(av_clicks);
        av_views = commasNumber(av_views);
        $('#top_total_click').text(parseFloat(totalClicksSum) > 0 ? totalClicksSum : 0.00);
        $('#top_total_view').text(parseFloat(totalOfViews) > 0 ? totalOfViews : 0.00);
        $('#top_average_clicks').text(parseFloat(av_clicks) > 0 ? av_clicks : 0.00);
        $('#top_average_views').text(parseFloat(av_views) > 0 ? av_views : 0.00);
    }

}

function loadExDetails() {
    $.ajax({
        type: 'POST',
        url: url_graph_ex_details,
        dataType: 'json',
        data: {
            ini: $("#from").val(),
            end: $("#to").val()
        },
        success: function (response) {

            if (response.status) {
                global_exData = response.data;
                drawExDetails(global_exData);
                $("#top_total_mytour").text(commasNumber(total_tours));
                $("#top_total_retrievalreads").text(commasNumber(total_retrieval));
                $("#top_total_retrievalexh").text(commasNumber(total_exhibitor_retrieval));
            } else {
                /* no se pudieron cargar los detalles*/
            }
            counter++;
            checkAjax();
        }
    });
}

function loadDates() {

    $.ajax({
        type: 'POST',
        url: url_graph_get_dates,
        dataType: 'json',
        data: {
            idEdition: $("#idEdition").val()
        },
        success: function (response) {
            if (response.status) {
                var ini = $("#from").val().replace(/-/g, "/");
                var fin = $("#to").val().replace(/-/g, "/");
                var current = getFormatedDate();

                if (Date.parse(fin) > Date.parse(current)) {
                    fin = current;
                }

                ini = (ini === '0000/00/00' || ini === 'NULL') ? '2019/07/01' : ini;
                fin = (fin === '0000/00/00' || fin === 'NULL') ? current : fin;

                $('#from').datepicker('update', ini);
                $('#to').datepicker('update', fin);
                var period = $('#from').val() + " - " + $('#to').val();
                $(".from-to").text(period);
                loadData();
                KioskButton(response.data['kiosk']);
            } else {
                KioskButton(false);
                loadData();
                /* no se pudieron cargar los detalles*/
            }
        }
    });
}

var totalAmount = 0;
function drawExDetails(exData) {
    totalAmount = 0;
    cData["drawExDetails"] = exData;
    clearTable("#main_exTab");
    /*Delete table information*/
    /*Create rows*/
    totalOfEx = 0;
    exarray = [];
    exarrayexport = [];
    total_tours = 0;
    total_retrieval = 0;
    total_exhibitor_retrieval = 0;
    for (var i in exData) {
        totalOfEx++;
        //Este es el llenado del DataTable de Expositor
        /* Get id and Name */
        idEx = exData[i].idExpositor;
        name = exData[i].Nombre;
        sum = 0;
        upgrade = exData[i].upgrade;
        package = exData[i]['PaqueteES'];
        var tours = exData[i].Recorrido;
        var retrieval = exData[i].Lectura;
        $.each(exData[i], function (key, value) {
            if (key == "Lectura") {
                total_retrieval += parseInt(value);
                if (parseInt(value) > 0)
                    total_exhibitor_retrieval += 1;
            }
            if (key == "Recorrido")
                total_tours += parseInt(value);
            if (key !== "Views" && key !== "idExpositor" && key !== "Nombre" && key !== "upgrade" && key !== ("Paquete" + lang.toUpperCase()) && key !== "Lectura" && key !== "Recorrido") //si el objeto es "vistas" asignarlo a un objeto y no en la tabla
                sum += parseInt(value);
        });
        totalAmount += sum;
        package = (package !== '' && typeof package !== null) ? package : '';
        AddExRow(name, sum, idEx, parseInt(upgrade), parseInt(tours), parseInt(retrieval), package);
    }

    if (!jQuery.isEmptyObject(exarray)) {
        var addnew = $('#main_exTab').dataTable().fnAddData(exarray);//api datatables sin plugin
    }
    if (totalOfEx > 0) {
        loadTops(exData);
    } else {
        $("#top_ex").find(".content").empty();
    }
    $('#top_average_ex').text(commasNumber((totalAmount / totalOfEx).toFixed(2)));
}

function loadSearch() {

    from = $("#from").val();
    to = $("#to").val();
    idEdition = $("#idEdition").val();

    $.ajax({
        type: 'POST',
        url: url_graph_get_search,
        dataType: 'json',
        data: {
            ini: from,
            end: to,
            type: 0//busquedas texto
        },
        success: function (response) {

            if (response.status) {
                sData = null;
                sData = response.data;
                drawSearchDetails(response.data);
                topSearch(response.data);
            } else {
                searcharray = [];
                $("#top_search").find(".content").empty();
                clearTable("#main_searchTab");
            }
            counter++;
            checkAjax();
        }
    });

    $.ajax({
        type: 'POST',
        url: url_graph_get_search,
        dataType: 'json',
        data: {
            ini: from,
            end: to,
            type: 1//categorias
        },
        success: function (response) {
            if (response.status) {
                sData = null;
                sData = response.data;
                google.charts.setOnLoadCallback(drawCategoriesDetails(response.data));
                topCategories(response.data);
            } else {
                categoriesarray = [];
                $("#top_categories").find(".content").empty();
                clearTable("#main_categoriesTab");
            }
            counter++;
            checkAjax();
        }
    });
}

function drawSearchDetails(sData) {
    searcharray = [];
    clearTable("#main_searchTab");
    searchtBody = $("#searchtbody");
    totalSearch = 0;
    for (var i in sData) {
        /* Get id and Name */
        amount = sData[i].amount;
        name = sData[i].first_word;
        totalSearch += parseInt(amount);
        var rows = [name, amount];
        searcharray.push(rows);
        //updateTotalClicks();
    }
    AddSearchTerm('#main_searchTab', searcharray);
    $("#top_total_search").text(commasNumber(totalSearch.toFixed(2)));
}

function drawCategoriesDetails(sData) {
    categoriesarray = [];
    clearTable("#main_categoriesTab");
    categoriestBody = $("#categoriestbody");
    totalCategories = 0;
    for (var i in sData) {
        /* Get id and Name */
        amount = sData[i].amount;
        name = sData[i].first_word;
        totalCategories += parseInt(amount);
        var rowc = [name, amount];
        categoriesarray.push(rowc);
    }
    AddSearchTerm('#main_categoriesTab', categoriesarray);
    $("#top_total_categories").text(commasNumber(totalCategories));
}

function loadVisitor() {

    from = $("#from").val();
    to = $("#to").val();
    idEdition = $("#idEdition").val();

    $.ajax({
        type: 'POST',
        url: url_graph_get_visitors,
        dataType: 'json',
        data: {
            ini: from,
            end: to,
            idEdition: idEdition,
        },
        success: function (response) {

            if (response.status) {
                topVisitor(response.data);
            } else {
                /* no se pudieron cargar los detalles*/
            }
            counter++;
            checkAjax();
        }
    });

}

function loadMyTour() {
    drawExDetails(global_exData);
    $("#top_total_mytour").text(commasNumber(total_tours.toFixed(2)));
    $("#top_total_retrievalreads").text(commasNumber(total_retrieval.toFixed(2)));
    $("#top_total_retrievalexh").text(commasNumber(total_exhibitor_retrieval.toFixed(2)));

    //return;
    mtData = null;
    mtData = [];
    lrData = null;
    lrData = [];
    ////desasignar valores del arreglo para que cuando se cambie de un evento a otro se muestren datos correctos de los tours

    $.ajax({
        type: 'POST',
        url: url_graph_get_tour,
        dataType: 'json',
        data: {
            ini: $("#from").val(),
            end: $("#to").val()
        },
        success: function (response) {
            var sum = 0;
            var sum2 = 0;
            var sum3 = 0;
            if (response.status) {

                mtData = null;
                mtData = [];
                lrData = null;
                lrData = [];
                $.each(response.data['tour'], function (key, val) {
                    mtData[val.idExpositor] = val.amount;
                    sum += parseInt(val.amount);
                });
                $.each(response.data['retrieval'], function (key, val) {
                    lrData[val.idExpositor] = val.amount;
                    sum2 += parseInt(val.amount);
                    sum3++;
                });
            }
            $("#top_total_mytour").text(sum);
            $("#top_total_retrievalreads").text(sum2);
            $("#top_total_retrievalexh").text(sum3);
            drawExDetails(global_exData);
            counter++;
            checkAjax();
        }
    });
}

/***************************************  Tops ********************************/

var topElements = 10;

function loadTops(exData) {
    topExhibitors(exData);
}

function topExhibitors(exData) {
    content = $("#top_ex").find(".content");
    content.empty();

    temporal = [];
    /*Create an temporal array to sort after*/

    for (var i in exData) {
        idEx = exData[i].idExpositor;
        name = exData[i].Nombre;
        sum = 0;
        upgrade = exData[i].upgrade;
        var pqt = exData[i].PaqueteES;
        var tours = exData[i].Recorrido;
        var retrieval = exData[i].Lectura;
        $.each(exData[i], function (key, value) {
            if (key !== "Views" && key !== "idExpositor" && key !== "Nombre" && key !== "upgrade" && key !== ("Paquete" + lang.toUpperCase()) && key !== "Lectura" && key !== "Recorrido") //si el objeto es "vistas" asignarlo a un objeto y no en la tabla
                sum += parseInt(value);
        });
        temporal[i] = {name: name, amount: sum, upgrade: upgrade, id: idEx, tours: tours, retrieval: retrieval, pqt: pqt};
    }
    
    /*Sort array by amount of tours*/
    temporal.sort(SortByTours);
    
    /*Sort array by amount of clicks*/
    /*temporal.sort(SortByClicks);*/

    /*Create a table with first topElements*/
    for (i = 0; i < topElements && temporal.length > i; i++) {
        content.append(top_ExhRow(temporal[i].name, temporal[i].amount, parseInt(temporal[i].upgrade), parseInt(temporal[i].id), parseInt(temporal[i].tours), parseInt(temporal[i].retrieval), temporal[i].pqt));
    }

}

function topSearch(sData) {
    content = $("#top_search").find(".content");
    content.empty();
    temporal = [];
    /*Create an temporal array to sort after*/
    /*Get all Data related with*/
    for (var x in sData) {
        name = sData[x].first_word;
        sum = sData[x].amount;
        temporal[x] = {name: name, amount: sum};
    }
    /*Sort array by amount of clicks*/
    temporal.sort(SortByClicks);
    /*Create a table with first topElements*/
    for (i = 0; i < topElements && temporal.length > i; i++) {
        content.append(top_SearchRow(temporal[i].name, temporal[i].amount));
    }
}

function topCategories(sData) {
    content = $("#top_categories").find(".content");
    content.empty();
    temporal = [];
    /*Create an temporal array to sort after*/

    /*Get all Data related with*/
    for (var x in sData) {
        name = sData[x].first_word;
        sum = sData[x].amount;
        temporal[x] = {name: name, amount: sum};
    }

    /*Sort array by amount of clicks*/
    temporal.sort(SortByClicks);

    /*Create a table with first topElements*/
    for (i = 0; i < topElements && temporal.length > i; i++) {
        content.append(top_SearchRow(temporal[i].name, temporal[i].amount));
    }
}

function top_ExhRow(text, value, upgrade, idEx, tours, retrieval, pqt) {

    url_exhibitor_path = url_exhibitor_path.replace("idexhibitor", idEx);

    row = jQuery('<tr/>', {class: ""});
    var td = jQuery('<td/>', {style: "text-align:left;"}).appendTo(row);
    jQuery('<a/>', {href: url_exhibitor_path, target: "_blank", text: text, style: "color:#green;"}).appendTo(td);

    var v = {};

    v = (upgrade > 0) ? {'class': 'top_upgrade', html: pqt} : {};
    jQuery('<td/>', v).appendTo(row);

    v = {'class': 'top_mytour center', html: tours};

    jQuery('<td/>', v).appendTo(row);

    if (retrieval > 0) {
        v = {'class': 'top_leadretrieval center', html: commasNumber(retrieval, true)};
    } else {
        v = {'class': 'top_leadretrieval center', html: 0};
    }
    jQuery('<td/>', v).appendTo(row);

    jQuery('<td/>', {"class": "top_clicks center", text: commasNumber(value, true)}).appendTo(row);
    url_exhibitor_path = url_exhibitor_path.replace(idEx, "idexhibitor");
    return row;
}

function top_SearchRow(text, value) {
    row = jQuery('<tr/>',
            {
            });

    field = jQuery('<td/>',
            {
                text: text
            }).appendTo(row);

    jQuery('<td/>',
            {
                "class": "center",
                text: value
            }).appendTo(row);
    return row;
}

function topVisitor(bData) {
    //visitantes unicos
    uniqueVisitors = $(".data_visitor_B");
    value = bData.uniqueVisitors == null ? '0.00' : bData.uniqueVisitors;
    uniqueVisitors.html(value);
    $(uniqueVisitors).text(commasNumber(value));
    //visitas
    visits = $(".data_visitor_A");
    value = bData.visits == null ? '0.00' : bData.visits;
    visits.html(value);
    $(visits).text(commasNumber(value));
    //unicos tours
    uniquetour = $(".data_visitor_C");
    value = bData.uniqueTour == null ? '0.00' : bData.uniqueTour;
    uniquetour.html(value);
    $(uniquetour).text(commasNumber(value));
    //regresos
    comeback = $(".data_visitor_D");
    value = bData.comeback == null ? '0.00' : bData.comeback;
    comeback.html(value);
    $(comeback).text(commasNumber(value));
    //registrados
    registered = $(".data_visitor_E");
    value = bData.registered == null ? '0.00' : bData.registered;
    registered.html(value);
    $(registered).text(commasNumber(value));
}

function initDataTables() {

    var options = {
        responsive: true,
        paging: true,
        "order": [[1, "desc"], [0, "asc"]],
        language: {
            "url": url_lang
        },
        aaSorting: [],
        bAutoWidth: false
    };

    $('#main_searchTab').DataTable(options);
    options.order = [[1, "desc"]];
    $('#main_categoriesTab').DataTable(options);

    options.order = [[3, "desc"]];//agregar opciÃ³n de ordenamiento usada en tabla de expositores
    $('#main_productsTab').DataTable(options);

    options.order = [[2, "desc"]];//agregar opciÃ³n de ordenamiento usada en tabla de expositores

    table = $('#main_exTab').DataTable(options);

    /*  BÚSQUEDA POR DESTACADOS EN TABLA DE EXPOSITORES    */
    //http://www.datatables.net/examples/api/multi_filter_select.html
    //http://www.datatables.net/forums/discussion/997/fnfilter-how-to-reset-all-filters-without-multiple-requests
    //https://www.datatables.net/forums/discussion/7257/api-fnfilter-filter-using-an-or-on-a-single-column

    $(document).on("click", ".switch", function () {
        var check = $(this).find('.flipswitch');
        filterExhibitorTable(check.attr('id'), check.attr('data-col'));
    });

}

function filterExhibitorTable(id, col) {
    switch (id) {
        case 'featured-filter':
            var re_search = $('#featured-filter').prop('checked') ? "Oro|Plata|Cobre" : "";
            break;

        case 'mytour-filter':
            var re_search = $('#mytour-filter').prop('checked') ? "1|2|3|4|5|6|7|8|9" : "";
            break;

        case 'retrieval-filter':
            var re_search = $('#retrieval-filter').prop('checked') ? "1|2|3|4|5|6|7|8|9" : "";
            break;

    }
    var oTable = $('#main_exTab').dataTable();
    oTable.fnFilter(re_search, col, true);
}


function AddExRow(name, amount, id, upgrade, mytour, leadret, package) {
    //Se genera el DataTable de Expositores
    url_exhibitor_path = url_exhibitor_path.replace("idexhibitor", id);
    var exhibitor_featured = (upgrade > 0 && package !== '') ? "<div style='display:inline-block;' class='upgrade'><span class='glyphicon glyphicon-star' style='font-size: 10px;'></span>" + package + "</div>" : "0";
    //var exhibitor_tour = (parseInt(mytour) > 0) ? "<div style='display:inline-block;' class='mytour'><span class='glyphicon glyphicon-pushpin' style='font-size: 10px;'></span>" + textosGenerales.msapi_myTour + ": " + mytour + "</div>" : "-";
    var exhibitor_tour = (parseInt(mytour) > 0) ? mytour : "0";
    var exhibitor_ret = (parseInt(leadret) > 0) ? commasNumber(leadret, true) : "0";

    var report_name = (upgrade < 0) ? name + " (" + general_text.lb_coexpositor + ")" : name;
    name = (upgrade < 0) ? "<div style='display:inline-block;' class='coex'><span class='glyphicon glyphicon-tag' style='font-size: 10px;'></span> " + general_text.lb_coexpositor + "</div>" + " " + name : name;

    var open = "<a href='" + url_exhibitor_path + "' target='_blank' ><i class='material-icons'>open_in_new</i></a>";
    //removido class tip de open(href) data-toggle='tooltip' data-placement='right' title='" + textosGenerales.lb_detalle_nuevotab + "' class='tip'
    var rowe = [name, exhibitor_featured, exhibitor_tour, exhibitor_ret, commasNumber(amount, true), open];
    //var rowe = [name, exhibitor_featured, exhibitor_tour, exhibitor_ret, amount, open];
    var rowex = [report_name, (package === null) ? '' : package, exhibitor_tour, exhibitor_ret, commasNumber(amount, true)];
    exarray.push(rowe);
    exarrayexport.push(rowex);
    url_exhibitor_path = url_exhibitor_path.replace(id, "idexhibitor");
}

function checkAjax() {

    if (counter === 5) {
//        $("#loader").slideUp('2000');
    }
}

function loadProductDir() {
//    from = $("#from").val();
//    to = $("#to").val();
//    idEdition = $("#idEdition").val();
//
//    $.ajax({
//        type: 'POST',
//        url: url_graph_get_products,
//        dataType: 'json',
//        data: {
//            ini: from,
//            end: to,
//            idEdition: idEdition
//        },
//        success: function (response) {
//
//            if (response.status) {
//                (jQuery.isEmptyObject(response.data)) ? clearProduct() : drawProductDetails(response.data);
//            } else {
//                clearProduct();
//            }
////            counter++;
////            checkAjax();
//        }
//    });
    clearProduct();

}

function drawProductDetails(prData) {
    clearTable("#main_productsTab");
    $(".tabe").css("display", "none");
    $("#button-products").remove();
//    productarray = [];
//
//    for (var i in prData) {
//        var amount = prData[i].cantidad;
//        var name = prData[i].Producto;
//        var ex = prData[i].NombreExpositor;
//        var ca = prData[i].Categoria;
//        var rows = [name, ca, ex, amount];
//        productarray.push(rows);
//    }
//    if (!jQuery.isEmptyObject(productarray)) {
//        AddSearchTerm('#main_productsTab', productarray);
//        $(".tabe").css("display", "block");
//        $(".export-products").css("display", "block");
//    }
}

function clearProduct() {
    $(".tabe").css("display", "none");
    $("#button-products").remove();
    clearTable("#main_productsTab");
}

function KioskButton(flag) {
    $(".details-kiosk").empty();
    url_kiosk = url_kiosk_path.replace("idedition", parseInt($("#idEdition").val()));
    if (flag) {
        var cont = $(".details-kiosk");
        var a = jQuery('<a/>', {"href": url_kiosk, "target": "_blank"}).appendTo(cont);
        var body = jQuery('<button/>', {"class": "btn btn-default kioskos", "style": "color: #05b652;"}).appendTo(a);
        jQuery('<span/>', {'class': 'glyphicon glyphicon-blackboard', "font-size": "20px;"}).appendTo(body);
        jQuery('<span/>', {"text": general_text.lb_kioskos}).appendTo(body);
    }

}

function initReport() {

    var container = $("#user-export");

    var option = [
        {
            class: ' export-summary',
            li: 'button-summary',
            tooltip: "Exporta Sumario",
            click: '',
            color: '',
            text: '<i class="material-icons">subject</i>'
        },
        {
            class: ' export-exhibitors',
            li: 'button-exhibitors',
            tooltip: "Exporta Lista de Expositores",
            click: '',
            color: 'green darken-3',
            text: '<i class="material-icons">person</i>'
        },
        
        {
            class: ' export-searches',
            li: 'button-searches',
            tooltip: "Exporta Lista de Búsquedas",
            click: '',
            color: 'red darken-3',
            text: '<i class="material-icons">search</i>'
        },
        {
            class: ' export-categories',
            li: 'button-categories',
            tooltip: "Exporta Lista de Categorias",
            click: '',
            color: 'orange darken-3',
            text: '<i class="material-icons">layers</i>'
        },
        {
            class: ' export-products',
            li: 'button-products',
            tooltip: "Exporta Lista de Productos",
            click: '',
            color: 'purple darken-3',
            text: '<i class="material-icons">person</i>'
        },
        {
            class: ' printchart',
            li: 'button-printchart',
            tooltip: "Imprimir Gráfica",
            color: '',
            text: '<i class="material-icons">insert_chart</i>',
            click: 'printChart("main_chart")'
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

    });

}
