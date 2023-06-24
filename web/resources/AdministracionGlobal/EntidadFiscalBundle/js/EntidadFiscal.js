$(init);
var table = null;

$(".add-record").on("click", function () {
    clearForm('frm_billing_entity');
    $('#add_entity').attr('data-action', "insert");
    $("#mdl_billing_entity").modal("open");
});

$("#add_entity").on("click", function () {
    $("#frm_billing_entity").submit();
});

$(document).on('click', '.edit-record', function () {
    clearForm('frm_billing_entity');
    $('#add_entity').attr('data-action', "update");
    var id = $(this).attr("id-record");
    $('#RazonSocial').val(co['entity'][id]['RazonSocial']);
    $('#RFC').val(co['entity'][id]['RFC']);
    $('#RepresentanteLegal').val(co['entity'][id]['RepresentanteLegal']);
    $('#Email').val(co['entity'][id]['Email']);
    $('#CodigoPostal').val(co['entity'][id]['CodigoPostal']);
    $('#idPais option[value=' + co['entity'][id]['idPais'] + ']').prop('selected', 'selected');
    getEstados(id);
    $('#Ciudad').val(co['entity'][id]['Ciudad']);
    $('#Colonia').val(co['entity'][id]['Colonia']);
    $('#Delegacion').val(co['entity'][id]['Delegacion']);
    $('#Calle').val(co['entity'][id]['Calle']);
    $('#NumeroInterior').val(co['entity'][id]['NumeroInterior']);
    $('#NumeroExterior').val(co['entity'][id]['NumeroExterior']);
    $('#id').val(id);
    $(".input-field label").addClass("active");
    $('#mdl_billing_entity').modal("open");
});

$(document).on('click', '.delete-record', function () {
    var id = $(this).attr('id-record');
    $('#delete_entity').attr('id-record', id);
    $('#modal_delete').modal("open");
});

$('#delete_entity').on('click', function () {
    var id = $(this).attr('id-record');
    $('#modal_delete').modal("close");
    show_loader_wrapper();
    DeleteData(id);
});

function init() {
    table = $('#tbl_billing_entity').DataTable({
        "language": {
            "url": url_lang
        }});
    $('select').material_select();
    initForm();
}

function initForm() {
    $("#frm_billing_entity").validate({
        rules: {
            'RazonSocial': {
                required: true,
                maxlength: 100
            },
            'RFC': {
                required: true,
                maxlength: 20
            },
            'RepresentanteLegal': {
                required: true,
                maxlength: 200
            },
            'Email': {
                required: true,
                email: true,
                maxlength: 100
            },
            'idPais': {
                required: true,
            },
            'idEstado': {
                required: true,
            },
            'Ciudad': {
                required: true,
                maxlength: 50
            },
            'Delegacion': {
                required: true,
                maxlength: 50
            },
            'Colonia': {
                required: true,
                maxlength: 50
            },
            'Calle': {
                required: true,
                maxlength: 50
            },
            'NumeroExterior': {
                required: true,
                maxlength: 10,
                digits: true
            },
            'NumeroInterior': {
                maxlength: 10,
                digits: true
            },
            'CodigoPostal': {
                required: true,
                maxlength: 10,
                digits: true
            },
        },
        messages: {
            'RazonSocial': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'RFC': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'RepresentanteLegal': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'Email': {
                required: general_text.sas_requerido,
                email: general_text.sas_ingresaCorreoValido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'idPais': {
                required: general_text.sas_requerido,
            },
            'idEstado': {
                required: general_text.sas_requerido,
            },
            'Ciudad': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'Delegacion': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'Colonia': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'Calle': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'NumeroExterior': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
                digits: general_text.sas_soloDigitos
            },
            'NumeroInterior': {
                maxlength: general_text.sas_ingresaMaxCaracteres,
                digits: general_text.sas_soloDigitos
            },
            'CodigoPostal': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
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
            $('#mdl_billing_entity').modal("close");
            var post = $('#frm_billing_entity').serializeArray();
            var action = $('#add_entity').attr('data-action');
            post[post.length] = {name: "action", value: action};
            if (action === "insert") {
                saveData();
                return;
            }
            if (action === "update") {
                var idEntity = $('#id').val();
                UpdateData(idEntity);
                return;
            }
        }
    });
}

function saveData() {
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_insert_entity, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: $("#frm_billing_entity").serialize(), // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_modal_error(response['data']);
                return;
            } else {
                if (!response['status_aux']) {
                    $('.tooltipped').tooltip({delay: 50});
                    show_alert('danger', response['message']);
                } else {
                    updateJson(response.data);
                    setRow(response.data, "insert");
                    $('.tooltipped').tooltip({delay: 50});
                    show_alert('success', response['message']);
                }
            }

        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function UpdateData(idEntity) {
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_update_entity + "/" + idEntity, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: $("#frm_billing_entity").serialize(), // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_modal_error(response['data']);
                return;
            } else {
                if (!response['status_aux']) {
                    $('.tooltipped').tooltip({delay: 50});
                    show_alert('danger', response['message']);
                } else {
                    updateJson(response.data);
                    setRow(response.data, "update");
                    $('.tooltipped').tooltip({delay: 50});
                    show_alert('success', response['message']);
                }
            }
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}
function DeleteData(id) {
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_delete_entity + '/' + id, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_modal_error(response['data']);
                return;
            }
            setRow(response.data, "delete");
            $('.tooltipped').tooltip({delay: 50});
            show_alert('success', 'Datos eliminados correctamente.');
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();

}
function setRow(data, action) {
    if (action === 'insert') {
        var row = table.row.add([
            data.idEntidadFiscal,
            data.RazonSocial,
            data.RFC,
            data.RepresentanteLegal,
            data.Email,
            '<i class="material-icons edit-record" id-record="' + data.idEntidadFiscal + '">mode_edit</i>',
            '<i class="material-icons delete-record" id-record="' + data.idEntidadFiscal + '">delete_forever</i>'
        ]).draw().node();
        $(row).attr('id', data.idEntidadFiscal);
    }
    if (action === 'update') {
        var row = table.row('#' + data.idEntidadFiscal).node();
        $(row).find('td:nth-child(2)').text(data.RazonSocial);
        $(row).find('td:nth-child(3)').text(data.RFC);
        $(row).find('td:nth-child(4)').text(data.RepresentanteLegal);
        $(row).find('td:nth-child(5)').text(data.Email);
    }
    if (action === 'delete') {
        table.row('#' + data.idEntidadFiscal).remove().draw();
    }

}
function updateJson(post) {
    co['entity'][post['idEntidadFiscal']] = {
        "RazonSocial": post['RazonSocial'],
        "RFC": post['RFC'],
        "RepresentanteLegal": post['RepresentanteLegal'],
        "Email": post['Email'],
        "Pais": post['Pais'],
        "idPais": post['idPais'],
        "Estado": post['Estado'],
        "idEstado": post['idEstado'],
        "Ciudad": post['Ciudad'],
        "Delegacion": post['Delegacion'],
        "Colonia": post['Colonia'],
        "Calle": post['Calle'],
        "NumeroExterior": post['NumeroExterior'],
        "NumeroInterior": post['NumeroInterior'],
        "CodigoPostal": post['CodigoPostal']
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
    $('#' + idForm).find('select#idEstado').html('<option value="">' + general_text['sas_sinOpcion'] + '</option>');
    $('#' + idForm).find('input[type="checkbox"] input[type="radio"]').not('input[type="checkbox"]:disabled input[type="radio"]:disabled').prop('checked', false);
}


$("#idPais").on("change", function () {
    var id, loader = $(this).attr('loader-element');
    getEstados(id, loader);

});

//$("select#idPais").change(getEstados);

function getEstados(id, loader) {
    var idPais = $("select#idPais").val();
    var url = url_get_estados.replace("0000", idPais);
    var loader_element = loader;
    showProgressBar(loader_element);
    $.ajax({
        type: "get",
        url: url,
        dataType: 'json',
        beforeSend: function (xhr) {
            setProgressBar(loader_element, "50%");
        },
        success: function (result) {
            setProgressBar(loader_element, "70%");
            if (!result['status']) {
                show_modal_error(result['data']);
                hideProgressBar(loader_element);
                return;
            }
            var estados = result['data'];
            if (estados.length === 0) {
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

            if (id !== undefined) {
                $('#idEstado option[value=' + co['entity'][id]['idEstado'] + ']').prop('selected', 'selected');
            }
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
        beforeSend: function (xhr) {
            setProgressBar(loader_element, "50%");
        },
        success: function (result) {
            setProgressBar(loader_element, "70%");
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
    $('#Delegacion').focus();
}

function setProgressBar(progressBar, progress) {
    $(progressBar + " .determinate").attr("style", "width: " + progress);
}

function showProgressBar(progressBar) {
    $('[loader-element="' + progressBar + '"]').attr('disabled', 'disabled');
    $(progressBar + " .determinate").attr("style", "width: 0%");
    $(progressBar + " .progress").fadeIn("fast");
}

function hideProgressBar(progressBar) {
    $(progressBar + " .determinate").attr("style", "width: 100%");
    setTimeout(function () {
        $(progressBar + " .progress").fadeOut("fast");
    }, 250);
    $('[loader-element="' + progressBar + '"]').removeAttr('disabled');
}

