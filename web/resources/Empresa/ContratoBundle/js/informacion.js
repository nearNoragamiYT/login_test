var c_contact = {"1": {}, "2": {}, "3": {}, "4": {}}, idContrato = 0;
$(init);

function init() {
    $(".uncompleted").on("click", function (e) {
        hide_loader_top();
    });

    $("select").material_select();

    $("select#idPais").change(getEstados);

    $("select#idEstado").change(function () {
        $("#Estado").val($("#idEstado option:selected").text());
    });

    $(".edit-entidad").on("click", function () {
        var i = parseInt($(this).attr("id-record"));
        if (i == 0) {
            clearEntityForm();
            $("#mdl-entidad").modal("open");
        } else {
            setEntity(i);
        }
    });

    $("#save-entity").on("click", function () {
        $("#frm-entidad").submit();
    });
    validateEntity();

    $(".edit-contact").on("click", function () {
        var i = parseInt($(this).attr("id-record"));
        var type = $(this).attr("type");
        if (i == 0) {
            clearContactForm();
            $("#mdl-contacto").modal("open");
            $("#idContactoTipo").val(type);
        } else {
            setContact(type, i);
        }
    });

    $("#save-contact").on("click", function () {
        $("#frm-contacto").submit();
    });
    validateContact();

    $("#btnSaveContract").on("click", saveContract);

    c_contact = ($("#c_contact").val() != "" && $("#c_contact").val() != "null") ? JSON.parse($("#c_contact").val()) : c_contact;
    if (c_contact[1] == undefined) {
        c_contact[1] = {};
    }
    if (c_contact[2] == undefined) {
        c_contact[2] = {};
    }
    if (c_contact[3] == undefined) {
        c_contact[3] = {};
    }
    if (c_contact[4] == undefined) {
        c_contact[4] = {};
    }
    idContrato = ($("#idContrato").val() == "") ? 0 : $("#idContrato").val();

    $(".empresaTipo").on("change", function () {
        if ($("input[name='EmpresaTipo']:checked").attr("coexpositor") === "1") {
            $(".seleccion-padre").show();
        } else {
            $(".seleccion-padre").hide();
            $("#idEmpresaPadre").val($("#idEmpresa").val())
        }
    });

    $(".empresaTipo").trigger("change");


}

function getEstados() {
    $("#frm-entidad #RFC").removeAttr('disabled').parent('.input-field').fadeIn();
    $("#Pais").val($("#idPais option:selected").text());
    var idPais = $("select#idPais").val();
    var url = url_get_estados.replace("0000", idPais);
    var loader_element = $(this).attr('loader-element');
    /*if (idPais != "134" && idPais != "193" && idPais != "221") {
     $("#RFC").val('XAXX010101000').attr('disabled', true).siblings('label').addClass('active');
     } else {
     $("#RFC").removeAttr('disabled').siblings('label').removeClass('active');
     }*/
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
                var html_estado = '<option value="0">' + general_text['sas_sinOpcion'] + '</option>';
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
                    if ($("#idPais").val() == "134" || $("#idPais").val() == "193" || $("#idPais").val() == "221") {
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

function validateContact() {
    $("#frm-contacto").validate({
        rules: {
            'idContacto': {
                required: true
            },
            'Nombre': {
                required: true
            },
            'ApellidoPaterno': {
                required: true
            },
            'ApellidoMaterno': {
                required: true
            },
            'Puesto': {
                required: true
            },
            'Email': {
                required: true
            },
            'Telefono': {
                required: true
            }
        },
        messages: {
            'idContacto': {
                required: general_text.sas_requerido
            },
            'Nombre': {
                required: general_text.sas_requerido
            },
            'ApellidoPaterno': {
                required: general_text.sas_requerido
            },
            'ApellidoMaterno': {
                required: general_text.sas_requerido
            },
            'Puesto': {
                required: general_text.sas_requerido
            },
            'Email': {
                required: general_text.sas_requerido
            },
            'Telefono': {
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
            $("#mdl-contacto").modal("close");
            saveContact();
            return false;
        }
    });
}

function setEntity(i) {
    $("#lbl-RazonSocial").text(list_entidad_fiscal[i]['DF_RazonSocial']);
    $("#lbl-RepresentanteLegal").text(list_entidad_fiscal[i]['DF_RepresentanteLegal']);
    $("#lbl-Email").text(list_entidad_fiscal[i]['DF_Email']);
    $("#lbl-RFC").text(list_entidad_fiscal[i]['DF_RFC']);
    $("#lbl-Calle").text(list_entidad_fiscal[i]['DF_Calle']);
    $("#lbl-NumeroExterior").text(list_entidad_fiscal[i]['DF_NumeroExterior']);
    $("#lbl-NumeroInterior").text(list_entidad_fiscal[i]['DF_NumeroInterior']);
    $("#lbl-Ciudad").text(list_entidad_fiscal[i]['DF_Ciudad']);
    $("#lbl-Colonia").text(list_entidad_fiscal[i]['DF_Colonia']);
    $("#lbl-Pais").text(list_entidad_fiscal[i]['DF_Pais']);
    $("#lbl-Estado").text(list_entidad_fiscal[i]['DF_Estado']);
    $("#lbl-CodigoPostal").text(list_entidad_fiscal[i]['DF_CodigoPostal']);
    $("#idEmpresaEntidadFiscalSel").val(i);
}

function setContact(type, i) {
    $("#lbl-" + type + "-name").text(((list_contacto[i]['Nombre'] === null) ? "" : list_contacto[i]['Nombre']) + " " + ((list_contacto[i]['ApellidoPaterno'] === null) ? "" : list_contacto[i]['ApellidoPaterno']) + " " + ((list_contacto[i]['ApellidoMaterno'] === null) ? "" : list_contacto[i]['ApellidoMaterno']));
    $("#lbl-" + type + "-title").text((list_contacto[i]['Puesto'] === null) ? "" : list_contacto[i]['Puesto']);
    $("#lbl-" + type + "-email").text((list_contacto[i]['Email'] === null) ? "" : list_contacto[i]['Email']);
    $("#lbl-" + type + "-phone").text((list_contacto[i]['Telefono'] === null) ? "" : list_contacto[i]['Telefono']);
    c_contact[type]['idContacto'] = list_contacto[i]['idContacto'];
    c_contact[type]['Nombre'] = list_contacto[i]['Nombre'];
    c_contact[type]['ApellidoPaterno'] = list_contacto[i]['ApellidoPaterno'];
    c_contact[type]['ApellidoMaterno'] = list_contacto[i]['ApellidoMaterno'];
    c_contact[type]['Puesto'] = list_contacto[i]['Puesto'];
    c_contact[type]['Email'] = list_contacto[i]['Email'];
    c_contact[type]['Telefono'] = list_contacto[i]['Telefono'];
}

function saveEntity() {
    var disabled = $("#frm-entidad input:disabled").removeAttr("disabled");
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
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
            setEntity(result.data['idEmpresaEntidadFiscal']);
            $("#dropdown-entidad").append("<li><a id-record='" + result.data['idEmpresaEntidadFiscal'] + "' class='edit-entidad'>" + result.data['DF_RazonSocial'] + "</a></li>");
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function saveContact() {
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_insert_contacto, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: $("#frm-contacto").serialize(), // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            list_contacto[result.data['idContacto']] = result.data;
            setContact(result.data['idContactoTipo'], result.data['idContacto']);
            $.each($("#dropdown-contacto-contact"), function (i, e) {
                $(e).append("<li><a id-record='" + result.data['idContacto'] + "' class='edit-entidad'>" + result.data['Nombre'] + " " + result.data['ApellidoPaterno'] + " " + result.data['ApellidoMaterno'] + "</a></li>");
            });
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function clearEntityForm() {
    $("#idEntidadFiscal").val('');
    $("#frm-entidad input").not("#idEmpresa").val('');
    $("#frm-entidad select").val('');
    $('select').material_select();
    //$("#frm-entidad #RFC").attr('disabled', true).parent('.input-field').hide();
}

function clearContactForm() {
    $("#idContacto").val('');
    $("#frm-contacto input").not("#idEmpresa, #idContactoTipo").val('');
    $("#frm-contacto select").val('');
    $('select').material_select();
}

function saveContract() {
    var post = {
        "idContrato": ($("#idContrato").val() != "") ? $("#idContrato").val() : 0,
        "idEmpresa": $("#frm-entidad #idEmpresa").val(),
        "idEmpresaEntidadFiscal": $("#idEmpresaEntidadFiscalSel").val(),
        "Contactos": JSON.stringify(c_contact),
        "EmpresaTipo": $("input[name='EmpresaTipo']:checked").val(),
        "idEmpresaPadre": ($("#idEmpresaPadre").val()!==null)?$("#idEmpresaPadre").val():$("#idEmpresa").val()
    }
    if (post.idEmpresaEntidadFiscal == "") {
        $(".entity-panel").addClass('panel-highlight');
        $('html, body').animate({scrollTop: $(".entity-panel").offset().top}, 2000);
        setTimeout(function () {
            $(".entity-panel").removeClass("panel-highlight");
        }, 5000);
        show_alert("warning", section_text.sas_asegureseSeleccionarEntidadFiscal);
        return;
    }
    if (Object.keys(c_contact[1]).length == 0 || Object.keys(c_contact[2]).length == 0 || Object.keys(c_contact[3]).length == 0) {
        $(".contact-panel").addClass('panel-highlight');
        $('html, body').animate({scrollTop: $(".contact-panel").offset().top}, 2000);
        setTimeout(function () {
            $(".contact-panel").removeClass("panel-highlight");
        }, 5000);
        show_alert("warning", section_text.sas_asegureseSeleccionarContacto);
        return;
    }
    if (typeof post.EmpresaTipo === "undefined") {
        $(".company-type-panel").addClass('panel-highlight');
        $('html, body').animate({scrollTop: $(".company-type-panel").offset().top}, 2000);
        setTimeout(function () {
            $(".company-type-panel").removeClass("panel-highlight");
        }, 5000);
        show_alert("warning", section_text.sas_asegureseSeleccionaEmpresaTipo);
        return;
    }
    if (post.idEmpresaPadre == "" ) {
        $(".company-type-panel").addClass('panel-highlight');
        $('html, body').animate({scrollTop: $(".company-type-panel").offset().top}, 2000);
        setTimeout(function () {
            $(".company-type-panel").removeClass("panel-highlight");
        }, 5000);
        show_alert("warning", section_text.sas_asegureseSeleccionarEmpresaPadre);
        return;
    }
    show_loader_wrapper();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_save_information, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: post, // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            $("#idContrato").val(result.data['idContrato']);
            if (idContrato == 0) {
                idContrato = result.data['idContrato'];
                url_next_step += "/" + idContrato;
            }
            $("#contract-number").text(result.data['idContrato']);
            show_alert("success", general_text.sas_guardoExito);
            $("#mdl-next-step").modal({dismissible: false}).modal("open");
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}
