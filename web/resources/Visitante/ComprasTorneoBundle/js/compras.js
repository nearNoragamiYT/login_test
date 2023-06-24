$(document).ready(function () {
    init();
    $('input.oculto').parent().hide()
    $(".generate-table-invisible").trigger("click");
    show_loader_wrapper();
    $('.datepicker').pickadate({
        selectMonths: true,
        selectYears: 16,
        format: 'yyyy-mm-dd'
    });

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
        dt = $("#ComprasTorneo-table").dataTable();
        $('.input-sm > option[value="' + data.seting.DisplayLength + '"]').attr("selected", true);
        var oSettings = dt.fnSettings();
        oSettings._iDisplayLength = Number(data.seting.DisplayLength);
        dt.fnDraw();
        setTimeout(function () {
            dt.fnSort([Number(data.seting.sort.index), data.seting.sort.order]);
        }, 1500);
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
            var str = data.param[i].text
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
        "table_name": "ComprasTorneo-table",
        "wrapper": "cover-ComprasTorneo-table",
        "columns": Visitante_table_columns,
        "column_categories": Visitante_table_column_categories,
        "text_datatable": url_lang,
        "custom_filters": true,
        "server_side": true,
        "cache_data": true,
        "cache_pages": 10,
        "url_get_data": url_compras_torneo_get_to_dt,
        "url_get_data_filtro": url_compras_torneo_get_to_dt_filtro,
        "export_data": true,
        "url_export_data": url_compras_torneo_export_data,
        "callback_init": callbackEmpresaTable,
        "row_column_id": 'idCompra',
        "Editor_row": true,
        "lang": lang,
    });

    $(document).on("click", ".edit-record", function () {
        show_loader_wrapper();
        var id_vis = $(this).attr("vis-id");
        var id_comp = $(this).attr("data-id");
        seting = $('#ComprasTorneo-table').dataTable().fnSettings();
        var data = {DisplayLength: seting._iDisplayLength, page: (seting._iDisplayStart / seting._iDisplayLength), sort: {index: seting.aaSorting[0][0], order: seting.aaSorting[0][1]}};
        $.ajax({
            type: "post",
            dataType: 'json',
            data: data,
            url: url_set_session,
            success: function () {
                window.location = url_data_compra + '/' + id_vis + '/' + id_comp;
                $("#visitante-datosgenerales").attr("class", "active");
            },
            error: function () {
                hide_loader_wrapper();
                show_modal_error("Ocurrio un Error, Intente de Nuevo");
            },
        });
    });
}

function callbackEmpresaTable($data_table) {
    empresaTable = $data_table;
    // LENGTH - Inline-Form control
    var length_sel = empresaTable.closest('.dataTables_wrapper').find('div[id$=_length] select');
    length_sel.addClass('form-control input-sm');
}


