/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//Global variables
var table = null;
$(init);
/**
 * @description start all functions that need
 */
function init() {
    //start datatables
    table = $('#table-contacts').DataTable({
        "language": {
            "url": url_lang
        }
    });
    //validate form and rules of validate
    jQuery.extend(jQuery.validator.messages, {
        required: general_text['sas_campoRequerido'],
        email: general_text['sas_emailInvalido'],
        digits: general_text['sas_soloDigitos']
    });
    validateForm();
    //show modal to add contact
    $('#btn-add-contact').on('click', function () {
        clearForm('form-contact');
        $('#idContactoComiteOrganizador').val(0);
        $('#add-contact').attr('data-action', "new");
        $('#modal-add-contact').find('h4').text(section_text['sas_agregarContacto']);
        $('#modal-add-contact').find('#add-contact').text(general_text['sas_agregar']);
        if ([1, 2].indexOf(user['idTipoUsuario']) < 0) {
            $("#idComiteOrganizador option[value=1]").attr("disabled", "disabled");
        }
        $('#modal-add-contact').modal("open");
    });
    //show modal to edit contact
    $(document).on('click', '.edit-record', function () {
        clearForm('form-contact');
        var id = $(this).attr('data-id');
        if ([1, 2].indexOf(user['idTipoUsuario']) < 0 && parseInt(coPe[id]['idComiteOrganizador']) === 1) {
            show_alert("info", section_text['sas_noEditar'] + " " + coPe[id]["Nombre"]);
            return;
        }
        $('#idContactoComiteOrganizador').val(id);
        $('#idComiteOrganizador').val(coPe[id]['idComiteOrganizador']);
        $('#Nombre').val(coPe[id]["Nombre"]);
        $('#Email').val(coPe[id]["Email"]);
        $('#Telefono').val(coPe[id]["Telefono"]);
        $('#Puesto').val(coPe[id]["Puesto"]);
        $('#RedSocial').val(coPe[id]["RedSocial"]);
        $('#modal-add-contact').find('h4').text(section_text['sas_editarContacto']);
        $('#modal-add-contact').find('#add-contact').text(general_text['sas_editar']);
        $('#add-contact').attr('data-action', "edit");
        $('#form-contact').find('input').each(function (index, element) {
            if (!$(element).is(':disabled')) {
                $(element).addClass('valid').next().addClass('active');
            }
        });
        if ([1, 2].indexOf(user['idTipoUsuario']) < 0) {
            $("#idComiteOrganizador option[value=1]").attr("disabled", "disabled");
        }
        $('#modal-add-contact').modal("open");
    });
    //show modal to delete contact
    $(document).on('click', '.delete-record', function () {
        var id = $(this).attr('data-id');
        $('#delete-contact').attr('data-id', id);
        $('.pe-nombre').text(coPe[id]['Nombre']);
        $('#modal-delete-contact').modal("open");
    });
    //submit form on click
    $('#add-contact').on('click', function () {
        $('#form-contact').submit();
    });
    //button to delete the contact
    $('#delete-contact').on('click', function () {
        var id = $(this).attr('data-id');
        $('#modal-delete-contact').modal("close");
        show_loader_wrapper();
        formAjax(null, url_delete_contact + '/' + id, 'GET');
    });
}

/**
 * @description Validate the form with validate.js
 */
function validateForm() {
    $('#form-contact').validate({
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
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            $('#modal-add-contact').modal("close");
            var post = $('#form-contact').serializeArray();
            var action = $('#add-contact').attr('data-action');
            post[post.length] = {name: "action", value: action};
            if (action === "new") {
                formAjax(post, url_add_contact);
                return;
            }
            if (action === "edit") {
                var idContact = $('#idContactoComiteOrganizador').val();
                formAjax(post, url_edit_contact + "/" + idContact);
                return;
            }
        }
    });
}

/**
 * @description Use this function to add, edit and delete contacts
 * @param {Array} post All input information to save
 * @param {String} url The url to use for each action
 */
function formAjax(post, url, method) {
    method = (method === undefined) ? "POST" : method;
    $.ajax({
        type: method,
        url: url,
        dataType: 'json',
        data: post,
        success: function (response) {
            if (response['status']) {
                switch (response.data['action']) {
                    case "new":
                        var row = table.row.add([
                            response.data['idContactoComiteOrganizador'],
                            response.data['Nombre'],
                            response.data['Email'],
                            response.data['Telefono'],
                            response.data['Puesto'],
                            response.data['RedSocial'],
                            '<i class="material-icons edit-record tooltipped" data-position="left" data-delay="50" data-tooltip="' + general_text['sas_editar'] + '" data-id="' + response.data['idContactoComiteOrganizador'] + '">mode_edit</i>',
                            '<i class="material-icons delete-record tooltipped" data-position="right" data-delay="50" data-tooltip="' + general_text['sas_eliminar'] + '" data-id="' + response.data['idContactoComiteOrganizador'] + '">delete_forever</i>'
                        ]).draw(false).node();
                        $(row).attr('id', response.data['idContactoComiteOrganizador']);
                        $('.tooltipped').tooltip({delay: 50})
                        updateJson(response.data);
                        show_alert('success', section_text['sas_exitoInformacionGuardada']);
                        break;
                    case "edit":
                        var row = table.row('#' + response.data['idContactoComiteOrganizador']).node();
                        $(row).find('td:nth-child(2)').text(response.data['Nombre']);
                        $(row).find('td:nth-child(3)').text(response.data['Email']);
                        $(row).find('td:nth-child(4)').text(response.data['Telefono']);
                        $(row).find('td:nth-child(5)').text(response.data['Puesto']);
                        $(row).find('td:nth-child(6)').text(response.data['RedSocial']);
                        updateJson(response.data);
                        show_alert('success', section_text['sas_exitoInformacionEditada']);
                        break;
                    case "delete":
                        table.row('#' + response.data['idContactoComiteOrganizador']).remove().draw();
                        show_alert('success', section_text['sas_exitoInformacionEliminada']);
                        break;
                }
            } else {
                show_alert('danger', response.error);
            }
            hide_loader_wrapper();
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            if (post === null) {

                $('#modal-delete-contact').modal("open");
            } else {
                if (post['action'] === "new" || post['action'] === "edit") {
                    $('#modal-add-contact').modal("open");
                }
            }
            show_modal_error(request.responseText);
        }
    });
}

/**
 * @description Update the json of persomal
 * @param {Array} post all data of send at server
 */
function updateJson(post) {
    coPe[post['idContactoComiteOrganizador']] = {
        "idContactoComiteOrganizador": post['idContactoComiteOrganizador'],
        "Nombre": post['Nombre'],
        "Email": post['Email'],
        "Telefono": post['Telefono'],
        "Puesto": post['Puesto'],
        "RedSocial": post['RedSocial']
    }
}

/**
 * @description Clear all inputs of a form except hidden and disabled inputs
 * @param {String} idForm Get a id of form to clear it
 */
function clearForm(idForm) {
    $('#' + idForm).find('input').each(function (index, element) {
        if (!$(element).is(':disabled')) {
            $(element).removeClass('valid').next().removeClass('active');
        }
    });
    $('#' + idForm).find('input[type="text"]').not('input[type="text"]:disabled').val("");
    $('#' + idForm).find('input[type="email"]').not('input[type="email"]:disabled').val("");
    $('#' + idForm).find('input[type="tel"]').not('input[type="tel"]:disabled').val("");
    $('#' + idForm).find('textarea').not('textarea:disabled').val("");
    $('#' + idForm).find('select').not('select:disabled').val("");
    $('#' + idForm).find('input[type="checkbox"] input[type="radio"]').not('input[type="checkbox"]:disabled input[type="radio"]:disabled').prop('checked', false);
}

