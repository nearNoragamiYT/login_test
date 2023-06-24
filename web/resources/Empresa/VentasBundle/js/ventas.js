var oTable = "", tr = "", action = "", itemToUpdate = "", itemToDelete = "", idEmpresaEmail = "", exhibitors = "", index = 0;

$(document).ready(function () {
    init();
    $('select.oculto').parent().hide()
    $(".generate-table-invisible").trigger("click");
    show_loader_wrapper();
    if (lang == 'es') {
        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: 16,
            monthsFull: ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
            monthsShort: ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'],
            weekdaysFull: ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'],
            weekdaysShort: ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'],
            today: 'hoy',
            clear: 'borrar',
            close: 'cerrar',
            format: 'yyyy-mm-dd',
        });
    } else {
        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: 16,
            format: 'yyyy-mm-dd'
        });
    }
    setTimeout(function () {
        $.ajax({
            type: "post",
            dataType: 'json',
            url: url_get_session,
            success: function (Response) {
                session(Response);
            },
            error: function () {
                hide_loader_wrapper();
                show_modal_error("Ocurrio un Error, Intente de Nuevo");
            },
        });
    }, 1000);
});

function session(data) {
    if (data.seting != null) {
        var dt = $("#Ventas-table").dataTable();
        $('.input-sm > option[value="' + data.seting.DisplayLength + '"]').attr("selected", true);
        var oSettings = dt.fnSettings();
        oSettings._iDisplayLength = Number(data.seting.DisplayLength);
        dt.fnDraw();
//        setTimeout(function () {
//            dt.fnSort([Number(data.seting.sort.index), data.seting.sort.order]);
//        }, 1500);
        setTimeout(function () {
            dt.fnPageChange(Number(data.seting.page));
        }, 2000);
        setTimeout(function () {
            hide_loader_wrapper();
        }, 2100);

    } else {
        hide_loader_wrapper();
    }
    if (data.param.length > 0) {
        var summary = $(".summary-detail");
        summary.empty();
        for (var i = 0; i < data.param.length; i++) {
            var str = data.param[i].name
            str = str.replace(new RegExp("\"", "g"), "");
            if (data.columns[str] !== undefined) {
                var chip = data.columns[str].text;
                if (data.columns[str].filter_options.is_select) {
                    chip = chip + ": <b>" + data.columns[str].filter_options.values[data.param[i].value] + "</b>";
                } else {
                    chip = chip + ": <b>" + data.param[i].value + "</b>";
                    chip = chip.replace(new RegExp("%", "g"), "");
                }
                item_div = $('<div/>', {'class': 'chip'});
                item_div.html(chip);
                summary.append(item_div);
            }
        }
    }
}

function init() {
    init_table({
        "table_name": "Ventas-table",
        "wrapper": "cover-ventas-table",
        "columns": Empresa_table_columns,
        "column_categories": Empresa_table_column_categories,
        "text_datatable": url_lang,
        "custom_filters": true,
        "server_side": true,
        "cache_data": true,
        "cache_pages": 10,
        "url_get_data": url_empresa_get_to_dt,
        url_get_data_filtro: url_empresa_get_to_dt_filtro,
        "export_data": false,
        "url_export_data": url_export_empresa_data,
        "callback_init": callbackEmpresaTable,
        "row_column_id": 'CodigoCliente',
        "edit_rows": false,
        "Empresa_row": false,
        "Ventas_row": true,
        "lang": lang,
    });

//    generateCompaniesTable('sales-table');

    validateAddCompanyForm();

    $(document).on("click", ".edit-record", function () {
        var link = $(this).attr("link");
        seting = $('#Ventas-table').dataTable().fnSettings();
        var data = {DisplayLength: seting._iDisplayLength, page: (seting._iDisplayStart / seting._iDisplayLength), sort: {index: seting.aaSorting[0][0], order: seting.aaSorting[0][1]}};
        $.ajax({
            type: "post",
            dataType: 'json',
            data: data,
            url: url_set_session,
            success: function () {
                show_loader_wrapper();
                window.location = link;
            }
        });
    });

    $("#btn-add-company").on("click", function () {
        $("#add-company-form").submit();
    });

    $("#add-company").on('click', function () {
        clearForm('add-company-form');
        action = "insert";
        $('#add-company-modal').modal({dismissible: false}).modal("open");
    });

    $("#delete-record").on("click", function () {
        $("#delete-record-modal").modal("close");
        deleteCompany();
    });
    $(document).on("click", ".delete-record", function () {
        itemToDelete = $(this).attr("data-id");
        var nombreComercial = $(this).attr("data-NC");
        tr = $(this).parents("tr");
        action = "delete";
        $("#deleteText").html(section_text["sas_textoEliminarRegistro"] + ' ' + nombreComercial + "?");
        $("#delete-record-modal").modal({dismissible: false}).modal("open");
    });

    $(document).on("click", ".email-record", function () {
        idEmpresaEmail = $(this).attr("data-id");
        tr = $(this).parents("tr");
        $("#text-email").html(section_text["sas_preguntaEnvioEmail"].replace("%company_name%", companies[idEmpresaEmail]["DC_NombreComercial"]));
        $('#send-email-modal').modal({dismissible: false}).modal("open");
    });

    $('#send-email').click(function () {
        show_loader_wrapper();
        index = 1;
        var lang = $('input[name=lang]:checked').val();
        if (lang === "") {
            companies[idEmpresaEmail]['LANG'] = "ES";
        } else {
            companies[idEmpresaEmail]['LANG'] = lang;
        }
        sendEmailED([companies[idEmpresaEmail]]);
    });

    $('#send-email-to-all').click(function () {
        exhibitors = new Array();
        index = 0;
        var keys_companies = Object.keys(companies);
        var total_companies = keys_companies.length;
        for (var i = 0; i < total_companies; i++) {
            if ((companies[keys_companies[i]]['idEtapa'] == 1 || companies[keys_companies[i]]['idEtapa'] == 2) && companies[keys_companies[i]]['Email'] != "") {
                exhibitors[index] = {
                    "idEmpresa": companies[keys_companies[i]]['idEmpresa'],
                    "idEdicion": companies[keys_companies[i]]['idEdicion'],
                    "DC_NombreComercial": companies[keys_companies[i]]['DC_NombreComercial'],
                    "Email": companies[keys_companies[i]]['Email'],
                    "Password": companies[keys_companies[i]]['Password'],
                    "idPais": companies[keys_companies[i]]['DC_idPais'],
                    "Pais": companies[keys_companies[i]]['DC_Pais']
                };
                index++;
            }
        }
        $("#send-email-all-body").html(section_text.sas_textoEnviarEmailTodos.replace("%index%", index));
        $('#send-email-all-modal').modal({dismissible: false}).modal("open");
    });

    $("#send-all").click(function () {
        show_loader_wrapper();
        sendEmailED(exhibitors);
    });
}

function callbackEmpresaTable($data_table) {
    empresaTable = $data_table;
    // LENGTH - Inline-Form control
    var length_sel = empresaTable.closest('.dataTables_wrapper').find('div[id$=_length] select');
    length_sel.addClass('form-control input-sm');
}

function generateCompaniesTable(id) {
    oTable = $('#' + id).DataTable({
        "language": {
            "url": url_lang
        }
    });
}
function validateAddCompanyForm() {
    $("#add-company-form").validate({
        rules: {
            'CodigoCliente': {
                required: true,
                maxlength: 100
            },
            'idEmpresaTipo': {
                required: true
            },
            'DC_NombreComercial': {
                required: true,
                maxlength: 100
            },
            'DC_idPais': {
                required: true
            },
            'Nombre': {
                required: true,
                maxlength: 200
            },
            'ApellidoPaterno': {
                required: true,
                maxlength: 200
            },
            'ApellidoMaterno': {
                required: true,
                maxlength: 200
            },
            'Puesto': {
                required: true,
                maxlength: 200
            },
            'Email': {
                required: true,
                email: true,
                maxlength: 100
            },
            'Telefono': {
                required: true
            },
        },
        messages: {
            'CodigoCliente': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'idEmpresaTipo': {
                required: general_text.sas_requerido,
            },
            'DC_NombreComercial': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'DC_idPais': {
                required: general_text.sas_requerido,
            },
            'Nombre': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'ApellidoPaterno': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'ApellidoMaterno': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'Puesto': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'Email': {
                required: general_text.sas_requerido,
                email: general_text.sas_ingresaCorreoValido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'Telefono': {
                required: general_text.sas_requerido
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
            show_loader_wrapper();
            $('#add-company-modal').modal("close");
            var post = $('#add-company-form').serialize();
            addCompany(post);
            return;
        }
    });
}

function addCompany(post) {
    $.ajax({
        type: "post",
        url: url_company_add,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                hide_loader_wrapper();
                show_alert("danger", response['data']);
                return;
            }
            window.location.replace(url_company_comercial.replace("0000", response.data["idEmpresa"]));
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function deleteCompany() {
    $.ajax({
        type: "post",
        url: url_company_delete,
        dataType: 'json',
        data: {idEmpresa: itemToDelete},
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            show_alert("success", general_text.sas_eliminoExito);
            setTimeout(function () {
                location.reload();
            }, 1000);

        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function sendEmailED(data) {
    hide_loader_wrapper();
    $.ajax({
        type: "post",
        dataType: 'json',
        async: false,
        data: {
            exhibitors: data
        },
        url: url_company_send_welcome_ed,
        success: function (response) {
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            if (index == 1)
                show_alert("success", section_text.sas_exitoEnvioIndividual);
            else
                show_alert("success", section_text.sas_exitoEnvioTodos);
        },
        error: function (request, status, error) {
            show_modal_error(request.responseText);
        }
    });
    $('#send-email-modal').modal("close");
    $('#send-email-all-modal').modal("close");
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
}
function setRow(data, action) {
    if (data.idEvento == 1)
        data.idEvento = "ESM";
    if (data.idEvento == 2)
        data.idEvento = "ESI";
    switch (action) {
        case 'insert':
            oTable.row.add([
                data.idEmpresa,
                data.CodigoCliente,
                data.idEvento,
                data.DC_NombreComercial,
                data.Email,
                data.Password,
                section_text["sas_prospecto"],
                '<span id="add-' + data.idEmpresa + '"><a href="#!">' + section_text["sas_agregarContrato"] + '</a></span>',
                '<i class="material-icons edit-record" data-id="' + data.idEmpresa + '">mode_edit</i>' +
                        '<i class="material-icons delete-record" data-id="' + data.idEmpresa + '">delete_forever</i>'
            ]).draw('full-hold');
            break;
        case 'delete':
            oTable.row(tr).remove().draw();
            break;
    }
}