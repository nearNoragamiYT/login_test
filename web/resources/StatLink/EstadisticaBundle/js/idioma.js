var columna1_label = '';
var leyendaB = '';
var leyendaR = '';
var tipo = "idioma";
$(document).ready(function () {
    $('#statistics').DataTable({
        "language": {
            "url": url_lang
        }
    });
    fillExportArray(data_graph);
    $(".period").css("display", "none");
    columna1_label = column_label;
    google.charts.setOnLoadCallback(drawColumnChart);
    window.onresize = drawColumnChart;

//    var reportcolumns = ["#", textosGenerales.lb_clave_campania, textosGenerales.descripcion, textosGenerales.registrados];
//    var title = (lang === "es") ? textosGenerales.estadistica + ' ' + textosGenerales.lb_campania : textosGenerales.lb_campania + ' ' + textosGenerales.estadistica;
//
//    $(".export-records").click(function () {
//        var repotitle = $(".header-title").text().replace('-', '') + " - " + title;
//        performExport(exportArray, repotitle, reportcolumns, -1);
//    });

});