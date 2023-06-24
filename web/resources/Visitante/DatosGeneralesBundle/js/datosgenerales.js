$(init);

function init() {
    $('select').material_select();
    $("#visitante-datosgenerales").attr("class", "active");
    hide_loader_wrapper();
    initFrmDatosGenerales();
    fieldvalue();
}
$("#DE_idArea").change(function () {
    var value = $(this).val();
    if (value == "10") {
        $("#hide_AreaOtro").show();
        $("#AreaOtro").focus();
    } else {
        $("#hide_AreaOtro").hide();
        $("#AreaOtro")[0].value = "";
    }

});
$("#DE_idCargo").change(function () {
    var value = $(this).val();
    if (value == "10") {
        $("#hide_CargoOtro").show();
        $("#CargoOtro").focus();
    } else {
        $("#hide_CargoOtro").hide();
        $("#CargoOtro")[0].value = "";
    }

});
$("#idNombreComercial").change(function () {
    var value = $(this).val();
    if (value == "101") {
        $("#hide_NombreComercialOtro").show();
        $("#NombreComercialOtro").focus();
    } else {
        $("#hide_NombreComercialOtro").hide();
        $("#NombreComercialOtro")[0].value = "";
    }

});

function fieldvalue() {
    $.each(visitante, function (index, value) {
        switch (index) {
            case "NombreComercialOtro":
                if (value !== null) {
                    $("#hide_NombreComercialOtro").show();
                } else {
                    $("#hide_NombreComercialOtro").hide();
                }
                break;
            case "CargoOtro":
                if (value !== null) {
                    $("#hide_CargoOtro").show();
                } else {
                    $("#hide_CargoOtro").hide();
                }
                break;
            case "AreaOtro":
                if (value !== null) {
                    $("#hide_AreaOtro").show();
                } else {
                    $("#hide_AreaOtro").hide();
                }
                break;

        }
    });
}
function initFrmDatosGenerales() {
    $("#frm-datos-generales").validate({
        rules: {
            'DE_RazonSocial': {
                required: true
            },
//            'NombreComercialOtro': {
//                required: true
//            },
            'Email': {
                required: true,
                email: true
            },
            'Nombre': {
                required: true
            },
            'ApellidoPaterno': {
                required: true
            },
            'EmailOpcional': {
                email: true
            },
            'DE_idArea': {
                required: true
            },
            'DE_idCargo': {
                required: true
            },
            'AreaOtro': {
                required: true
            },
            'CargoOtro': {
                required: true
            }
        },
        messages: {
            'DE_RazonSocial': {
                required: general_text.sas_requeridoCampo
            },
//            'NombreComercialOtro': {
//                required: general_text.sas_requeridoCampo
//            },
            'Email': {
                required: general_text.sas_requeridoCampo,
                email: general_text.sas_mailInvalido
            },
            'Nombre': {
                required: general_text.sas_requeridoCampo
            },
            'ApellidoPaterno': {
                required: general_text.sas_requeridoCampo
            },
            'EmailOpcional': {
                email: general_text.sas_mailInvalido
            },
            'DE_idArea': {
                required: general_text.sas_requeridoCampo
            },
            'DE_idCargo': {
                required: general_text.sas_requeridoCampo
            },
            'AreaOtro': {
                required: general_text.sas_requeridoCampo
            },
            'CargoOtro': {
                required: general_text.sas_requeridoCampo
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
                if ($(element).attr('type') === "checkbox") {
                    element = $(element).parents('p');
                }
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            var data = $(form).serialize();
            updateGeneralData(data);
        }
    });
}

function updateGeneralData(data) {
    $.ajax({
        type: "post",
        dataType: 'json',
        data: data,
        url: url_update_datosgenerales,
        success: function (response) {
            hide_loader_wrapper();
            if (!response.status) {
                show_toast("danger", general_text.sas_errorGuardado);
                return;
            }
            show_toast("success", general_text.sas_exitoGuardado);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_toast("danger", general_text.sas_errorGuardado);
        }
    });
}

$(document).on("click", ".load", function () {
    show_loader_wrapper();
});
