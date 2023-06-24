/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(init);

function init() {
    initFormContactoCO();
}

function initFormContactoCO() {
    var formID = "#frm-contacto-comite-organizador";
    $(formID).validate({
        rules: {
            'Nombre': {
                required: true
            },
            'Email': {
                required: true
            },
            'Puesto': {
                required: true
            },
        },
        messages: {
            'Nombre': {
                required: general_text.sas_requerido
            },
            'Email': {
                required: general_text.sas_requerido
            },
            'Puesto': {
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

$(".btn-new-ef").click(showNewContact);
function showNewContact() {
    $(".frm-contacto-comite-organizador #idContactoComiteOrganizador").val("");
    $(".frm-contacto-comite-organizador #Nombre").val("");
    $(".frm-contacto-comite-organizador #Email").val("");
    $(".frm-contacto-comite-organizador #Puesto").val("");
    $(".frm-contacto-comite-organizador #Telefono").val("");
    $(".frm-contacto-comite-organizador #RedSocial").val("");

    Materialize.updateTextFields();

    $(".contactos").css("display", "none");
    $(".frm-contacto-comite-organizador").fadeIn();
}

$(".btn-cancel-submit").click(cancelSubmit);
function cancelSubmit() {
    $(".frm-contacto-comite-organizador #idContactoComiteOrganizador").val("");
    $(".frm-contacto-comite-organizador #Nombre").val("");
    $(".frm-contacto-comite-organizador #Email").val("");
    $(".frm-contacto-comite-organizador #Puesto").val("");
    $(".frm-contacto-comite-organizador #Telefono").val("");
    $(".frm-contacto-comite-organizador #RedSocial").val("");
    Materialize.updateTextFields();

    $(".frm-contacto-comite-organizador").css("display", "none");
    $(".contactos").fadeIn();
}

$(".edit").click(showEditContact);
function showEditContact() {
    if (!isset(contactos[$(this).attr("id-contacto")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var contacto = contactos[$(this).attr("id-contacto")];

    $(".frm-contacto-comite-organizador #idContactoComiteOrganizador").val(contacto["idContactoComiteOrganizador"]);
    $(".frm-contacto-comite-organizador #Nombre").val(contacto["Nombre"]);
    $(".frm-contacto-comite-organizador #Email").val(contacto["Email"]);
    $(".frm-contacto-comite-organizador #Puesto").val(contacto["Puesto"]);
    $(".frm-contacto-comite-organizador #Telefono").val(contacto["Telefono"]);
    $(".frm-contacto-comite-organizador #RedSocial").val(contacto["RedSocial"]);
    Materialize.updateTextFields();

    $(".contactos").css("display", "none");
    $(".frm-contacto-comite-organizador").fadeIn();
}

$(".delete").click(showDeleteContact);

function showDeleteContact() {
    if (!isset(contactos[$(this).attr("id-contacto")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var contacto = contactos[$(this).attr("id-contacto")];

    $("#frm-contacto-eliminar #idContactoComiteOrganizador").val(contacto['idContactoComiteOrganizador']);
    $(".contacto").text(contacto['Nombre']);
    $("#modal-delete-contacto").modal("open");
}

$(".click-to-toggle ul a").click(function () {
    $(this).parents(".click-to-toggle").find("a").first().trigger("click");
});