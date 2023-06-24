/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(init);

function init() {
    initForm();
    $('#tbl-template-texto').DataTable({
        "language": {
            "url": url_lang
        }
    });
}

function initForm() {
    $("#frm-template-texto").validate({
        rules: {
            'idTemplate': {
                required: true
            },
            'Etiqueta': {
                required: true
            }
        },
        messages: {
            'idTemplate': {
                required: general_text.sas_requerido,
            },
            'Etiqueta': {
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
    $("#modal-template-texto").openModal();
});

$(document).on('click', '.edit-record', function () {
    clearForm();
    if (!isset(templateTextos[$(this).attr("id-record")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }

    var texto = templateTextos[$(this).attr("id-record")];

    $('#frm-template-texto #idTemplateTexto').val(texto['idTemplateTexto']);
    $('#frm-template-texto #idTemplate').val(texto['idTemplate']);
    $('#frm-template-texto #Etiqueta').val(texto['Etiqueta']);
    $('#frm-template-texto #TextoES').val(texto['TextoES']);
    $('#frm-template-texto #TextoEN').val(texto['TextoEN']);
    Materialize.updateTextFields();
    $("#modal-template-texto").openModal();
});

function clearForm() {
    $('#frm-template-texto')[0].reset();
    Materialize.updateTextFields();
}

$(document).on('click', '.delete-record', function () {
    var id = $(this).attr('id-record');
    if (!isset(templateTextos[$(this).attr("id-record")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }

    var texto = templateTextos[$(this).attr("id-record")];

    $('#frm-template-texto-eliminar #idTemplateTexto').val(texto['idTemplateTexto']);
    $('#frm-template-texto-eliminar .template-texto').text(texto['Etiqueta']);
    $('#modal-delete-template-texto').openModal();
});