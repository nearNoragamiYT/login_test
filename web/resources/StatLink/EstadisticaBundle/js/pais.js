var leyendaB = '';
var preregistrados = '';
var tipo = "pais";
var tipoMapa = 'world';
var resolucion = 'countries';
$(document).ready(function () {
    $('#statistics').DataTable({
        "order": [[ 1, "asc" ]],
        "language": {
            "url": url_lang
        }
    });
    fillExportArray(data_graph);
    $(".period").css("display", "none");
    leyendaB = country_label;
    preregistrados = preregister_label;
    google.charts.setOnLoadCallback(drawGeoChart);
    window.onresize = drawGeoChart;

    var reportcolumns = ["#", "Pais", "Total Preregistros"];
    var title = (lang === "es") ? "Estadistica" + ' ' + "Pais" : "Country" + ' ' + "Statistics";

    $(".export-records").click(function () {
        var repotitle = $("#idEdition option:selected").text().replace('-', '') + " - " + title;
        performExport(exportArray, repotitle, reportcolumns, -1);
    });
});