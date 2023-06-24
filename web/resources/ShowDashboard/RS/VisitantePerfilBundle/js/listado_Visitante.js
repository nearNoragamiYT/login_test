oTable = "";
$(init);

function init() {

    init_table({
        "table_name": "Rs_Visitor-table",
        "wrapper": "cover-Rs_Visitor-table",
        "columns": rs_visitante_table_columns,
        "column_categories": rs_visitante_table_column_categories,
        "text_datatable": url_lang,
        "custom_filters": true,
        "server_side": true,
        "cache_data": true,
        "cache_pages": 10,
        "url_get_data": url_rs_visitante_get_to_dt,
        "url_get_data_filtro": url_rs_visitante_get_to_dt_filtro,
        "export_data": true,
        "url_export_data": url_export_rs_visitante_data,
        "callback_init": callbackRsVisitanteTable,
        "row_column_id": 'idVisitor',
        "edit_rows": false,
        "RsVisitor_row": true,
        "lang": lang,
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
    }, 1500);

    $(".generate-table-invisible").trigger("click");

    $(document).on("click", ".edit-record", function () {
        var link = $(this).attr("link");
        var table = document.getElementsByTagName("table");
        table = table[0].id
        seting = $('#' + table).dataTable().fnSettings();
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

}

function callbackRsVisitanteTable($data_table) {
    hotelesTable = $data_table;
    // LENGTH - Inline-Form control
    var length_sel = hotelesTable.closest('.dataTables_wrapper').find('div[id$=_length] select');
    length_sel.addClass('form-control input-sm');
}

function session(data) {
    if (data.seting != null) {
        var table = document.getElementsByTagName("table");
        table = table[0].id;
        dt = $("#" + table).dataTable();
        $('.input-sm > option[value="' + data.seting.DisplayLength + '"]').attr("selected", true);
        var oSettings = dt.fnSettings();
        oSettings._iDisplayLength = Number(data.seting.DisplayLength);
        dt.fnDraw();
        setTimeout(function () {
            dt.fnSort([Number(data.seting.sort.index), data.sort.order]);
        }, 1500);
        setTimeout(function () {
            dt.fnPageChange(Number(data.seting.page));
        }, 3500);
        setTimeout(function () {
            hide_loader_wrapper();
        }, 4000);

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