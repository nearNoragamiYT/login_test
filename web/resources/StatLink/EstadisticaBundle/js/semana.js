var leyendaB = '';
var leyendaR = '';
var acumulado = '';
var tipo = 'semana';

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

    var reportcolumns = ["#", "Fecha Inicio", "Fecha Termino", "Preregistros por Semana", "Preregistros Acumulados"];
    var title = (lang === "es") ? "Estadistica" + ' ' + "Semana" : "Week" + ' ' + "Statistic";

    $(".export-records").click(function () {
        var repotitle = $("#idEdition option:selected").text().replace('-', '') + " - " + title;
        performExport(exportArray, repotitle, reportcolumns, -1);
    });

});