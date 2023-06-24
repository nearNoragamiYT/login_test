/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var advisorSelected = null;
$(init);

function init() {
    $(document).on('click', '.change-user', function () {
        advisorSelected = this;
    });
    $(document).on('change', '.change-user', function () {
        if ($(this).val() == "-1") {
            show_toast('info', section_text['sas_elegirAsesorValido']);
            $(advisorSelected).val($(advisorSelected).attr('data-original'));
            return;
        }
        showModalAdviser(this);
    });
    $("#change-adviser").on("click", function () {
        changeAdviser($(this).attr('data-id-company'), $(this).attr('data-id-user'), $(this).attr('data-id-original'));
    });


    $("#assigned-companies-table").IxpoFilters({
        headers: fieldsFilters, // REQUIRED data headers of table
        data: assigned_companies, // REQUIRED data for table
        idRecord: "idEmpresa", // REQUIRED id of record to identify used for update, delete or add
        configVariable: "config_empresas_asignadas", // REQUIRED name of variable to save conf in php
        filterVariable: "filters_post_empresas_asignadas", // REQUIRED name of variable to save filters applied in php
        urlFilters: url_apply_filters, // REQUIRED name of variable to save filters applied in php
        totalRecords: count, // REQUIRED name of variable to save filters applied in php
        filtersApplied: active_filters, // OPTIONAL filters applied in a post data
        filtersTitle: section_text.sas_filtrosAsesoresComerciales, // OPTIONAL filters applied in a post data
        config: config, // OPTIONAL query config to show
        recordsPerPage: [10, 25, 50, 100], // OPTIONAL list of options values for show records per page
    });
}

function showModalAdviser(ele) {
    var idEmpresa = $(ele).attr("data-id");
    var idOriginal = $(ele).attr("data-original");
    var idUsuario = $(ele).val();
    $("#modal-change-adviser").modal({
        dismissible: false,
        complete: function () {
            $(advisorSelected).val($(advisorSelected).attr('data-original'));
        }}).modal("open");
    $("#change-adviser").attr('data-id-company', idEmpresa);
    $("#change-adviser").attr('data-id-user', idUsuario);
    $("#change-adviser").attr('data-id-original', idOriginal);
}

function changeAdviser(idEmpresa, idUsuario, idOriginal) {
    show_loader_wrapper();
    $.ajax({
        type: "post",
        url: url_update_advisor,
        dataType: 'json',
        data: {idEmpresa: idEmpresa, idUsuario: idUsuario, idOriginal: idOriginal},
        success: function (response) {
            hide_loader_wrapper();
            $("#modal-change-adviser").modal("close");
            if (!response.status) {
                show_toast("warning", response.msj);
                $(advisorSelected).val($(advisorSelected).attr('data-original'));
                return;
            }
            var adviser = $(advisorSelected).find('option[value="' + idUsuario + '"]').text();
            $(advisorSelected).attr("data-original", idUsuario);
            $(advisorSelected).parent().attr('data-search', adviser);
            $(advisorSelected).parent().attr('data-order', adviser);
            show_toast("success", general_text.sas_guardoExito);
        },
        error: function (response) {
            hide_loader_wrapper();
            show_modal_error(general_text.sas_errorInterno + "<br>" + response.responseText);
        }
    });
}


