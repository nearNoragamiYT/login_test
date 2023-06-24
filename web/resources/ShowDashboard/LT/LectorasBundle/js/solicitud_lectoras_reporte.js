var oTable = "";
$(document).ready(function () {
    initSolicitudLectoras();
});
function initSolicitudLectoras() {
    generateEmpresaLectorasReporteTable('reporte-solicitud-lectoras-table');
    $(".edit-record").on("click", function () {
        var link = url_edit_empresa_data + "/" + $(this).attr("data-id");
        window.location = link;
    });
}

function generateEmpresaLectorasReporteTable(id) {
    oTable = $('#' + id).DataTable({
        "language": {
            "url": url_lang
        }
    });
}