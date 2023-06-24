var oTable = "";
$(document).ready(function () {
    initEmpresaLectoras();
});
function initEmpresaLectoras() {
    generateEmpresaLectorasReporteTable('expositores-lectoras-table');
    oTable.column(0).visible(false);

    $(".edit-record").on("click", function () {
        var link = url_edit_empresa_data + "/" + $(this).attr("data-id");
        window.location = link;
    });

}

function generateEmpresaLectorasReporteTable(id) {
    oTable = $('#' + id).DataTable({

        "language": {
            "url": url_lang
        },
        "order": [[0, "asc"]]
    });

}