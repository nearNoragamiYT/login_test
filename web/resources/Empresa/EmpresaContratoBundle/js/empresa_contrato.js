var oTable = "", tr = "", itemToUpdate = "", itemToDelete = "", action = "";

$(document).ready(function () {
    initContracts();
    viewChangeUpgrade();
});

function initContracts() {
    $("#empresa-contrato").attr("class", "active");

    generateContractsTable('contracts-table');

    validateChangeUpgradeForm();

    $(document).on("click", ".company-menu>div>ul>li", function () {
        show_loader_wrapper();
    });

    $(document).on("click", ".edit-record", function () {
        show_loader_wrapper();
    });

    $("#add-contract").on("click", function () {
        show_loader_wrapper();
    });

    $("#delete-record").on("click", function () {
        show_loader_wrapper();
        $("#delete-record-modal").modal("close");
        cancelContract();
    });
    $(document).on("click", ".delete-record", function () {
        itemToUpdate = $(this).attr("data-id");
        tr = $(this).parents("tr");
        action = "update";
        $("#deleteText").html(section_text["sas_confirmacionCancelarContrato"] + ' ' + contracts[itemToUpdate]["NoFolio"] + "?");
        $("#delete-record-modal").modal({dismissible: false}).modal("open");
    });

    $("#change-upgrade").on('click', function () {
        $("#PaqueteActual").val(packages[company["idPaquete"]]["Paquete" + lang.toUpperCase()]);
        $("#idNuevoPaquete").val("").change();
        $("#upgrade-head").html(section_text["sas_seleccionarPaqueteMKF"]);
        $("#change-upgrade-form input[type='text']").removeClass('valid').next().addClass('active');
        $('#change-upgrade-modal').modal({dismissible: false}).modal("open");
    });
    $("#btn-change-upgrade").on("click", function () {
        $("#change-upgrade-form").submit();
    });
}

function generateContractsTable(id) {
    oTable = $('#' + id).DataTable({
        "language": {
            "url": url_lang
        },
        "order": [[0, "desc"]]
    });
}
function cancelContract() {
    var post = "";
    post += "idContrato=" + itemToUpdate;
    post += "&idEmpresa=" + idEmpresa;
    post += "&idEvento=" + contracts[itemToUpdate]["idEvento"];
    post += "&idEdicion=" + contracts[itemToUpdate]["idEdicion"];
    $.ajax({
        type: "post",
        url: url_contract_cancel,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            contracts[response.data["idContrato"]]["idStatusContrato"] = 5;
            viewChangeUpgrade();
            var canceled = contracts[response.data["idContrato"]];
            setRow(canceled, action);
            $("#add-contract").fadeIn();
            show_alert("success", general_text.sas_eliminoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}
function validateChangeUpgradeForm() {
    $("#change-upgrade-form").validate({
        rules: {
            'idNuevoPaquete': {
                required: true
            }
        },
        messages: {
            'idNuevoPaquete': {
                required: general_text.sas_requerido,
            },
            'idNuevo': {
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
            $('#change-upgrade-modal').modal("close");
            var post = $('#change-upgrade-form').serialize();
            changeUpgrade(post);
            return;
        }
    });
}
function changeUpgrade(post) {
    $.ajax({
        type: "post",
        url: url_upgrade_change,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            company["idPaquete"] = response.data["idNuevoPaquete"];
            $("#header-paquete").html(packages[response.data["idNuevoPaquete"]]["Paquete" + lang.toUpperCase()]);

            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}
function viewChangeUpgrade() {
    var keys = Object.keys(contracts);
    var total = keys.length;

    $("#change-upgrade").hide();
    for (var i = 0; i < total; i++) {
        if (contracts[keys[i]]["idStatusContrato"] == 4) {
            $("#change-upgrade").show();
            return;
        } else {
            $("#change-upgrade").hide();
        }
    }

    for (var i = 0; i < total; i++) {
        if (contracts[keys[i]]["idStatusContrato"] == 5) {
            $("#add-contract").show();
        } else {
            $("#add-contract").hide();
            return;
        }
    }
}
function setRow(data, action) {
    switch (action) {
        case 'update':
            oTable.row(tr).data([
                data.NoFolio,
                editions[data.idEdicion]["Edicion_ES"],
                data.ListadoStand,
                data.AreaContratada,
                '<span style="color: red">' + status_list[data.idStatusContrato]["Status"] + '</span>',
                ""
            ]).draw();
            break;
    }
}