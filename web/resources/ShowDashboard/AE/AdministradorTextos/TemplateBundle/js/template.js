/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(init);

function init() {
    initForm();
    $('#tbl-template').DataTable({
        "language": {
            "url": url_lang
        }}
    );
}

function initForm() {
    $("#frm-template").validate({
        rules: {
            'idProductoIxpo': {
                required: true
            },
            'idModulo': {
                required: true
            },
            'idVisitanteTipo': {
                required: true
            },
            'Template': {
                required: true
            }
        },
        messages: {
            'idProductoIxpo': {
                required: general_text.sas_requerido,
            },
            'idModulo': {
                required: general_text.sas_requerido,
            },
            'idVisitanteTipo': {
                required: general_text.sas_requerido,
            },
            'Template': {
                required: general_text.sas_requerido,
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
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            form.submit();
        }
    });
}

$(".add-record").on("click", function () {
    clearForm();
    $("#modal-template").openModal();
});

$(document).on('click', '.edit-record', function () {
    clearForm();
    if (!isset(templates[$(this).attr("id-record")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }

    var template = templates[$(this).attr("id-record")];

    $('#frm-template #idTemplate').val(template['idTemplate']);
    $('#frm-template #idProductoIxpo').val(template['idProductoIxpo']);
    $('#frm-template #idModuloIxpo').val(template['idModuloIxpo']);
    $('#frm-template #idVisitanteTipo').val(template['idVisitanteTipo']);
    $('#frm-template #Template').val(template['Template']);
    Materialize.updateTextFields();
    $("#modal-template").openModal();
});

function clearForm() {
    $('#frm-template #idTemplate').val("");
    $('#frm-template #idProductoIxpo').val("");
    $('#frm-template #idModuloIxpo').val("");
    $('#frm-template #idVisitanteTipo').val($("#frm-template #idVisitanteTipo option:first").val());
    $('#frm-template #Template').val("");
    Materialize.updateTextFields();
}

$(document).on('click', '.delete-record', function () {
    var id = $(this).attr('id-record');
    if (!isset(templates[$(this).attr("id-record")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }

    var template = templates[$(this).attr("id-record")];

    $('#frm-template-eliminar #idTemplate').val(template['idTemplate']);
    $('#frm-template-eliminar .template').text(template['Template']);
    $('#modal-delete-template').openModal();
});