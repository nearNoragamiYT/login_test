jQuery.extend(jQuery.validator.messages, {
    required: general_text.sas_campoRequerido,
    email: general_text.sas_emailInvalido,
    number: general_text.sas_soloNumeros,
    digits: general_text.sas_soloDigitos
});

$(init);

function init() {
    validarEntrega();
    validarDevolucion();
    generarTotal();
    agregarQuitarEquipos();
    Split(['#entrega', '#devolucion'], {
        sizes: [50, 50],
        minSize: 300,
        elementStyle: function (dimension, size, gutterSize) {
            return {
                'flex-basis': 'calc(' + size + '% - ' + gutterSize + 'px)'
            };
        },
        gutterStyle: function (dimension, gutterSize) {
            return {
                'flex-basis': gutterSize + 'px'
            };
        }
    });
    var $input = $('.datepicker').pickadate({format: 'yyyy-mm-dd'});
    picker = $input.pickadate('picker');
    picker.set('min', false);

    $(".collapsible").collapsible();

    $("#entregar").on("click", function () {
        $("#Entrega").submit();
    });
    $("#recibir").on("click", function () {
        $("#Devolucion").submit();
    });
    $("#entrega-lectoras").addClass("active");

    $(".company-menu a").on("click", function () {
        show_loader_wrapper();
    });
}

function validarEntrega() {
    $("#Entrega").validate({
        errorClass: "invalid",
        validClass: "valid",
        errorElement: "div",
        errorPlacement: function (error, element) {
            $(element).parent().append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass(errorClass);
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass(errorClass);
        },
        success: function (success) {
            success.html('<i class="material-icons green-text right">check</i>');
        },
        submitHandler: function (form) {
            var equiposEntregados = {};
            var equipos = {};
            var detalleEquipos = {};
            $.each(empresaLectoras, function (id, lec) {
                var i = 1;
                detalleEquipos["ScannerDetalle"] = lec['ScannerDetalle'];
                detalleEquipos["ScannerTipo"] = lec['ScannerTipo'];
                detalleEquipos["PrecioScanner"] = lec['PrecioScanner'];
                detalleEquipos["MonedaScanner"] = lec['MonedaScanner'];
                detalleEquipos["CantidadScanners"] = lec['CantidadScanners'];
                $.each($(".equipos-entregados-" + id + ":checked"), function (e, ele) {
                    var idE = $(ele).val();
                    equipos[idE] = {
                        "Cantidad": $("#entrega-cantidad-" + idE).val(),
                        "Total": $("#entrega-total-" + idE).val(),
                        "EquipoAdicional": empresaLectoras[id]["Equipos"][idE]["EquipoAdicional"],
                        "Moneda": empresaLectoras[id]["Equipos"][idE]["Moneda"]
                    }
                    if ($(".equipos-entregados-" + id + ":checked").length == i) {
                        detalleEquipos["Equipos"] = equipos;
                        equiposEntregados[id] = detalleEquipos;
                        equipos = {};
                        detalleEquipos = {};
                    }
                    i++;
                });
                if (equiposEntregados[id] == undefined) {
                    detalleEquipos["Equipos"] = null;
                    equiposEntregados[id] = detalleEquipos;
                    equipos = {};
                    detalleEquipos = {};
                }
            });
            DetalleEntrega["EquiposEntregados"] = equiposEntregados;
            show_loader_wrapper();
            enviarDetalle($(form).serializeArray(), "Entrega");
        }
    });
}

function validarDevolucion() {
    $("#Devolucion").validate({
        errorClass: "invalid",
        validClass: "valid",
        errorElement: "div",
        errorPlacement: function (error, element) {
            $(element).parent().append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass(errorClass);
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass(errorClass);
        },
        success: function (success) {
            success.html('<i class="material-icons green-text right">check</i>');
        },
        submitHandler: function (form) {
            var equiposDevueltos = {};
            var equipos = {};
            var detalleEquipos = {};
            $.each(empresaLectoras, function (id, lec) {
                var i = 1;
                var detetalleScanner = {};
                var ScannersNoDevueltos = {};
                $.each($(".lectoras-devueltas-" + id), function (id, det) {
                    if ($(this).is(":checked")) {
                        detetalleScanner[$(this).val()] = lec["ScannerDetalle"][$(this).val()];
                    } else {
                        ScannersNoDevueltos[$(this).val()] = lec["ScannerDetalle"][$(this).val()];
                    }
                });
                detalleEquipos["ScannerDetalle"] = detetalleScanner;
                detalleEquipos["ScannersNoDevueltos"] = ScannersNoDevueltos;
                detalleEquipos["ScannerTipo"] = lec['ScannerTipo'];
                detalleEquipos["PrecioScanner"] = lec['PrecioScanner'];
                detalleEquipos["MonedaScanner"] = lec['MonedaScanner'];
                detalleEquipos["CantidadScanners"] = lec['CantidadScanners'];
                $.each($(".equipos-devueltos-" + id + ":checked"), function (e, ele) {
                    var idE = $(ele).val();
                    equipos[idE] = {
                        "Cantidad": $("#devuelto-cantidad-" + idE).val(),
                        "Total": $("#devuelto-total-" + idE).val(),
                        "EquipoAdicional": empresaLectoras[id]["Equipos"][idE]["EquipoAdicional"],
                        "Moneda": empresaLectoras[id]["Equipos"][idE]["Moneda"]
                    }
                    if ($(".equipos-devueltos-" + id + ":checked").length == i) {
                        detalleEquipos["Equipos"] = equipos;
                        equiposDevueltos[id] = detalleEquipos;
                        equipos = {};
                        detalleEquipos = {};
                    }
                    i++;
                });
                if (equiposDevueltos[id] == undefined) {
                    detalleEquipos["Equipos"] = null;
                    equiposDevueltos[id] = detalleEquipos;
                    equipos = {};
                    detalleEquipos = {};
                }
            });
            DetalleEntrega["EquiposDevueltos"] = equiposDevueltos;
            show_loader_wrapper();
            enviarDetalle($(form).serializeArray(), "Devolucion");
        }
    });
}

function enviarDetalle(form, status) {
    DetalleEntrega[status] = {};
    var detalle = {};
    $.each(form, function (i, val) {
        detalle[val.name] = val.value;
    });
    DetalleEntrega[status] = detalle;
    $.ajax({
        url: url_enviar_detalle,
        type: "POST",
        dataType: "json",
        data: {"DetalleEntregaScannerJSON": DetalleEntrega, "status": status},
        success: function (response) {
            hide_loader_wrapper();
            show_toast('success', general_text.sas_guardoExito);
            $("#mostrar-pdf").attr("src", "data:application/pdf;base64," + escape(response.pdf));
            $("#modal-mostrar-pdf").modal("open");
        },
        error: function (response, textStatus, error) {
            hide_loader_wrapper();
            show_modal_error(general_text.sas_errorInterno + "<br>" + response.responseText);
        }
    });
}

function  agregarQuitarEquipos() {
    $(".valign-wrapper input[type=checkbox]").on("change", function () {
        if ($(this).is(":checked")) {
            var id = $(this).val();
            var cls = $(this).attr('data-class');
            var precio = $("#" + cls + "-cantidad-" + id).attr('data-precio');
            $("#" + cls + "-cantidad-" + id).val(1).prop('disabled', false).next().addClass('active');
            $("#" + cls + "-total-" + id).val(precio).prop('disabled', false).next().addClass('active');
        } else {
            var id = $(this).val();
            var cls = $(this).attr('data-class');
            $("#" + cls + "-cantidad-" + id).val("0").prop('disabled', true);
            $("#" + cls + "-total-" + id).val("0").prop('disabled', true);
        }
    });
}

function generarTotal() {
    $(".cantidad").on("change", function () {
        var id = $(this).attr("data-id");
        var precio = parseInt($(this).attr('data-precio'));
        var cls = $(this).attr('data-class');
        var cantidad = parseInt($(this).val());
        $("#" + cls + "-" + id).val(precio * cantidad);
    });
}