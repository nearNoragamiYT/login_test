var main_div = document.getElementById('graficas-swipe');
var table, encuesta;
var accion = 0;
var color = [];
var logic_operator = ['El operador OR, exige que se cumpla cualquiera de las condiciones para que el registro se incluya en el conjunto de resultados',
    'El operador AND, exige que se cumplan todas las condiciones para que el registro se incluya en el conjunto de resultados'];
color[1] = ['#1F9BDE', '#A8A9AD', '#3F5364', '#231F20', '#4285F4', '#615B4D'];
$(init);

$(document).ready(function () {
    google.charts.load('current', {packages: ['corechart', 'bar']});
    $('ul.tabs').tabs();
    $('select').material_select();
    $('.btn-collapse').sideNav({
        menuWidth: 300,
        edge: 'right',
        closeOnClick: true,
    });

    $('.count-filter').on('DOMSubtreeModified', function () {
        if ($(this).html() == 0)
            $(this).hide();
        else
            $(this).show();
    });

    $('.datos-generales').change(function () {
        var badge = $(this).closest('.collapsible-body').siblings().children('span');
        if ($(this).val() != '') {
            if ($(this).attr('edit-id') == 0) {
                badge.html(parseInt(badge.html()) + 1);
                $('#sumary-badge').html(parseInt($('#sumary-badge').html()) + 1);
                $(this).attr('edit-id', 1)
            }
        } else {
            if ($(this).attr('edit-id') == 1) {
                badge.html(parseInt(badge.html()) - 1);
                $('#sumary-badge').html(parseInt($('#sumary-badge').html()) - 1);
                $(this).attr('edit-id', 0)
            }
        }
    });

    $('.filter-chk').change(function () {
        var badge = $(this).closest('.collapsible-body').siblings().children('span');
        if ($(this).prop('checked')) {
            badge.html(parseInt(badge.html()) + 1);
            $('#sumary-badge').html(parseInt($('#sumary-badge').html()) + 1);
        } else {
            badge.html(parseInt(badge.html()) - 1);
            $('#sumary-badge').html(parseInt($('#sumary-badge').html()) - 1);
        }
    });

    $('#logic').change(function () {
        var badge = $(this).closest('.collapsible-body').siblings().children('span');
        if ($(this).prop('checked')){
            badge.html('AND');
            $('#txt-operator').hide().html(logic_operator[1]).fadeIn('slow');
        }
        else{
            badge.html('OR');
            $('#txt-operator').hide().html(logic_operator[0]).fadeIn('slow');
        }
    });

    $("#graphics").click(function () {
        if (accion == 0) {
            main_div.innerHTML = '';
            main_div.className = 'card';
            show_loader_top();
            $('#tbl-demografic_processing').show();
            accion = 1;
            $.ajax({
                type: "post",
                url: url_get_stats,
                dataType: 'json',
                success: function (result) {
                    if (result['status']) {
                        encuesta = result['data'];
                        google.charts.setOnLoadCallback(drawChartPerfil);
                        return;
                    }
                    show_toast("danger", 'Error');
                    $('#tbl-demografic_processing').hide();
                },
                error: function () {
                    show_toast("danger", 'Error');
                    $('#tbl-demografic_processing').hide();
                }
            });
        }
        $('footer').hide();
        $('#menu-slide').show();
        $('.btn-collapse').sideNav('show');
    });

    $("#tab").click(function () {
        $('footer').show();
        $('#menu-slide').hide();
    });

    $(".clear-filters").click(function () {
        $('.count-filter').html(0);
        $('.datos-generales').attr('edit-id', 0);
        $('#filters')[0].reset();
        $('.filter-chk').prop('checked', false);
//        $(':checkbox, :radio').prop('checked', false);
    });

// Graficas Responsivas

//    $(window).bind('resize', function (e) {
//        window.resizeEvt;
//        $(window).resize(function () {
//            clearTimeout(window.resizeEvt);
//            if (accion == 1) {
//                show_loader_wrapper();
//                window.resizeEvt = setTimeout(function () {
//                    main_div.innerHTML = '';                    
//                    drawChartPerfil();
//                }, 250);
//            }
//        });
//    });
});

function init() {
    show_loader_wrapper();
    drawFilters();
    $('.collapsible').collapsible();
    $('.tooltipped').tooltip({delay: 50});
    $(".custom-filters-summary").hide();
    $('#div-dinamic-table').hide();
}

function drawFilters() {
    var cover = document.getElementById('cover-table');

    //main div
    var custom_filters_form = document.createElement("div");
    custom_filters_form.className = "custom-filters-form";

    //wrapper
    var fieldset = document.createElement("fieldset");
    fieldset.className = "col s12 custom-filters";
    custom_filters_form.appendChild(fieldset);

    //div Titulo y Cierre
    var div_titulo = document.createElement("div");
    div_titulo.className = "row";
    fieldset.appendChild(div_titulo);

    var label_titulo = document.createElement("h5");
    label_titulo.className = "col s10";
    label_titulo.innerHTML = 'Filtros Perfil Visitante';
    div_titulo.appendChild(label_titulo);

    var icon = document.createElement("i");
    icon.className = "material-icons left";
    icon.innerHTML = "search";
    label_titulo.appendChild(icon);

    var div_close = document.createElement("div");
    div_close.className = 'col s2';
    div_titulo.appendChild(div_close);

    var div_close_filters = document.createElement("a");
    div_close_filters.className = "btn-flat right close-custom truncate";
    div_close_filters.innerHTML = "Cerrar";
    div_close_filters.onclick = function () {
        $(".custom-filters").hide();
        $(".custom-filters-summary").show();
    };
    div_close.appendChild(div_close_filters);

//    var icon = document.createElement("i");
//    icon.className = "material-icons right";
//    icon.innerHTML = "clear";
//    div_close_filters.appendChild(icon);

    //Menu de Filtros
    var list = document.createElement("ul");
    list.className = "collapsible";
    list.setAttribute('data_collapsible', 'accordion');
    list.style.height = (window.innerHeight * .60) + "px";
    list.style.overflow = 'auto';
    fieldset.appendChild(list);

    //Funcion Logica
    var filter_node = document.createElement("li");
    list.appendChild(filter_node);

    var div_head = document.createElement("div");
    div_head.className = 'collapsible-header';
    div_head.innerHTML = 'Operador Logico de la Consulta';
    var badge = document.createElement('span');
    badge.className = 'new badge badge-logic';
    badge.setAttribute("data-badge-caption", "");
    badge.innerHTML = 'OR';
    div_head.prepend(badge);
    filter_node.appendChild(div_head);

    var div_body = document.createElement("div");
    div_body.className = 'collapsible-body';
    filter_node.appendChild(div_body);

    var div_row = document.createElement("div");
    div_row.className = 'row valign-wrapper';
    div_row.style.padding = "10px 0px";
    div_body.appendChild(div_row);

    var div_col = document.createElement("div");
    div_col.className = 'switch col s2 center-align';
    div_row.appendChild(div_col);

    var label_filter = document.createElement("label");
    label_filter.innerHTML = 'OR<input id="logic" type="checkbox"><span class="lever"></span>AND';
    div_col.appendChild(label_filter);
    
    var div_col = document.createElement("div");
    div_col.className = 'col s10';
    div_row.appendChild(div_col);
    
    var span_logic = document.createElement("span");
    span_logic.id = 'txt-operator';
    span_logic.innerHTML = 'El operador OR, exige que se cumpla cualquiera de las condiciones para que el registro se incluya en el conjunto de resultados';
    div_col.appendChild(span_logic);

    // Filtros Generales
    var filter_node = document.createElement("li");
    list.appendChild(filter_node);

    var div_head = document.createElement("div");
    div_head.className = 'collapsible-header';
    div_head.innerHTML = texto_perfil.sas_datosGenerales;
    var badge = document.createElement('span');
    badge.className = 'new badge count-filter';
    badge.setAttribute("data-badge-caption", "");
    badge.innerHTML = 0;
    div_head.prepend(badge);
    filter_node.appendChild(div_head);

    var div_body = document.createElement("div");
    div_body.className = 'collapsible-body';
    filter_node.appendChild(div_body);

    var div_row = document.createElement("div");
    div_row.className = 'row';
    div_body.appendChild(div_row);

    var form_filters = document.createElement("form");
    form_filters.id = "filters";
    div_row.appendChild(form_filters);

    $.each(filtros, function (key, value) {
        if ('is_select' in value.filter_options && value.filter_options.is_select) {
            var div_col = document.createElement("div");
            div_col.className = 'input-field col s5';
            form_filters.appendChild(div_col);

            var filter = document.createElement("select");
            filter.id = key;
            filter.name = key;
            filter.className = 'validate datos-generales';
            filter.setAttribute("edit-id", 0);
            div_col.appendChild(filter);

            var option = document.createElement("option");
            option.value = '';
            option.innerHTML = 'Selecciona una opción';
//            option.setAttribute("disabled", '');
//            option.setAttribute("selected", '');
            filter.appendChild(option);

            $.each(value.filter_options.values, function (key, value) {
                var option = document.createElement("option");
                option.value = key;
                option.innerHTML = value;
                filter.appendChild(option);
            });

            var label_filter = document.createElement("label");
            label_filter.for = key;
            label_filter.innerHTML = value.text;
            div_col.appendChild(label_filter);

            var div_col = document.createElement("div");
            div_col.className = 'col s1';
            form_filters.appendChild(div_col);

            return;
        }

        var div_col = document.createElement("div");
        div_col.className = 'input-field col s5';
        form_filters.appendChild(div_col);

        var filter = document.createElement("input");
        filter.id = key;
        filter.name = key;
        filter.type = "text";
        filter.className = 'validate datos-generales';
        filter.setAttribute("edit-id", 0);
        div_col.appendChild(filter);

        var label_filter = document.createElement("label");
        label_filter.for = key;
        label_filter.innerHTML = value.text;
        div_col.appendChild(label_filter);

        var div_col = document.createElement("div");
        div_col.className = 'col s1';
        form_filters.appendChild(div_col);
    });

    // Filtros Encuesta
    $.each(encuesta.encuesta, function (key, value) {
        var encuesta_node = document.createElement("li");
        list.appendChild(encuesta_node);

        var div_head = document.createElement("div");
        div_head.className = 'collapsible-header';
        div_head.innerHTML = value['Pregunta' + lang.toUpperCase()];
        var badge = document.createElement('span');
        badge.className = 'new badge count-filter';
        badge.setAttribute("data-badge-caption", "");
        badge.innerHTML = 0;
        div_head.prepend(badge);
        encuesta_node.appendChild(div_head);

        var div_body = document.createElement("div");
        div_body.className = 'collapsible-body';
        encuesta_node.appendChild(div_body);

        var div_row = document.createElement("div");
        div_row.className = 'row';
        div_body.appendChild(div_row);

        switch (value.idPreguntaTipo) {
            case 1:
                // check btn
                var form_check = document.createElement("form");
                form_check.className = "check";
                div_row.appendChild(form_check);

                $.each(value.Respuestas, function (i, content) {
                    var div_col = document.createElement("div");
                    div_col.className = 'input-field col s6';
                    form_check.appendChild(div_col);

                    var div_check = document.createElement("p");
                    div_col.appendChild(div_check);

                    var check = document.createElement("input");
                    check.type = 'checkbox';
                    check.className = 'filled-in filter-chk';
                    check.name = 'Respuesta' + i;
                    check.id = 'Respuesta' + i;
                    check.value = 1;
                    div_check.appendChild(check);

                    var label_check = document.createElement("label");
                    label_check.setAttribute('for', 'Respuesta' + i);
                    label_check.innerHTML = content['Respuesta' + lang.toUpperCase()];
                    div_check.appendChild(label_check);
                });

                break;

            case 2:
                // radio btn
                var form_check = document.createElement("form");
                form_check.className = "check";
                div_row.appendChild(form_check);

                $.each(value.Respuestas, function (i, content) {
                    var div_col = document.createElement("div");
                    div_col.className = 'input-field col s6';
                    form_check.appendChild(div_col);

                    var div_check = document.createElement("p");
                    div_col.appendChild(div_check);

                    var check = document.createElement("input");
                    check.type = 'checkbox';
                    check.className = 'filled-in filter-chk';
                    check.name = 'Respuesta' + i;
                    check.id = 'Respuesta' + i;
                    check.value = 1;
                    div_check.appendChild(check);

                    var label_check = document.createElement("label");
                    label_check.setAttribute('for', 'Respuesta' + i);
                    label_check.innerHTML = content['Respuesta' + lang.toUpperCase()];
                    div_check.appendChild(label_check);
                });

                break;

//            case 2:
//                // radio btn  
//                var form_radio = document.createElement("form");
//                form_radio.className = "radio";
//                div_row.appendChild(form_radio);
//
//                $.each(value.Respuestas, function (i, content) {
//                    var div_col = document.createElement("div");
//                    div_col.className = 'input-field col s6';
//                    form_radio.appendChild(div_col);
//
//                    var div_radio = document.createElement("p");
//                    div_col.appendChild(div_radio);
//
//                    var radio = document.createElement("input");
//                    radio.type = 'radio';
//                    radio.name = 'Pregunta' + content.idPregunta;
//                    radio.id = 'Respuesta' + i;
//                    radio.value = 1;
//                    div_radio.appendChild(radio);
//
//                    var label_radio = document.createElement("label");
//                    label_radio.setAttribute('for', 'Respuesta' + i);
//                    label_radio.innerHTML = content['Respuesta' + lang.toUpperCase()];
//                    div_radio.appendChild(label_radio);
//                });
//
//                break;

        }
    });

    // Botones
    var div_button = document.createElement('div');
    div_button.className = 'col s12';
    fieldset.appendChild(div_button);

    var button_filter = document.createElement('button');
    button_filter.className = 'btn generate-table teal right';
    button_filter.id = "aply-filters";
    button_filter.innerHTML = texto_perfil.sas_aplicarFiltros;
    button_filter.onclick = function () {
        $('ul.tabs').tabs('select_tab', 'tab_id');
        $(".custom-filters").hide();
        $("#leyend").hide();
        initTable();
        accion = 0;
        $(".custom-filters-summary").show();
        $('#div-dinamic-table').show();
        $('ul.tabs').tabs('select_tab', 'tab_id');
    };
    div_button.appendChild(button_filter);

    var button_clean = document.createElement('a');
    button_clean.className = 'waves-effect btn-flat clear-filters right';
    button_clean.innerHTML = texto_perfil.sas_limpiarFiltros;
    div_button.appendChild(button_clean);

    // div Sumario
    var div_summary = document.createElement("div");
    div_summary.className = "row custom-filters-summary";
    custom_filters_form.appendChild(div_summary);

    var btn = document.createElement("a");
    btn.className = "col s12 btn-flat left-align show-custom-filters";
    btn.innerHTML = 'Filtros Perfil Visitante';
    btn.onclick = function () {
        $(".custom-filters").show();
        $(".custom-filters-summary").hide();
    };
    var badge = document.createElement('span');
    badge.className = 'new badge count-filter';
    badge.setAttribute("data-badge-caption", "Filtros Activos");
    badge.id = "sumary-badge";
    badge.innerHTML = 0;
    btn.prepend(badge);

    var icon = document.createElement("i");
    icon.className = "material-icons left";
    icon.innerHTML = "search";
    btn.appendChild(icon);
    div_summary.appendChild(btn);

    cover.insertBefore(custom_filters_form, cover.childNodes[0]);
    hide_loader_wrapper();
}

function initTable() {
    table = $('#tbl-demografic').dataTable({
        pageLength: 100,
        processing: true,
        searching: false,
        serverSide: true,
        bDestroy: true,
        language: {
            url: url_lang
        },
        ajax: {
            url: url_get_dt,
            type: "POST",
            data: {
                "columns": {},
                "logic": $('#logic').prop('checked') ? 'AND' : 'OR',
                "general_filter": $('#filters').serializeArray(),
                "radio_filter": $('.radio').serializeArray(),
                "check_filter": $('.check').serializeArray()
            },
        },
        scrollY: (window.innerHeight * .44) + "px",
        scrollX: true,
        scrollCollapse: true,
        fixedHeader: true,
        fixedColumns: {leftColumns: 2}
    });
}

function drawChartPerfil() {
    var nav = document.getElementById('slide');
    nav.innerHTML = ''
    var li = document.createElement('li');
    nav.appendChild(li);
    var a = document.createElement('a');
    a.innerHTML = 'Perfil Vistante';
    a.className = 'waves-effect';
    a.setAttribute("href", "#");
    li.appendChild(a);
    var icon = document.createElement("i");
    icon.className = "material-icons icon-user";
    icon.innerHTML = "face";
    a.appendChild(icon);
    var li = document.createElement('li');
    nav.appendChild(li);
    var divider = document.createElement('div');
    divider.className = 'divider';
    li.appendChild(divider);

    $.each(encuesta, function (key, value) {
        var text = value.PreguntaES.split(" ");
        var temp_text = ''
        if (text.length > 5) {
            size = parseInt(text.length / 6) + 1;
            size = parseInt(text.length / size);
            var j = size - 1;
            for (var i = 0; i < text.length; i++) {
                temp_text += text[i] + ' ';
                if (i == j) {
                    temp_text += '<br>'
                    j += size;
                }
            }
        } else
            temp_text = value.PreguntaES;

        var li = document.createElement('li');
        var a = document.createElement('a');
        a.innerHTML = value.PreguntaES;
        a.className = 'waves-effect truncate tooltipped';
        a.setAttribute("href", "#");
        a.setAttribute("data-position", "left");
        a.setAttribute("data-tooltip", temp_text);
        a.onclick = function () {
            $("html").animate({
                scrollTop: ($("#card-" + key).offset().top - 62)
            }, 1000);
        };
        li.appendChild(a);
        nav.appendChild(li);

        if (Object.keys(value.Respuestas).length < 7) {
            var data = new google.visualization.DataTable();
            var div_row = document.createElement('div');
            div_row.className = 'col l6 m12 s12';
            div_row.id = 'card-' + key;
            main_div.appendChild(div_row);
            var div_card = document.createElement('div');
            div_card.className = 'row card medium';
            div_row.appendChild(div_card);
            if (value['idpadre'] != undefined) {
                var div_subtitulo = document.createElement('div');
                div_subtitulo.className = 'left-align col s10 subtitle';
                div_subtitulo.innerHTML = encuesta[value.idpadre][String('Pregunta' + lang.toUpperCase())];
                div_card.appendChild(div_subtitulo);
                var div_subtotal = document.createElement('div');
                div_subtotal.className = 'right-align col s2 subtitle';
                div_subtotal.innerHTML = 'Total: ' + encuesta[value.idpadre]['stat'];
                div_card.appendChild(div_subtotal);
            }
            var div_titulo = document.createElement('div');
            div_titulo.className = 'left-align col s10 title';
            div_titulo.innerHTML = value.PreguntaES;
            div_card.appendChild(div_titulo);
            var div_total = document.createElement('div');
            div_total.className = 'right-align col s2';
            div_total.innerHTML = 'Total: ' + value.stat;
            div_card.appendChild(div_total);
            var div_grafic = document.createElement('div');
            div_grafic.className = 'container-grafic col s12';
            div_grafic.id = 'grafic-' + key;
            div_card.appendChild(div_grafic);

            data.addColumn('string', '');
            data.addColumn('number', '');
            $.each(value.Respuestas, function (key, content) {
                var txt = content['Respuesta' + lang.toUpperCase()] + ': ' + content.stat
                data.addRow([txt, parseInt(content.stat)]);
            });
            var options = {
                colors: color[1],
                pieSliceText: 'percentage',
                pieSliceTextStyle: {fontSize: 11},
                chartArea: {width: '95%'},
                pieHole: 0.4,
                legend: {
                    position: 'labeled',
                    textStyle: {
                        color: 'black',
                        fontSize: 11
                    }},
            };
            var chart = new google.visualization.PieChart(document.getElementById('grafic-' + key));
            chart.draw(data, options);

        } else {
            var data = new google.visualization.DataTable();
            var div_row = document.createElement('div');
            div_row.className = 'col l6 m12 s12';
            div_row.id = 'card-' + key;
            main_div.appendChild(div_row);
            var div_card = document.createElement('div');
            div_card.className = 'row card medium';
            div_row.appendChild(div_card);
            if (value['idpadre'] != undefined) {
                var div_subtitulo = document.createElement('div');
                div_subtitulo.className = 'left-align col s10 subtitle';
                div_subtitulo.innerHTML = encuesta[value.idpadre][String('Pregunta' + lang.toUpperCase())];
                div_card.appendChild(div_subtitulo);
                var div_subtotal = document.createElement('div');
                div_subtotal.className = 'right-align col s2 subtitle';
                div_subtotal.innerHTML = 'Total: ' + encuesta[value.idpadre]['stat'];
                div_card.appendChild(div_subtotal);
            }
            var div_titulo = document.createElement('div');
            div_titulo.className = 'left-align col s10 title';
            div_titulo.innerHTML = value.PreguntaES;
            div_card.appendChild(div_titulo);
            var div_total = document.createElement('div');
            div_total.className = 'right-align col s2';
            div_total.innerHTML = 'Total: ' + value.stat;
            div_card.appendChild(div_total);
            var div_grafic = document.createElement('div');
            div_grafic.className = 'container-grafic col s12';
            div_grafic.id = 'grafic-' + key;
            div_card.appendChild(div_grafic);

            data.addColumn('string', '');
            data.addColumn('number', '');
            data.addColumn({type: 'string', role: 'annotation'});
            $.each(value.Respuestas, function (key, content) {
                var percent = (content.stat / value.stat) * 100;
                percent = percent.toFixed(2);
                data.addRow([content['Respuesta' + lang.toUpperCase()], parseInt(content.stat), content.stat + " : " + percent + "%"]);
            });
            var options = {
                colors: color[1],
                chartArea: {width: '46%', height: '95%', left: '40%', top: '3%'},
                legend: {position: 'none'},
                annotations: {
                    textStyle: {
                        color: 'black',
                        fontSize: 11,
                    },
                    alwaysOutside: true
                },
                vAxis: {
                    textStyle: {
                        fontSize: 11,
                    }
                }
            };
            var chart = new google.visualization.BarChart(document.getElementById('grafic-' + key));
            chart.draw(data, options);
        }
    });
    $(document).ready(function () {
        $('.tooltipped').tooltip({delay: 50, html: true});
    });

    $('#tbl-demografic_processing').hide();
    hide_loader_top();
}