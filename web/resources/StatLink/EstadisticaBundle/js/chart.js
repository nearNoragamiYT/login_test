var selectedItem;
var clicks = 0;
var color = [];
color[1] = ['#1F9BDE', '#A8A9AD', '#3F5364', '#231F20', '#4285F4', '#615B4D'];
var exportArray = [];
$(document).ready(function () {
    $(".tip").tooltip();
//    $('.show-chart').on('click', function () {
//        $("#chart").slideToggle();
//        $(".printchart").slideToggle();//mostrar botón de imprimir gráfica solo cuando esta esté visible
//        //$('.show-chart a').toggleClass("chart-option-selected");
//        $('.show-chart span').toggleClass("glyphicon-eye-open glyphicon-eye-close");
//
//        var title_open = textosGenerales.show_chart_help;
//        var title_close = textosGenerales.hide_chart_help;
//
//        if ($(".show-chart span").hasClass('glyphicon-eye-open')) {
//            $(".show-chart").attr('title', title_open);
//            $(".show-chart").attr('data-original-title', title_open);
//        }
//        else
//        {
//            $(".show-chart").attr('title', title_close);
//            $(".show-chart").attr('data-original-title', title_close);
//        }
//
//    });
});

function drawLineChart() {
    var chart;
    var data;
    var width;
    data = new google.visualization.DataTable();
    data.addColumn('string', '');

    if (tipo === "historicodia" || tipo === "historicoacumulado") {
        data.addColumn('number', $("#year1").val());
        data.addColumn('number', $("#year2").val());
        if ($("#year3").length) {
            data.addColumn('number', $("#year3").val());
        }
    } else {
        data.addColumn('number', leyendaB);
        data.addColumn('number', acumulado);
    }



    chart = new google.charts.Line(document.getElementById('chart'));

    width = window.innerWidth > 992 ? $("#chartRef").width() * 0.98 : $("#chartRef").width() * 0.85;

    var options = getLineChartOptions(acumulado, leyendaB, leyendaR, width);

    $.each(data_graph, function (i, row) {
        var d;
        var a;
        var h;
        switch (tipo) {
            case "dia":
                d = parseInt(row.Preregistro);
                a = parseInt(row.PreregistroAcumulado);
                h = row.Dia;
                data.addRow([h, d, a]);
                break;
            case "semana":
                d = parseInt(row.Preregistro);
                a = parseInt(row.PreregistroAcumulado);
                h = (i + 1).toString();
                data.addRow([h, d, a]);
                break;
            case "historicodia":

                if ($("#year3").length) {
                    var a1 = parseInt(row.PreregistroA1);
                    var a2 = parseInt(row.PreregistroA2);
                    var a3 = parseInt(row.PreregistroA3);
                    h = "" + row.DiaFaltante;
                    data.addRow([h, a1, a2, a3]);
                } else {
                    var a1 = parseInt(row.PreregistroA1);
                    var a2 = parseInt(row.PreregistroA2);
                    h = "" + row.DiaFaltante;
                    data.addRow([h, a1, a2]);
                }

                break;
            case "historicoacumulado":

                if ($("#year3").length) {
                    var a1 = parseInt(row.AcumuladoA1);
                    var a2 = parseInt(row.AcumuladoA2);
                    var a3 = parseInt(row.AcumuladoA3);
                    h = "" + row.DiaFaltante;
                    data.addRow([h, a1, a2, a3]);
                } else {
                    var a1 = parseInt(row.AcumuladoA1);
                    var a2 = parseInt(row.AcumuladoA2);
                    h = "" + row.DiaFaltante;
                    data.addRow([h, a1, a2]);
                }

                break;
        }

    });
    chart.draw(data, options);
}

function drawGeoChart() {
    var chart;
    var data;
    var width;
    data = new google.visualization.DataTable();
    data.addColumn('string', '');
    data.addColumn('number', preregistrados);

    chart = new google.visualization.GeoChart(document.getElementById('geochart'));

    width = ($(".container").width() * 0.98);

    var options = {
        width: width,
        height: 400,
        fontSize: 12,
        datalessRegion: '#BBCCAA',
        keepAspectRatio: true,
        region: tipoMapa,
        resolution: resolucion,
        colors: ['#e0ecf4', '#3cba54'],
        magnifyingGlass: {
            enable: true,
            zoomFactor: 7.5
        },
        hAxis: {
            minValue: 0,
            slantedTextAngle: 60,
            title: leyendaB,
            gridlines: {
                color: '#AADDFC'
            }
        },
        vAxis: {
            title: preregistrados
        },
        chartArea: {},
        backgroundColor: {
            stroke: '#FFF',
            fill: '#FFF',
            strokeWidth: 2
        }

    };

    $.each(data_graph, function (i, row) {
        var a;
        var h;
        switch (tipo) {
            case "estado":
                a = parseInt(row.Preregistro);
                h = row.Estado;
                break;
            case "pais":
                a = parseInt(row.Preregistro);
                h = row.Pais;
                break;
        }
        data.addRow([h, a]);

    });
    chart.draw(data, options);

}

function drawPieChart() {
    var chart;
    var data;
    var width;
    data = new google.visualization.DataTable();
    data.addColumn('string', '');
    data.addColumn('number', '');

    chart = new google.visualization.PieChart(document.getElementById('chart'));

    width = ($(".panel-body").width() * 0.98);

    var options = {
        title: leyendaB,
        width: width,
        height: 400,
        fontSize: 12,
        chartArea: {width: "100%", height: "65%"},
        pieSliceText: 'value',
        backgroundColor: {
            //stroke: '#CCC',
            stroke: '#FFF',
            fill: '#FFF',
            strokeWidth: 2
        },
        legend: {
            position: 'top',
            textStyle: {
                fontSize: 12
            }
        },
        tooltip: {
            text: 'percentage'
        }
    };

    $.each(data_graph, function (i, row) {
        var a;
        var n;

        switch (tipo) {
            case "cupon":
                a = row.Cupon + ' (' + row.Preregistro + ')';
                n = parseInt(row.Preregistro);
                data.addRow([a, n]);
                break;
            case "actividad":
                a = row.nombre + ' (' + row.total + ')';
                n = parseInt(row.total);
                data.addRow([a, n]);
                break;
        }

    });

    chart.draw(data, options);
}

function getLineChartOptions(acumulado, leyendaB, leyendaR, width) {
    var o;
    if (tipo === "historicodia" || tipo === "historicoacumulado") {

        o = {
            width: width,
            height: 400,
            fontSize: 12,
            pointSize: 3,
            colors: ['red', '#5A78C4', '#9C9696'],
            series: {
                0: {
                    targetAxisIndex: 0
                },
                1: {
                    targetAxisIndex: 0
                },
                2: {
                    targetAxisIndex: 0
                },
                3: {
                    targetAxisIndex: 0
                }
            },
            hAxis: {
                title: leyendaB,
                slantedTextAngle: 60

            },
            vAxes: {
                0: {
                    title: leyendaR
                }
            },
            backgroundColor: {
                //stroke: '#CCC',
                stroke: '#FFF',
                fill: '#FFF',
                strokeWidth: 2
            },
            legend: {
                position: 'top',
                textStyle: {
                    fontSize: 12
                }
            }
            /*, explorer: {//OPCIONES DE ZOOM http://jsfiddle.net/duJA8/
             maxZoomOut: 2,
             keepInBounds: true
             }*/

        };
    } else {
        o = {
            width: width,
            height: 400,
            colors: ['#db3236', '#4885ed'],
            dataOpacity: 0.3,
            series: {
                0: {axis: 'Left'},
                1: {axis: 'Right'},
            },
            axes: {
                x: {
                    0: {label: horizontal_label}
                },
                y: {
                    Left: {label: left_label},
                    Right: {label: right_label}
                }
            },
        };
    }
    return o;
}

function drawColumnChart() {
    var chart;
    var data;
    var width;
    data = new google.visualization.DataTable();
    data.addColumn('string', '');
    if (tipo === "ecosistema") {
        data.addColumn('number', columna1_label);
        data.addColumn('number', columna2_label);
    } else
    {
        data.addColumn('number', columna1_label);
    }

    chart = new google.charts.Bar(document.getElementById('chart'));
    width = window.innerWidth > 992 ? $("#chartRef").width() * 0.98 : $("#chartRef").width() * 0.85;


    var options = {
        width: width,
        height: 400,
        colors: ['#db3236'],
        dataOpacity: 0.3,
        rx: 80,
        series: {
            0: {axis: 'Right'}
        },
        axes: {
            x: {
                0: {label: leyendaB}
            },
            y: {
                Right: {label: leyendaR}
            }
        },
        bar: {groupWidth: "40%"},
//        width: width,
//        height: 400,
//        fontSize: 12,
//        bar: {groupWidth: 200},
//        hAxis: {
//            title: leyendaB,
//            gridlines: {
//                color: '#AADDFC'
//            },
//            slantedTextAngle: 60
//
//
//        },
//        vAxis: {
//            viewWindow: {min: 0},
//            title: leyendaR
//        },
//        animation: {
//            duration: 1000,
//            easing: 'inAndOut'
//        },
//        chartArea: {},
//        backgroundColor: {
//            //stroke: '#CCC',
//            stroke: '#FFF',
//            fill: '#FFF',
//            strokeWidth: 2
//        },
//        legend: {
//            position: 'top',
//            textStyle: {
//                fontSize: 12
//            }
//        }
    };

//ANIMACIÓN
//var view = new google.visualization.DataView(data);
//view.setColumns([0, {
//    type: 'number',
//    label: data.getColumnLabel(1),
//    calc: function () {return 0;}
//}]);

    $.each(data_graph, function (i, row) {
        switch (tipo) {
            case "ecosistema":
                var integer = parseInt(row.Capacidad);
                var integer2 = parseInt(row.Registrados);
                var capacidad = (isNaN(integer)) ? 0 : integer;
                var registrado = (isNaN(integer2)) ? 0 : integer2;
                data.addRow([row.Nombre, capacidad, registrado]);
                break;
            case "perfilse":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.VisitanteTipo + " (" + integer + ")", a]);
                break;
            case "perfil":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.Perfil + " (" + integer + ")", a]);
                break;
            case "tipopago":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.Tipo_Registro, a]);
                break;
            case "campania":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.Descripcion, a]);
                options.bar['groupWidth'] = "90%";
                break;
            case "idioma":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.IdiomaES, a]);
                break;
            case "asistencia":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.Dia, a]);
        }

    });

    //ANIMACIÓN
//    var animate = google.visualization.events.addListener(chart, 'ready', function () {
//    // remove the listener so this doesn't repeat ad infinitum
//    google.visualization.events.removeListener(animate);
//    // draw the chart using the real data, triggering the animation
//    chart.draw(data, options);
//});
//   chart.draw(view, options);
    chart.draw(data, google.charts.Bar.convertOptions(options));
}

function drawColumnChartX() {
    var chart;
    var data;
    var width;
    data = new google.visualization.DataTable();
    data.addColumn('string', '');
    data.addColumn('number', columna1_label);
    data.addColumn({type: 'string', role: 'annotation'});

    chart = new google.charts.Bar(document.getElementById('chart'));
    width = window.innerWidth > 992 ? $("#chartRef").width() * 0.98 : $("#chartRef").width() * 0.85;


    var options = {
        width: width,
        height: 400,
        colors: ['#db3236'],
        dataOpacity: 0.3,
        rx: 80,
        series: {
            0: {axis: 'Right'}
        },
        axes: {
            x: {
                0: {label: leyendaB}
            },
            y: {
                Right: {label: leyendaR}
            }
        },
        bar: {groupWidth: "40%"},

    };

    $.each(data_graph, function (i, row) {
        switch (tipo) {
            case "ecosistema":
                var integer = parseInt(row.Capacidad);
                var integer2 = parseInt(row.Registrados);
                var capacidad = (isNaN(integer)) ? 0 : integer;
                var registrado = (isNaN(integer2)) ? 0 : integer2;
                data.addRow([row.Nombre, capacidad, registrado]);
                break;
            case "perfilse":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.VisitanteTipo + " (" + integer + ")", a]);
                break;
            case "perfil":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.Perfil + " (" + integer + ")", a]);
                break;
            case "tipopago":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.Tipo_Registro, a]);
                break;
            case "campania":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;

                data.addRow([row.Descripcion, a, row.Cupon]);
                break;
            case "idioma":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.IdiomaES, a]);
                break;
            case "asistencia":
                var integer = parseInt(row.Preregistro);
                var a = (isNaN(integer)) ? 0 : integer;
                data.addRow([row.Dia, a]);
        }

    });

    chart.draw(data, google.charts.Bar.convertOptions(options));

    google.visualization.events.addListener(chart, 'select', function () {
        clicks++;
        if (chart.getSelection()[0] !== undefined) {
            selectedItem = chart.getSelection()[0];
        }

        setTimeout(function () {

            if (clicks >= 2) {
                var topping = data.getValue(selectedItem.row, 2);
                topping = {where: topping};
                $('#graficas').html('');
                $('#loader-wrapper').show();                
                $.ajax({
                    type: "post",
                    url: url_get_stats,
                    data: topping,
                    dataType: 'json',
                    success: function (result) {
                        if (result['status']) {
                            encuesta = result['data'];
                            google.charts.setOnLoadCallback(drawChartPerfil);
                            $('#modal_perfil').modal('open');
                            $('#loader-wrapper').hide();
                            return;
                        }
                        show_toast("danger", 'Error');
                        $('#loader-wrapper').hide();
                    },
                    error: function () {
                        show_toast("danger", 'Error');
                        $('#loader-wrapper').hide();
                    }
                });
            }
            clicks = 0;
        }, 250);
    });
}

function initDataTableDetail() {
    table = $('.table-detail').DataTable({
        responsive: false,
        paging: false,
        language: {
            "url": datatable_lang
        },
        bAutoWidth: false,
        "bSort": false,
        "info": false
    });
}

function fillExportArray(data) {
    var cont = 1;
    $.each(data, function (key, val) {
        var row = [];
        row.push(cont);
        $.each(val, function (key2, val2) {
            val2 = (val2 === null) ? '-' : val2;
            row.push(val2);
        });
        exportArray.push(row);
        cont++;
    });

}

function initReport() {

    var container = $(".user-body");

    var option = [{class: 'export-records', text: "Exportacion", click: ''},
        {class: 'printchart', text: "Imprimir" + " " + "Grafica", click: 'printChart("chart")'}];

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
    $("#loader").slideUp('2000');
}

function drawChartPerfil() {
    var main_div = document.getElementById('graficas');
//    var nav = document.getElementById('slide');
//    nav.innerHTML = '';
//    var li = document.createElement('li');
//    nav.appendChild(li);
//    var a = document.createElement('a');
//    a.innerHTML = 'Perfil Vistante';
//    a.className = 'waves-effect';
//    a.setAttribute("href", "#");
//    li.appendChild(a);
//    var icon = document.createElement("i");
//    icon.className = "material-icons icon-user";
//    icon.innerHTML = "face";
//    a.appendChild(icon);
//    var li = document.createElement('li');
//    nav.appendChild(li);
//    var divider = document.createElement('div');
//    divider.className = 'divider';
//    li.appendChild(divider);

    $.each(encuesta, function (key, value) {
//        var text = value.PreguntaES.split(" ");
//        var temp_text = ''
//        if (text.length > 5) {
//            size = parseInt(text.length / 6) + 1;
//            size = parseInt(text.length / size);
//            var j = size - 1;
//            for (var i = 0; i < text.length; i++) {
//                temp_text += text[i] + ' ';
//                if (i == j) {
//                    temp_text += '<br>'
//                    j += size;
//                }
//            }
//        } else
//            temp_text = value.PreguntaES;

//        var li = document.createElement('li');
//        var a = document.createElement('a');
//        a.innerHTML = value.PreguntaES;
//        a.className = 'waves-effect truncate tooltipped';
//        a.setAttribute("href", "#");
//        a.setAttribute("data-position", "left");
//        a.setAttribute("data-tooltip", temp_text);
//        a.onclick = function () {
//            $("html").animate({
//                scrollTop: ($("#card-" + key).offset().top - 62)
//            }, 1000);
//        };
//        li.appendChild(a);
//        nav.appendChild(li);

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
//    $(document).ready(function () {
//        $('.tooltipped').tooltip({delay: 50, html: true});
//    });

//    $('#loader-wrapper').hide();
}
