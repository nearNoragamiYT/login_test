hide_loader_top();
  google.load('visualization', '1', {
    packages: ['controls']
  });
  google.setOnLoadCallback(drawChart);

  function drawChart() {
    var data = new google.visualization.DataTable();
    data.addColumn('date', 'Date');
    data.addColumn('number', 'Preregistrados');


    var cont = 0
    setInterval(function() {
      var JSON = $.ajax({
        url: "http://localhost/sockets/ejemplo/DatoSensores.php?q=1",
        dataType: 'json',
        async: false
      }).responseText;
      var Respuesta = jQuery.parseJSON(JSON);
      anterior = Respuesta;
      data.addRows([
        [new Date(2013 + cont, 0),
          parseInt(Respuesta[0].Registros)
        ]
      ]);

      // Create the dashboard
      var dash = new google.visualization.Dashboard(document.getElementById('dashboard'));
      // bind the chart to the filter
      dash.bind([rangeFilter], [chart]);
      // draw the dashboard
      dash.draw(data);
      cont++;
    }, 3000);


    var dateFormat = new google.visualization.DateFormat({
      pattern: 'MMM/y'
    });
    dateFormat.format(data, 0);

    var rangeFilter = new google.visualization.ControlWrapper({
      controlType: 'ChartRangeFilter',
      containerId: 'range_filter_div',
      options: {
        filterColumnIndex: 0,
        ui: {
          chartOptions: {
            height: 50,
            width: 600, // must be the same in both the chart and the control
            chartArea: {
              width: '70%' // must be the same in both the chart and the control
            },
            hAxis: {
              format: 'MMM/y'
            }
          },
          chartView: {
            columns: [0, 1]
          }
        }
      },
      state: {
        range: {
          // set the starting range to January - April 2012 (change to whatever date range you like)
          start: new Date(2013, 0),
          end: new Date(2013, 3)
        }
      }
    });

    var chart = new google.visualization.ChartWrapper({
      chartType: 'LineChart',
      containerId: 'chart_div',
      options: {
        // width and chartArea.width should be the same for the filter and chart
        chartArea: {
          width: '70%', // must be the same in both the chart and the control
          height: '50%'
        },
        width: 600, // must be the same in both the chart and the control
        height: 300,
        fontName: ["Arial"],
        colors: ['#274358', '#5e87a5', '#a2cdf6'],
        curveType: ['none'],
        fontSize: ['13'],
        hAxis: {
          title: 'Período',
          titleTextStyle: {
            italic: false,
            color: 'black',
            fontSize: 15
          },
          format: 'MMM/y'
        },
        legend: {
          position: 'right',
          textStyle: {
            color: 'black',
            fontSize: 12
          }
        },
        lineWidth: 2,
        pointSize: 7,
        tooltip: {
          textStyle: {
            color: 'Black'
          },
          showColorCode: false
        }
      }
    });
  }
