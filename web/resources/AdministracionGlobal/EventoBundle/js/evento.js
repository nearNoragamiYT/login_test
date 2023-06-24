var oTable = "", tr = "", itemToDelete = "";
$(init);

function init() {
    oTable = $('#tbl-event').DataTable({
        "language": {
            "url": url_lang
        }
    });
    $('select').material_select();
    $(".add-record").on("click", function () {
        clearForm();
        $("#mdl-event").modal("open");
    });
    $(".save-record").on("click", function () {
        $("#frm-event").submit();
    });
    $("#delete-record").on("click", deleteData);
    $(document).on("click", ".edit-record", function () {
        id = $(this).attr("id-record");
        tr = $(this).parents("tr");
        clearForm();
        setDataForm(id);
        $("#mdl-event").modal("open");
    });
    $(document).on("click", ".delete-record", function () {
        itemToDelete = $(this).attr("id-record");
        tr = $(this).parents("tr");
        $("#mdl-delete-event").modal("open");
    });
    initForm();
}

function initForm() {
    $("#frm-event").validate({
        rules: {
            'Evento_ES': {
                required: true,
                maxlength: 50
            },
            'Evento_EN': {
                required: false
            },
            'Evento_PT': {
                required: false
            },
            'Evento_FR': {
                required: false
            },
        },
        messages: {
            'Evento_ES': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'Evento_EN': {
                required: general_text.sas_requerido,
            },
            'Evento_PT': {
                required: general_text.sas_requerido,
            },
            'Evento_FR': {
                required: general_text.sas_requerido,
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
            if ($('.alert').length > 0) {
                $('.alert').remove();
            }
            show_loader_wrapper();
            if ($("#id").val() == "") {
                saveData();
            } else {
                updateData();
            }
            return;
        }
    });
}

function saveData() {
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_insert, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: $("#frm-event").serialize(), // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            $("#mdl-event").modal("close");
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            ev[result.data.idEvento] = result.data;
            setRow(result.data, "insert");
            show_alert("success", general_text['sas_guardoExito']);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function updateData() {
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_update, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: $("#frm-event").serialize(), // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_wrapper();
            $("#mdl-event").modal("close");
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            ev[response.data.idEvento] = response.data;
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
        data: {idEvento: itemToDelete}, // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_wrapper();
            $("#mdl-delete-event").modal("close");
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            delete  ev[response.data.idEvento];
            setRow(response.data, "delete");
            show_alert("success", general_text.sas_eliminoExito);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function setRow(data, action) {/*console.log(data);console.log(action);*/
    switch (action) {
        case 'insert':
            oTable.row.add([
                data.idEvento,
                data.Evento_ES,
                /*data.Evento_EN,
                 data.Evento_PT,
                 data.Evento_FR,*/
                '<i class="material-icons edit-record" id-record="' + data.idEvento + '">mode_edit</i>',
                '<i class="material-icons delete-record" id-record="' + data.idEvento + '">delete_forever</i>'
            ]).draw('full-hold');
            break;
        case 'update':
            oTable.row(tr).data([
                data.idEvento,
                data.Evento_ES,
                /*data.Evento_EN,
                 data.Evento_PT,
                 data.Evento_FR,*/
                '<i class="material-icons edit-record" id-record="' + data.idEvento + '">mode_edit</i>',
                '<i class="material-icons delete-record" id-record="' + data.idEvento + '">delete_forever</i>'
            ]).draw();
            break;
        case 'delete':
            oTable.row(tr).remove().draw();
            break;
    }

}

function clearForm() {
    $("#frm-event input, textarea").val("");
    $("#frm-event input[type='text'], textarea").removeClass('valid').next().removeClass('active');
}

function setDataForm(i) {
    $("#id").val(i);
    $("#Evento_ES").val(ev[i]["Evento_ES"]);
    $("#Evento_EN").val(ev[i]["Evento_EN"]);
    $("#Evento_PT").val(ev[i]["Evento_PT"]);
    $("#Evento_FR").val(ev[i]["Evento_FR"]);
    $("#frm-event input[type='text'], textarea").removeClass('valid').next().addClass('active');
}

