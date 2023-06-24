var oTable = "", tr = "", itemToDelete = "";
$(document).ready(function () {
    initPuertas();
});
function initPuertas() {
    generatePuertasTable('lista-puertas-table');
    validateAddPuertaForm();

    $(document).on("click", ".edit-record", function () {
        var link = url_edit_puerta_data + "/" + $(this).attr("data-id");
        window.location = link;
    });

    $("#btn-add-puerta").on("click", function () {
        $("#add-puerta-form").submit();
    });

    $("#add-puerta").on('click', function () {
        action = "insert";
        $("#NombrePuerta").val("");
        $('#add-puerta-modal').modal({dismissible: false}).modal("open");
        $('#add-puerta-modal').modal("open");
    });

    $(document).on("click", ".delete-record", function () {
        itemToDelete = $(this).attr("data-id");
        tr = $(this).parents("tr");
        action = "delete";
        var text = section_text["sas_textoEliminaPuerta"].replace('%puerta%', '<u>' + puertas[itemToDelete]["NombrePuerta"] + '</u>');
        $("#deleteText").html(text);
        $("#delete-record-modal").modal({dismissible: false}).modal("open");
    });

    $("#delete-record").on("click", function () {
        $("#delete-record-modal").modal('close');
        show_loader_top();
        deletePuerta();
    });

}

function generatePuertasTable(id) {
    oTable = $('#' + id).DataTable({

        "language": {
            "url": url_lang
        },
        "order": [[0, "asc"]]
    });

}



function validateAddPuertaForm() {
    $("#add-puerta-form").validate({
        rules: {
            'NombrePuerta': {
                required: true
            }
        },
        messages: {
            'NombrePuerta': {
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
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            $('#add-puerta-modal').modal("close");
            var post = $('#add-puerta-form').serialize();
            addPuerta(post);
        }
    });
}

function addPuerta(post) {
    $.ajax({
        type: "post",
        url: url_add_puerta,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_wrapper();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            puertas[response.data["idPuerta"]] = response.data;
            setRow(response.data, action);
            show_alert("success", general_text.sas_guardoExito);

        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function deletePuerta() {
    $.ajax({
        type: "post",
        url: url_delete_puerta,
        dataType: 'json',
        data: {idPuerta: itemToDelete},
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            delete puertas[response.data["idPuerta"]];
            setRow(response.data, action);
            show_alert("success", general_text.sas_eliminoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}


function setRow(data, action) {
    switch (action) {
        case 'insert':
            oTable.row.add([
                data.idPuerta,
                data.NombrePuerta,
                '0',
                '<i class="material-icons edit-record tooltipped" data-position="left" data-tooltip="' + general_text.sas_editar + '" data-delay="50" data-id="' + data.idPuerta + '">edit</i>' +
                        '<i class="material-icons delete-record" data-id="' + data.idPuerta + '">delete_forever</i>'
            ]).draw('full-hold');
            break;
            /*case 'update':
             oTable.row(tr).data([
             data.NombrePuerta,
             data.LectorasAsignadas,
             '<i class="material-icons edit-record" data-id="' + data.idPuerta + '">mode_edit</i>' +
             '<i class="material-icons delete-record" data-id="' + data.idPuerta + '">delete_forever</i>'
             ]).draw();
             break;*/
        case 'delete':
            oTable.row(tr).remove().draw();
            break;
    }
}