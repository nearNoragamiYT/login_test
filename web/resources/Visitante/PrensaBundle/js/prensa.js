var oTable = "", tr = "", action = "", idDistrito = 0, prensa = [], prensa_to_update = [], prensa_list = [], sendMail = '';

jQuery.extend(jQuery.validator.messages, {
    required: general_text.sas_campoRequerido,
});


$(document).ready(function () {
    validateStatusPartner();

    $('#table-prensa tfoot th').each(function (key, value) {
        var title = $(this).text();
        var replace_title = Prensa_table_columns[title];
        var class_search = "";
        if (title !== "") {
            class_search = "dataTableSearch";
        }
        if (replace_title !== undefined) {
            title = replace_title.text_placeholder;
        }
        if (replace_title !== undefined) {
            switch (replace_title.filter_options.type) {
                case "select":
                    $(this)[0].innerHTML = "";

                    /*create element select*/
                    var select = document.createElement("select");
                    select.setAttribute("id", title.replace(' ', '-'));
                    select.className = "browser-default " + class_search;
                    $(this)[0].appendChild(select);

                    var html = '<option value="" selected disabled>Selecciona una opción</option>';
                    html += '<option value="">TODOS</option>';
                    $.each(replace_title.filter_options.values, function (index, value) {
                        var key = Object.keys(replace_title.filter_options.values[index]);
                        html += '<option value="' + replace_title.filter_options.values[index][key[0]] + '">';
                        html += replace_title.filter_options.values[index][key[1]].toUpperCase();
                        html += '</option>';
                    });

                    $("#" + title.replace(' ', '-')).html(html);
                    //select.style.width = "180px";
                    break;
                case "input":
                    $(this).html('<input id="' + title.replace(' ', '-') + '" placeholder="Buscar ' + title + '"  class="' + class_search + '"/>');
                    break;
                case "date":
                    $(this)[0].innerHTML = "";
                    input_date = document.createElement("input");
                    input_date.setAttribute("type", "date");
                    input_date.className = "datepicker " + class_search;
                    $(this)[0].appendChild(input_date);
                    $('.datepicker').pickadate({
                        selectMonths: true,
                        selectYears: 16,
                        format: 'yyyy-mm-dd'
                    });
                    break;
                case undefined:
                    break;
            }
        } else {
            $(this).html('<input id="' + title.replace(' ', '-') + '" placeholder="Buscar ' + title + '"  class="' + class_search + '"/>');
        }


    });
    var columnas = [
        {name: "accion", orderable: false},
        {name: "idVisitanteNoAutorizado", visible: false, searchable: false},
        {name: "idVisitante"},
        {name: "NombreCompleto"},
        {name: "Email"},
        {name: "NombreComercial"},
        {name: "Cargo"},
        {name: "FechaPreregistro"},
        {name: "NombreStatus"},
        {name: "edit", orderable: false, searchable: false},
        {name: "sendEmail", orderable: false, searchable: false},
    ];
    /* Colocar los usuario que no tienen permitido enviar Digibade */
    if (user['idUsuario'] == 69) {
        columnas.pop();
    }
    oTable = $('#table-prensa').DataTable({
        "language": {
            "url": url_lang
        },
        "order": [[3, "asc"]],
        processing: true,
        serverSide: true,
        bDestroy: true,
        ajax: {url: url_get_data,
            type: "POST",
            "dataSrc": function (response) {

                prensa_list = [];
                for (var key in response.listDataId) {
                    prensa_list.push(key);
                }
                return response.data;
            }},
        columns: columnas
    });




    oTable.columns().every(function () {
        var that = this;
        
        $('input', this.footer()).keyup(function (e) {

            if (e.which == 13) {
                $('.dataTableSearch').each(function (index, val) {
                    var column = oTable.columns((index + 2));
                    if (column.search()[0] !== val.value) {
                        column
                                .search(val.value)
                                .draw();
                    }
                });
            }
//            if (e.which == 8 || e.which ==  46) {
//                if ('' == this.value) {
//                    $('.dataTableSearch').each(function (index, val) {
//                        var column = oTable.columns(index);
//                        if (column.search()[0] !== val.value) {
//                            column
//                                    .search(val.value)
//                                    .draw();
//                        }
//                    });
//                }
//            }
        });
    });

    /*Search input type select*/
    oTable.columns().every(function () {
        var find_id = $("#table-prensa").find("select");
        $(find_id, this.footer()).on('change', function (e) {
            $('.dataTableSearch').each(function (index, val) {
                var column = oTable.columns((index + 2));
                if (column.search()[0] !== val.value) {
                    column
                            .search(val.value)
                            .draw();
                }
            });
        });
    });

    /*Search input type date*/
    oTable.columns().every(function () {
        var find_date = $("#table-prensa").find(".datepicker");
        $(find_date, this.footer()).on('change ', function (e) {
            $('.dataTableSearch').each(function (index, val) {
                var column = oTable.columns((index + 2));
                if (column.search()[0] !== val.value) {
                    column
                            .search(val.value)
                            .draw();
                    $(this).next().find('.picker__close').click();
                }
            });

        });
    });

    oTable.on('draw', function () {
        if (prensa_to_update.length) {
            oTable.rows().data().each(function (i, e) {
                var idVisitante = (oTable.rows(e).data()[0][2] != "") ? oTable.rows(e).data()[0][2] : "0";
                var idVisitanteNoAutorizado = (oTable.rows(e).data()[0][1] != "") ? oTable.rows(e).data()[0][1] : "0";
                var idcheck = idVisitante + "-" + idVisitanteNoAutorizado
                if (prensa_to_update.includes(idcheck)) {
                    var check = $("#e-" + idcheck);
                    check.prop("checked", true);
                }
            });
        }
        $('.tooltipped').tooltip();
        tabPermisos();
    });

    $('#x').after($('#y'));
    $("#y th").first().children().css("display", "none");
    $("#y th").last().children().css("display", "none");
});

$("#btn-select-all").on("change", function () {
    show_loader_top();
    var rows = oTable.rows({'search': 'applied'}).nodes();
    if ($("#btn-select-all").is(":checked")) {
        prensa_to_update = prensa_list;
        $('input[type="checkbox"]:enabled', rows).prop('checked', true);
    } else {
        prensa_to_update = [];
        $('input[type="checkbox"]:enabled', rows).prop('checked', false);
    }
    hide_loader_top();
});

$(document).on("change", ".prensa-check", function () {
    var value = $(this).val();
    var index = $.inArray(value, prensa_to_update);
    if ($(this).is(":checked")) {

        if (index === -1) {
            prensa_to_update.push(value);
        }
    } else {
        prensa_to_update.splice(index, 1);
    }
});


$(document).on("click", "#btn-confirm-update", function () {
    $("#status-form").submit();
});


function validateStatusPartner() {
    $("#status-form").validate({
        rules: {
            'idStatusAutorizado': {
                required: true
            }
        },
        ignore: ":hidden:not(select)",
        errorElement: "div",
        errorClass: "invalid",
        errorPlacement: function (error, element) {
            if ($(element).parent('div').find('i.material-icons').length > 0) {
                $(error).attr('icon', true);
            }

            var placement = $(element).data('error');
            if (placement) {
                $(placement).append(error);
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            upadatePartner();

        }
    });
}

function upadatePartner(idStatus) {
    $.ajax({
        type: "post",
        url: url_update_status_partner,
        dataType: 'json',
        data: {
            prensa: prensa_to_update,
            idStatus: $("#idStatusAutorizado").val()

        },
        success: function (response) {
            if (!response['status']) {
                show_alert("danger", response['data']);
                $('#mdl-confirm-update').modal("close");
                hide_loader_wrapper();
                return;
            }


            switch (parseInt($("#idStatusAutorizado").val())) {
                case 2:
                    sendMail = url_send_confirmacion;
                    break;
                case 3:
                    sendMail = url_send_rechazo;
                    break;
                default:
                    sendMail = '';
            }

            if (sendMail !== '') {
                var sender = response.data;
                var pointer = 0;
                queue = setInterval(function () {
                    if (pointer < sender.length) {
                        var c_sender = [];
                        c_sender[0] = sender[pointer];
                        var post = {
                            idVisitante: sender[pointer]['idvisitante']
                        };
                        sending(post, sendMail);
                        pointer++;
                    } else {
                        if (pointer >= sender.length) {
                            hide_loader_wrapper();
                            show_alert("success", general_text.sas_guardoExito);
                            clearInterval(queue);
                        }
                    }
                }, 2000);
            }
            prensa_to_update = [];
            $("#idStatusAutorizado").val(0);
            $('#mdl-confirm-update').modal("close");
            oTable.ajax.reload(function () {
                show_toast("success", "Procesado Correctamente");
            });

        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
            hide_loader_wrapper();
        }
    });
    // hide_loader_wrapper();
}

function sending(post, url_sending) {
	show_loader_wrapper
    $.ajax({
        type: "post",
        url: url_sending,
        dataType: 'json',
        data: post,
        success: function (response) {
            if (!response['status']) {
                return;
            }
            location.reload();
 show_toast("success", "Envío Completo");
            hide_loader_wrapper();
                    },
        error: function (request, status, error) {
            alert('ERROR SEND');
        }
    });
}

$("#updatePrensa").on('click', function () {
    if (prensa_to_update.length) {
        $('#mdl-confirm-update').modal({dismissible: false}).modal("open");
    } else {
        show_toast("warning", "Selecciona al menos un Prensa para continuar");
    }
    return;

});

$(document).on("click", ".edit-record ", function () {
    show_loader_wrapper();
    var link = url_visitante_datos_generales + "/" + $(this).attr('idVisitante');
    window.location = link;
});

$(document).on("click", ".send-record ", function () {
	    show_loader_wrapper();

    var idVisitante = $(this).attr('idVisitante');
    show_loader_wrapper();
    $.ajax({
        type: "post",
        url: url_send_digibage,
        dataType: 'json',
        data: {
            idVisitante: idVisitante},
        success: function (response) {
            if (!response['status']) {
                return;
            }
            location.reload();
            show_toast("success", "Envío exitoso");
             hide_loader_wrapper();
        },
        error: function (request, status, error) {
            alert('ERROR SEND');
        }
    });
});

$(document).on("click", ".download-gafete", function () {
    let data = $(this).attr("idVisitante");
    let post = "&idVisitante=" + data;
    show_loader_wrapper();
    $.ajax({
        type: "post",
        url: url_download_gafete,
        xhrFields: {
            responseType: 'blob'
        },
        data: post,
        success: function (response, status, xhr) {
            var URL = window.URL || window.webkitURL;
            var downloadUrl = URL.createObjectURL(response);
            var a = document.createElement("a");

            a.href = downloadUrl;
            a.download = data;
            a.click();

            setTimeout(function () {
                URL.revokeObjectURL(downloadUrl);
            }, 1000);
            location.reload();
            show_toast("success", "Descargado con éxito");
            hide_loader_wrapper();
        },
        error: function (request, status, error) {
            show_modal_error("Ocurrio un Error, Intente de Nuevo");
            hide_loader_wrapper();
        }
    });
});
