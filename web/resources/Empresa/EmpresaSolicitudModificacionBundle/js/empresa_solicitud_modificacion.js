var oTable = "", GeneralTable = "", tr = "", itemToUpdate = "", itemToDelete = "", action = "", disabled = "", empToUpdate = "", pendigTable = "", approvedTable = "", rejectedTable = "", parentTabs = new AcrossTabs.Parent();
var requestStatus = [
    section_text.sas_solicitudRechazada,
    section_text.sas_solicitudCompletada,
    section_text.sas_solicitudPendiente
]
$(document).ready(function () {
    initModificactionRequest();
});

function initModificactionRequest() {
    $("#empresa-solicitudes-modificacion").attr("class", "active");

    if (idEmpresa != "") {
        $("#aditional-data").find("#link-ed").show();
        $("#aditional-data").find("#link").attr("href", "http://expoantad.infoexpo.com.mx/2017/ed/web/utilerias/info/" + idUsuario + "/" + idForma + "/" + token + "/" + lang);
        generateModificationRequestTable('modification-request-table');
    } else {
        $("#aditional-data").find("#link-ed").hide();
        generateGeneralRequestTable('pending-request-table');
        generateGeneralRequestTable('approved-request-table');
        generateGeneralRequestTable('rejected-request-table');
        $("#pending-total").text(pendingTable.rows()[0].length);
        $("#approved-total").text(approvedTable.rows()[0].length);
        $("#rejected-total").text(rejectedTable.rows()[0].length);
        $('.collapsible').collapsible();
    }
    $(document).on("click", ".company-menu>div>ul>li", function () {
        show_loader_wrapper();
    });

    validateModificationRequestForm();
    $(document).on("click", ".edit-record", function () {
        itemToUpdate = $(this).attr("data-id");
        empToUpdate = $(this).attr("data-idEmp");
        tr = $(this).parents("tr");
        action = "update";
        setMRData();
        $("#edit-modification-request-head").html(section_text['sas_editarSolicitud']);
        $('#edit-modification-request-modal').modal({dismissible: false}).modal("open");
    });

    $(document).on("click", ".edit-form", function () {
        parentTabs.closeAllTabs();
        var url = $(this).attr('data-url');
        parentTabs.openNewTab({"url": url, "windowName": "Cambio de Directorio"});
    });

    $("#btn-edit-modification-request").on("click", function () {
        $("#edit-modification-request-form").submit();
    });

    $("#delete-record").on("click", function () {
        $("#delete-record-modal").modal("close");
        deleteModificationRequest();
    });

    $(document).on("click", ".delete-record", function () {
        itemToDelete = $(this).attr("data-id");
        empToUpdate = $(this).attr("data-idEmp");
        tr = $(this).parents("tr");
        action = "delete";
        //Modulo general ó Modulo interno
        if (empToUpdate) {
            $("#deleteText").html('Está seguro de borrar la solicitud: ' + modification_request[empToUpdate][itemToDelete]["idSolicitudCambio"] + ', de la empresa: ' + empToUpdate + "?");
        } else {
            $("#deleteText").html('Está seguro de borrar la solicitud: ' + modification_request[itemToDelete]["idSolicitudCambio"] + "?");
        }
        $("#delete-record-modal").modal({dismissible: false}).modal("open");
    });

}

function setMRData(id) {
    //Modulo general ó Modulo interno
    if (empToUpdate) {
        var entity = modification_request[empToUpdate][itemToUpdate];
    } else {
        var entity = modification_request[itemToUpdate];
    }
    $("#idSolicitudCambio").val(entity["idSolicitudCambio"]);
    //Estatus Soilicitud de cambio
    if (entity["StatusSolicitudCambio"] == 0)
    {
        $("#StatusSolicitudCambio").val(entity["StatusSolicitudCambio"]).change();
    }
    if (entity["StatusSolicitudCambio"] == 1)
    {
        $("#StatusSolicitudCambio").val(entity["StatusSolicitudCambio"]).change();
    }
    if (entity["StatusSolicitudCambio"] == 2)
    {
        $("#StatusSolicitudCambio").val(entity["StatusSolicitudCambio"]).change();
    }

    $("#idCampoModificacion").val(fieldsForm[entity["idCampoModificacion"]]['NombreCampoES']);
    //Si el campo NO es Categorias, oculta los divs
    if (entity["idCampoModificacion"] != 18)
    {
        $("#CatPrincipal").hide();
        $("#CatSecundaria").hide();
        $("#CatOtra").hide();
    } else {
        $("#CatPrincipal").show();
        $("#CatSecundaria").show();
        $("#CatOtra").show();
        $("#CategoriaPrincipal").val(categories[entity["CategoriaPrincipal"]]['NombreCategoriaES']);
        $("#CategoriaSecundaria").val(categories[entity["CategoriaSecundaria"]]['NombreCategoriaES']);
        $("#OtraCategoria").val(entity["OtraCategoria"]);
        if (entity["OtraCategoria"] == "")
        {
            $("#CatOtra").hide();
        }
    }
    $("#Observacion").val(entity["Observacion"]);
    $("#ObservacionComite").val(entity["ObservacionComite"]);
    $("#FechaSolicitudCambio").val(entity["FechaSolicitudCambio"]);
    $("#edit-modification-request-form input[type='text'], textarea").removeClass('valid').next().addClass('active');
}

//tabla de modulo interno
function generateModificationRequestTable(id) {
    var btn, span;
    oTable = $('#' + id).DataTable({
        "language": {
            "url": url_lang
        }
    });
}

//tabla del modulo general
function generateGeneralRequestTable(id) {
    switch (id) {
        case "pending-request-table":
            pendingTable = $('#' + id).DataTable({
                "language": {
                    "url": url_lang
                }
            });
            break;
        case "approved-request-table":
            approvedTable = $('#' + id).DataTable({
                "language": {
                    "url": url_lang
                }
            });
            break;
        case "rejected-request-table":
            rejectedTable = $('#' + id).DataTable({
                "language": {
                    "url": url_lang
                }
            });
            break;
        default :
            pendingTable = $('#pending-request-table').DataTable({
                "language": {
                    "url": url_lang
                }
            });
            break;
    }
}

function validateModificationRequestForm() {
    $("#edit-modification-request-form").validate({
        rules: {
            'StatusSolicitudCambio': {
                required: true,
                maxlength: 100
            },
            'ObservacionComite': {
                required: true,
                maxlength: 200
            },
        },
        messages: {
            'StatusSolicitudCambio': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'ObservacionComite': {
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
            show_loader_wrapper();
            $('#edit-modification-request-modal').modal("close");
            disabled = $("#edit-modification-request-form input:disabled").removeAttr("disabled");
            var post = $('#edit-modification-request-form').serialize();
            if (action == "update")
                updateModificationRequest(post);
            return;
        }
    });
}

function updateModificationRequest(post) {
    //Modulo general ó Modulo interno
    if (empToUpdate) {
        post += "&idSolicitudCambio=" + itemToUpdate;
        post += "&idEmpresa=" + empToUpdate;
    } else {
        post += "&idSolicitudCambio=" + itemToUpdate;
    }

    $.ajax({
        type: "POST",
        url: url_modification_request_update,
        dataType: 'json',
        data: post,
        success: function (response) {
            disabled.attr("disabled", "disabled");
            hide_loader_wrapper();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            //Modulo general ó Modulo interno
            if (empToUpdate) {
                var currentStatus = response.data['StatusSolicitudCambio'];
                var lastStatus = modification_request[empToUpdate][response.data["idSolicitudCambio"]]['StatusSolicitudCambio'];
                modification_request[empToUpdate][response.data["idSolicitudCambio"]] = response.data;
                updateTables(empToUpdate, response.data["idSolicitudCambio"], currentStatus, lastStatus);
                show_alert("success", general_text.sas_guardoExito);
            } else {
                modification_request[response.data["idSolicitudCambio"]] = response.data;
                show_alert("success", general_text.sas_guardoExito);
                location.reload();
            }
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function updateTables(idE, idR, currentStatus, lastStatus) {
    var tr = $("tr[data-unique=" + idE + "-" + idR + "]").clone();
    switch (parseInt(lastStatus)) {
        case 0:
            rejectedTable.row($("tr[data-unique=" + idE + "-" + idR + "]")).remove().draw();
            break;
        case 1:
            approvedTable.row($("tr[data-unique=" + idE + "-" + idR + "]")).remove().draw();
            break;
        case 2:
            pendingTable.row($("tr[data-unique=" + idE + "-" + idR + "]")).remove().draw();
            break;
    }
    $(tr).find(".ObservacionComite").text(modification_request[idE][idR]['ObservacionComite']);
    switch (parseInt(currentStatus)) {
        case 0:
            rejectedTable.row.add($(tr)).draw();
            break;
        case 1:
            approvedTable.row.add($(tr)).draw();
            break;
        case 2:
            pendingTable.row.add($(tr)).draw();
        case 2:
            break;
    }
    $("#pending-total").text(pendingTable.rows()[0].length);
    $("#approved-total").text(approvedTable.rows()[0].length);
    $("#rejected-total").text(rejectedTable.rows()[0].length);
}

function createRowTable(idR, currentStatus) {
    var tr = document.createElement('tr');
    tr.id = idR;
    var td = document.createElement("td");
    td.className = "id";
    td.style.width = "200px";
}

function deleteModificationRequest() {
    //Modulo general ó Modulo interno
    if (empToUpdate) {
        var post = modification_request[empToUpdate][itemToDelete];
        post['idEmpresa'] = empToUpdate;
    } else {
        var post = modification_request[itemToDelete];
        post['idEmpresa'] = idEmpresa;
    }

    $.ajax({
        type: "post",
        url: url_modification_request_delete,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            //Modulo general ó Modulo interno
            if (empToUpdate) {
                delete modification_request[response.data["idSolicitudCambio"]];
                setGeneralRow(response.data, action);
            } else {
                delete modification_request[response.data["idSolicitudCambio"]];
                setRow(response.data, action);
            }
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
        case 'update':
            oTable.row(tr).data([
                data.idSolicitudCambio,
                data.StatusSolicitudCambio,
                data.idCampoModificacion,
                data.Observacion,
                data.CategoriaPrincipal,
                data.CategoriaSecundaria,
                data.OtraCategoria,
                data.ObservacionComite,
                data.FechaSolicitudCambio,
                '<i class="material-icons edit-record" data-id="' + data.idSolicitudCambio + '">mode_edit</i>' +
                        '<i class="material-icons delete-record" data-id="' + data.idSolicitudCambio + '">delete_forever</i>'
            ]).draw();
            break;
        case 'delete':
            oTable.row(tr).remove().draw();
            break;
    }
}

function setGeneralRow(data, action) {
    switch (action) {
        case 'update':
            oGeneralTable.row(tr).data([
                data.idSolicitudCambio,
                data.StatusSolicitudCambio,
                data.idCampoModificacion,
                data.Observacion,
                data.CategoriaPrincipal,
                data.CategoriaSecundaria,
                data.OtraCategoria,
                data.ObservacionComite,
                data.FechaSolicitudCambio,
                '<i class="material-icons edit-record" data-id="' + data.idSolicitudCambio + '">mode_edit</i>' +
                        '<i class="material-icons delete-record" data-id="' + data.idSolicitudCambio + '">delete_forever</i>'
            ]).draw();
            break;
        case 'delete':
            oGeneralTable.row(tr).remove().draw();
            break;
    }
}