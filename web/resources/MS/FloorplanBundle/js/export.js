var visitorTable;
function initVisitorTable() {
    init_table({
        "table_name": "visitor-table",
        "wrapper": "cover-visitor-table",
        "columns": visitor_table_columns,
        "column_categories": visitor_table_column_categories,
        "text_datatable": url_lang,
        "custom_filters": true,
        "server_side": true,
        "cache_data": true,
        "cache_pages": 10,
        "url_get_data": url_visitor_get_to_dt,
        "export_data": true,
        "url_export_data": url_export_general_data,
        "callback_init": callbackVisitorTable,
        "row_column_id": 'idVisitante',
        "edit_rows": false,
        "fn_edit_row": function ($row, row_index, row_data) {
            var _id_Visitante = row_data['idVisitante'];
            var last_view = request_processing.current_view;

            if (typeof current_visitor['idVisitante'] == 'undefined' || current_visitor['idVisitante'] !== _id_Visitante) {
                last_view = "general_data";
                current_visitor['idVisitante'] = _id_Visitante;
                current_visitor.row_dt = {
                    "index": $row,
                    "dom_obj": $row
                };
            }
            slideData(last_view);
        }
    });
}

function callbackVisitorTable($data_table) {
    visitorTable = $data_table;
    // LENGTH - Inline-Form control
    var length_sel = visitorTable.closest('.dataTables_wrapper').find('div[id$=_length] select');
    length_sel.addClass('form-control input-sm');
}