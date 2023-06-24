$(document).ready(function () {
  init();
});


function init() {
       init_table({
        "table_name": "log-table",
        "wrapper": "cover-log-table",
        "columns": log_table_columns,
        "column_categories": log_table_column_categories,
        "text_datatable": url_lang,
        "custom_filters": true,
        "server_side": true,
        "cache_data": true,
        "cache_pages": 10,
        "url_get_data": url_log_get_to_dt,
        "export_data": true,
        "url_export_data": url_export_log_data,
        "callback_init": callbackLogTable,
        "row_column_id": 'idLog',
        "edit_rows": false,
        "lang": lang,
    });
    
}
function callbackLogTable($data_table) {
    logTable = $data_table;
    // LENGTH - Inline-Form control
    var length_sel = logTable.closest('.dataTables_wrapper').find('div[id$=_length] select');
    length_sel.addClass('form-control input-sm');
}

