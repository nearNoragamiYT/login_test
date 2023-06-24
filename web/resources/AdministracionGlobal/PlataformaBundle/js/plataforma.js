var oTable = "", tr = "", itemToDelete = "";
$(init);

$(".add-record").on("click", function () {
    clearForm();
    $("#mdl-platform").modal("open");
});
$("#save-record").on("click", function () {
    $("#frm-platform").submit();
});
$("#delete-record").on("click", deleteData);

$(document).on("click", ".edit-record", function () {
    var id = $(this).attr("id-record");
    tr = $(this).parents("tr");
    clearForm();
    setDataForm(id);
    $("#mdl-platform").modal("open");
});

$(document).on("click", ".delete-record", function () {
    itemToDelete = $(this).attr("id-record");
    tr = $(this).parents("tr");
    $("#mdl-delete-platform").modal("open");
});

function init() {
    oTable = $('#tbl-platform').DataTable({
        "language": {
            "url": url_lang
        }
    });
    $('select').material_select();
    initForm();
}

function initForm() {
    $("#frm-platform").validate({
        rules: {
            'Prefijo': {
                required: true,
                maxlength: 30,
            },
            'PlataformaIxpo': {
                required: true,
                maxlength: 30
            }
        },
        messages: {
            'Prefijo': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'PlataformaIxpo': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
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
            if ($('.alert').length > 0) {
                $('.alert').remove();
            }
            show_loader_wrapper();
            if ($("#id").val() === "") {
                saveData();
            } else {
                updateData();
            }
            return;
        }
    });
}

function saveData() {
    $("#Activa").val(($("#Activa").is(':checked')) ? 1 : 0);
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_insert, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: $("#frm-platform").serialize(), // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_wrapper();
            $("#mdl-platform").modal("close");
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            pl[response.data.idPlataformaIxpo] = response.data;
            setRow(response.data, "insert");
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function updateData() {
    $("#Activa").val(($("#Activa").is(':checked')) ? 1 : 0);
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_update, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: $("#frm-platform").serialize(), // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_wrapper();
            $("#mdl-platform").modal("close");
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            pl[response.data.idPlataformaIxpo] = response.data;
            setRow(response.data, "update");
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function deleteData() {
    if ($('.alert').length > 0) {
        $('.alert').remove();
    }
    show_loader_wrapper();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_delete, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: {idPlataformaIxpo: itemToDelete}, // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_wrapper();
            $("#mdl-delete-platform").modal("close");
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            delete  pl[response.data.idPlataformaIxpo];
            setRow(response.data, "delete");
            show_alert("success", general_text.sas_eliminoExito);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function setRow(data, action) {
    switch (action) {
        case 'insert':
            oTable.row.add([
                data.idPlataformaIxpo,
                data.Prefijo,
                data.PlataformaIxpo,
                data.Ruta,
                '<i class="material-icons edit-record" id-record="' + data.idPlataformaIxpo + '">mode_edit</i>',
                '<i class="material-icons delete-record" id-record="' + data.idPlataformaIxpo + '">delete_forever</i>'
            ]).draw('full-hold');
            break;
        case 'update':
            oTable.row(tr).data([
                data.idPlataformaIxpo,
                data.Prefijo,
                data.PlataformaIxpo,
                data.Ruta,
                '<i class="material-icons edit-record" id-record="' + data.idPlataformaIxpo + '">mode_edit</i>',
                '<i class="material-icons delete-record" id-record="' + data.idPlataformaIxpo + '">delete_forever</i>'
            ]).draw();
            break;
        case 'delete':
            oTable.row(tr).remove().draw();
            break;
    }

}

function clearForm() {
    $("#frm-platform input, textarea").val("");
    $("#Activa").prop("checked", false);
    $("#frm-platform input[type='text'], textarea").removeClass('valid').next().removeClass('active');
}

function setDataForm(i) {
    $("#id").val(i);
    $("#Prefijo").val(pl[i]["Prefijo"]);
    $("#PlataformaIxpo").val(pl[i]["PlataformaIxpo"]);
    $("#Ruta").val(pl[i]["Ruta"]);
    $("#RutaConfiguracion").val(pl[i]["RutaConfiguracion"]);
    Materialize.updateTextFields();
}
