var oTable = "", tr = "", itemToDelete = "";
$(document).ready(function () {
    initLectorasPuerta();
});
function initLectorasPuerta() {
    generatePuertasTable('lectoras-puertas-table');
    setPuertaData();
    validateLectoraPuertaForm();
    $("#savePuertaData").on("click", function () {
        var data = {"NombrePuerta": $("#NombrePuerta").val(), "idPuerta": $("#idPuerta").val()};
        if (data !== "") {
            show_loader_top();
            savePuertaData(data);
        } else {
            show_alert("warning", section_text.sas_alertaNombrePuerta);
        }
    });

    $("#idScannerTipo").on("change", function () {
        var nota = section_text["sas_notaCodigoLectora"];
        nota = nota.replace("%lectora%", "<b>" + $("option:selected", this).text() + "</b>");
        if ($("option:selected", this).attr("RequierePassport")) {
            nota = nota.replace("%codigo%", " <b>" + section_text['sas_passportLectora'] + ".</b>");
        } else {
            nota = nota.replace("%codigo%", " <b>" + section_text['sas_NoSerieLectora'] + ".</b>");
        }
        $("#NotaCodigoLectora").html("*" + nota);
    });

    $("#btn-add-lectora-puerta").on("click", function () {
        $("#add-lectora-puerta-form").submit();
    });

    $("#add-lectora-puerta").on("click", function () {
        clearForm('add-lectora-puerta-form');
        action = "insert";
        $("#lectora-puerta-head").html(section_text["sas_agregaLectora"]);
        /* Ponemos en Status 1(En Uso) Automaticamente al guardar la lectora */
        $("#idStatusScanner").val(1).change();
        $('#add-lectora-puerta-modal').modal({dismissible: false}).modal("open");
    });

    $(document).on("click", ".edit-record", function () {
        itemToUpdate = $(this).attr("data-id");
        tr = $(this).parents("tr");
        action = "update";
        setLectoraPuertaData();
        $("#lectora-puerta-head").html(section_text["sas_editarLectora"]);
        $('#add-lectora-puerta-modal').modal({dismissible: false}).modal("open");
    });

    $(document).on("click", ".delete-record", function () {
        itemToDelete = $(this).attr("data-id");
        tr = $(this).parents("tr");
        action = "delete";
        var text = section_text["sas_textoEliminarRegistro"].replace('%lectora%', '<u>' + lectoras_puerta[itemToDelete]["ScannerTipo"] + '</u>');
        text = text.replace('%CodigoLectora%', '<u>' + lectoras_puerta[itemToDelete]["CodigoLectora"] + '</u>') + " ?";
        $("#deleteText").html(text);
        $("#delete-record-modal").modal({dismissible: false}).modal("open");
    });

    $("#delete-record").on("click", function () {
        $("#delete-record-modal").modal('close');
        show_loader_top();
        deletePuertaLectora();
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

function validateLectoraPuertaForm() {
    $("#add-lectora-puerta-form").validate({
        rules: {
            'CodigoLectora': {
                required: true,
                maxlength: 100
            },
            'idScannerTipo': {
                required: true
            },
            'idStatusScanner': {
                required: true
            }
        },
        messages: {
            'CodigoLectora': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'idScannerTipo': {
                required: general_text.sas_requerido
            },
            'idStatusScanner': {
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
            $('#add-lectora-puerta-modal').modal('close');
            disabled = $("#add-lectora-puerta-form select:disabled").removeAttr("disabled");
            disabled = $("#add-lectora-puerta-form input:disabled").removeAttr("disabled");
            $("#ScannerTipo").val($('#idScannerTipo option:selected').text());
            $("#Status").val($('#idStatusScanner option:selected').text());
            var post = $('#add-lectora-puerta-form').serialize();
            show_loader_top();
            if (action == "insert")
                addLectoraPuerta(post);
            if (action == "update")
                updateLectoraPuerta(post);
            return;
        }
    });
}

function addLectoraPuerta(post) {
    $.ajax({
        type: "post",
        url: url_add_lectora_puerta,
        dataType: 'json',
        data: post,
        success: function (response) {
            disabled.attr("disabled", "disabled");
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            response.data.Cortesia = (response.data.Cortesia === "TRUE");
            lectoras_puerta[response.data["idEmpresaScanner"]] = response.data;
            setRow(response.data, action);
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function updateLectoraPuerta(post) {
    post += "&idEmpresaScanner=" + itemToUpdate;
    $.ajax({
        type: "post",
        url: url_edit_lectora_puerta,
        dataType: 'json',
        data: post,
        success: function (response) {
            disabled.attr("disabled", "disabled");
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            lectoras_puerta[response.data["idEmpresaScanner"]] = response.data;
            setRow(response.data, action);
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function deletePuertaLectora() {
    $.ajax({
        type: "post",
        url: url_delete_lectora_puerta,
        dataType: 'json',
        data: {idEmpresaScanner: itemToDelete},
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            delete lectoras_puerta[response.data["idEmpresaScanner"]];
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

function setPuertaData() {
    $("#idPuerta").val(puerta["idPuerta"]);
    $("#NombrePuerta").val(puerta["NombrePuerta"]);
    $("#save-puerta-form input[type='text'], textarea").removeClass('valid').next().addClass('active');
}

function savePuertaData(post) {
    $.ajax({
        type: "post",
        url: url_save_data_puerta,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            //Cambiamos el valor en el breadcrum
            $('.nav-wrapper').find('a').not(".show-loader-top").text(response.data.NombrePuerta);
            $("#NombrePuertaHeader").text(response.data.NombrePuerta);
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function setLectoraPuertaData(id) {
    var lectora = lectoras_puerta[itemToUpdate];
    $("#idScannerTipo").val(lectora['idScannerTipo']).change();
    $("#CodigoLectora").val(lectora['CodigoLectora']);
    $("#idStatusScanner").val(lectora['idStatusScanner']).change();
    $("#idStatusScanner").attr('disabled', false);
    $("#add-lectora-puerta-form input[type='text'], textarea").removeClass('valid').next().addClass('active');
}

function clearForm(idForm) {
    $('#' + idForm).find('input').each(function (index, element) {
        if (!$(element).is(':disabled')) {
            $(element).removeClass('valid').next().removeClass('active');
        }
    });
    $("#idStatusScanner").attr('disabled', true);
    $("#NotaCodigoLectora").html("");
    $('#' + idForm).find('input[type="text"]').not('input[type="text"]:disabled').val("");
    $('#' + idForm).find('input[type="email"]').not('input[type="email"]:disabled').val("");
    $('#' + idForm).find('input[type="tel"]').not('input[type="tel"]:disabled').val("");
    $('#' + idForm).find('textarea').not('textarea:disabled').val("");
    $('#' + idForm).find('select').not('select:disabled').val("");
    $('#' + idForm).find('input[type="checkbox"] input[type="radio"]').not('input[type="checkbox"]:disabled input[type="radio"]:disabled').prop('checked', false);
}

function setRow(data, action) {
    switch (action) {
        case 'insert':
            oTable.row.add([
                data.CodigoLectora,
                data.ScannerTipo,
                data.Status,
                '<i class="material-icons edit-record" data-id="' + data.idEmpresaScanner + '">edit</i>' +
                        '<i class="material-icons delete-record" data-id="' + data.idEmpresaScanner + '">delete_forever</i>'
            ]).draw('full-hold');
            break;
        case 'update':
            oTable.row(tr).data([
                data.CodigoLectora,
                data.ScannerTipo,
                data.Status,
                '<i class="material-icons edit-record" data-id="' + data.idEmpresaScanner + '">mode_edit</i>' +
                        '<i class="material-icons delete-record" data-id="' + data.idEmpresaScanner + '">delete_forever</i>'
            ]).draw();
            break;
        case 'delete':
            oTable.row(tr).remove().draw();
            break;
    }
}