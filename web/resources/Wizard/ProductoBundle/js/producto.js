/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(init);

function init() {
    initFormProducto();
}

function initFormProducto() {
    var formID = "#frm-producto";
    $(formID).validate({
        rules: {
            'idPlataformaIxpo[]': {
                required: true
            },
            'idEdicion': {
                required: true
            }
        },
        messages: {
            'idPlataformaIxpo[]': {
                required: general_text.sas_requerido
            },
            'idEdicion': {
                required: general_text.sas_requerido
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

$('input[name="idPlataformaIxpo[]"]').change(changePlataforma);

function aplicarEdicionProductoIxpo(productos) {
    if (Object.keys(productos).length === 0) {
        return;
    }
    $.each(productos, function (key, value) {
        var inputIdPlataforma = $('#producto-' + value['idProductoIxpo']).parents('.mpi-plataforma-content').find('input[name="idPlataformaIxpo[]"]');
        if (!inputIdPlataforma.is("checked")) {
            inputIdPlataforma.trigger("click");
        }
        $('#producto-' + value['idProductoIxpo']).trigger("click");
    });
}

function changePlataforma() {
    if ($(this).is(":checked")) {
        $('.mpi-plataforma-content[id-plataforma=' + $(this).val() + '] .mpi-productos-container').slideDown();
        $('.mpi-plataforma-content[id-plataforma=' + $(this).val() + '] input[type="radio"]').removeAttr("disabled");
        $('.mpi-plataforma-content[id-plataforma=' + $(this).val() + '] input[type="radio"]:first').trigger("click");
    } else {
        $('.mpi-plataforma-content[id-plataforma=' + $(this).val() + '] input[type="radio"]').attr("disabled", "disabled");
        $('.mpi-plataforma-content[id-plataforma=' + $(this).val() + '] .mpi-productos-container').slideUp();
    }
}

$("#idEdicion").change(changeEdicion);

function changeEdicion() {
    var idEvento = $("#idEdicion option:selected").attr("id-evento");
    $("#idEvento").val(idEvento);
    $('input[name="idPlataformaIxpo[]"]').prop("checked", false).change();
    if (isset(edicionProducto[$("#idEdicion").val()])) {
        aplicarEdicionProductoIxpo(edicionProducto[$("#idEdicion").val()]);
    }
}