/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(init);
function init() {
    initFormServicio();
    $('.tooltipped').tooltip({delay: 50});
}

var editorOptions = {
    language: lang,
    toolbarButtons: [
        'fontFamily', 'fontSize',
        '|',
        'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', 'color',
        '|',
        'align', 'inlineStyle', 'formatOL', 'formatUL', 'outdent', 'indent',
        '-',
        'paragraphFormat', 'quote', 'insertHR', 'insertLink', 'insertTable', 'insertImage',
        '|',
        'undo', 'redo', 'fullscreen', 'html'],
    imageUploadURL: url_upload_image,
    imageMaxSize: 2097152,
    imagePaste: true,
    imageMove: true,
    imageAllowedTypes: ['jpeg', 'jpg', 'png'],
    imageEditButtons: ['imageDisplay', 'imageAlign', 'imageInfo', 'imageRemove'],
    fontFamilySelection: true,
    fontFamily: {
        "Roboto,sans-serif": 'Roboto',
        'Arial,Helvetica,sans-serif': 'Arial',
        'Georgia,serif': 'Georgia',
        'Impact,Charcoal,sans-serif': 'Impact',
        'Tahoma,Geneva,sans-serif': 'Tahoma',
        "'Times New Roman',Times,serif": 'Times New Roman',
        'Verdana,Geneva,sans-serif': 'Verdana'
    },
    colorsText: [
        '#ffcdd2', '#ef9a9a', '#ef5350', '#ff0000', '#e53935', '#c62828', '#b71c1c',
        '#e1bee7', '#ce93d8', '#ab47bc', '#9c27b0', '#8e24aa', '#6a1b9a', '#4a148c',
        '#d1c4e9', '#b39ddb', '#7e57c2', '#673ab7', '#5e35b1', '#4527a0', '#311b92',
        '#bbdefb', '#90caf9', '#42a5f5', '#2196f3', '#1e88e5', '#1565c0', '#0d47a1',
        '#b2ebf2', '#80deea', '#26c6da', '#00bcd4', '#00acc1', '#00838f', '#006064',
        '#c8e6c9', '#a5d6a7', '#66bb6a', '#4caf50', '#43a047', '#2e7d32', '#1b5e20',
        '#dcedc8', '#c5e1a5', '#9ccc65', '#8bc34a', '#7cb342', '#558b2f', '#33691e',
        '#fff9c4', '#fff59d', '#ffee58', '#ffeb3b', '#fdd835', '#f9a825', '#f57f17',
        '#ffe0b2', '#ffcc80', '#ffa726', '#ff9800', '#fb8c00', '#ef6c00', '#e65100',
        '#ffffff', '#eeeeee', '#bdbdbd', '#9e9e9e', '#757575', '#424242', '#000000',
        'REMOVE'
    ],
    colorsBackground: [
        '#ffcdd2', '#ef9a9a', '#ef5350', '#ff0000', '#e53935', '#c62828', '#b71c1c',
        '#e1bee7', '#ce93d8', '#ab47bc', '#9c27b0', '#8e24aa', '#6a1b9a', '#4a148c',
        '#d1c4e9', '#b39ddb', '#7e57c2', '#673ab7', '#5e35b1', '#4527a0', '#311b92',
        '#bbdefb', '#90caf9', '#42a5f5', '#2196f3', '#1e88e5', '#1565c0', '#0d47a1',
        '#b2ebf2', '#80deea', '#26c6da', '#00bcd4', '#00acc1', '#00838f', '#006064',
        '#c8e6c9', '#a5d6a7', '#66bb6a', '#4caf50', '#43a047', '#2e7d32', '#1b5e20',
        '#dcedc8', '#c5e1a5', '#9ccc65', '#8bc34a', '#7cb342', '#558b2f', '#33691e',
        '#fff9c4', '#fff59d', '#ffee58', '#ffeb3b', '#fdd835', '#f9a825', '#f57f17',
        '#ffe0b2', '#ffcc80', '#ffa726', '#ff9800', '#fb8c00', '#ef6c00', '#e65100',
        '#ffffff', '#eeeeee', '#bdbdbd', '#9e9e9e', '#757575', '#424242', '#000000',
        'REMOVE'
    ]
};
if ($("#FechaLimite").length > 0) {
    var deadLine = new Cleave('#FechaLimite', {
        date: true,
        datePattern: ['Y', 'm', 'd']
    });
}

function showLeanOverlay(froalaElement) {
    var sectionForm = froalaElement.parents(".form-section");
    var froalaControls = $('<div/>', {
        "class": 'froala-controls',
    });
    var btnUndo = $('<a/>', {
        "class": 'btn-cancel-editing btn-floating waves-effect white tooltipped',
        "data-tooltip": section_text['ed_cancelarEdicion']
    }).appendTo(froalaControls);
    $('<i/>', {
        "class": 'material-icons blue-text',
        "text": "undo"
    }).appendTo(btnUndo);
    var btnSave = $('<a/>', {
        "class": 'btn-save-editor btn-floating waves-effect waves-light green tooltipped',
        "data-tooltip": general_text['sas_guardar']
    }).appendTo(froalaControls);
    $('<i/>', {
        "class": 'material-icons',
        "text": "cloud_upload"
    }).appendTo(btnSave);
    sectionForm.find(".froala-editor").after(froalaControls);
    $('.tooltipped').tooltip({delay: 50});
}

function hideLeanOverlay() {
    $(".froala-controls").remove();
}

$(document).on("click", ".editor", showEditor);
function showEditor() {
    if ($('.editing').length > 0) {
        Materialize.toast(general_text['sas_estasEditando'], 4000);
        return;
    }
    showLeanOverlay($(this));
    var customClass = $.trim($(this).attr('class').replace("editor", ""));
    $(this).removeClass("editor").addClass("editing").unbind('click');
    $(this).find(".froala-editor").froalaEditor(editorOptions)
            .on('froalaEditor.image.error', function (e, editor, error, response) {
                show_toast("warning", error.message);
            }).on('froalaEditor.image.removed', function (e, editor, $img) {
        var img = $img[0].src;
        var ary = img.split("/");
        $.ajax({
            method: "POST",
            url: url_delete_image,
            data: {
                name: ary[ary.length - 1]
            },
            success: function (result) {
                show_toast("success", general_text.sas_eliminoExito);
            },
            error: function (request, status, error) {
                console.log("error image no deleted");
            }
        });
    });
    $('a[href="https://www.froala.com/wysiwyg-editor?k=u"]').remove();
}

$(document).on("click", ".btn-cancel-editing", cancelEditing);
function cancelEditing(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).tooltip('remove');
    var editor = $(".editing");
    var froalaEditor = $("#" + editor.attr("id") + " .froala-editor");
    var html = froalaEditor.data('froala.editor')._original_html;
    froalaEditor.html(html);
    froalaEditor.froalaEditor('destroy');
    editor.removeClass("editing").addClass("editor");
    hideLeanOverlay();
}

$(document).on("click", ".btn-save-editor", saveEditing);
function saveEditing(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).tooltip('remove');
    var btn = $(this);
    var editor = btn.parents(".editing");
    var froalaEditor = $("#" + editor.attr("id") + " .froala-editor");
    var html = froalaEditor.froalaEditor('html.get').replace("'", "''");
    hideLeanOverlay();
    showLoaderEditor(editor.attr("id"));
    $.ajax({
        type: "post",
        url: url_save_html,
        dataType: 'json',
        data: {"Etiqueta": editor.attr("id"), "Texto": html},
        success: function (result) {
            html = html.replace("''", "'");
            hideLoaderEditor(editor.attr("id"));
            if (!result['status']) {
                html = froalaEditor.data('froala.editor')._original_html;
                froalaEditor.html(html);
                froalaEditor.froalaEditor('destroy');
                editor.removeClass("editing").addClass("editor");
                show_alert("danger", result['data']);
                return;
            }
            editor.removeClass("editing").addClass("editor");
            froalaEditor.froalaEditor('destroy');
            froalaEditor.html(html);
            show_alert("success", general_text['sas_guardoExito']);
        },
        error: function (request, status, error) {
            hideLoaderEditor(editor.attr("id"));
            showLeanOverlay(editor);
            show_modal_error(request.responseText);
        }
    });
}

function showLoaderEditor(seccion) {
    if ($("#" + seccion + " .fr-box").length === 0) {
        return;
    }

    var editor = $("#" + seccion);
    editor.find(".btn-save-editor").attr("disabled", "disabled");
    var savingEditor = $('<div/>', {"class": 'saving-editor'}).prependTo(editor);
    var progress = $('<div/>', {"class": 'progress'}).appendTo(savingEditor);
    $('<div/>', {"class": 'indeterminate'}).appendTo(progress);
}

function hideLoaderEditor(seccion) {
    var editor = $("#" + seccion);
    editor.find(".btn-save-editor").prop("disabled", false);
    editor.find(".saving-editor").remove();
}

/*$(document).on("click", ".btn-preview-form", previewForm);

 function previewForm() {
 if ($('.editing').length > 0) {
 Materialize.toast(general_text['sas_estasEditando'], 4000);
 return;
 }

 var html = $(".container-editor").html();
 $("#modal-preview-form .modal-content").html(html);
 var htmlForma = $("#modal-preview-form .modal-content");
 htmlForma.find(".froala-editor").each(function () {
 var card = $(this).parents(".card");
 if ($(this).html() === "") {
 //$(this).parents(".form-section").find(".form-section-subheader").remove();
 $(this).remove();
 if (card.find(".froala-editor").length === 0) {
 card.remove();
 }
 }
 });

 htmlForma.find(".editor").removeClass("editor");
 htmlForma.find(".form-section-subheader").remove();
 $('#modal-preview-form').modal("open");
 }*/

/* Servicios */

function initFormServicio() {
    var formID = "#frm-servicio";
    $(formID).validate({
        rules: {
            'TituloES': {
                required: true
            },
            'TituloEN': {
                required: true
            },
            'DescripcionES': {
                required: true
            },
            'DescripcionEN': {
                required: true
            },
            'PrecioAntesFechaES': {
                required: true,
                number: true,
                min: 0
            },
            'PrecioAntesFechaEN': {
                required: true,
                number: true,
                min: 0
            },
            'FechaLimite': {
                required: {
                    depends: function (element) {
                        return $("#FechaLimiteCheck").is(":checked");
                    }
                }
            },
            'PrecioDespuesFechaES': {
                required: {
                    depends: function (element) {
                        return $("#FechaLimiteCheck").is(":checked");
                    }
                }
            },
            'PrecioDespuesFechaEN': {
                required: {
                    depends: function (element) {
                        return $("#FechaLimiteCheck").is(":checked");
                    }
                }
            },
            'MonedaEN': {
                required: true
            },
            'MonedaES': {
                required: true
            },
            'Orden': {
                required: true,
                digits: true,
                min: 0
            }
        },
        messages: {
            'TituloES': {
                required: general_text.sas_requerido
            },
            'TituloEN': {
                required: general_text.sas_requerido
            },
            'DescripcionES': {
                required: general_text.sas_requerido
            },
            'DescripcionEN': {
                required: general_text.sas_requerido
            },
            'PrecioAntesFechaES': {
                required: general_text.sas_requerido,
                number: general_text.sas_cantidadValida,
                min: general_text.sas_cantidadMayor
            },
            'PrecioAntesFechaEN': {
                required: general_text.sas_requerido,
                number: general_text.sas_cantidadValida,
                min: general_text.sas_cantidadMayor
            },
            'FechaLimite': {
                required: general_text.sas_requerido
            },
            'PrecioDespuesFechaES': {
                required: general_text.sas_requerido
            },
            'PrecioDespuesFechaEN': {
                required: general_text.sas_requerido
            },
            'MonedaEN': {
                required: general_text.sas_requerido
            },
            'MonedaES': {
                required: general_text.sas_requerido
            },
            'Orden': {
                required: general_text.sas_requerido,
                digits: general_text.sas_cantidadValida,
                min: general_text.sas_cantidadMayor
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
            show_loader_wrapper();

            $.ajax({
                type: "post", // podría ser get, post, put o delete.
                url: url_update_service, // url del recurso
                dataType: 'json', // formato que regresa la respuesta
                data: $(form).serializeArray(), // datos a pasar al servidor, en caso de necesitarlo
                success: function (result) {
                    hide_loader_wrapper();
                    if (!result['status']) {
                        show_modal_error(result['data']);
                        return;
                    }

                    $('#modal-servicio').modal("close");
                    if (Object.keys(result['data']).length === 0) {
                        show_alert("warning", general_text['sas_errorPeticion']);
                        return;
                    }
                    var servicio = result['data'];
                    servicios[servicio["idServicio"]] = servicio;
                    var addRecord = true;
                    if (isset($("#frm-servicio #idServicio").val())) {
                        addRecord = false;
                    }
                    drawServiceRow(servicio, addRecord);
                },
                error: function (request, status, error) {
                    hide_loader_wrapper();
                    show_modal_error(request.responseText);
                }
            });
        }
    });
}

$(document).on("click", ".btn-add-service", showServiceForm);
function showServiceForm() {
    clearForm();
    setDefaultServiceForm();
    if (isset($(this).attr("id-record"))) {
        var servicio = servicios[$(this).attr("id-record")];
        setServiceForm(servicio);
    }
    $(".taxable").change();
    $('input.autocomplete').autocomplete({data: currency, limit: 10});
    $('#modal-servicio').modal("open");
}

function clearForm() {
    $('#frm-servicio')[0].reset();
    $('#frm-servicio input[type="hidden"]').val("");
    Materialize.updateTextFields();
}

function setServiceForm(servicio) {
    $("#frm-servicio #idServicio").val(servicio['idServicio']);
    $("#idForma").val(servicio['idForma']);
    $("#idEvento").val(servicio['idEvento']);
    $("#idEdicion").val(servicio['idEdicion']);
    $("#Titulo" + lang.toUpperCase()).val(servicio['Titulo' + lang.toUpperCase()]);
    $("#PrecioAntesFecha" + lang.toUpperCase()).val(servicio['PrecioAntesFecha' + lang.toUpperCase()]);
    $("#Descripcion" + lang.toUpperCase()).val(servicio['Descripcion' + lang.toUpperCase()]);
    if (isset(servicio['FechaLimite'])) {
        $("#FechaLimiteCheck").prop('checked', true).change();
        $("#FechaLimite").val(servicio['FechaLimite']);
        $("#PrecioDespuesFecha" + lang.toUpperCase()).val(servicio['PrecioDespuesFecha' + lang.toUpperCase()]);
    }
    $("#Orden").val(servicio['Orden']);
    $("#Moneda" + lang.toUpperCase()).val(servicio['Moneda' + lang.toUpperCase()]);
    Materialize.updateTextFields();
}

function setDefaultServiceForm() {
    $("#idForma").val(forma['idForma']);
    $("#idEvento").val(forma['idEvento']);
    $("#idEdicion").val(forma['idEdicion']);
    $("#FechaLimiteCheck").prop('checked', false).change();
    Materialize.updateTextFields();
}

function drawServiceRow(servicio, addRecord) {
    var tr = $('<tr/>');
    $('<td/>', {"text": servicio['idServicio']}).appendTo(tr);
    var titulo = $('<td/>', {"text": servicio['Titulo' + lang.toUpperCase()]}).appendTo(tr);
    if (isset(servicio['Descripcion' + lang.toUpperCase()])) {
        $('<div/>', {"class": "service-description", "html": servicio['Descripcion' + lang.toUpperCase()]}).appendTo(titulo);
    }
    $('<td/>', {
        "class": "center-align",
        "text": servicio['Moneda' + lang.toUpperCase()]
    }).appendTo(tr);
    $('<td/>', {
        "class": "right-align",
        "text": "$ " + $.number(servicio['PrecioAntesFecha' + lang.toUpperCase()], 2)
    }).appendTo(tr);
    $('<td/>', {
        "class": "center-align",
        "text": servicio['FechaLimite']
    }).appendTo(tr);
    var precioDespues = "";
    if (isset(servicio['PrecioDespuesFecha' + lang.toUpperCase()])) {
        precioDespues = "$ " + $.number(servicio['PrecioDespuesFecha' + lang.toUpperCase()], 2)
    }
    $('<td/>', {
        "class": "right-align",
        "text": precioDespues
    }).appendTo(tr);
    $('<td/>', {
        "class": "right-align",
        "text": servicio['Orden']
    }).appendTo(tr);
    var controles = $('<td/>', {"class": "right-align"}).appendTo(tr);
    $('<i/>', {"class": "material-icons edit-record", "id-record": servicio['idServicio'], "text": "edit"}).appendTo(controles);
    $('<i/>', {"class": "material-icons delete-record", "id-record": servicio['idServicio'], "text": "delete_forever"}).appendTo(controles);
    if (addRecord) {
        tr.appendTo(".tbl-servicios tbody");
    } else {
        var tr_original = $(".edit-record[id-record='" + servicio['idServicio'] + "']").parents("tr");
        tr_original.replaceWith(tr);
    }
}

$(document).on("click", ".edit-record", showServiceForm);
$(document).on("click", ".delete-record", showDeleteService);
function showDeleteService() {
    if (!isset($(this).attr("id-record"))) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var servicio = servicios[$(this).attr("id-record")];
    $("#frm-servicio-eliminar #idServicio").val(servicio["idServicio"]);
    $(".servicio").html(servicio["Titulo" + lang.toUpperCase()]);
    $('#modal-delete-servicio').modal("open");
}


$("#frm-servicio-eliminar").submit(function (e) {
    e.preventDefault();
    e.stopPropagation();
    var servicio = servicios[$("#frm-servicio-eliminar #idServicio").val()];
    $.ajax({
        type: "POST",
        url: $("#frm-servicio-eliminar").attr("action"),
        data: $("#frm-servicio-eliminar").serialize(),
        dataType: 'json',
        success: function (result) {
            hide_loader_wrapper();
            if (!result['status']) {
                show_modal_error(result['data']);
                return;
            }
            $(".delete-record[id-record='" + servicio['idServicio'] + "']").parents("tr").remove();
            delete servicios[servicio["idServicio"]];
            $('#modal-delete-servicio').modal("close");
            show_alert("success", general_text['sas_eliminoExito']);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
});
$(document).on("change", ".taxable", calculateTax);
function calculateTax() {
    var value = $(this).val();
    var taxedField = "#" + $(this).attr("taxed-field");
    if (isFloat(value) && parseFloat(value) > 0) {
        $(taxedField).val(calcularTotal(value));
        Materialize.updateTextFields();
        return;
    }
    $(taxedField).val("");
    Materialize.updateTextFields();
}


function isFloat(val) {
    var floatRegex = /^-?\d+(?:[.,]\d*?)?$/;
    if (!floatRegex.test(val))
        return false;
    val = parseFloat(val);
    if (isNaN(val))
        return false;
    return true;
}

/* Calcular total en base al subtotal
 * total = subtotal * (1 + ( iva / 100 ) )
 */
function calcularTotal(subtotal) {
    return (parseFloat(subtotal) * 1.16).toFixed(2);
}

$("#FechaLimiteCheck").change(checkLimitDate);
function checkLimitDate() {
    if ($("#FechaLimiteCheck").is(":checked")) {
        $("#FechaLimite").prop("readonly", false);
        $("#PrecioDespuesFecha" + lang.toUpperCase()).prop("readonly", false);
        Materialize.updateTextFields();
        if ($("#FechaLimite").val() === "") {
            $("#FechaLimite").trigger("focus");
        }
    } else {
        $("#FechaLimite").val("");
        $("#PrecioDespuesFecha" + lang.toUpperCase()).val("");
        $("#FechaLimite").attr("readonly", "readonly");
        $("#PrecioDespuesFecha" + lang.toUpperCase()).attr("readonly", "readonly");
        Materialize.updateTextFields();
    }
}

/* Servicios */