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
            'Email': {
                required: true
            },
            'Password': {
                required: true
            },
        },
        messages: {
            'Email': {
                required: general_text.sas_requerido
            },
            'Password': {
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
            show_loader_wrapper()

            form.submit();
        }
    });
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

$(".btn-new-account").click(showNewAccount);
function showNewAccount() {
    clearForm();
    if (!isset(usuarios[$(this).attr("id-contacto")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var usuario = usuarios[$(this).attr("id-contacto")];
    fillUserForm(usuario);
    $(".usuarios").css("display", "none");
    $(".frm-usuario").fadeIn();
}

$(".edit").click(showEditAccount);
function showEditAccount() {
    clearForm();
    if (!isset(usuarios[$(this).attr("id-usuario")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var usuario = usuarios[$(this).attr("id-usuario")];
    fillUserForm(usuario);
    $(".usuarios").css("display", "none");
    $(".frm-usuario").fadeIn();
}

$(".delete").click(showDeleteAccount);

function showDeleteAccount() {
    if (!isset(usuarios[$(this).attr("id-usuario")])) {
        show_alert("warning", general_text['sas_errorIDUsuario']);
        return;
    }
    var usuario = usuarios[$(this).attr("id-usuario")];
    if (!isset(usuario['idUsuario'])) {
        show_alert("warning", general_text['sas_errorIDUsuario']);
        return;
    }
    $("#frm-usuario-eliminar #idUsuario").val(usuario['idUsuario']);
    $(".usuario").text(usuario['Nombre']);
    $("#modal-delete-usuario").modal("open");
}

$(".click-to-toggle ul a").click(function () {
    $(this).parents(".click-to-toggle").find("a").first().trigger("click");
});

$(".btn-cancel-submit").click(cancelSubmit);
function cancelSubmit() {
    clearForm();
    $(".frm-usuario").css("display", "none");
    $(".usuarios").fadeIn();
}

function clearForm() {
    $('#frm-usuario')[0].reset();
    $("#Password").attr("type", "text");
    $("#Nombre").text("");
    /*$("#ComiteOrganizador").text("");
     $("#Puesto").text("");*/
    Materialize.updateTextFields();
}

function fillUserForm(usuario) {
    $("#idUsuario").val(usuario['idUsuario']);
    $("#idContactoComiteOrganizador").val(usuario['idContactoComiteOrganizador']);
    $("#idComiteOrganizador").val(usuario['idComiteOrganizador']);
    $("#Nombre").text(usuario['Nombre']);
    /*$("#ComiteOrganizador").text(usuario['ComiteOrganizador']);
     $("#Puesto").text(usuario['Puesto']);*/

    var tipoUsuario = 3;
    if (usuario['Staff']) {
        tipoUsuario = 2;
    }
    if (usuario['TipoUsuario']) {
        tipoUsuario = usuario['TipoUsuario'];
    }
    $("#TipoUsuario").val(tipoUsuario);

    var email = usuario['EmailContacto'];
    if (isset(usuario['Email'])) {
        email = usuario['Email'];
    }
    $("#Email").val(email);
    var password = "";
    if (isset(usuario['Password'])) {
        password = "____";
        $("#Password").attr("type", "password");
    }
    $("#Password").val(password);
    Materialize.updateTextFields();
}

/*$(document).on("keypress", "#Password", changePassword);
 
 function changePassword() {
 if (document.getElementById("Password").type === "password") {
 document.getElementById("Password").type = "text";
 }
 }*/
