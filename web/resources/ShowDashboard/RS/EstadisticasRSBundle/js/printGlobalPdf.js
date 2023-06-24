google.charts.load("visualization", "1", {packages: ["corechart"]});
google.charts.setOnLoadCallback(AsistenciaDia);

google.charts.load('current', {'packages': ['bar']});
google.charts.setOnLoadCallback(Comparativo);

google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(AsistenciaHora);

var suma_asisDia = "";
var suma_dia1 = 0;
var suma_dia2 = 0;
var suma_hora = "";

var img64 = [];
$("#all-grafic").click(function () {
    show_loader_wrapper();
    var doc = new jsPDF('landscape');
    $.each(img64, function (i, v) {
        if (i == 0) {
            var imgData = '';
            doc.setFontSize(20);
            doc.addImage(imgData, 'JPEG', 0, 0, 297, 210);
            doc.text("Asistencia por día", 125, 45);
            doc.text("Total: " + suma_asisDia, 240, 50);
            doc.addImage(v, 'JPEG', 30, 65, 250, 100);
            doc.addPage();
        } else if (i == 1) {
            var imgData = '';
            doc.setFontSize(20);
            doc.addImage(imgData, 'JPEG', 0, 0, 297, 210);
            doc.text("Comparativo de asistencia por edición", 90, 50);
            doc.text("Total 2017: " + suma_dia1, 225, 45);
            doc.text("Total 2018: " + suma_dia2, 225, 55);
            doc.addImage(v, 'JPEG', 30, 65, 250, 100);
            doc.addPage();
        } else if (i == 2) {
            var imgData = '';
            doc.setFontSize(20);
            doc.addImage(imgData, 'JPEG', 0, 0, 297, 210);
            doc.text("Asistencia por hora", 115, 45);
            doc.text("Total: " + suma_hora, 240, 50);
            doc.addImage(v, 'JPEG', 30, 65, 250, 100);
            doc.addPage();
        } else if (i == 3) {
            var imgData = '';
            doc.setFontSize(20);
            doc.addImage(imgData, 'JPEG', 0, 0, 297, 210);
            doc.text("Tabla de asistencia por hora", 105, 45);
            doc.text("Total: " + suma_hora, 240, 50);
            doc.addImage(v, 'JPEG', 40, 65, 220, 100);
        }
    });
    if (img64.length == 4) {
        doc.save('Graficas.pdf');
        hide_loader_wrapper();
    }
});

/* Asistencia por dia */
function AsistenciaDia() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Días');
    data.addColumn('number', 'Pre registrados');
    data.addColumn({type: 'number', role: 'annotation'});
    data.addColumn('number', 'Registro Sitio');
    data.addColumn({type: 'number', role: 'annotation'});

    var options = {
        legend: {position: 'top'},
        vAxis: {
            maxValue: 0
        },

        annotations: {
            stemColor: 'none',
            alwaysOutside: true,
            textStyle: {
                fontSize: 14,
                color: 'black',
                auraColor: 'none'
            }
        },
        bars: 'vertical',
        colors: colores[idEdicion],
        height: 300
    };

    var JSON = $.ajax({
        url: url_Asistencia,
        dataType: 'json',
        async: false
    }).responseJSON;

    var totalPre = 0;
    var totalSitio = 0;

    $.each(JSON, function (index, value) {
        data.addRows([
            [JSON[index].Fecha.toString(),
                parseInt(JSON[index].Preregistrados),
                parseInt(JSON[index].Preregistrados),
                parseInt(JSON[index].Sitio),
                parseInt(JSON[index].Sitio)]
        ]);
        totalPre = totalPre + parseInt(JSON[index].Preregistrados);
        totalSitio = totalSitio + parseInt(JSON[index].Sitio);
        suma_asisDia = totalPre + totalSitio;
    });

    var cajadiv = document.getElementById("total_1");
    var texto = document.createTextNode('Total: ' + suma_asisDia);
    cajadiv.appendChild(texto);

    var bar = document.getElementById('bar');
    var chart = new google.visualization.ColumnChart(bar);

    var cont = 0;
    google.visualization.events.addListener(chart, 'ready', function () {
        img64[cont] = chart.getImageURI();
    });
    chart.draw(data, options);
}


/* Comparativo de asistencia por día por edición */
function Comparativo() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Días');
    data.addColumn('number', '2018');
    data.addColumn({type: 'number', role: 'annotation'});
    data.addColumn('number', '2019');
    data.addColumn({type: 'number', role: 'annotation'});

    var options = {
        vAxis: {
            maxValue: 10
        },
        legend: {position: 'top'},
        annotations: {
            stemColor: 'none',
            alwaysOutside: true,
            textStyle: {
                fontSize: 14,
                color: 'black',
                auraColor: 'none'
            }
        },
        bars: 'vertical',
        colors: colores[idEdicion],
        height: 300
    };

    var JSON = $.ajax({
        url: url_Comparativo,
        dataType: 'json',
        async: false
    }).responseJSON;

    var dia = 1;

    $.each(JSON, function (index, value) {

        if ((index + 0) % 1 == 0) {
            var a2018 = JSON[index]['2018'];
            var a2019 = parseInt(JSON[index]['2019']);
            data.addRows([
                ['Día ' + dia, a2018, a2018, a2019, a2019]
            ]);
            dia++;

            suma_dia1 = suma_dia1 + a2018;
            suma_dia2 = suma_dia2 + a2019;
        }
    });

    var cajadiv = document.getElementById("Dia1");
    var texto = document.createTextNode('Total 2018: ' + suma_dia1);
    cajadiv.appendChild(texto);

    var cajadiv = document.getElementById("Dia2");
    var texto = document.createTextNode('Total 2019: ' + suma_dia2);
    cajadiv.appendChild(texto);

    var chart2 = new google.visualization.ColumnChart(document.getElementById('bars'));
    var cont = 1;
    google.visualization.events.addListener(chart2, 'ready', function () {
        img64[cont] = chart2.getImageURI();
    });
    chart2.draw(data, options);
}

/* Aistencia por hora */
function AsistenciaHora() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Horario');
    data.addColumn('number', 'Día 1');
    data.addColumn('number', 'Día 2');
    data.addColumn('number', 'Día 3');

    var JSON = $.ajax({
        url: url_Hora,
        dataType: 'json',
        async: false
    }).responseJSON;

    tableDraw();
    var hora_dia = [];
    var div_p = document.getElementById("barr");
    var table = document.createElement('tablaHora');
    table.className = 'table';
    div_p.appendChild(table);

    var tr = document.createElement('tr');
    tr.className = 'tr';

    var td_1 = document.createElement('th');
    td_1.innerHTML = 'Horario';
    td_1.className = 'center-align';
    tr.appendChild(td_1);

    var td_2 = document.createElement('th');
    td_2.innerHTML = 'Día 1';
    td_2.className = 'center-align';
    tr.appendChild(td_2);

    var td_3 = document.createElement('th');
    td_3.innerHTML = 'Día 2';
    td_3.className = 'center-align';
    tr.appendChild(td_3);

    var td_4 = document.createElement('th');
    td_4.innerHTML = 'Día 3';
    td_4.className = 'center-alig';
    tr.appendChild(td_4);

    table.appendChild(tr);

//    var columns = [td_1.innerText, td_2.innerText, td_3.innerText];
    var total_dia1 = 0;
    var total_dia2 = 0;
    var total_dia3 = 0;
    $.each(JSON, function (index, value) {

        hora_dia.push(JSON[index].total);
        if ((index + 1) % 3 == 0) {

            var tr = document.createElement('tr');
            tr.className = 'tr';

            var td_1 = document.createElement('td');
            td_1.innerHTML = JSON[index].HoraInicial.toString() + ' - ' + JSON[index].HoraFinal.toString();
            td_1.className = 'center-align row1';
            tr.appendChild(td_1);

            var td_2 = document.createElement('td');
            td_2.innerHTML = hora_dia[0];
            td_2.className = 'center-align row1';
            tr.appendChild(td_2);
            total_dia1 = total_dia1 + hora_dia[0];

            var td_3 = document.createElement('td');
            td_3.innerHTML = hora_dia[1];
            td_3.className = 'center-align row1';
            tr.appendChild(td_3);
            total_dia2 = total_dia2 + hora_dia[1];

            var td_4 = document.createElement('td');
            td_4.innerHTML = hora_dia[2];
            td_4.className = 'center-align row1';
            tr.appendChild(td_4);
            total_dia3 = total_dia3 + hora_dia[2];

            suma_hora = total_dia1 + total_dia2 + total_dia3;

            table.appendChild(tr);
            let hora = JSON[index].HoraInicial.split(':');
            data.addRow([hora[0], parseInt(hora_dia[0]), parseInt(hora_dia[1]), parseInt(hora_dia[2])]);
            hora_dia = [];
        }
    });

    var cajadiv = document.getElementById("total_2");
    var texto = document.createTextNode('Total: ' + suma_hora);
    cajadiv.appendChild(texto);

    var trtotal = document.createElement('tr');

    var tdTotal = document.createElement('td');
    tdTotal.innerHTML = 'Total:';
    tdTotal.className = 'right-align text';
    table.appendChild(trtotal);
    trtotal.appendChild(tdTotal);

    var td_5 = document.createElement('td');
    td_5.innerHTML = total_dia1;
    td_5.className = 'center-align totalTD';
    trtotal.appendChild(td_5);

    var td_6 = document.createElement('td');
    td_6.innerHTML = total_dia2;
    td_6.className = 'center-align totalTD';
    trtotal.appendChild(td_6);

    var td_7 = document.createElement('td');
    td_7.innerHTML = total_dia3;
    td_7.className = 'center-align totalTD';
    trtotal.appendChild(td_7);


    var options = {
        legend: {position: 'top'},
        pointSize: 10,
        series: {
            0: {pointShape: 'circle'},
            4: {curveType: 'function'}
        },
        colors: colores[idEdicion],
        height: 300
    };

    var chart3 = new google.visualization.LineChart(document.getElementById('hour'));
    var cont = 2;
    google.visualization.events.addListener(chart3, 'ready', function () {
        img64[cont] = chart3.getImageURI();
    });
    chart3.draw(data, options);
}


/* Asistencia por hora tabla */
function tableDraw() {
    html2canvas(document.getElementById('barr')).then(function (canvas) {
        var image = canvas.toDataURL("image/png");
        var cont = 3;
        img64[cont] = image;
        return false;
    });
    return true;
}
