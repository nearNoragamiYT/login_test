var oTable = "", tr = "", itemToUpdate = "", itemToDelete = "", action = "", disabled = "";

$(document).ready(function () {
    initFinancialCompanies();
});

function initFinancialCompanies() {
    $("#empresa-fiscal").attr("class", "active");

    generateFinancialCompaniesTable('financial-companies-table');

    validateAddFinancialCompanyForm();
    validateChangeFinancialForm();

    $(document).on("click", ".company-menu>div>ul>li", function () {
        show_loader_wrapper();
    });

    $(".progress-estado").hide();
    $("#DF_idPais").on("change", function () {
        $(".progress-estado").show();
        var id, loader = $(this).attr('loader-element');
        getEstados(id, loader);
    });

    $("#btn-add-financial-company").on("click", function () {
        $("#add-financial-company-form").submit();
    });

    $("#add-financial-company").on('click', function () {
        clearForm('add-financial-company-form');
        action = "insert";
        $("#financial-companies-head").html(section_text["sas_agregarEmpresaFiscal"]);
        $('#add-financial-company-modal').modal({dismissible: false}).modal("open");
    });

    $(document).on("click", ".edit-record", function () {
        itemToUpdate = $(this).attr("data-id");
        tr = $(this).parents("tr");
        action = "update";
        setFinancialCompanyData();
        $("#financial-companies-head").html(section_text["sas_editarEntidadFiscal"]);
        $('#add-financial-company-modal').modal({dismissible: false}).modal("open");
    });

    $("#delete-record").on("click", function () {
        $("#delete-record-modal").modal("close");
        deleteFinancialCompany();
    });
    $(document).on("click", ".delete-record", function () {
        itemToDelete = $(this).attr("data-id");
        tr = $(this).parents("tr");
        action = "delete";
        $("#deleteText").html(section_text["sas_textoEliminarRegistro"] + ' ' + financial_companies[itemToDelete]["DF_RazonSocial"] + "?");
        $("#delete-record-modal").modal({dismissible: false}).modal("open");
    });

    $("#change-financial-principal").on('click', function () {
        setPrincipalFinancialView();
        $('#change-financial-modal').modal({dismissible: false}).modal("open");
    });

    $("#btn-change-financial").on("click", function () {
        $("#change-financial-form").submit();
    });
}

function generateFinancialCompaniesTable(id) {
    var btn, span;
    oTable = $('#' + id).DataTable({
        "language": {
            "url": url_lang
        }
    });
}
function validateAddFinancialCompanyForm() {
    $("#add-financial-company-form").validate({
        rules: {
            'DF_RazonSocial': {
                required: true,
                maxlength: 100
            },
            'DF_RFC': {
                required: true, /*function (e) {
                 if ($("#DF_idPais").val() == "134" || $("#DF_idPais").val() == "193" || $("#DF_idPais").val() == "221") {
                 return true;
                 } else {
                 return false;
                 }
                 },*/
                maxlength: 100
            },
            'DF_idPais': {
                required: true
            },
            'DF_idEstado': {
                required: true
            },
            'DF_Ciudad': {
                required: true,
                maxlength: 200
            },
        },
        messages: {
            'DF_RazonSocial': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'DF_RFC': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'DC_idPais': {
                required: general_text.sas_requerido,
            },
            'DC_idEstado': {
                required: general_text.sas_requerido,
            },
            'DF_Ciudad': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
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
                $(placement).append(error);
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            $('#add-financial-company-modal').modal("close");
            disabled = $("#add-financial-company-form input:disabled").removeAttr("disabled");
            var post = $('#add-financial-company-form').serialize();
            if (action == "insert")
                addFinancialCompany(post);
            if (action == "update")
                updateFinancialCompany(post);
            return;
        }
    });
}
function validateChangeFinancialForm() {
    $("#change-financial-form").validate({
        rules: {
            'idActual': {
                required: true
            },
            'idNuevo': {
                required: true
            }
        },
        messages: {
            'idActual': {
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
            $('#change-financial-modal').modal("close");
            var post = $('#change-financial-form').serialize();
            changePrincipalFinancial(post);
            return;
        }
    });
}

function addFinancialCompany(post) {
    var keys = Object.keys(financial_companies);
    var total = keys.length;
    if (total > 0)
        post += "&Principal=false";
    else
        post += "&Principal=true";

    $.ajax({
        type: "post",
        url: url_financial_company_add,
        dataType: 'json',
        data: post,
        success: function (response) {
            disabled.attr("disabled", "disabled");
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            financial_companies[response.data["idEmpresaEntidadFiscal"]] = response.data;
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
function updateFinancialCompany(post) {
    post += "&idEmpresaEntidadFiscal=" + itemToUpdate;
    $.ajax({
        type: "post",
        url: url_financial_company_update,
        dataType: 'json',
        data: post,
        success: function (response) {
            disabled.attr("disabled", "disabled");
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            financial_companies[response.data["idEmpresaEntidadFiscal"]] = response.data;
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
function deleteFinancialCompany() {
    $.ajax({
        type: "post",
        url: url_financial_company_delete,
        dataType: 'json',
        data: {idEmpresaEntidadFiscal: itemToDelete},
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            delete financial_companies[response.data["idEmpresaEntidadFiscal"]]
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
function changePrincipalFinancial(post) {
    $.ajax({
        type: "post",
        url: url_financial_company_change,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            var currentPrincipal = financial_companies[response.data["idActual"]];
            currentPrincipal["Principal"] = "No";
            tr = $('tr#' + response.data["idActual"]);
            setRow(currentPrincipal, "update");

            var newPrincipal = financial_companies[response.data["idNuevo"]];
            newPrincipal["Principal"] = "Si";
            tr = $('tr#' + response.data["idNuevo"]);
            setRow(newPrincipal, "update");

            show_alert("success", general_text.sas_eliminoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}
function setFinancialCompanyData(id) {
    //$("#DF_RFC").removeAttr('disabled').parent('.input-field').fadeIn();
    var entity = financial_companies[itemToUpdate];
    $("#DF_RazonSocial").val(entity["DF_RazonSocial"]);
    $("#DF_RepresentanteLegal").val(entity["DF_RepresentanteLegal"]);
    $("#DF_Email").val(entity["DF_Email"]);
    $("#DF_idPais").val(entity['DF_idPais']).change();
    setTimeout(function () {
        $("#DF_idEstado").val(entity['DF_idEstado']).change();
    }, 2000);
    $("#DF_RFC").val(entity["DF_RFC"]);
    $("#DF_CodigoPostal").val(entity["DF_CodigoPostal"]);
    $("#DF_Ciudad").val(entity["DF_Ciudad"]);
    $("#DF_Colonia").val(entity["DF_Colonia"]);
    $("#DF_Calle").val(entity["DF_Calle"]);
    /*$("#DF_NumeroExterior").val(entity["DF_NumeroExterior"]);
     $("#DF_NumeroInterior").val(entity["DF_NumeroInterior"]);*/
    $("#Principal").val(entity["Principal"]);
    $("#add-financial-company-form input[type='text'], textarea").removeClass('valid').next().addClass('active');
}
function setPrincipalFinancialView() {
    var keys = Object.keys(financial_companies);
    var total = keys.length;

    for (var i = 0; i < total; i++) {
        if (financial_companies[keys[i]]["Principal"] == true || financial_companies[keys[i]]["Principal"] == "Si") {
            var principal = financial_companies[keys[i]];
        }
    }
    if (typeof principal === 'undefined') {
        $("#idActual").val("null");
        $("#RazonSocial").val("");
    } else {
        $("#idActual").val(principal["idEmpresaEntidadFiscal"]);
        $("#RazonSocial").val(principal["DF_RazonSocial"]);
    }
    $("#idNuevo").val("").change();
    $("#change-financial-form input[type='text']").removeClass('valid').next().addClass('active');
}

function getEstados(id, loader) {
    //$("#DF_RFC").removeAttr('disabled').parent('.input-field').fadeIn();
    var idPais = $("select#DF_idPais").val();
    var url = url_get_estados.replace("0000", idPais);
    var loader_element = loader;
    /*if (idPais != "134" && idPais != "193" && idPais != "221") {
     $("#DF_RFC").val('XAXX010101000').attr('disabled', true).siblings('label').addClass('active');
     } else {
     $("#DF_RFC").removeAttr('disabled').siblings('label').removeClass('active');
     }*/
    showProgressBar(loader_element);
    $.ajax({
        type: "get",
        url: url,
        dataType: 'json',
        beforeSend: function (xhr) {
            setProgressBar(loader_element, "50%");
        },
        success: function (result) {
            setProgressBar(loader_element, "70%");
            if (!result['status']) {
                show_modal_error(result['data']);
                hideProgressBar(loader_element);
                return;
            }
            var estados = result['data'];
            if (Object.keys(estados).length === 0) {
                hideProgressBar(loader_element);
                var html_estado = '<option value="0">' + general_text['sas_sinOpcion'] + '</option>';
                $("select#DF_idEstado").html(html_estado);
                return;
            }

            var html_estado = '<option value="">' + general_text['sas_seleccionaOpcion'] + '</option>';

            $.each(estados, function (index, value) {
                html_estado += '<option value="' + value['idEstado'] + '">';
                html_estado += value['Estado'];
                html_estado += '</option>';
            });

            $("select#DF_idEstado").html(html_estado);

            hideProgressBar(loader_element);
        },
        error: function (request, status, error) {
            hideProgressBar(loader_element);
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}
function clearForm(idForm) {
    $('#' + idForm).find('input').each(function (index, element) {
        if (!$(element).is(':disabled')) {
            $(element).removeClass('valid').next().removeClass('active');
        }
    });
    $('#' + idForm).find('input[type="text"]').not('input[type="text"]:disabled').val("");
    $('#' + idForm).find('input[type="email"]').not('input[type="email"]:disabled').val("");
    $('#' + idForm).find('input[type="tel"]').not('input[type="tel"]:disabled').val("");
    $('#' + idForm).find('textarea').not('textarea:disabled').val("");
    $('#' + idForm).find('select').not('select:disabled').val("");
    $('#' + idForm).find('input[type="checkbox"] input[type="radio"]').not('input[type="checkbox"]:disabled input[type="radio"]:disabled').prop('checked', false);
    //$("#DF_RFC").attr('disabled', true).parent('.input-field').hide();
}
function setRow(data, action) {
    if (data["Principal"] == "false" || data["Principal"] == "" || data["Principal"] == false || data["Principal"] == 0 || data["Principal"] == "null" || data["Principal"] == undefined)
        data["Principal"] = "No";
    if (data["Principal"] == "true" || data["Principal"] == true || data["Principal"] == 1)
        data["Principal"] = "Si";

    switch (action) {
        case 'insert':
            oTable.row.add([
                data.idEmpresaEntidadFiscal,
                data.DF_RazonSocial,
                data.DF_RFC,
                /*data.DF_RepresentanteLegal,
                 data.DF_Email,*/
                data["Principal"],
                '<i class="material-icons edit-record" data-id="' + data.idEmpresaEntidadFiscal + '">mode_edit</i>' +
                        '<i class="material-icons delete-record" data-id="' + data.idEmpresaEntidadFiscal + '">delete_forever</i>'
            ]).draw('full-hold');
            break;
        case 'update':
            oTable.row(tr).data([
                data.idEmpresaEntidadFiscal,
                data.DF_RazonSocial,
                data.DF_RFC,
                /*data.DF_RepresentanteLegal,
                 data.DF_Email,*/
                data["Principal"],
                '<i class="material-icons edit-record" data-id="' + data.idEmpresaEntidadFiscal + '">mode_edit</i>' +
                        '<i class="material-icons delete-record" data-id="' + data.idEmpresaEntidadFiscal + '">delete_forever</i>'
            ]).draw();
            break;
        case 'delete':
            oTable.row(tr).remove().draw();
            break;
    }
}
function setProgressBar(progressBar, progress) {
    $(progressBar + " .determinate").attr("style", "width: " + progress);
}
function showProgressBar(progressBar) {
    $('[loader-element="' + progressBar + '"]').attr('disabled', 'disabled');
    $(progressBar + " .determinate").attr("style", "width: 0%");
    $(progressBar + " .progress").fadeIn("fast");
}
function hideProgressBar(progressBar) {
    $(progressBar + " .determinate").attr("style", "width: 100%");
    setTimeout(function () {
        $(progressBar + " .progress").fadeOut("fast");
    }, 250);
    $('[loader-element="' + progressBar + '"]').removeAttr('disabled');
}
