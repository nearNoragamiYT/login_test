var leyendaB = '';
var preregistrados = '';
//var acumulado = textosGenerales.acumulado;
var tipo = "estado";
var tipoMapa = 'MX';
var resolucion = 'provinces';

$(document).ready(function () {
    $('#statistics').DataTable({
        "order": [[ 1, "asc" ]],
        "language": {
            "url": url_lang
        }
    });
    fillExportArray(data_graph);
    $(".period").css("display", "none");
    leyendaB = state_label;
    preregistrados = preregister_label;
    google.charts.setOnLoadCallback(drawGeoChart);
    window.onresize = drawGeoChart;

    var reportcolumns = ["#", "Pais", "Preregistrados"];
    var title = (lang === "es") ? "Estadisticas" + ' ' + "Estado" : "State" + ' ' + "Statistic";

    $(".export-records").click(function () {
        var repotitle = $("#idEdition option:selected").text().replace('-', '') + " - " + title;
        performExport(exportArray, repotitle, reportcolumns, -1);
    });
});