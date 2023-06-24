/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(init);

function init() {
    initFormUsuario();
}

function initFormUsuario() {
    var formID = "#frm-usuario";
    $(formID).validate({
        rules: {
            'Nombre': {
                required: true
            },
            'Puesto': {
                required: true
            },
            'Email': {
                required: true
            },
            'Password': {
                required: true
            },
            'idComiteOrganizador': {
                required: true
            },
            'idTipoUsuario': {
                required: true
            },
        },
        messages: {
            'Nombre': {
                required: general_text.sas_requerido
            },
            'Puesto': {
                required: general_text.sas_requerido
            },
            'Email': {
                required: general_text.sas_requerido
            },
            'Password': {
                required: general_text.sas_requerido
            },
            'idComiteOrganizador': {
                required: general_text.sas_requerido
            },
            'idTipoUsuario': {
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

$("#chk-cambiar-password").on("change", activarCambioPassword);

function activarCambioPassword() {
    if ($(this).is(":checked")) {
        $("#Password").removeAttr("disabled");
        $("#Password").attr("type", "text");
        $("#Password").val("");
        $("#Password").focus();
        $(".btn-generate-password").fadeIn();
        return;
    }
    $("#Password").attr("type", "password");
    $("#Password").val("____");
    $("#Password").attr("disabled", "disabled");
    $("label[for='Password']").addClass("active");
    $(".btn-generate-password").fadeOut();
}

$(".btn-generate-password").click(function () {
    $("#Password").val(generatePassword());
    $("#Password").focus();
});

function generatePassword() {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < 6; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}

$(".edicion-item").on("click", mostrarOcultarPermisos);

function mostrarOcultarPermisos() {
    var idEdicion = $(this).attr("id-edicion");
    if ($(this).hasClass("active")) {
        $(".edicion-item.active").removeClass("active");
        $(".tab-edicion").css("display", "none");
        return;
    }
    $(".edicion-item.active").removeClass("active");
    $(".edicion-item[id-edicion=" + idEdicion + "]").addClass("active");
    $(".tab-edicion").css("display", "none");
    $(".tab-edicion[id-edicion=" + idEdicion + "]").fadeIn();
}