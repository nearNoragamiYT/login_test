$(document).ready(function () {
    initComercialCompany();
});

function initComercialCompany() {
    $("#empresa-datos-adicionales").attr("class", "active");
    $(".company-menu").on("click", show_loader_wrapper);
    validateAditionalDataForm();
    setAditionalData();

    $("#switch-montaje").on("click", function () {
        $("#Montaje").prop("checked", !$("#Montaje").is(":checked")).change();
    });
    $("#Montaje").on("change", function () {
        if ($(this).is(":checked")) {
            $("#content-montaje").slideDown();
        } else {
            clearMontaje();
        }
    });

    $("#saveAditionalData").on("click", function () {
        $("#save-aditional-data-form").submit();
    });

    $('#sendMail').on("click", function () {
        var correos = {}
        var correoAdicional = $('#correoAdicional').val();
        var idEmpresa = $('#idEmprea').val();
        correos['idEmpresa'] = idEmpresa;
        correos['correoAdicional'] = correoAdicional;    
        if (correos['correoAdicional'] == "") {
            show_toast('danger', general_text.sas_errorEnviar);
            $("#correoAdicional").focus();
        } else {
            sendAdditionalMail(correos);
        }
    });
}

function setAditionalData() {
    $("#idEmpresa").val(aditionalData["idEmpresa"]);
    $("#idTipoStandContratado").val(aditionalData["idTipoStandContratado"]).change();
    $("#idTipoPrecio").val(aditionalData["idTipoPrecio"]).change();
    $("#ObservacionesFacturacion").val(aditionalData["ObservacionesFacturacion"]);
    $("#EmpresasAdicionales").val(aditionalData["EmpresasAdicionales"]);
    $("#NumeroGafetes").val(aditionalData["NumeroGafetes"]);
    $("#NumeroGafetesCompra").val(aditionalData["NumeroGafetesCompra"]);

    if (aditionalData["GafetesPagados"])
        $("#GafetesPagados").prop("checked", true);
    else
        $("#GafetesPagados").prop("checked", false);

    $("#GafetesComentario").val(aditionalData["GafetesComentario"]);
    $("#NumeroVitrinas").val(aditionalData["NumeroVitrinas"]);
    $("#NumeroCatalogos").val(aditionalData["NumeroCatalogos"]);

    $("#NumeroInvitaciones").val(aditionalData["NumeroInvitaciones"]);
    $("#UsuarioInvitaciones").val(aditionalData["UsuarioInvitaciones"]);
    $("#PasswordInvitaciones").val(aditionalData["PasswordInvitaciones"]);

    $("#UsuarioEncuentroNegocios").val(aditionalData["UsuarioEncuentroNegocios"]);
    $("#PasswordEncuentroNegocios").val(aditionalData["PasswordEncuentroNegocios"]);
    if (aditionalData['Montaje']) {
        $("#Montaje").prop("checked", true);
        $("#content-montaje").slideDown();
        $("#MontajeAndenEntrada").val(aditionalData["MontajeAndenEntrada"]);
        $("#MontajeSalaEntrada").val(aditionalData["MontajeSalaEntrada"]);
        $("#MontajeDiaEntrada").val(aditionalData["MontajeDiaEntrada"]);
        $("#MontajeHorarioEntrada").val(aditionalData["MontajeHorarioEntrada"]);
        $("#MontajeAndenSalida").val(aditionalData["MontajeAndenSalida"]);
        $("#MontajeSalaSalida").val(aditionalData["MontajeSalaSalida"]);
        $("#MontajeDiaSalida").val(aditionalData["MontajeDiaSalida"]);
        $("#MontajeHorarioSalida").val(aditionalData["MontajeHorarioSalida"]);
    } else {
        clearMontaje();
        $("#Montaje").prop("checked", false);
    }

    $("#save-aditional-data-form input[type='text'], textarea").removeClass('valid').next().addClass('active');
}

function clearMontaje() {
    $("#content-montaje").slideUp();
    $("#MontajeAndenEntrada").val("");
    $("#MontajeSalaEntrada").val("");
    $("#MontajeDiaEntrada").val("");
    $("#MontajeHorarioEntrada").val("");
    $("#MontajeAndenSalida").val("");
    $("#MontajeSalaSalida").val("");
    $("#MontajeDiaSalida").val("");
    $("#MontajeHorarioSalida").val("");
}

function validateAditionalDataForm() {
    $("#save-aditional-data-form").validate({
        rules: {
            'EmpresasAdicionales': {
                digits: true
            },
            'NumeroGafetes': {
                digits: true
            },
            'NumeroGafetesCompra': {
                digits: true
            },
            'NumeroVitrinas': {
                digits: true
            },
            'NumeroCatalogos': {
                digits: true
            },
        },
        messages: {
            'EmpresasAdicionales': {
                digits: general_text.sas_soloDigitos
            },
            'NumeroGafetes': {
                digits: general_text.sas_soloDigitos
            },
            'NumeroGafetesCompra': {
                digits: general_text.sas_soloDigitos
            },
            'NumeroVitrinas': {
                digits: general_text.sas_soloDigitos
            },
            'NumeroCatalogos': {
                digits: general_text.sas_soloDigitos
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
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            var post = $('#save-aditional-data-form').serialize();
            saveAditionalData(post);
            return;
        }
    });
}
function saveAditionalData(post) {
    $.ajax({
        type: "post",
        url: url_aditional_data_save,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function sendAdditionalMail(data) {
    hide_loader_wrapper();
    $.ajax({
        type: "post",
        dataType: 'json',
        data: data,
        url: url_aditional_send_email,
        success: function (response) {
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            show_alert("success", section_text.sas_exitoEnvioIndividual);

        },
        error: function (request, status, error) {
            show_modal_error(request.responseText);
        }
    });
}