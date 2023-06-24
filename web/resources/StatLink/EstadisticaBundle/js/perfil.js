var leyendaB = "";
var leyendaR = "";
var columna1_label = "";
var columna2_label = "";
var tipo = "perfil";
$(document).ready(function () {
    $('#statistics').DataTable({
        "language": {
            "url": url_lang
        }
    });
    fillExportArray(data_graph);
    $(".period").css("display", "none");
    //leyendaB = textosGenerales.perfil;
//    leyendaR = 'Total';
    columna1_label = column_label;
    google.charts.setOnLoadCallback(drawColumnChart);
    window.onresize = drawColumnChart;

    //var reportcolumns = ["#", textosGenerales.perfil, textosGenerales.total_label];
    //var title = (lang === "es") ? textosGenerales.estadistica + ' ' + textosGenerales.perfil : textosGenerales.perfil + ' ' + textosGenerales.estadistica;

//    $(".export-records").click(function () {
//        var repotitle = $("#idEdition option:selected").text().replace('-', '') + " - " + title;
//        console.log(repotitle);
//        performExport(exportArray, repotitle, reportcolumns, -1);
//    });
});