/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(initEditor);

function initEditor() {
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
        'paragraphFormat', 'quote', 'insertHR', 'insertLink', 'insertTable',
        '|',
        'undo', 'redo', 'fullscreen', 'html'],
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

$(document).on("click", ".editor", showEditor);

function showEditor() {
    if ($('.editing').length > 0) {
        Materialize.toast(general_text['sas_estasEditando'], 4000);
        return;
    }
    setEditMode($(this));
}

function setEditMode(editor) {
    var froalaControls = $('<div/>', {
        "class": 'froala-controls'
    });

    var btnUndo = $('<a/>', {
        "class": 'btn-cancel-editing btn-floating waves-effect white tooltipped',
        "data-tooltip": general_text['sas_cancelarEdicion']
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

    editor.find(".froala-editor").after(froalaControls);
    $('.tooltipped').tooltip({delay: 50});
    editor.removeClass("editor").addClass("editing").unbind('click');
    editor.find(".froala-editor").froalaEditor(editorOptions);
    $('a[href="https://froala.com/wysiwyg-editor"]').remove();
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
    destroyEditMode();
}

function destroyEditMode() {
    var editor = $(".editing");
    editor.find(".froala-editor").froalaEditor('destroy');
    editor.removeClass("editing").addClass("editor");
    $(".froala-controls").remove();
}

/* Guardado de informacion */

$(document).on("click", ".btn-save-editor", saveEditing);

function saveEditing(e) {
    /*e.preventDefault();
     e.stopPropagation();
     $(this).tooltip('remove');*/
    var editor = $(this).parents(".editing");
    var froalaEditor = $("#" + editor.attr("id") + " .froala-editor");
    var html = froalaEditor.froalaEditor('html.get').replace("'", "''");
    showLoaderSavingEditor(editor);
    html = html.replace('<ul>', '<ul class="browser-default">');
    $.ajax({
        type: "post",
        url: url_save_html,
        dataType: 'json',
        data: {
            "Seccion": editor.attr("seccion"),
            "Etiqueta": editor.attr("id"),
            "Texto": html
        },
        success: function (result) {
            html = html.replace("''", "'");
            hideLoaderSavingEditor(editor);
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            froalaEditor.html(html);
            destroyEditMode();
            show_alert("success", general_text['sas_guardoExito']);
        },
        error: function (request, status, error) {
            hideLoaderSavingEditor(editor);
            show_modal_error(request.responseText);
        }
    });
}

function showLoaderSavingEditor(editor) {
    if (editor.find(".fr-box").length === 0) {
        return;
    }

    var savingEditor = $('<div/>', {"class": 'saving-editor'}).prependTo(editor);
    var progress = $('<div/>', {"class": 'progress'}).appendTo(savingEditor);
    $('<div/>', {"class": 'indeterminate'}).appendTo(progress);
}

function hideLoaderSavingEditor(editor) {
    editor.find(".saving-editor").remove();
}

/* Guardado de informacion */