//var enjoyhint_instance = null;
//var enjoyhint_script_data = [];
//
//function initTooltips() {
//    $('body').tooltip({
//        selector: '.tip',
//        html: true
//    });
//}
//
//function initRangeBox() {
//    $(".current-range").click(function () {
//        //$(".range-box").slideDown();
//        $(".range-box").show();
//        return false;
//    });
//
//    $(".close-box").click(function () {
//        //$(".range-box").slideUp();
//        $(".range-box").hide();
//        return false;
//    });
//}

//function initDatePickers() {
//    $('#from').datepicker({
//        format: 'yyyy/mm/dd',
//        autoclose: true,
//        language: $("#lang").val(),
//        startDate: '2013-01-01',
//        endDate: getFormatedDate()
//    });
//
//    $('#to').datepicker({
//        format: 'yyyy/mm/dd',
//        autoclose: true,
//        language: $("#lang").val(),
//        startDate: '2013-01-01',
//        endDate: getFormatedDate()
//    });
//}

//function initFlipSwitch() {
//    $(".flipswitch").bootstrapSwitch();
//    $(".bootstrap-switch-handle-on").text(textosGenerales.si);
//    $(".bootstrap-switch-handle-off").text(textosGenerales.no);
//}

//function dialogConfirm(param) {
//    var defaults = {
//        "title": "",
//        "content": "",
//        "callback": ""
//    };
//    var settings = $.extend({}, defaults, param);
//    $("#confirm-modal").find(".modal-title").html(settings.title);
//    $("#confirm-modal").find("#text-confirm").html(settings.content);
//    $("#confirm-modal").find(".btn-submit").text(textosGenerales.aceptar).unbind("click").click(settings.callback);
//    $("#confirm-modal").modal("show");
//}

function toUpper(text) {
    return text.toUpperCase();

}

function toLowerT(text) {
    return text.toLowerCase();
}

function textExists(test, word) {
    var exist = false;
        if (parseInt(test.indexOf(word)) > (-1)) {
            exist = true;
        }    
    return exist;
}

//http://www.hybridplanet.co.in/tutorial/javascript/how-to-create-csv-or-excel-file-from-json-via-javascript
//http://jsfiddle.net/hybrid13i/JXrwM/
function JSONToCSVConvertor(JSONData, ReportTitle, ShowLabel) {
    //If JSONData is not an object then JSON.parse will parse the JSON string in an Object
    var arrData = typeof JSONData != 'object' ? JSON.parse(JSONData) : JSONData;

    var CSV = '';
    //Set Report title in first row or line

    CSV += ReportTitle + '\r\n\n';

    //This condition will generate the Label/Header
    if (ShowLabel) {
        var row = "";

        //This loop will extract the label from 1st index of on array
        for (var index in arrData[0]) {

            //Now convert each value to string and comma-seprated
            row += index + ',';
        }

        row = row.slice(0, -1);

        //append Label row with line break
        CSV += row + '\r\n';
    }

    //1st loop is to extract each row
    for (var i = 0; i < arrData.length; i++) {
        var row = "";

        //2nd loop will extract each column and convert it in string comma-seprated
        for (var index in arrData[i]) {
            row += '"' + arrData[i][index] + '",';
        }

        row.slice(0, row.length - 1);

        //add a line break after each row
        CSV += row + '\r\n';
    }

    if (CSV == '') {
        alert("Invalid data");
        return;
    }

    //Generate a file name
    var fileName = "";
    //this will remove the blank-spaces from the title and replace it with an underscore
    fileName += ReportTitle.replace(/ /g, "_");

    //Initialize file format you want csv or xls
    var uri = 'data:text/csv;charset=utf-8,' + escape(CSV);

    // Now the little tricky part.
    // you can use either>> window.open(uri);
    // but this will not work in some browsers
    // or you will not get the correct file extension    

    //this trick will generate a temp <a /> tag
    var link = document.createElement("a");
    link.href = uri;

    //set the visibility hidden so it will not effect on your web-layout
    link.style = "visibility:hidden";
    link.download = fileName + ".csv";

    //this part will append the anchor tag and remove it after automatic click
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function getFormatedDate() {
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1;//January is 0! 
    var yyyy = today.getFullYear();
    if (dd < 10) {
        dd = '0' + dd;
    }
    if (mm < 10) {
        mm = '0' + mm;
    }
    return yyyy + '/' + mm + '/' + dd;
}

function printChart(q_chart) {    
    var w = window.open();
    if (!w) {
        alert('Please enable popups in order to print!');
    }
    else {

        w.document.write(document.getElementById(q_chart).innerHTML);
        w.print();
        w.close();
    }
}

function printDetail(q_chart) {
    var w = window.open();
    if (!w) {
        alert('Please enable popups in order to print!');
    }
    else {
        $(".question").hide();
        $("#clickDetails").addClass("table-center-click");
        w.document.write(document.getElementById(q_chart).innerHTML);
        w.print();
        w.close();
        $(".question").show();
        $("#clickDetails").removeClass("table-center-click");
    }
}


//formato tipo oración (primera letra de cada palabra en mayuscula)
//http://stackoverflow.com/questions/7467381/capitalize-the-first-letter-of-every-word
function capitalize(str) {
    str = str.toLowerCase();
    return str.replace(/([^ -])([^ -]*)/gi, function (v, v1, v2) {
        return v1.toUpperCase() + v2;
    });
}

function VisitorsTabVisible() {
    if (parseInt($("#estructura").val()) === 2 || parseInt($("#estructura").val()) === 4) {
        $("#op2").css("display", "none");
    }
}

function setPercent(obj, value, limit) {
    if (value >= limit) {
        $("#" + obj).html(limit);
        return;
    }
    value = value + 0.1;
    $("#" + obj).html(value);
    setTimeout(function (o, v, l) {
        return function () {
            setPercent(o, v, l);
        }
    }(obj, value, limit), 1000);
}

function getPercent(obj) {
    c = $("#" + obj);
    total = parseInt(c.attr("data-total"));
    c.html(((total * 100) / totalAmount).toFixed(2) + "%");
}

function empty(val) {
    if (val === undefined || val === null || val === "")
        return true;
}

//This will sort your array
function SortByTours(a, b) {
    var aT = parseInt(a.tours);
    var bT = parseInt(b.tours);
    return ((bT < aT) ? -1 : ((bT > aT) ? 1 : 0));
}

//This will sort your array
function SortByClicks(a, b) {
    var aT = parseInt(a.amount);
    var bT = parseInt(b.amount);
    return ((bT < aT) ? -1 : ((bT > aT) ? 1 : 0));
}

function clearTable(table) {
    var table = $(table).DataTable();

    table
            .clear()
            .draw();
}

function activeTab(tab) {
    $(".nav-tabs li").removeClass("active");
    $(tab).addClass("active");
}

function noDataExport() {
    obj = null;
    obj = $(this).parent().parent();

    dialogConfirm({
        "title": textosValidacion.lb_atencion,
        "content": textosValidacion.sin_datos_exportar,
        "callback": function () {
            $("#confirm-modal").modal("hide");
        }
    });
}
function performExportExPa(source, title, columns, order, featured=false, tour=false, retrieval=false) {
    if (jQuery.isEmptyObject(source)) {//si no hay datos que exportar -> mostrar error
        noDataExport(textosValidacion.sin_datos_exportar);
        return;
    }

    title = title + " -" + getFormatedDate();

    exportExPaXLScolumns(source, title, columns, order,featured,tour,retrieval);
}
//order con valor menor a 0=no ordenar
function exportExPaXLScolumns(data, title_report, columns, order_report, ft, tour, ret) {
    feature = ft === true ? ["Oro","Plata","Cobre"] : ["Oro", "Plata", "Cobre","Básico"];
    mytour = tour === true ? 1 : 0;
    retrieval = ret === true ? "1" :"0";
    title_report = title_report.replace(/ /g, "_");
    title_report = title_report.replace(/\//g, "-");

    var inpt, i;
    var d = document.createElement("div");
    var json_data = '{';

    var hide_columns = [];

    var total = data.length, total_values;
    for (i = 0; i < total; i++) {
        total_values = data[i].length;
        if (feature.indexOf(data[i][1]) >= 0 && data[i][2] >= mytour && data[i][3] >= retrieval) {
            json_data += i > 0 ? "," : "";
            json_data += '"' + i + '":';
            for (var j = 0; j < total_values; j++) {

                if (data[i][j] === '[object Object]') {
                    data[i].remove(j);
                    hide_columns.push(j);
                    continue;
                }
                if (i === 8 && j === 16) {
                    var s = 0;
                }
                if (data[i][j] !== "") {
                    data[i][j] = String(data[i][j]).replace(/"/g, '');
                }

            }
            json_data += JSON.stringify(data[i]);
        }
    }

    json_data += "}";
    inpt = document.createElement("input");
    inpt.setAttribute("type", "hidden");
    inpt.setAttribute("name", "data");
    inpt.value = json_data;
    d.appendChild(inpt);

    for (i = 0; columns.length > i; i++) {

        inpt = document.createElement("input");
        inpt.setAttribute("type", "hidden");
        inpt.setAttribute("name", "columns[" + i + "]");
        inpt.value = columns[i];
        d.appendChild(inpt);
    }

    inpt = document.createElement("input");
    inpt.setAttribute("type", "hidden");
    inpt.setAttribute("name", "title_report");
    inpt.value = title_report;
    d.appendChild(inpt);
    inpt = document.createElement("input");
    inpt.setAttribute("type", "hidden");
    inpt.setAttribute("name", "order_report");
    inpt.value = order_report;
    d.appendChild(inpt);

    $("#form-export").append(d).submit();
    $(d).remove();
}
//Normal
function performExport(source, title, columns, order) {
    if (jQuery.isEmptyObject(source)) {//si no hay datos que exportar -> mostrar error
        noDataExport(textosValidacion.sin_datos_exportar);
        return;
    }

    title = title + " -" + getFormatedDate();

    exportXLScolumns(source, title, columns, order);
}

//order con valor menor a 0=no ordenar
function exportXLScolumns(data, title_report, columns, order_report) {

    title_report = title_report.replace(/ /g, "_");
    title_report = title_report.replace(/\//g, "-");

    var inpt, i;
    var d = document.createElement("div");
    var json_data = '{';

    var hide_columns = [];

    var total = data.length, total_values;
    for (i = 0; i < total; i++) {
        total_values = data[i].length;
        (i > 0) && (json_data += ",");
        json_data += '"' + i + '":';
        for (var j = 0; j < total_values; j++) {

            if (data[i][j] === '[object Object]') {
                data[i].remove(j);
                hide_columns.push(j);
                continue;
            }
            if (i === 8 && j === 16) {
                var s = 0;
            }
            if (data[i][j] !== "") {
                data[i][j] = String(data[i][j]).replace(/"/g, '');
            }

        }
        json_data += JSON.stringify(data[i]);
    }

    json_data += "}";
    inpt = document.createElement("input");
    inpt.setAttribute("type", "hidden");
    inpt.setAttribute("name", "data");
    inpt.value = json_data;
    d.appendChild(inpt);

    for (i = 0; columns.length > i; i++) {

        inpt = document.createElement("input");
        inpt.setAttribute("type", "hidden");
        inpt.setAttribute("name", "columns[" + i + "]");
        inpt.value = columns[i];
        d.appendChild(inpt);
    }

    inpt = document.createElement("input");
    inpt.setAttribute("type", "hidden");
    inpt.setAttribute("name", "title_report");
    inpt.value = title_report;
    d.appendChild(inpt);
    inpt = document.createElement("input");
    inpt.setAttribute("type", "hidden");
    inpt.setAttribute("name", "order_report");
    inpt.value = order_report;
    d.appendChild(inpt);

    $("#form-export").append(d).submit();
    $(d).remove();
}
//
//function disableForm(form_name) {
//    $("#" + form_name).find('input:button').prop('disabled', true);
//    $("#" + form_name).find('button').prop('disabled', true);
//    $("#" + form_name).find('button:button').prop('disabled', true);
//    $('#' + form_name).css("cursor", "not-allowed");
//}
//
//function enableForm(form_name) {
//    $('#' + form_name).find('input:button').prop('disabled', false);
//    $('#' + form_name).find('button').prop('disabled', false);
//    $('#' + form_name).find('button:button').prop('disabled', false);
//    $('#' + form_name).css("cursor", "auto");
//}
//
//
//function initHelp(event) {
//    enjoyhint_instance = new EnjoyHint(event);
//    $(".enjoyhint_skip_btn").html(textosGenerales.bt_cerrar + " <span class='glyphicon glyphicon-remove-sign'></span>");
//    $(".enjoyhint_next_btn").html(textosGenerales.lb_siguiente + " <span class='glyphicon glyphicon-forward'></span>");
//    //    event puede ser:
//    //    {
//    //  onStart:function(){
//    //    //do something
//    //  }
//    //}
//    //también existe la función de onEnd
//    //https://github.com/xbsoftware/enjoyhint
//}
//
//function showHelp() {
//    enjoyhint_instance.setScript(enjoyhint_script_data);
//    enjoyhint_instance.runScript();
//}
//
function AddSearchTerm(table, array) {

    if (!jQuery.isEmptyObject(array)) {
        var addnew = $(table).dataTable().fnAddData(array);//api datatables sin plugin
    }
}
//
function emptyrow(columns) {
    var row = [];
    for (i = 0; i < columns; i++) {
        row.push("");
    }
    return row;
}
//
//var sidebar = $(".sidebar-menu");
//
//function drawModules() {
//    clearModules();
//    if (current.hasOwnProperty("mod") && !jQuery.isEmptyObject(current['mod'])) {
//        for (var i in current['mod']) {
//            var module = current['mod'][i];
//            addModule(module['idModulo'], module['Descripcion' + lang.toUpperCase()], module['Ruta'], module['Icono'], 'module_' + module['idModulo']);
//            if (module.hasOwnProperty("sub") && !jQuery.isEmptyObject(module['sub'])) {
//                for (var j in module['sub']) {
//                    addSubModule(module['sub'][j]['idModulo'], module['sub'][j]['Descripcion' + lang.toUpperCase()], module['sub'][j]['Ruta'], module['sub'][j]['Icono'], '.module_' + module['idModulo']);
//                }
//            }
//            else {
//                jQuery('<div/>',
//                        {
//                            "class": "no_assigned",
//                            "html": (textosGenerales.lb_no_modulos).toUpperCase(),
//                            style: "text-align:center;"
//                        }
//                ).appendTo('.module_' + module['idModulo']);
//            }
//        }
//    } else {
//        jQuery('<div/>',
//                {
//                    "class": "no_assigned",
//                    "html": (textosGenerales.lb_no_modulos).toUpperCase(),
//                    style: "text-align:center;"
//                }
//        ).appendTo(sidebar);
//    }
//}
//
//function clearModules() {
//    sidebar.empty();
//
//    jQuery('<li/>',
//            {
//                "class": "header",
//                "html": (textosGenerales.titulo_panel).toUpperCase()
//            }
//    ).appendTo(sidebar);
//}
//
//function addModule(id, name, route, icon, moduleclass) {
//
//    var tree = jQuery('<li/>',
//            {
//                "class": "treeview "
//            }
//    ).appendTo(sidebar);
//
//    var h = jQuery('<a/>',
//            {
//                "href": "#"
//            }
//    ).appendTo(tree);
//    jQuery('<i/>',
//            {
//                "class": "glyphicon " + icon
//            }
//    ).appendTo(h);
//    jQuery('<span/>',
//            {
//                "text": name
//            }
//    ).appendTo(h);
//    jQuery('<i/>',
//            {
//                "class": "fa fa-angle-left pull-right"
//            }
//    ).appendTo(h);
//    var ul = jQuery('<ul/>',
//            {
//                "class": "treeview-menu " + moduleclass
//            }
//    ).appendTo(sidebar);
//
//    if (parseInt(id) === parseInt($("#m_actual").val())) {
//        $(tree).trigger("click");
//        $(tree).addClass('active');
//        $(ul).addClass('menu-open');
//
//    }
//
//}
//
//function addSubModule(id, name, route, icon, moduleclass) {
//
//    var final_route = (route !== '' && route !== '#') ? routes[route] + "/" + $("#actual").val() : '#';
//    //var final_route = ((parseInt(id) !== parseInt($("#actual").val()))) ? route : '#';
//    var status = (parseInt(id) === parseInt($("#sm_actual").val())) ? 'active' : '';
//
//    var tree = jQuery('<li/>',
//            {
//                class: "" + status
//            }
//    ).appendTo(moduleclass);
//
//    var h = jQuery('<a/>',
//            {
//                "href": final_route
//            }
//    ).appendTo(tree);
//    jQuery('<i/>',
//            {
//                "class": "glyphicon " + icon
//            }
//    ).appendTo(h);
//    jQuery('<span/>',
//            {
//                "text": name
//            }
//    ).appendTo(h);
//
//}
//
//$("#idEdition").change(function () {
//    if (parseInt($(this).val()) !== parseInt($("#actual").val())) {
//        $("#loader").slideDown('2000');
//        var redirect = welcome_path + "/" + $(this).val();
//        document.location.href = redirect;
//    }
//});

function performExportByKey(fields, source, exp, title) {

    exp = [];
    if (jQuery.isEmptyObject(source)) {//si no hay datos que exportar -> mostrar error
        noDataExport(textosValidacion.sin_datos_exportar);
        return;
    }

    for (var i in source) {

        aux = [];
        $.each(fields, function (index, value) {
            aux[$("." + value).text()] = source[i][value];
        });
        exp.push(aux);
    }
    exportXLSByKey(exp, title, -1);
}

function exportXLSByKey(data, title_report, order_report) {

    title_report = title_report.replace(/ /g, "_");

    columns = Object.keys(data[0]);
    var inpt, i;
    var d = document.createElement("div");
    var hide_columns = [],
            json_data = '{';
    var st;
    for (var i in data) {
        st = '{';
        total_values = data[i].length;
        (i > 0) && (json_data += ",");
        json_data += '"' + i + '":';

        $.each(columns, function (index, value) {
            st += '"' + value + '":"' + data[i][value] + '",';
        });
        st = st.replace(/,(?=[^,]*$)/, '');//quitar última coma de la cadena
        st += '}';
        json_data += st;
    }

    json_data += "}";

    inpt = document.createElement("input");
    inpt.setAttribute("type", "hidden");
    inpt.setAttribute("name", "data");
    inpt.value = json_data;
    d.appendChild(inpt);

    for (i = 0; columns.length > i; i++) {

        inpt = document.createElement("input");
        inpt.setAttribute("type", "hidden");
        inpt.setAttribute("name", "columns[" + i + "]");
        inpt.value = columns[i];
        d.appendChild(inpt);
    }

    inpt = document.createElement("input");
    inpt.setAttribute("type", "hidden");
    inpt.setAttribute("name", "title_report");
    inpt.value = title_report;
    d.appendChild(inpt);
    inpt = document.createElement("input");
    inpt.setAttribute("type", "hidden");
    inpt.setAttribute("name", "order_report");
    inpt.value = order_report;
    d.appendChild(inpt);


    $("#form-export").append(d).submit();
    $(d).remove();
}
