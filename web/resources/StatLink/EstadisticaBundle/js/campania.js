var columna1_label = '';
var leyendaB = '';
var leyendaR = '';
var tipo = "campania";
$(document).ready(function () {
    $('#statistics').DataTable({
        "language": {
            "url": url_lang
        }
    });
    fillExportArray(data_graph);
    $(".period").css("display", "none");
    columna1_label = column_label;
    google.charts.setOnLoadCallback(drawColumnChartX);
    window.onresize = drawColumnChartX;

    var reportcolumns = ["#", "Dia", "Total Dia", "Acumulado"];
    var title = (lang === "es") ? "Estadistica por" + ' ' + "Dia" : "Day" + ' ' + "Statistic";

    $(".export-records").click(function () {
        var repotitle = $("#idEdition option:selected").text().replace('-', '') + " - " + title;
        performExport(exportArray, repotitle, reportcolumns, -1);
    });
});