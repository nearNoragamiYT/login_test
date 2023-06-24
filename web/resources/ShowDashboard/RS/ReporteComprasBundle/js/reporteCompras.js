var campo = "";
var idTotal = "";

$(document).ready(function () {
    validateFechasReporte();
    $('.datepicker').pickadate({
        format: 'yyyy-mm-dd',
        monthsFull: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthsShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        weekdaysFull: ['Domingo', 'Lunes', 'Martes', 'MiÃ©rcoles', 'Jueves', 'Viernes', 'SÃ¡bado'],
        weekdaysShort: ['Dom', 'Lun', 'Mar', 'MiÃ©', 'Jue', 'Vie', 'SÃ¡b'],
        selectMonths: true,
        selectYears: 100, // Puedes cambiarlo para mostrar mÃ¡s o menos aÃ±os
        today: 'Hoy',
        clear: 'Limpiar',
        close: 'Ok',
        labelMonthNext: 'Siguiente mes',
        labelMonthPrev: 'Mes anterior',
        labelMonthSelect: 'Selecciona un mes',
        labelYearSelect: 'Selecciona un aÃ±o',
    });

    $('select').material_select();
    $("#fechaInicial").prop("disabled", true);
    $("#horaInicial").prop("disabled", true);
    $("#horaFinal").prop("disabled", true);
    $("#fechaFinal").prop("disabled", true);
    $(".filter").hide();
    $(".nodo").hide();

    $('.timepicker').timepicker({
        timeFormat: 'HH:mm:ss',
        interval: 60,
        minTime: '6',
        maxTime: '10:00pm',
//        defaultTime: 'now',
        startTime: '6:00',
        dynamic: false,
        dropdown: true,
        scrollbar: true
    });

    table = $('#comprasTable').DataTable({
        responsive: true,
        paging: true,
        searching: false,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "NingÃºn dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Ãšltimo",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });

    table2 = $('#comprasTables').DataTable({
        responsive: true,
        paging: true,
        searching: false,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "NingÃºn dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Ãšltimo",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });

    table3 = $('#comprasTabless').DataTable({
        responsive: true,
        paging: true,
        searching: false,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "NingÃºn dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Ãšltimo",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });

//    $('.reset').click(function () {
//        $("#purchasesReport")[0].reset();
//    });


    jQuery.extend(jQuery.validator.messages, {
        required: general_text.sas_campoRequerido
    });

    $("#Compras").click(function () {
        $("#tablaAPC").slideToggle("slow");
    });
//    setInterval(compraStatus(), 1000);
});

$("#btn-total").change(function () {
    campo = $(this).val();
    switch (campo) {
        case "0":
            $(".filter").hide();
            $(".nodo").hide();
            $("#fechaInicial").prop('disabled', false);
            $("#horaInicial").prop('disabled', false);
            $("#horaFinal").prop('disabled', false);
            $("#fechaFinal").prop('disabled', false);
            break;
        case "1":
            $(".filter").hide();
            $(".nodo").hide();
            $("#fechaInicial").prop('disabled', false);
            $("#horaInicial").prop('disabled', false);
            $("#horaFinal").prop('disabled', false);
            $("#fechaFinal").prop('disabled', false);

            break;
        case "2":
            $(".filter").show();
            $(".nodo").show();
            $("#fechaInicial").prop('disabled', false);
            $("#horaInicial").prop('disabled', false);
            $("#horaFinal").prop('disabled', false);
            $("#fechaFinal").prop('disabled', false);
            break;
        case "3":
            $(".filter").hide();
            $(".nodo").hide();
            $("#fechaInicial").prop('disabled', false);
            $("#horaInicial").prop('disabled', false);
            $("#horaFinal").prop('disabled', false);
            $("#fechaFinal").prop('disabled', false);
            break;
    }
});

$("#btnFiltrar").on('click', function (e) {
    e.preventDefault();
    var form = $("#purchasesReport").serialize();
    updateReportNodo(form);
});

$("#clear").on('click', function () {

    switch (idTotal) {
        case "0":
            $('.close').hide();
            break;
        case "1":
            $('.close2').hide();
            break;
    }

});

function validateDates() {
    let fecha = true;
    let initialDate = $("#fechaInicial").val();
    let finalDate = $("#fechaFinal").val();

    let initDate = new Date(initialDate);//se obtiene fecha inicial
    let initMonth = (initDate.getMonth() + 1).toString();
    let initDay = (initDate.getDate() + 1).toString();

    if (initMonth.length <= 1) {
        initialMonth = "0" + initMonth;
    }
    if (initDay.length <= 1) {
        initDay = "0" + initDay;
    }

    let endDate = new Date(finalDate);//se obtiene fecha final
    let enMonth = (endDate.getMonth() + 1).toString();
    let enDay = (endDate.getDate() + 1).toString();

    if (enMonth.length <= 1) {
        enMonth = "0" + enMonth;
    }
    if (enDay.length <= 1) {
        enDay = "0" + enDay;
    }

    let startingDate = initDate.getFullYear() + "-" + initMonth + "-" + initDay;//se da formato a la fecha inicial
    let finishingDate = endDate.getFullYear() + "-" + enMonth + "-" + enDay;//se da formato a la fecha final

    if (finishingDate < startingDate) {
        show_toast("warning", "Fecha final menor a fecha de inicio");
        fecha = false;
    }

    if (startingDate > finishingDate) {
        show_toast("warning", "Fecha inicial mayor a fecha final");
        fecha = false;
    }

    return fecha;
}

function validateTime() {
    let hora = true;
    let initialHour = $("#fechaInicial").val() + " " + $("#horaInicial").val();
    let finalHour = $("#fechaFinal").val() + " " + $("#horaFinal").val();

    let startHour = new Date(initialHour);
    let initHour = startHour.getHours().toString();
    let initMinutes = startHour.getMinutes().toString();

    if (initHour.length <= 1) {
        initHour = "0" + initHour;
    }

    if (initMinutes.length <= 1) {
        initMinutes = "0" + initMinutes;
    }

    let endHour = new Date(finalHour);
    let enHour = endHour.getHours().toString();
    let enMinutes = endHour.getMinutes().toString();

    if (enHour.length <= 1) {
        enHour = "0" + enHour;
    }

    if (enMinutes.length <= 1) {
        enMinutes = "0" + enMinutes;
    }

    let startingHour = initHour + ":" + initMinutes;
    let endingHour = enHour + ":" + enMinutes;

    if (endingHour < startingHour) {
        show_toast("warning", "Hora final menor a hora inicial");
        hora = false;
    }

    if (startingHour > endingHour) {
        show_toast("warning", "Hora inicial mayor a hora final");
        hora = false;
    }

    if (startingHour == endingHour) {
        show_toast("warning", "horas de busqueda iguales");
        hora = false;
    }

    return hora;
}

$("#btnPurchases").on('click', function (e) {
    e.preventDefault();
    $("#purchasesReport").submit();
});

function validateFechasReporte() {
    $("#purchasesReport").validate({
        rules: {
            'fechaInicial': {
                required: true
            },
            'horaInicial': {
                required: true
            },
            'horaFinal': {
                required: true
            },
            'fechaFinal': {
                required: true
            }
        },
        ignore: ":hidden:not(select)",
        errorElement: "div",
        errorClass: "invalid",
        errorPlacement: function (error, element) {
            if ($(element).parent('div').find('i.material-icons').length > 0) {
                $(error).attr('icon', true);
            }
            var placement = $(element).data('error');
            if (placement) {
                $(placement).append(error);
            } else {
                if ($(element).attr('type') === "radio") {
                    element = $(element).parents('p');
                }
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            idTotal = $("#btn-total").val();

            switch (idTotal) {
                case "0":
                    var total = idTotal;
                    break;
                case "1":
                    var tarjeta = idTotal;
                    break;
                case "2":
                    var nodo = idTotal;
                    break;
                case "3":
                    var efectivo = idTotal;
                    break;
            }

            let dateFlag = validateDates(), timeFlag = validateTime();

            if (dateFlag && timeFlag && total) {
                var post = $("#" + form.id).serialize();
                generateReport(post);
            }
            if (dateFlag && timeFlag && tarjeta) {
                var post = $("#" + form.id).serialize();
                generateReportes(post);
            }
            if (dateFlag && timeFlag && nodo) {
                var post = $("#" + form.id).serialize();
                generateReportNodo(post);
            }
            if (dateFlag && timeFlag && efectivo) {
                var post = $("#" + form.id).serialize();
                generateReportes(post);
            }
        }
    });
}

function generateReport(post) {
    show_loader_wrapper();
    let data_ = new URLSearchParams(post);
    fetch(url_generateReport, {
        method: 'POST',
        body: data_
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    if (json.data.length != 0) {
                        $('.close').show();
                        $("#divTabla").removeClass('hide');
                        document.getElementById("btnExportacion").setAttribute('exportacion', 'excel');
                        table.clear().draw();//limpiamos la tabla 
                        json.data.forEach(function (visitante) {
                            table.row.add([
                                visitante.idCompra,
                                visitante.FechaPagado,
                                visitante.NombreCompleto,
                                visitante.Email,
                                visitante.ProductoES,
                                visitante.Total
                            ]).draw().node();
                        });
                        hide_loader_wrapper();
                    } else {
                        table.clear().draw();//limpiamos la tabla 
                        document.getElementById("divTabla").className = 'hide';
                        hide_loader_wrapper();
                        show_toast("warning", 'No se encontraron compras.');
                    }
                }
            });
}

function generateReportNodo(post) {
    show_loader_wrapper();
    let data_ = new URLSearchParams(post);
    fetch(url_reporteNodo, {
        method: 'POST',
        body: data_
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    if (json.data.length != 0) {
                        $('.close').show();
                        $("#divTabla3").removeClass('hide');
                        document.getElementById("btnExportacion3").setAttribute('exportacion', 'excel');
                        table3.clear().draw();//limpiamos la tabla 
                        json.data.forEach(function (visitante) {
                            table3.row.add([
                                visitante.idCompra,
                                visitante.idNodo,
                                visitante.FechaPagado,
                                visitante.NombreCompleto,
                                visitante.Email,
                                visitante.ProductoES,
                                visitante.Total
                            ]).draw().node();
                        });
                        hide_loader_wrapper();
                    } else {
                        table3.clear().draw();//limpiamos la tabla 
                        document.getElementById("divTabla3").className = 'hide';
                        hide_loader_wrapper();
                        show_toast("warning", 'No se encontraron compras.');
                    }
                }
            });
}

function updateReportNodo(form) {
    show_loader_wrapper();
    let data_ = new URLSearchParams(form);
    fetch(url_updateReporteNodo, {
        method: 'POST',
        body: data_
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    if (json.data.length != 0) {
                        $('.close').show();
                        $("#divTabla3").removeClass('hide');
                        document.getElementById("btnExportacion3").setAttribute('exportacion', 'excel');
                        table3.clear().draw();//limpiamos la tabla 
                        json.data.forEach(function (visitante) {
                            table3.row.add([
                                visitante.idCompra,
                                visitante.idNodo,
                                visitante.FechaPagado,
                                visitante.NombreCompleto,
                                visitante.Email,
                                visitante.ProductoES,
                                visitante.Total
                            ]).draw().node();
                        });
                        hide_loader_wrapper();
                    } else {
                        table3.clear().draw();//limpiamos la tabla 
                        document.getElementById("divTabla3").className = 'hide';
                        hide_loader_wrapper();
                        show_toast("warning", 'No se encontraron compras.');
                    }
                }
            });
}

function generateReportes(post) {
    show_loader_wrapper();
    let data_ = new URLSearchParams(post);
    fetch(url_reportes, {
        method: 'POST',
        body: data_
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    if (json.data.length != 0) {
                        $('.close').show();
                        $("#divTabla2").removeClass('hide');
                        document.getElementById("btnExportacion2").setAttribute('exportacion', 'excel');
                        table2.clear().draw();//limpiamos la tabla 
                        json.data.forEach(function (visitante) {

                            if (visitante.idFormaPago == 1) {
                                formaPago = "Tarjeta";
                            } else if (visitante.idFormaPago == 3) {
                                formaPago = "Efectivo";
                            }
                            table2.row.add([
                                visitante.idCompra,
                                formaPago,
                                visitante.FechaPagado,
                                visitante.NombreCompleto,
                                visitante.Email,
                                visitante.ProductoES,
                                visitante.Total
                            ]).draw().node();
                        });
                        hide_loader_wrapper();
                    } else {
                        table2.clear().draw();//limpiamos la tabla 
                        document.getElementById("divTabla2").className = 'hide';
                        hide_loader_wrapper();
                        show_toast("warning", 'No se encontraron compras.');
                    }
                }
            });
}

function compraStatus() {
    fetch(url_compraStatus, {
        method: 'POST',
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    if (json.data.length != 0) {
                        $("#comprasCompletas").html(json.data[0]['count']);
                        $("#comprasCanceladas").html(json.data[2]['count']);
                        $("#comprasPruebas").html(json.data[0]['count']);
                    }
                } else {

                }
            });
}