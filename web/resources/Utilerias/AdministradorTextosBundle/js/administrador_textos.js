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
    generateTable('table-text', "0");
    //validate form and rules of validate
    jQuery.extend(jQuery.validator.messages, {
        required: 'Campo requerido.'
    });
    validateForm();
    //show modal to add text
    $('#btn-add-text').on('click', function () {
        clearForm('form-text');
        $('#idTexto').val(0);
        $('#idSeccion').val($('#Seccion').val());
        $('#add-text').attr('data-action', "new");
        $('#modal-add-text').find('h4').text('Agregar Texto');
        $('#modal-add-text').find('#add-text').text('Agregar');
        $('#modal-add-text').openModal({dismissible: false});
    });
    //show modal to edit text
    $(document).on('click', '.edit-record', function () {
        clearForm('form-text');
        var id = $(this).attr('data-id');
        $('#idTexto').val(id);
        $('#idSeccion').val($('#Seccion').val());
        $('#Etiqueta').val(texts[$('#Seccion').val()][id]['Etiqueta']).next().addClass('active');
        $('#Texto_EN').val(texts[$('#Seccion').val()][id]['Texto_EN']).next().addClass('active');
        $('#Texto_ES').val(texts[$('#Seccion').val()][id]['Texto_ES']).next().addClass('active');
        $('#Texto_FR').val(texts[$('#Seccion').val()][id]['Texto_FR']).next().addClass('active');
        $('#Texto_PT').val(texts[$('#Seccion').val()][id]['Texto_PT']).next().addClass('active');
        $('#modal-add-text').find('h4').text("Editar Texto");
        $('#modal-add-text').find('#add-text').text("Editar");
        $('#add-text').attr('data-action', "edit");
        $('#form-text').find('input, textarea').each(function (index, element) {
            if (!$(element).is(':disabled') && $(element).val() !== "") {
                $(element).addClass('valid').next().addClass('active');
            }
        });
        $('#modal-add-text').openModal({dismissible: false});
    });
    //show modal to delete text
    $(document).on('click', '.delete-record', function () {
        var id = $(this).attr('data-id');
        $('#delete-text').attr('data-id', id);
        $('.tx-nombre').text(texts[$('#Seccion').val()][id]['Etiqueta']);
        $('#modal-delete-text').openModal();
    });
    //submit form on click
    $('#add-text').on('click', function () {
        $('#form-text').submit();
    });
    //button to delete the text
    $('#delete-text').on('click', function () {
        var id = $(this).attr('data-id');
        var section = $('#Seccion').val();
        $('#modal-delete-text').modal("close");
        show_loader_wrapper();
        formAjax(null, url_delete_text + '/' + section + '/' + id, 'GET');
    });
    //change type text
    $(document).on('change', '#Seccion', function () {
        changeTable($(this).val());
    });
}
/**
 * @description Validate the form with validate.js
 */
function validateForm() {
    $('#form-text').validate({
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
            $('#modal-add-text').modal("close");
            var post = $(form).serializeArray();
            var action = $('#add-text').attr('data-action');
            post[post.length] = {name: "action", value: action};
            if (action === "new") {
                formAjax(post, url_add_text);
                return;
            }
            if (action === "edit") {
                var idText = $('#idTexto').val();
                formAjax(post, url_edit_text + "/" + idText);
                return;
            }
        }
    });
}

/**
 * @description Use this function to add, edit and delete texts
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
                        updateJson(response.data);
                        if ($('#Seccion').val() == $('#idSeccion').val()) {
                            var row = table.row.add([
                                response.data['idTexto'],
                                response.data['Etiqueta'],
                                response.data['Texto_EN'],
                                response.data['Texto_ES'],
                                response.data['Texto_FR'],
                                response.data['Texto_PT'],
                                '<i class="material-icons edit-record tooltipped" data-position="left" data-delay="50" data-tooltip="Editar" data-id="' + response.data['idTexto'] + '">mode_edit</i>',
                                '<i class="material-icons delete-record tooltipped" data-position="right" data-delay="50" data-tooltip="Eliminar" data-id="' + response.data['idTexto'] + '">delete_forever</i>'
                            ]).draw(false).node();
                            $(row).attr('id', response.data['idTexto']);
                            $('.tooltipped').tooltip({delay: 50});
                        }
                        show_alert('success', 'El texto ha sido guardado correctamente.');
                        break;
                    case "edit":
                        updateJson(response.data);
                        if ($('#Seccion').val() == $('#idSeccion').val()) {
                            var row = table.row('#' + response.data['idTexto']).node();
                            $(row).find('td:nth-child(2)').text(response.data['Etiqueta']);
                            $(row).find('td:nth-child(3)').text(response.data['Texto_EN']);
                            $(row).find('td:nth-child(4)').text(response.data['Texto_ES']);
                            $(row).find('td:nth-child(5)').text(response.data['Texto_FR']);
                            $(row).find('td:nth-child(6)').text(response.data['Texto_PT']);
                        } else {
                            delete texts[$('#Seccion').val()][response.data['idTexto']];
                            table.row('#' + response.data['idTexto']).remove().draw();
                            changeTable($('#idSeccion').val());
                        }
                        show_alert('success', 'El texto ha sido editado correctamente.');
                        break;
                    case "delete":
                        table.row('#' + response.data['idTexto']).remove().draw();
                        delete texts[$('#Seccion').val()][response.data['idTexto']];
                        show_alert('success', 'Su texto ha sido eliminado correctamente.');
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
                $('#modal-delete-text').openModal();
            } else {
                if (post['action'] === "new" || post['action'] === "edit") {
                    $('#modal-add-text').openModal();
                }
            }
            show_modal_error(request.responseText);
        }
    });
}

/**
 * @description cahnge the table for type of text selected
 * @param {text} idSection is a id of a section to show in a table
 */
function changeTable(idSection) {
    table.destroy();
    table = null;
    $('#table-text').find("tbody tr").remove();
    $.each(texts[idSection], function (id, value) {
        var tr = "", td = "";
        tr = document.createElement('tr');
        tr.id = id;
        td = document.createElement('td');
        $(td).text(id);
        tr.appendChild(td);
        td = document.createElement('td');
        $(td).text(value['Etiqueta']);
        tr.appendChild(td);
        td = document.createElement('td');
        $(td).text(value['Texto_EN']);
        tr.appendChild(td);
        td = document.createElement('td');
        $(td).text(value['Texto_ES']);
        tr.appendChild(td);
        td = document.createElement('td');
        $(td).text(value['Texto_FR']);
        tr.appendChild(td);
        td = document.createElement('td');
        $(td).text(value['Texto_PT']);
        tr.appendChild(td);
        td = document.createElement('td');
        $(td).html('<i class="material-icons edit-record tooltipped" data-position="left" data-delay="50" data-tooltip="Ediar" data-id="' + id + '">mode_edit</i>');
        tr.appendChild(td);
        td = document.createElement('td');
        $(td).html('<i class="material-icons delete-record tooltipped" data-position="right" data-delay="50" data-tooltip="Eliminar" data-id="' + id + '">delete_forever</i>');
        tr.appendChild(td);
        $('#table-text').find("tbody").append(tr);
    });
    generateTable('table-text', idSection);
    $('.tooltipped').tooltip({delay: 50});
}

/**
 * @description Costumize table
 * @param {text} id recibe the id of table
 * @param {bool} addSelect is a boolean variable to show the select of type of text
 * @return {null}
 */
function generateTable(id, idSection) {
    table = $('#' + id).DataTable({
        "language": {
            "url": url_lang
        }
    });
    var select = '<div class="col s6"><select id="Seccion" name="Seccion">';
    $.each(Sections, function (id, section) {
        var selected = (id === idSection) ? "selected" : "";
        select = select + '<option value="' + id + '" ' + selected + '>' + section + '</option>';
    });
    select = select + '</select></div>';
    setTimeout(function () {
        $('#table-text_wrapper > div .s6').before(select);
        $('#table-text_wrapper > div .s6').removeClass('offset-s6');
        $('select').material_select();
    }, 500);
}


/**
 * @description Update the json of persomal
 * @param {Array} post all data of send at server
 */
function updateJson(post) {
    texts[$('#idSeccion').val()][post['idTexto']] = {
        "Etiqueta": post['Etiqueta'],
        "Seccion": post[$('#Seccion').val()],
        "Texto_EN": post['Texto_EN'],
        "Texto_ES": post['Texto_ES'],
        "Texto_FR": post['Texto_FR'],
        "Texto_PT": post['Texto_PT'],
        "idTexto": post['idTexto']
    };
}

/**
 * @description Clear all inputs of a form except hidden and disabled inputs
 * @param {String} idForm Get a id of form to clear it
 */
function clearForm(idForm) {
    $('#' + idForm).find('input textarea').each(function (index, element) {
        if (!$(element).is(':disabled')) {
            $(element).removeClass('valid').next().removeClass('active');
        }
    });
    $('#' + idForm).find('input[type="text"]').not('input[type="text"]:disabled').val("");
    $('#' + idForm).find('input[type="email"]').not('input[type="email"]:disabled').val("");
    $('#' + idForm).find('input[type="tel"]').not('input[type="tel"]:disabled').val("");
    $('#' + idForm).find('textarea').not(':disabled').val("");
    $('#' + idForm).find('select').not('select:disabled').val("");
    $('#' + idForm).find('input[type="checkbox"] input[type="radio"]').not('input[type="checkbox"]:disabled input[type="radio"]:disabled').prop('checked', false);
}
