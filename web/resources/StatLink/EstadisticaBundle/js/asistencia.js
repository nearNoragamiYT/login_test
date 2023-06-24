var columna1_label = 'Asistencia por día';
var leyendaB = '';
var leyendaR = '';
var tipo = "asistencia";
$(document).ready(function () {
    $('#statistics').DataTable({
        "language": {
            "url": url_lang
        }
    });    
    fillExportArray(data_graph);
    $(".period").css("display", "none");
    leyendaB = "Día";
    leyendaR = "Número de Asistentes";
    google.charts.setOnLoadCallback(drawColumnChart);
    window.onresize = drawColumnChart;

    var reportcolumns = ["#", "Dia", "Total Asistencia", "Acumulado"];
    var title = (lang === "es") ? "Estadistica por" + ' ' + "Dia" : "Day" + ' ' + "Statistic";

    $(".export-records").click(function () {
        var repotitle = $("#idEdition option:selected").text().replace('-', '') + " - " + title;
        performExport(exportArray, repotitle, reportcolumns, -1);
    });
    setTimeout(function(){
        window.location.reload(1);
    },30000);
});