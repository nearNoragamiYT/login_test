var oTable = "";
$(document).ready(function () {
    initEmpresaLectoras();
});
function initEmpresaLectoras() {
    generateEmpresaLectorasReporteTable('reporte-empresa-lectoras-table');
}

function generateEmpresaLectorasReporteTable(id) {
    oTable = $('#' + id).DataTable({
        "language": {
            "url": url_lang
        }
    });
}