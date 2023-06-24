$(init);
function init() {
    init_table({
        "table_name": "Contrato-table",
        "wrapper": "cover-contrato-table",
        "columns": Contrato_table_columns,
        "column_categories": Contrato_table_column_categories,
        "text_datatable": url_lang,
        "custom_filters": true,
        "server_side": true,
        "cache_data": true,
        "cache_pages": 10,
        "url_get_data": url_contrato_get_to_dt,
        url_get_data_filtro: url_contrato_get_to_dt_filtro,
        "export_data": false,
        "url_export_data": url_export_contrato_data,
        "callback_init": callbackContratoTable,
        "row_column_id": 'idContrato',
        "edit_rows": false,
        "Empresa_row": false,
        "Ventas_row": true,
        "lang": lang,
    });
}

function callbackContratoTable($data_table) {
    empresaTable = $data_table;
    // LENGTH - Inline-Form control
    var length_sel = empresaTable.closest('.dataTables_wrapper').find('div[id$=_length] select');
    length_sel.addClass('form-control input-sm');
}
