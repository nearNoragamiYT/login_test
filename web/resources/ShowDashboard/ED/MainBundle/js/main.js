/*
 * @description this options is for bat chart to display value in top bar
 */
Chart.plugins.register({
    afterDraw: function (chartInstance) {
        if (chartInstance.config.options.showDatapoints) {
            var helpers = Chart.helpers;
            var ctx = chartInstance.chart.ctx;
            ctx.font = Chart.helpers.fontString("14", 'bold', '"Times New Roman"');
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            ctx.fillStyle = "#000";
            chartInstance.data.datasets.forEach(function (dataset) {
                for (var i = 0; i < dataset.data.length; i++) {
                    var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                    var scaleMax = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._yScale.maxHeight;
                    var yPos = (scaleMax - model.y) / scaleMax >= 0.93 ? model.y + 20 : model.y - 5;
                    ctx.fillText(dataset.data[i], model.x, yPos);
                }
            });
        }
    }
});
//--- variabled for charts ---//
var mkfRequests = null, packages = null, standRequests = null, piePercent = null, tooltipoCallBack = null;
//--- variables for configurations ---//
var piePorcent = {}, pieValue = {}, barValue = {}, requestMKFStatus = {}, FPStatus = {}, requestFPStatus = {};
$(init);
function init() {
    setConfigurations();
    //--- main function for exhibitors ---//
    mainExhibitors();
    //--- main function for ED ---//
    mainED();
    //--- main function for MKF ---//
    mainMKF();
    //--- main function for FP  ---//
    mainFP();
    //--- button for update data of chart ---//
    $('.refresh').on('click', function () {
        var loader = $(this).attr('data-loader');
        $("#" + loader).slideDown();
        var url = $(this).attr('data-url');
        $(this).addClass('update');
        updateStats(url, loader, this);
    });
    $("#refresh-all").on('click', function () {
        show_loader_wrapper();
        var url = $(this).attr('data-url');
        updateAllStats(url);
    });
}

function mainExhibitors() {
    //--- init charts for clients ---//
    initClientsChart();
    //--- init charts for exhibitors ---//
    initExhibitorsChart();
    //--- init charts for exhibitors that singup in ED ---//
    initExhibitorsSingupChart();
}
function mainED() {
    //--- init chart for all forms ---//
    initAllFormsChart();
    //--- set total of badges ---//
    setBadges();
    //--- set total of retrievals  ---//
    //setRetrievals();
    //--- init obligatory forms stats ---//
    initObligatoryFormsChart();
}
function mainMKF() {
    //--- init packages stats ---//
    initPackagesChart();
    //--- init package requests stats ---//
    initPackageRequestsChart();
}
function mainFP() {
    //--- init status stands stats ---//
    initStatusStandsChart();
    //--- init stand requests stats ---//
    initModificationRequestsChart();
}

function initClientsChart() {
    var chart = document.getElementById("clients-chart");
    var totalC = parseInt(stats['Exhibitors']['TotalContratos']);
    var totalP = parseInt(stats['Exhibitors']['TotalPrecontratos']);
    var total = totalC + totalP;
    var title = section_text['sas_clientesEdicion'].replace("%total%", stats['Exhibitors']['TotalClientes']);
    var title = title.replace("%edition%", edition['Edicion_' + lang.toUpperCase()]);
    pieValue.title['fontColor'] = exhibitorColors.color;
    pieValue.title['text'] = title;
    var pieChart = new Chart(chart, {
        type: "pie",
        data: {
            datasets: [{
                    data: [
                        totalC,
                        totalP
                    ],
                    backgroundColor: [
                        exhibitorColors.primario,
                        exhibitorColors.secundario
                    ]
                }],
            labels: [
                general_text.sas_contratos + " (" + ((totalC / total) * 100).toFixed(2) + "%)",
                general_text.sas_precontratos + " (" + ((totalP / total) * 100).toFixed(2) + "%)"
            ]
        },
        options: pieValue
    });
}

function initExhibitorsChart() {
    var chart = document.getElementById("exhibitors-chart");
    var totalE = parseInt(stats['Exhibitors']['TotalExpositores']);
    var totalC = parseInt(stats['Exhibitors']['TotalCoexpositores']);
    var total = totalE + totalC;
    var title = section_text['sas_expositoresEdicion'].replace("%total%", stats['Exhibitors']['TotalED']);
    var title = title.replace("%edition%", edition['Edicion_' + lang.toUpperCase()]);
    pieValue.title['fontColor'] = exhibitorColors.color;
    pieValue.title['text'] = title;
    new Chart(chart, {
        type: "pie",
        data: {
            datasets: [{
                    data: [
                        totalE,
                        totalC
                    ],
                    backgroundColor: [
                        exhibitorColors.primario,
                        exhibitorColors.secundario
                    ]
                }],
            labels: [
                general_text.sas_expositores + " (" + ((totalE / total) * 100).toFixed(2) + "%)",
                general_text.sas_empresasAdicionales + " (" + ((totalC / total) * 100).toFixed(2) + "%)"
            ]
        },
        options: pieValue
    });
}

function initExhibitorsSingupChart() {
    var chart = document.getElementById("exhibitors-singup-chart");
    var totalEE = parseInt(stats['Exhibitors']['TotalExpositoresEntraron']);
    var totalENE = parseInt(stats['Exhibitors']['TotalExpositoresNoEntraron']);
    var total = totalEE + totalENE;
    pieValue.title['fontColor'] = exhibitorColors.color;
    pieValue.title['text'] = section_text.sas_expositoresEntraronED;
    new Chart(chart, {
        type: "pie",
        data: {
            datasets: [{
                    data: [
                        totalEE,
                        totalENE
                    ],
                    backgroundColor: [
                        exhibitorColors.primario,
                        exhibitorColors.secundario
                    ]
                }],
            labels: [
                general_text.sas_entraron + " (" + ((totalEE / total) * 100).toFixed(2) + "%)",
                general_text.sas_noEntraron + " (" + ((totalENE / total) * 100).toFixed(2) + "%)"
            ]
        },
        options: pieValue
    });
}

function initAllFormsChart() {
    var chart = document.getElementById("all-forms-chart");
    var totalFC = parseInt(stats['ED']['TotalFormasCompletas']);
    var totalFP = parseInt(stats['ED']['TotalFormasPendientes']);
    var totalFSI = parseInt(stats['ED']['TotalFormasSinInteres']);
    var total = totalFC + totalFP + totalFSI;
    pieValue.title['fontColor'] = EDColors.color;
    pieValue.title['text'] = section_text.sas_formasExpositores;
    new Chart(chart, {
        type: "pie",
        data: {
            datasets: [{
                    data: [
                        totalFC,
                        totalFP,
                        totalFSI
                    ],
                    backgroundColor: [
                        EDColors.primario,
                        EDColors.secundario,
                        EDColors.terciario
                    ]
                }],
            labels: [
                general_text.sas_completas + " (" + ((totalFC / total) * 100).toFixed(2) + "%)",
                general_text.sas_pendientes + " (" + ((totalFP / total) * 100).toFixed(2) + "%)",
                general_text.sas_sinInteres + " (" + ((totalFSI / total) * 100).toFixed(2) + "%)"
            ]
        },
        options: pieValue
    });
}

function setBadges() {
    var dadgesContent = document.getElementById("total-badges");
    dadgesContent.textContent = stats['ED']['TotalGafetes'];
}

function setRetrievals() {
    var dadgesContent = document.getElementById("total-retrievals");
    dadgesContent.textContent = stats['ED']['TotalLectoras'];
}

function initObligatoryFormsChart() {
    var chart = document.getElementById("obligatory-forms-chart");
    var totalFOC = parseInt(stats['ED']['TotalFormasObligatoriasCompletas']);
    var totalFOP = parseInt(stats['ED']['TotalFormasObligatoriasPendientes']);
    var total = totalFOC + totalFOP;
    pieValue.title['fontColor'] = EDColors.color;
    pieValue.title['text'] = section_text.sas_formasObligatoriasExpositores;
    new Chart(chart, {
        type: "pie",
        data: {
            datasets: [{
                    data: [
                        totalFOC,
                        totalFOP
                    ],
                    backgroundColor: [
                        EDColors.primario,
                        EDColors.secundario
                    ]
                }],
            labels: [
                general_text.sas_completas + " (" + ((totalFOC / total) * 100).toFixed(2) + "%)",
                general_text.sas_pendientes + " (" + ((totalFOP / total) * 100).toFixed(2) + "%)"
            ]
        },
        options: pieValue
    });
}

function initPackagesChart() {
    if (packages != null) {
        packages.destroy();
    }
    var chart = document.getElementById("packages-chart");
    var data = [], labels = [];
    $.each(stats['MKF']['TotalPaquetes'], function (index, value) {
        data.push(value['Total']);
        labels.push(value['Paquete']);
    });
    barValue.title['fontColor'] = MKFColors.color;
    barValue.title['text'] = section_text.sas_paquetesAsignados;
    packages = new Chart(chart, {
        type: "bar",
        data: {
            datasets: [{
                    backgroundColor: [
                        MKFColors.primario,
                        MKFColors.secundario,
                        MKFColors.terciario,
                        MKFColors.cuaterciario
                    ],
                    label: general_text.sas_paquetes,
                    data: data
                }],
            labels: labels
        },
        options: barValue
    });
}

function initPackageRequestsChart() {
    if (mkfRequests != null) {
        mkfRequests.destroy();
    }
    var chart = document.getElementById("mkf-requests-chart");
    var data = [], labels = [];
    $.each(stats['MKF']['TotalPaquetesSolicitados'], function (index, value) {
        data.push(value['Total']);
        labels.push(requestMKFStatus[value['Status']]);
    });
    barValue.title['fontColor'] = MKFColors.color;
    barValue.title['text'] = section_text.sas_paquetesSolicitados;
    mkfRequests = new Chart(chart, {
        type: "bar",
        data: {
            datasets: [{
                    backgroundColor: [
                        MKFColors.primario,
                        MKFColors.secundario,
                        MKFColors.terciario,
                        MKFColors.cuaterciario
                    ],
                    label: general_text.sas_solicitudes,
                    data: data
                }],
            labels: labels
        },
        options: barValue
    });
}

function initStatusStandsChart() {
    var chart = document.getElementById("status-stands-chart");
    var data = [], labels = [], total = 0;
    $.each(stats['FP']['TotalEstatusStands'], function (i, value) {
        data.push(value['Total']);
        labels.push(value['NumerosStands'] + " " + general_text.sas_stands + " " + FPStatus[value['StandStatus']]);
        total = total + parseFloat(value['Total']);
    });
    var title = section_text['sas_areaCuadradaRecinto'].replace("%total%", total);
    piePorcent.title['fontColor'] = FPColors.color;
    piePorcent.title['text'] = title;
    piePorcent.tooltips['callbacks']['label'] = function (tooltipItem, data) {
        return data['labels'][tooltipItem['index']] + ": " + section_text['sas_areaCuadrada'].replace("%value%", data['datasets'][0]['data'][tooltipItem['index']]);
    };
    new Chart(chart, {
        type: "pie",
        data: {
            datasets: [{
                    data: data,
                    backgroundColor: [
                        FPColors.primario,
                        FPColors.secundario,
                        FPColors.terciario
                    ]
                }],
            labels: labels
        },
        options: piePorcent
    });
}

function initModificationRequestsChart() {
    if (standRequests != null) {
        standRequests.destroy();
    }
    var chart = document.getElementById("fp-modifications-chart");
    var modifications = stats['FP']['TotalSolicitudesModificacion']
    barValue.title['fontColor'] = FPColors.color;
    barValue.title['text'] = section_text.sas_solicitudesModificacion;
    standRequests = new Chart(chart, {
        type: "bar",
        data: {
            datasets: [{
                    backgroundColor: [
                        FPColors.primario,
                        FPColors.secundario,
                        FPColors.terciario
                    ],
                    label: "Modificaciones",
                    data: [
                        modifications['pendientes'],
                        modifications['aceptadas'],
                        modifications['rechazadas']
                    ]
                }],
            labels: [
                "Pendientes",
                "Aceptadas",
                "Rechazadas"
            ]
        },
        options: barValue
    });
}

/**
 *
 * @param {sting} url of resources for update at stats of a card
 * @param {string} loader id of loader of card for hide.
 */
function updateStats(url, loader, refreshBtn) {
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: url,
        success: function (response) {
            $("#" + loader).slideUp();
            $(refreshBtn).removeClass('update');
            $.extend(stats[response['json']], response.data);
            switch (response.status) {
                case "clients":
                    initClientsChart();
                    break;
                case "exhibitors":
                    initExhibitorsChart();
                    break;
                case "singup":
                    initExhibitorsSingupChart();
                    break;
                case "forms":
                    initAllFormsChart();
                    break;
                case "badges":
                    setBadges();
                    break;
                case "retrievals":
                    setRetrievals();
                    break;
                case "obligatories":
                    initObligatoryFormsChart();
                    break;
                case "packages":
                    initPackagesChart();
                    break;
                case "mkf-requests":
                    initPackageRequestsChart();
                    break;
                case "status-stands":
                    initStatusStandsChart();
                    break;
                case "fp-modifications":
                    initModificationRequestsChart();
                    break;
                default:
                    show_modal_error(general_text.sas_errorInterno + "<br>" + response);
                    break;
            }
        },
        error: function (response) {
            $("#" + loader).slideUp();
            $(refreshBtn).removeClass('update');
            show_modal_error(general_text.sas_errorInterno + "<br>" + response.responseText);
        }
    });
}

function updateAllStats(url) {
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: url,
        success: function (response) {
            hide_loader_wrapper();
            stats = response.stats
            mainExhibitors();
            mainED();
            mainMKF();
            mainFP();
        },
        error: function (response) {
            hide_loader_wrapper();
            show_modal_error(general_text.sas_errorInterno + "<br>" + response.responseText);
        }
    });
}
function setConfigurations() {
    requestMKFStatus = {
        "1": general_text.sas_nuevas,
        "2": general_text.sas_aceptadas,
        "3": general_text.sas_canceladas,
        "4": general_text.sas_rechazadas
    };
    FPStatus = {
        "reservado": general_text.sas_reservados,
        "libre": general_text.sas_libres,
        "contratado": general_text.sas_contratados
    };
    requestFPStatus = {
        "1": general_text.sas_nuevas,
        "2": general_text.sas_vistas,
        "3": general_text.sas_contratadas,
        "4": general_text.sas_rechazadas
    };
    piePorcent = {
        pieceLabel: {
            mode: 'percentage',
            precision: 2,
            fontSize: 12,
            position: 'outside',
            fontStyle: 'bold',
            fontColor: '#000    ',
            fontFamily: '"Times New Roman"'
        },
        title: {
            fontSize: 14,
            display: true
        },
        legend: {
            display: true,
            position: "top"
        },
        animation: {
            duration: 1500,
            Easing: 'easeInOutBounce'
        },
        tooltips: {
            callbacks: {
            }
        }
    };
    pieValue = {
        pieceLabel: {
            mode: 'value',
            fontSize: 12,
            position: 'outside',
            fontStyle: '',
            fontColor: '#000    ',
            fontFamily: '"Times New Roman"'
        },
        title: {
            fontSize: 14,
            display: true
        },
        legend: {
            display: true,
            position: "top"
        },
        animation: {
            duration: 2000
        }
    };
    barValue = {
        showDatapoints: true,
        title: {
            fontSize: 14,
            display: true,
            padding: 20
        },
        scales: {
            xAxes: [{
                    stacked: true
                }],
            yAxes: [{
                    stacked: true
                }]
        },
        legend: {
            display: false
        },
        animation: {
            duration: 2000
        }
    };
}