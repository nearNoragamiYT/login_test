var oTable = "", tr = "", action = "", idEmpresaEmail = "", exhibitors = "", nombreComercial = "", index = 0, companies = {};

$(document).ready(function () {
    init();
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

    $("#update-floorplan").click(function () {
        show_loader_wrapper();
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: url_update_floorplan,
            success: function (response, text) {
                $("#loader").slideUp();
                if (response.status) {
                    show_alert("success", 'La informacion del Plano ha sido Actualizada');
                }
                else {
                    show_alert("danger", 'Error al intentar actualizar la informacion del Plano');
                }
            },
            error: function (request, status, error) {
                $("#loader").slideUp();
                show_alert("danger", 'Error al intentar actualizar la informacion del Plano');
            }
        });
        hide_loader_wrapper();
    });
});

function session(data) {
    if (data.seting != null) {
        var dt = $("#Empresa-table").dataTable();
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
    if (data.param.length > 1) {
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
        "table_name": "Empresa-table",
        "wrapper": "cover-Empresa-table",
        "columns": Empresa_table_columns,
        "column_categories": Empresa_table_column_categories,
        "text_datatable": url_lang,
        "custom_filters": true,
        "server_side": true,
        "cache_data": true,
        "cache_pages": 10,
        "url_get_data": url_empresa_get_to_dt,
        url_get_data_filtro: url_empresa_get_to_dt_filtro,
        "export_data": true,
        "url_export_data": url_export_empresa_data,
        "callback_init": callbackEmpresaTable,
        "row_column_id": 'CodigoCliente',
        "edit_rows": false,
        "Empresa_row": true,
        "lang": lang,
    });

    $(document).on("click", ".edit-record", function () {
        var link = $(this).attr("link");
        seting = $('#Empresa-table').dataTable().fnSettings();
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

    $(document).on("click", ".gafete-record", function () {
        idEmailGafetes = $(this).attr("data-email");
        idEmpresaEmail = $(this).attr("data-id");
        nombreComercial = $(this).attr("data-NC");
        $("#text-gafete").html(section_text["sas_preguntaEnvioGafete"].replace("%company_name%", nombreComercial));
        $('#send-gafete-modal').modal({dismissible: false}).modal("open");
    });

    $(document).on("click", ".email-record", function () {
        idEmailGafetes = $(this).attr("data-email");
        idEmpresaEmail = $(this).attr("data-id");
        nombreComercial = $(this).attr("data-NC");
        $("#text-email").html(section_text["sas_preguntaEnvioEmail"].replace("%company_name%", nombreComercial));
        $('#send-email-modal').modal({dismissible: false}).modal("open");
    });

    $('#send-email').click(function () {
        companies['tipoCorreo'] = idEmailGafetes
        companies['idEmpresa'] = idEmpresaEmail
        show_loader_wrapper();
        index = 1;
        var lang = $('input[name=lang]:checked').val();
        if (lang === "") {
            companies['lang'] = "ES";
        } else {
            companies['lang'] = lang;
        }
        sendEmailED(companies);
    });

    $('#send-email-gafete').click(function () {
        companies['tipoCorreo'] = idEmailGafetes
        companies['idEmpresa'] = idEmpresaEmail
        show_loader_wrapper();
        index = 1;
        var lang = $('input[name=lang]:checked').val();
        if (lang === "") {
            companies['lang'] = "ES";
        } else {
            companies['lang'] = lang;
        }
        sendEmailED(companies);
    });

    $('#send-email-to-all').click(function () {
        var table = document.getElementsByTagName("table");
        table = table[0].id;
        dt = $("#" + table).DataTable();
        var oSettings = dt.settings();
        $("#send-email-all-body").html(section_text.sas_textoEnviarEmailTodos.replace("%index%", oSettings.rows()[0].length));
        $('#send-email-all-modal').modal({dismissible: false}).modal("open");
    });

    $("#send-all").click(function () {
        show_loader_wrapper();
        sendEmailED(null);
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
function sendEmailED(data) {

    hide_loader_wrapper();
    $.ajax({
        type: "post",
        dataType: 'json',
        data: data,
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
    $('#send-gafete-modal').modal("close");
    $('#send-email-all-modal').modal("close");
}
