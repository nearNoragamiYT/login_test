var listEntidades;
$(document).ready(function () {
    initEmpresaLectoras();
});

/** init **/
function initEmpresaLectoras() {
    $("#solicitud-lectoras").attr("class", "active");
    /*CalculateTotalAmount();*/
    $("select#idPais").change(getEstados);
    $("select#idEstado").change(function () {
        $("#Estado").val($("#idEstado option:selected").text());
    });
    validateDetallePagoLectoraForm();
    
    /* Entidades Fiscales */
    validateEntity();
    var listEntidades = "";
    if ($("#ListaEmpresaEntidaFiscal").val() !== "") {
        var entidades_fiscales = $("#ListaEmpresaEntidaFiscal").val().split(",");
        if (entidades_fiscales[0] !== $("#idEmpresaEntidadFiscalSolicitud").val() && $("#idEmpresaEntidadFiscalSolicitud").val() !== "") {
            listEntidades = $("#idEmpresaEntidadFiscalSolicitud").val() + "," + $("#ListaEmpresaEntidaFiscal").val();
        } else {
            listEntidades = $("#ListaEmpresaEntidaFiscal").val();
        }
    } else {
        listEntidades = $("#idEmpresaEntidadFiscalSolicitud").val();
    }
    createEntityList(listEntidades);
    $("#ListaEmpresaEntidaFiscal").val(listEntidades);

    $(document).on("click", ".tab", function () {
        $(".delete-entity").hide();
        var idEntidad = $(this).attr("id").replace("li-", "");
        if (idEntidad === $("#idEmpresaEntidadFiscalSolicitud").val()) {
            $("#btn-edit-entidad").hide();
        } else {
            $("#li-" + idEntidad + " .delete-entity").show();
            $("#btn-edit-entidad").show();
            $("#dropdown-entidad").attr('idEntidad', idEntidad);
        }
    });

    $("#save-entity").on("click", function () {
        $("#frm-entidad").submit();
    });

    $("#save-entidad-fiscal").on("click", function () {
        listEntidades = $("#ListaEmpresaEntidaFiscal").val();
        saveDetallePagoEntidad(listEntidades);
    });

    $(document).on("click", ".delete-entity", function () {
        $("#mdl-confirm-delete-entidad").modal({dismissible: false}).modal("open");
        var idEntidad = $(this).parent().attr("id").replace("tab-", "");
        $("#delete-entidad").attr("id-entidad", idEntidad);
    });

    $("#delete-entidad").on("click", function () {
        $("#mdl-confirm-delete-entidad").modal("close");
        listEntidades = $("#ListaEmpresaEntidaFiscal").val();
        var id = $(this).attr("id-entidad");
        var splitist = listEntidades.split(",");
        if (splitist.length === 1) {
            listEntidades = listEntidades.replace(id, "");
        } else if ($("#idEmpresaEntidadFiscalSolicitud").val() === "" && splitist[0] === id) {
            listEntidades = listEntidades.replace(id + ",", "");
        } else {
            listEntidades = listEntidades.replace("," + id, "");
        }
        $("#ListaEmpresaEntidaFiscal").val(listEntidades);
        createEntityList(listEntidades);
        $(".entity-panel").addClass('panel-highlight');
        $("#nota-entidad").text("*" + section_text.sas_notaEntidadFiscal);
        $("#save-entidad-fiscal").show();
    });

    $(document).on("click", ".new-entidad", function () {
        var i = parseInt($(this).attr("id-record"));
        if (i == 0) {
            clearEntityForm();
            $("#mdl-entidad").modal("open");
        } else {
            if ($("#li-" + i).length === 1) {
                show_alert("warning", "No se puede repetir Entidades Fiscales");
                return;
            }
            var listEntidades = "";
            if ($("#ListaEmpresaEntidaFiscal").val() === "") {
                listEntidades = i;
            } else {
                listEntidades = $("#ListaEmpresaEntidaFiscal").val() + "," + i;
            }
            $("#ListaEmpresaEntidaFiscal").val(listEntidades);
            createEntityList(listEntidades);
            $(".entity-panel").addClass('panel-highlight');
            $("#nota-entidad").text("*" + section_text.sas_notaEntidadFiscal);
            $("#save-entidad-fiscal").show();
            $('ul.tabs').tabs('select_tab', 'div-entidad-fiscal-' + i);
            $("#dropdown-entidad").attr('idEntidad', i);
            $("#btn-edit-entidad").show();
        }
    });

    $(document).on("click", ".edit-entidad", function () {
        var i = parseInt($(this).attr("id-record"));
        var idEntidad = $("#dropdown-entidad").attr('idEntidad');
        if ($("#li-" + i).length === 1) {
            show_alert("warning", "No se puede repetir Entidades Fiscales");
            return;
        }
        setEntidad(idEntidad, i);
    });
    /* Entidades Fiscales FIN */

    $("#save-record").on("click", function () {
        $("#save-detalle-pago-json").submit();
    });

    $("#change-fecha").on("click", changeFechaPago);

    $("#status-pago").on("change", function () {
        if ($(this).val() != "") {
            $("#mdl-confirm-status .modal-content").find('p').find('span').text($("#status-pago option:selected").text());
            $("#mdl-confirm-status").modal({dismissible: false}).modal("open");
        }
    });

    $(".modal-close").on("click", function () {
        $('#status-pago').val($("#status-pago").attr("idStatus"));
        $('#forma-pago').val($("#forma-pago").attr("idFormaPago"));
    });
    $("#change-status").on("click", changeStatus);
    ////////////Cambiar Forma Pago/////////////////
    $("#forma-pago").on("change", function () {
        if ($(this).val() != "") {
            $("#mdl-confirm-forma .modal-content").find('p').find('span').text($("#forma-pago option:selected").text());
            $("#mdl-confirm-forma").modal({dismissible: false}).modal("open");
        }
    });

    $(document).on("change", ".change-precio", function () {
        $("#precio-" + $(this).attr("id-record")).removeAttr("id");
        $(this).parent().attr("id", "precio-" + $(this).attr("id-record"));
        $(".cantidades").trigger("change");
    });

    $("#change-forma").on("click", changeFormaPago);
    ///////////Cambiar fecha de pago/////////////////
    if (lang == 'es') {
        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: 1,
            monthsFull: ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
            monthsShort: ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'],
            weekdaysFull: ['domingo', 'lunes', 'martes', 'miÃƒÆ’Ã‚Â©rcoles', 'jueves', 'viernes', 'sÃƒÆ’Ã‚Â¡bado'],
            weekdaysShort: ['dom', 'lun', 'mar', 'miÃƒÆ’Ã‚Â©', 'jue', 'vie', 'sÃƒÆ’Ã‚Â¡b'],
            today: 'hoy',
            clear: 'borrar',
            close: 'guardar',
            format: 'dd/mm/yyyy',
            min: '25,05,2017',
            onClose: function () {
                if ($("#FechaPago").val() !== "") {
                    $("#mdl-confirm-fecha .modal-content").find('p').find('span').text($("#FechaPago").val());
                    $("#mdl-confirm-fecha").modal({dismissible: false}).modal("open");
                }
            }
            /*max: '07,09,2017'*/
        });
    } else {
        $(".datepicker").pickadate({
            selectMonths: true, // Creates a dropdown to control month
            selectYears: 1, // Creates a dropdown of 15 years to control year
            format: 'dd/mm/yyyy',
            min: '25,05,2017'/*,
             max: '07,09,2017'*/
        });
    }

    $(".company-menu a").on("click", function () {
        show_loader_wrapper();
    });

    tableServices();
    $("#editar-servicios").on("click", function () {
        $(this).fadeOut(500);
        $(".not-edit").fadeOut(500);
    });
    $("#agregar-servicios").on("click", function () {
        validarServicios();
    });
    $('#FacturaLectora').on("keypress", function (e) {
        if (e.keyCode === 32) {
            return false;
        }
    });

    $('#guardar-observaciones').on("click", function () {
        if ($("#Observaciones").val() === "") {
            show_toast("warning", "Ingrese al menos una observacion");
            return;
        }
        show_loader_wrapper();
        var post = $('#save-detalle-pago-json').serialize();
        post += "&Observaciones=" + $("#Observaciones").val();
        updateDetallePagoLectora(post);
    });

    $("#estatus-entrega").on("click", function () {
        if ($(this).children().hasClass('blue')) {
            var link = url_edit_empresa_lectoras;
            window.location = link;
        }
        return false;
    });
}
/** FIN init **/

function tableServices() {
//--- funciones al cambiar la cantidad ---//
    $(document).on("change", ".cantidades", function () {
        var cantidad = parseInt(this.value);
        if (!isNaN(cantidad)) {
            var id = this.getAttribute('data-id');
            var precio = parseFloat(document.getElementById('precio-' + id).textContent);
            var total = document.getElementById("total-" + id);
            total.value = (cantidad * precio).toFixed(2);
            sumarTotales();
        }
    });
}

function sumarTotales() {
//--- functiones para actualizar los totales ---//
    var totales = document.getElementsByClassName("totales");
    var total = 0;
    for (var i = 0; i < totales.length; i++) {
        total = total + parseFloat(totales[i].value);
    }
    document.getElementById("SubtotalLectoras").value = total.toFixed(2);
    var iva = total.toFixed(2) * .16;
    document.getElementById("IvaLectoras").value = iva.toFixed(2);
    document.getElementById("TotalLectoras").value = parseFloat(iva.toFixed(2)) + parseFloat(total.toFixed(2));
}

function validarServicios() {
    var cantidades = document.getElementsByClassName("cantidades");
    var msj = true;
    for (var i = 0; i < cantidades.length; i++) {
        if (cantidades[i].value != "") {
            msj = false;
            break;
        }
    }
    if (msj) {
        show_toast("warning", "Debe de poner la cantidad de por lo menos un servicio");
        return;
    }
    enviarServicios();
}

function enviarServicios() {
    show_loader_wrapper();
    var servicios = {};
    var cantidades = document.getElementsByClassName("cantidades");
    for (var i = 0; i < cantidades.length; i++) {
        if (cantidades.value != "") {
            var id = cantidades[i].getAttribute('data-id');
            servicios[id] = {"Cantidad": cantidades[i].value, "Total": document.getElementById("total-" + id).value};
        }
    }
    var post = {
        "DetalleServicioJSON": JSON.stringify(servicios),
        "Lang": lang_forma,
        "IVA": document.getElementById("IvaLectoras").value,
        "Subtotal": document.getElementById("SubtotalLectoras").value,
        "Total": document.getElementById("TotalLectoras").value
    };
    $.ajax({
        type: "post", // podrÃƒÆ’Ã‚Â­a ser get, post, put o delete.
        url: url_request_retrievals, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: post, // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            show_toast("success", general_text.sas_exitoGuardado);
        },
        error: function (response) {
            hide_loader_wrapper();
            show_modal_error(response.responseText);
        }
    });
}

function changeStatus() {
    show_loader_wrapper();
    $("#mdl-confirm-status").modal('close');
    var post = {
        "status_pago": parseInt($("#status-pago").val()),
        "idEmpresa": $("#idEmpresa").val()
    };
    $.ajax({
        type: "post", // podrÃƒÆ’Ã‚Â­a ser get, post, put o delete.
        url: url_save_payment_status, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: post, // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            show_alert("success", general_text.sas_exitoGuardado);
            $("#status-pago").attr("idStatus", result['data']['status_pago']);
            //Actualizamos el estatus de Entrega
            if (result['data']['status_pago'] != "1") {
                $("#estatus-entrega").children().addClass("red").removeClass("blue").text('NO Entregar Equipos');
            } else {
                $("#estatus-entrega").children().addClass("blue").removeClass("red").text('Entregar Equipos');
            }
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function CalculateTotalAmount() {
    var arreglo = $('.SE_Total');
    var subtotal = 0;
    $.each(arreglo, function (i, Servicios) {
        if (Servicios.innerText !== "")
        {
            subtotal += parseFloat(Servicios.innerText);
        }
    });
    total = subtotal;
    if ($('#SubtotalLectoras').length > 0) {
        iva = subtotal * 0.16;
        total = subtotal + iva;
        $('#IvaLectoras').val(iva.toFixed(2));
        $('#SubtotalLectoras').val(subtotal.toFixed(2));
    }
    $('#TotalLectoras').val(total.toFixed(2));
}

function changeFormaPago() {
    show_loader_wrapper();
    $("#mdl-confirm-forma").modal('close');
    var post = $('#save-detalle-pago-json').serialize();
    post += "&Observaciones=" + $("#Observaciones").val();
    post += "&idFormaPago=" + parseInt($("#forma-pago").val());
    post += "&ListaEmpresaEntidaFiscal=" + $("#ListaEmpresaEntidaFiscal").val();
    post += "&idEmpresa=" + $("#idEmpresa").val();
    $.ajax({
        type: "post", // podrÃƒÆ’Ã‚Â­a ser get, post, put o delete.
        url: url_save_payment_method, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: post, // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            show_alert("success", general_text.sas_exitoGuardado);
            $("#forma-pago").attr("idFormaPago", result['data']['idFormaPago']);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function changeFechaPago() {
    show_loader_wrapper();
    $("#mdl-confirm-fecha").modal('close');
    var post = {
        "fecha_pago": $("#FechaPago").val(),
        "idEmpresa": $("#idEmpresa").val()
    };
    $.ajax({
        type: "post", // podrÃƒÆ’Ã‚Â­a ser get, post, put o delete.
        url: url_save_payment_date, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: post, // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            show_alert("success", general_text.sas_exitoGuardado);
            $("#p-fecha").attr("idFechaPago", result['data']['fecha_pago']);
            //$("#p-fecha").text(result['data']['fecha_pago']);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function validateDetallePagoLectoraForm() {
    $("#save-detalle-pago-json").validate({
        rules: {
            'PagoAcumulado': {
                required: true,
                maxlength: 20
            },
            'MonedaLectora': {
                required: true
            },
            'FacturaLectora': {
                required: true,
            }
        },
        messages: {
            'PagoAcumulado': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres
            },
            'MonedaLectora': {
                required: general_text.sas_requerido
            },
            'FacturaLectora': {
                required: general_text.sas_requerido,
            }
        },
        errorElement: "div",
        errorClass: "invalid",
        errorPlacement: function (error, element) {
            if ($(element).parent('div').find('i.material-icons').length > 0) {
                $(error).attr('icon', true);
            }
            var placement = $(element).data('error');
            if (placement) {
                $(placement).append(error)
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            var post = $('#save-detalle-pago-json').serialize();
            post += "&Observaciones=" + $("#Observaciones").val();
            updateDetallePagoLectora(post);
        }
    });
}

function updateDetallePagoLectora(post) {
    post += "&idFormaPago=" + parseInt($("#forma-pago").val());
    post += "&ListaEmpresaEntidaFiscal=" + $("#ListaEmpresaEntidaFiscal").val();
    post += "&idEmpresa=" + $("#idEmpresa").val();
    $.ajax({
        type: "post",
        url: url_update_payment_detail,
        dataType: 'json',
        data: post,
        success: function (response) {
            /*disabled.attr("disabled", "disabled");*/
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            /*lectoras_empresa[response.data["idEmpresaLectora"]] = response.data;
             setRow(response.data, action);*/
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function setEntidad(idEntidad, i) {
    $("#li-" + idEntidad).attr('id', "li-" + i);
    $("#tab-" + idEntidad).html(list_entidad_fiscal[i]['DF_RazonSocial'] + "<i class='material-icons delete-entity' style='font-size: 20px; margin-left: 20px; display:none;'>remove_circle_outline</i>");
    $("#tab-" + idEntidad).attr('href', "#div-entidad-fiscal-" + i);
    $("#tab-" + idEntidad).attr('id', "tab-" + i);
    $("#li-" + i + " .delete-entity").show();
    $("#lbl-RazonSocial-" + idEntidad).text(list_entidad_fiscal[i]['DF_RazonSocial']);
    $("#lbl-RazonSocial-" + idEntidad).attr('id', "lbl-RazonSocial-" + i);
    $("#lbl-RepresentanteLegal-" + idEntidad).text(list_entidad_fiscal[i]['DF_RepresentanteLegal']);
    $("#lbl-RepresentanteLegal-" + idEntidad).attr('id', "lbl-RepresentanteLegal-" + i);
    $("#lbl-Email-" + idEntidad).text(list_entidad_fiscal[i]['DF_Email']);
    $("#lbl-Email-" + idEntidad).attr('id', "lbl-Email-" + i);
    $("#lbl-Telefono-" + idEntidad).text(list_entidad_fiscal[i]['DF_Telefono']);
    $("#lbl-Telefono-" + idEntidad).attr('id', "lbl-Telefono-" + i);
    $("#lbl-RFC-" + idEntidad).text(list_entidad_fiscal[i]['DF_RFC']);
    $("#lbl-RFC-" + idEntidad).attr('id', "lbl-RFC-" + i);
    $("#lbl-Puesto-" + idEntidad).text(list_entidad_fiscal[i]['DF_Puesto']);
    $("#lbl-Puesto-" + idEntidad).attr('id', "lbl-Puesto-" + i);
    $("#lbl-Calle-" + idEntidad).text(list_entidad_fiscal[i]['DF_Calle']);
    $("#lbl-Calle-" + idEntidad).attr('id', "lbl-Calle-" + i);
    $("#lbl-NumeroExterior-" + idEntidad).text(list_entidad_fiscal[i]['DF_NumeroExterior']);
    $("#lbl-NumeroExterior-" + idEntidad).attr('id', "lbl-NumeroExterior-" + i);
    $("#lbl-NumeroInterior-" + idEntidad).text(list_entidad_fiscal[i]['DF_NumeroInterior']);
    $("#lbl-NumeroInterior-" + idEntidad).attr('id', "lbl-NumeroInterior-" + i);
    $("#lbl-Ciudad-" + idEntidad).text(list_entidad_fiscal[i]['DF_Ciudad']);
    $("#lbl-Ciudad-" + idEntidad).attr('id', "lbl-Ciudad-" + i);
    $("#lbl-Colonia-" + idEntidad).text(list_entidad_fiscal[i]['DF_Colonia']);
    $("#lbl-Colonia-" + idEntidad).attr('id', "lbl-Colonia-" + i);
    $("#lbl-Pais-" + idEntidad).text(list_entidad_fiscal[i]['DF_Pais']);
    $("#lbl-Pais-" + idEntidad).attr('id', "lbl-Pais-" + i);
    $("#lbl-Estado-" + idEntidad).text(list_entidad_fiscal[i]['DF_Estado']);
    $("#lbl-Estado-" + idEntidad).attr('id', "lbl-Estado-" + i);
    $("#lbl-CodigoPostal-" + idEntidad).text(list_entidad_fiscal[i]['DF_CodigoPostal']);
    $("#lbl-CodigoPostal-" + idEntidad).attr('id', "lbl-CodigoPostal-" + i);
    $("#div-entidad-fiscal-" + idEntidad).attr('id', "div-entidad-fiscal-" + i);
    var listEntidades = $("#ListaEmpresaEntidaFiscal").val();
    listEntidades = listEntidades.replace(idEntidad, i);
    $("#ListaEmpresaEntidaFiscal").val(listEntidades);
    $(".entity-panel").addClass('panel-highlight');
    $("#nota-entidad").text("*" + section_text.sas_notaEntidadFiscal);
    $("#dropdown-entidad").attr('idEntidad', i);
    $("#save-entidad-fiscal").show();
}

function clearEntityForm() {
    $("#idEntidadFiscal").val('');
    $("#frm-entidad input").not("#idEmpresa").val('');
    $("#frm-entidad select").val('');
    $('select').material_select();
    $("#frm-entidad #RFC").attr('disabled', true).parent('.input-field').hide();
    hideProgressBar(".progress-estado");
}

function getEstados() {
    $("#frm-entidad #RFC").removeAttr('disabled').parent('.input-field').fadeIn();
    $("#Pais").val($("#idPais option:selected").text());
    var idPais = $("select#idPais").val();
    var url = url_get_estados.replace("0000", idPais);
    var loader_element = $(this).attr('loader-element');
    if (idPais != "134") {
        $("#RFC").val('XAXX010101000').attr('disabled', true).siblings('label').addClass('active');
    } else {
        $("#RFC").val('').removeAttr('disabled').siblings('label').removeClass('active');
    }
    showProgressBar(loader_element);
    $.ajax({
        type: "get",
        url: url,
        dataType: 'json',
        success: function (result) {
            if (!result['status']) {
                show_alert("danger", result['data']);
                hideProgressBar(loader_element);
                return;
            }
            var estados = result['data'];
            if (Object.keys(estados).length === 0) {
                hideProgressBar(loader_element);
                var html_estado = '<option value="">' + general_text['sas_sinOpcion'] + '</option>';
                $("select#idEstado").html(html_estado);
                return;
            }

            var html_estado = '<option value="">' + general_text['sas_seleccionaOpcion'] + '</option>';
            $.each(estados, function (index, value) {
                html_estado += '<option value="' + value['idEstado'] + '">';
                html_estado += value['Estado'];
                html_estado += '</option>';
            });
            $("select#idEstado").html(html_estado);
            hideProgressBar(loader_element);
        },
        error: function (request, status, error) {
            hideProgressBar(loader_element);
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function showProgressBar(progressBar) {
    $('[loader-element="' + progressBar + '"]').attr('disabled', 'disabled');
    $(progressBar + " .progress").fadeIn("fast");
}

function hideProgressBar(progressBar) {
    setTimeout(function () {
        $(progressBar + " .progress").fadeOut("fast");
    }, 250);
    $('[loader-element="' + progressBar + '"]').removeAttr('disabled');
}

function validateEntity() {
    $("#frm-entidad").validate({
        rules: {
            'idEntidadFiscal': {
                required: true
            },
            'RazonSocial': {
                required: true
            },
            'RepresentanteLegal': {
                required: true
            },
            'Email': {
                required: true
            },
            'RFC': {
                required: function (e) {
                    if ($("#idPais").val() == "134") {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
            'Calle': {
                required: true
            },
            'NumeroExterior': {
                required: true
            },
            'Ciudad': {
                required: true
            },
            'idPais': {
                required: true
            },
            'idEstado': {
                required: true
            },
            'CodigoPostal': {
                required: true
            }
        },
        messages: {
            'RazonSocial': {
                required: general_text.sas_requerido
            },
            'RepresentanteLegal': {
                required: general_text.sas_requerido
            },
            'Email': {
                required: general_text.sas_requerido
            },
            'RFC': {
                required: general_text.sas_requerido
            },
            'Calle': {
                required: general_text.sas_requerido
            },
            'NumeroExterior': {
                required: general_text.sas_requerido
            },
            'Ciudad': {
                required: general_text.sas_requerido
            },
            'idPais': {
                required: general_text.sas_requerido
            },
            'idEstado': {
                required: general_text.sas_requerido
            },
            'CodigoPostal': {
                required: general_text.sas_requerido
            },
        },
        errorElement: "div",
        errorClass: "invalid",
        errorPlacement: function (error, element) {
            if ($(element).parent('div').find('i.material-icons').length > 0) {
                $(error).attr('icon', true);
            }

            var placement = $(element).data('error');
            if (placement) {
                $(placement).append(error)
            } else {
                if ($(element).attr('type') === "file") {
                    element = $(element).parents('.file-field').find('input[type="text"]');
                }
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            if ($('.alert').length > 0) {
                $('.alert').remove();
            }
            show_loader_wrapper();
            $("#mdl-entidad").modal("close");
            saveEntity();
            return false;
        }
    });
}

function saveEntity() {
    var disabled = $("#frm-entidad input:disabled").removeAttr("disabled");
    $.ajax({
        type: "post", // podrÃƒÆ’Ã‚Â­a ser get, post, put o delete.
        url: url_insert_entidad, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: $("#frm-entidad").serialize(), // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            disabled.attr("disabled", "disabled");
            hide_loader_wrapper();
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            list_entidad_fiscal[result.data['idEmpresaEntidadFiscal']] = result.data;
            var listEntidades = "";
            if ($("#ListaEmpresaEntidaFiscal").val() === "") {
                listEntidades = result.data['idEmpresaEntidadFiscal'];
            } else {
                listEntidades = $("#ListaEmpresaEntidaFiscal").val() + "," + result.data['idEmpresaEntidadFiscal'];
            }
            $("#ListaEmpresaEntidaFiscal").val(listEntidades);
            createEntityList(listEntidades);
            $(".entity-panel").addClass('panel-highlight');
            $("#nota-entidad").text("*" + section_text.sas_notaEntidadFiscal);
            $("#save-entidad-fiscal").show();
            $("#dropdown-entidad").append("<li><a id-record='" + result.data['idEmpresaEntidadFiscal'] + "' class='edit-entidad'>" + result.data['DF_RazonSocial'] + "</a></li>");
            $("#dropdown-entidad-nueva").append("<li><a id-record='" + result.data['idEmpresaEntidadFiscal'] + "' class='new-entidad'>" + result.data['DF_RazonSocial'] + "</a></li>");
            $('ul.tabs').tabs('select_tab', 'div-entidad-fiscal-' + result.data['idEmpresaEntidadFiscal']);
            $("#dropdown-entidad").attr('idEntidad', result.data['idEmpresaEntidadFiscal']);
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function saveDetallePagoEntidad(ListaEmpresaEntidaFiscal) {
    show_loader_wrapper();
    var post = {
        "ListaEmpresaEntidaFiscal": ListaEmpresaEntidaFiscal,
        "idEmpresa": $("#idEmpresa").val()
    };
    $.ajax({
        type: "post",
        url: url_save_entidad,
        dataType: 'json',
        data: post,
        success: function (response) {
            /*disabled.attr("disabled", "disabled");*/
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            $(".entity-panel").removeClass('panel-highlight');
            $("#nota-entidad").text("");
            $("#save-entidad-fiscal").hide();
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {

            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}


function  createEntityList(lista) {
    $("#tabs-entidad-fiscal li").remove();
    $(".div-tab").remove();
    $(".indicator").remove();
    var entidades_fiscales = String(lista).split(",");
    if (entidades_fiscales.length > 0 && entidades_fiscales != "") {
        $.each(entidades_fiscales, function (index, entidad) {
            if (list_entidad_fiscal[entidad] !== undefined) {
                var li = jQuery("<li/>", {
                    "id": "li-" + entidad,
                    "class": "tab col s3"
                }).appendTo("#tabs-entidad-fiscal");
                if (entidad === $("#idEmpresaEntidadFiscalSolicitud").val()) {
                    jQuery("<a/>", {
                        "href": "#div-entidad-fiscal-" + entidad,
                        "text": "*" + list_entidad_fiscal[entidad]['DF_RazonSocial'],
                        "class": "tooltipped",
                        "data-position": "top",
                        "data-tooltip": "Entidad Fiscal Seleccionada por el expositor",
                        "id": "tab-" + entidad
                    }).appendTo(li);
                } else {
                    jQuery("<a/>", {
                        "href": "#div-entidad-fiscal-" + entidad,
                        "html": list_entidad_fiscal[entidad]['DF_RazonSocial'] + "<i class='material-icons delete-entity' style='font-size: 20px; margin-left: 20px; display:none;'>remove_circle_outline</i>",
                        "id": "tab-" + entidad
                    }).appendTo(li);
                }
                var divTab = jQuery("<div/>", {
                    "id": "div-entidad-fiscal-" + entidad,
                    "class": "col s12 div-tab"
                }).insertAfter("#tabs-entidad-fiscal");
                var divSep = jQuery("<div/>", {
                    "class": "col s6"
                }).appendTo(divTab);
                var segundaColumna = 0;
                $.each(list_entidad_fiscal[entidad], function (index, value) {
                    if (segundaColumna === 7) {
                        divSep = jQuery("<div/>", {
                            "class": "col s6"
                        }).appendTo(divTab);
                    }
                    var valorEntidad = index.split("_");
                    if (valorEntidad[0] === "DF" && valorEntidad[1].substring(0, 2) !== "id" && valorEntidad[1] !== "Delegacion") {
                        var etiqueta = "";
                        if (valorEntidad[1] === "RFC") {
                            etiqueta = "sas_rfc";
                        } else {
                            etiqueta = "sas_" + toCamelCase(valorEntidad[1]);
                        }

                        var divRaw = jQuery("<div/>", {
                            "class": "row"
                        }).appendTo(divSep);
                        var divLabel = jQuery("<div/>", {
                            "class": "col s3 right-align"
                        }).appendTo(divRaw);
                        jQuery("<span/>", {
                            "class": "personal-info",
                            "text": general_text[etiqueta] + ":"
                        }).appendTo(divLabel);
                        var divValue = jQuery("<div/>", {
                            "class": "col s9 m9"
                        }).appendTo(divRaw);
                        jQuery("<span/>", {
                            "id": "lbl-" + valorEntidad[1] + "-" + entidad,
                            "text": value
                        }).appendTo(divValue);
                        segundaColumna += 1;
                    }
                });
            }

        });
        $(document).ready(function () {
            $('ul.tabs').tabs();
        });
        $(document).ready(function () {
            $('.tooltipped').tooltip({delay: 50});
        });

        $("#dropdown-entidad").attr('idEntidad', entidades_fiscales[0]);

        if ($("#idEmpresaEntidadFiscalSolicitud").val() === "") {
            $("#li-" + entidades_fiscales[0] + " .delete-entity").show();
        } else {
            $("#btn-edit-entidad").hide();
        }
    } else {
        $("#btn-edit-entidad").hide();
    }
}

function  toCamelCase(str) {
    return str
            .replace(/\s(.)/g, function ($1) {
                return $1.toUpperCase();
            })
            .replace(/\s/g, '')
            .replace(/^(.)/, function ($1) {
                return $1.toLowerCase();
            });
}
