var leyendaB = '';
var leyendaR = '';
var acumulado = '';
var tipo = "dia";
$(document).ready(function () {
    $('#statistics').DataTable({
        "order": [[ 1, "desc" ]],
        "language": {
            "url": url_lang
        }
    });
    fillExportArray(data_graph);
    $(".period").css("display", "none");
    leyendaB = left_label;
    leyendaR = left_label;
    acumulado = right_label;
    google.charts.setOnLoadCallback(drawLineChart);
    window.onresize = drawLineChart;

    var reportcolumns = ["#", "Dia", "Total Dia", "Acumulado"];
    var title = (lang === "es") ? "Estadistica por" + ' ' + "Dia" : "Day" + ' ' + "Statistic";

    $(".export-records").click(function () {
        var repotitle = $("#idEdition option:selected").text().replace('-', '') + " - " + title;
        performExport(exportArray, repotitle, reportcolumns, -1);
    });
});