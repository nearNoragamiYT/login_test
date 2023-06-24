$(document).ready(function () {
    init();
    var inv = $('.custom-filters').find('div.col.s4');
    inv = inv.last();
    inv[0].style.display = 'none';
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
        var dt = $("#Visitante-table").dataTable();
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
    //
    if(Usuario['idUsuario'] == 35 || Usuario['idUsuario'] == 1|| Usuario['idUsuario'] == 52){
        var campos = {
            "table_name": "Visitante-table",
            "wrapper": "cover-Visitante-table",
            "columns": Visitante_table_columns,
            "column_categories": Visitante_table_column_categories,
            "text_datatable": url_lang,
            "custom_filters": true,
            "server_side": true,
            "cache_data": true,
            "cache_pages": 10,
            "url_get_data": url_visitante_get_to_dt,
            "url_get_data_filtro": url_visitante_get_to_dt_filtro,
            "export_data": true,
            "url_export_data": url_visitors_export_data,
            "callback_init": callbackVisitanteTable,
            "row_column_id": 'idVisitante',
            "Visitor_row_v": true,
            "lang": lang,
        };
    }
    else{
        var campos = {
            "table_name": "Visitante-table",
            "wrapper": "cover-Visitante-table",
            "columns": Visitante_table_columns,
            "column_categories": Visitante_table_column_categories,
            "text_datatable": url_lang,
            "custom_filters": true,
            "server_side": true,
            "cache_data": true,
            "cache_pages": 10,
            "url_get_data": url_visitante_get_to_dt,
            "url_get_data_filtro": url_visitante_get_to_dt_filtro,
            "export_data": true,
            "url_export_data": url_visitors_export_data,
            "callback_init": callbackVisitanteTable,
            "row_column_id": 'idVisitante',
            "Visitor_row": true,
            "lang": lang,
        };
    }
    
    init_table(campos);

    $(document).on("click", ".edit-record", function () {
        var link = $(this).attr("link");
        seting = $('#Visitante-table').dataTable().fnSettings();
        var data = {DisplayLength: seting._iDisplayLength, page: (seting._iDisplayStart / seting._iDisplayLength), sort: {index: seting.aaSorting[0][0], order: seting.aaSorting[0][1]}};
        $.ajax({
            type: "post",
            dataType: 'json',
            data: data,
            url: url_set_session,
            success: function () {
                show_loader_wrapper();
                window.location = link;
            },
            error: function () {
                hide_loader_wrapper();
                show_modal_error("Ocurrio un Error, Intente de Nuevo");
            },

        });
    });

    $(document).on("click",".send-badge",function(){
    	show_loader_wrapper();
        var id = $(this).attr("data-badge"); //idVisitante

        $.ajax({
            type: "post",
            url: url_send_digibadge,
            dataType: "json",
            data : { "idVisitante": id },
            success: function(response){
                if (!response['status']) {
                    show_toast("danger", response["data"]);
                     hide_loader_wrapper();
                    return;
                }
                else{
                    show_toast("success", "Enviado Con Exito");

                }
                location.reload();
                hide_loader_wrapper();
            },
            error: function(request, status, error){
                alert('ERROR SEND');
                 hide_loader_wrapper();
            }
        });
    });
}

function callbackVisitanteTable($data_table) {
    empresaTable = $data_table;
    // LENGTH - Inline-Form control
    var length_sel = empresaTable.closest('.dataTables_wrapper').find('div[id$=_length] select');
    length_sel.addClass('form-control input-sm');
}

$(document).on("click", ".download-gafete", function () {
    let data = $(this).attr("data-id");
    let post = "&idVisitante=" + data;
    show_loader_wrapper();
    $.ajax({
        type: "post",
        url: url_download_gafete,
        xhrFields: {
            responseType: 'blob'
        },
        data: post,
        success: function (response, status, xhr) {
            var URL = window.URL || window.webkitURL;
            var downloadUrl = URL.createObjectURL(response);
            var a = document.createElement("a");

            a.href = downloadUrl;
            a.download = data;
            a.click();

            setTimeout(function () {
                URL.revokeObjectURL(downloadUrl);
            }, 1000);
            location.reload();
            show_toast("success", "Descargado con éxito");
            hide_loader_wrapper();
        },
        error: function (request, status, error) {
            show_modal_error("Ocurrio un Error, Intente de Nuevo");
            hide_loader_wrapper();
        }
    });
});

