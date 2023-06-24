/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(init);

function init() {
    initFormEntidadFiscal();
    $('.tooltipped').tooltip({delay: 50});
}

function initFormEntidadFiscal() {
    var formID = "#frm-entidad-fiscal";
    $(formID).validate({
        rules: {
            'RazonSocial': {
                required: true
            },
            'RepresentanteLegal': {
                required: true
            },
            'RFC': {
                required: true
            },
            'Email': {
                required: true
            },
            'idPais': {
                required: true
            },
            'CodigoPostal': {
                required: true
            },
            'idEstado': {
                required: true
            },
            'Ciudad': {
                required: true
            },
            'Calle': {
                required: true
            },
            'NumeroExterior': {
                required: true
            },
        },
        messages: {
            'RazonSocial': {
                required: general_text.sas_requerido
            },
            'RepresentanteLegal': {
                required: general_text.sas_requerido
            },
            'RFC': {
                required: general_text.sas_requerido
            },
            'Email': {
                required: general_text.sas_requerido
            },
            'idPais': {
                required: general_text.sas_requerido
            },
            'CodigoPostal': {
                required: general_text.sas_requerido
            },
            'idEstado': {
                required: general_text.sas_requerido
            },
            'Ciudad': {
                required: general_text.sas_requerido
            },
            'Calle': {
                required: general_text.sas_requerido
            },
            'NumeroExterior': {
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
            form.submit();
        }
    });
}

$("select#idPais").change(getEstados);

function getEstados() {
    var idPais = $("select#idPais").val();
    var url = url_get_estados.replace("0000", idPais);
    var loader_element = $(this).attr('loader-element');
    showProgressBar(loader_element);
    $.ajax({
        type: "get",
        url: url,
        dataType: 'json',
        success: function (result) {
            if (!result['status']) {
                show_modal_error(result['data']);
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

/* Busqueda CP */
var searchInterval;
$("#CodigoPostal").keyup(function () {
    clearTimeout(searchInterval);
    searchInterval = setTimeout(function () {
        if ($("#CodigoPostal").val().length < 4) {
            return;
        }
        getPECC();
    }, 1000);
});

$("#CodigoPostal").focusout(removePECC);

function removePECC() {
    $('.autocomplete-content').slideDown("fast", function () {
        $('.autocomplete-content').remove();
    });
}

function getPECC() {
    $('.autocomplete-content').remove();
    var codigoPostal = $("#CodigoPostal").val().trim();
    var url = url_get_pecc.replace("00000", codigoPostal);
    var loader_element = $("#CodigoPostal").attr('loader-element');
    showProgressBar(loader_element);
    $.ajax({
        type: "get",
        url: url,
        dataType: 'json',
        success: function (result) {
            if (!result['status']) {
                show_modal_error(result['data']);
                hideProgressBar(loader_element);
                return;
            }
            var codigos = result['data'];
            if (codigos.length === 0) {
                hideProgressBar(loader_element);
                return;
            }

            var ul = $('<ul/>', {
                "src": $('#img_back').val(),
                "class": 'dropdown-content autocomplete-content'
            });
            $.each(codigos, function (index, value) {
                var li = $('<li/>').data('pecc', value).click(setPECCValues).appendTo(ul);
                $('<span/>', {"text": value['label']}).appendTo(li);
            });
            $("label[for='CodigoPostal']").after(ul);
            hideProgressBar(loader_element);
        },
        error: function (request, status, error) {
            hideProgressBar(loader_element);
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function setPECCValues() {
    if (!isset($(this).data('pecc'))) {
        return;
    }
    $('#CodigoPostal').focus();
    var pecc = $(this).data('pecc');
    if (parseInt($("#idPais").val()) !== parseInt(pecc['idPais'])) {
        $('#idPais').val(pecc['idPais']).change();
    }
    setTimeout(function () {
        $('#idEstado').val(pecc['idEstado']).change();
    }, 1500);
    $('#CodigoPostal').val(pecc['CodigoPostal']).change();
    $('#Ciudad').val(pecc['Ciudad']).change();
    $('#Colonia').val(pecc['Colonia']).change();
    $('#Calle').focus();
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

$(".edit").click(showEditEF);

function showEditEF() {
    if (!isset(entidadesFiscales[$(this).attr("id-entidad-fiscal")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var entidadFiscal = entidadesFiscales[$(this).attr("id-entidad-fiscal")];

    $(".frm-entidad-fiscal #idComiteOrganizador").val(entidadFiscal["idComiteOrganizador"]);
    $(".frm-entidad-fiscal #idEntidadFiscal").val(entidadFiscal["idEntidadFiscal"]);
    $(".frm-entidad-fiscal #RazonSocial").val(entidadFiscal["RazonSocial"]);
    $(".frm-entidad-fiscal #RepresentanteLegal").val(entidadFiscal["RepresentanteLegal"]);
    $(".frm-entidad-fiscal #RFC").val(entidadFiscal["RFC"]);
    $(".frm-entidad-fiscal #Email").val(entidadFiscal["Email"]);
    $(".frm-entidad-fiscal #idPais").val(entidadFiscal["idPais"]).change();
    $(".frm-entidad-fiscal #CodigoPostal").val(entidadFiscal["CodigoPostal"]);
    setTimeout(function () {
        $(".frm-entidad-fiscal #idEstado").val(entidadFiscal["idEstado"]);
    }, 1500);
    $(".frm-entidad-fiscal #Ciudad").val(entidadFiscal["Ciudad"]);
    $(".frm-entidad-fiscal #Colonia").val(entidadFiscal["Colonia"]);
    $(".frm-entidad-fiscal #Delegacion").val(entidadFiscal["Delegacion"]);
    $(".frm-entidad-fiscal #Calle").val(entidadFiscal["Calle"]);
    $(".frm-entidad-fiscal #NumeroExterior").val(entidadFiscal["NumeroExterior"]);
    $(".frm-entidad-fiscal #NumeroInterior").val(entidadFiscal["NumeroInterior"]);
    Materialize.updateTextFields();

    $(".entidades-fiscales").css("display", "none");
    $(".frm-entidad-fiscal").fadeIn();
}

$(".delete").click(showDeleteEF);

function showDeleteEF() {
    if (!isset(entidadesFiscales[$(this).attr("id-entidad-fiscal")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var entidadFiscal = entidadesFiscales[$(this).attr("id-entidad-fiscal")];

    $("#frm-entidad-fiscal-eliminar #idEntidadFiscal").val(entidadFiscal['idEntidadFiscal']);
    $(".entidad-fiscal").text(entidadFiscal['RazonSocial']);
    $("#modal-delete-ef").modal("open");
}

$(".btn-cancel-submit").click(cancelSubmit);

function cancelSubmit() {
    $(".frm-entidad-fiscal #idEntidadFiscal").val("");
    $(".frm-entidad-fiscal #RazonSocial").val("");
    $(".frm-entidad-fiscal #RepresentanteLegal").val("");
    $(".frm-entidad-fiscal #RFC").val("");
    $(".frm-entidad-fiscal #Email").val("");
    $(".frm-entidad-fiscal #idPais").val("");
    $(".frm-entidad-fiscal #CodigoPostal").val("");
    $(".frm-entidad-fiscal #idEstado").val("");
    $(".frm-entidad-fiscal #Ciudad").val("");
    $(".frm-entidad-fiscal #Colonia").val("");
    $(".frm-entidad-fiscal #Delegacion").val("");
    $(".frm-entidad-fiscal #Calle").val("");
    $(".frm-entidad-fiscal #NumeroExterior").val("");
    $(".frm-entidad-fiscal #NumeroInterior").val("");
    Materialize.updateTextFields();
    
    $(".frm-entidad-fiscal").css("display", "none");
    $(".entidades-fiscales").fadeIn();
}

$(".btn-new-ef").click(showNewEF);

function showNewEF() {
    $(".frm-entidad-fiscal #idEntidadFiscal").val("");
    $(".frm-entidad-fiscal #RazonSocial").val("");
    $(".frm-entidad-fiscal #RepresentanteLegal").val("");
    $(".frm-entidad-fiscal #RFC").val("");
    $(".frm-entidad-fiscal #Email").val("");
    $(".frm-entidad-fiscal #idPais").val("");
    $(".frm-entidad-fiscal #CodigoPostal").val("");
    $(".frm-entidad-fiscal #idEstado").val("");
    $(".frm-entidad-fiscal #Ciudad").val("");
    $(".frm-entidad-fiscal #Colonia").val("");
    $(".frm-entidad-fiscal #Delegacion").val("");
    $(".frm-entidad-fiscal #Calle").val("");
    $(".frm-entidad-fiscal #NumeroExterior").val("");
    $(".frm-entidad-fiscal #NumeroInterior").val("");
    Materialize.updateTextFields();
    
    $(".entidades-fiscales").css("display", "none");
    $(".frm-entidad-fiscal").fadeIn();
}

$(".click-to-toggle ul a").click(function () {
    $(this).parents(".click-to-toggle").find("a").first().trigger("click");
});