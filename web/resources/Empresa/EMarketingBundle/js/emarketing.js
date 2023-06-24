oTable = "", hTable = "", companies_to_send = [], sender = {}, sending_flag = false, queue = "";
$(init);

function init() {
//    oTable = $("#tbl-companies").DataTable({
//        "language": {
//            "url": url_lang
//        }
//    });
    hTable = $("#tbl-history").DataTable({
        "language": {
            "url": url_lang
        }
    });
    $("#save-emarketing").on("click", saveEmarketing);
    $("#edit-emarketing").on("click", function () {
        $("#btn-show-companies-list").addClass("disabled");
        $("#Asunto").removeAttr("disabled");
        $("#Cuerpo").removeAttr("disabled");
        $("#save-emarketing").fadeIn();
        $(this).hide();
        tinymce.activeEditor.setMode('design');
    });
    $("#btn-show-companies-list").on("click", function () {
        if ($(this).hasClass("disabled")) {
            return;
        }
        if (!validateFieldsEMarketing()) {
            return;
        }
//        show_loader_top();
        $("#btn-select-all").prop("checked", false);
        $("#mdl-companies").modal("open");
        $(".company-check").prop("checked", false);
        $("#montaje-desmontaje").prop("checked", false);
        companies_to_send = [];
//        setTimeout(function () {
//            oTable.rows().every(function (rowIdx, tableLoop, rowLoop) {
//                if ($(this.data()[0]).is(":checked")) {
//                    $("#tbl-companies").DataTable().update('<input type="checkbox" id="e-' + $(this.data()[0]).attr('value') + '" value="' + $(this.data()[0]).attr('value') + '" class="company-check"/><label for="e-' + $(this.data()[0]).attr('value') + '"></label>', rowIdx, 0);
//                    var index = $.inArray($(this.data()[0]).attr('value'), companies_to_send);
//                    companies_to_send.splice(index, 1);
//                }
//            });
//            hide_loader_top();
//        }, 1000);

    });
    $("#btn-history").on("click", function () {
        $("#mdl-history").modal("open");
    });
    $("#btn-hide-history").on("click", function () {
        $("#mdl-history").modal("close");
    });
    $("#btn-send-emarketing").on("click", function () {
        $("#mdl-companies").modal("close");
        if (companies_to_send.length == 0) {
            show_alert("warning", section_text['sas_asegureseSeleccionarDestinatarios']);
            return false;
        }
        $("#mdl-confirm-sending").modal("open");
    });
    $("#btn-confirm-send-emarketing").on("click", function () {
        $("#mdl-confirm-sending").modal("close");
        sendEMarketing();
    });
    $(document).on("change", ".company-check", function () {
        var value = $(this).val();
        var index = $.inArray(value, companies_to_send);
        if ($(this).is(":checked")) {
//            $("#tbl-companies").DataTable().update('<input type="checkbox" id="e-' + value + '" value="' + value + '" class="company-check" checked="checked"/><label for="e-' + value + '"></label>', $(this).parents('tr'), 0, false);
            if (index === -1) {
                companies_to_send.push(value);
            }
        } else {
//            $("#tbl-companies").DataTable().update('<input type="checkbox" id="e-' + value + '" value="' + value + '" class="company-check" /><label for="e-' + value + '"></label>', $(this).parents('tr'), 0, false);
            companies_to_send.splice(index, 1);
        }
    });
    $("#btn-select-all").on("change", function () {
        show_loader_top();
        setTimeout(function () {
            if ($("#btn-select-all").is(":checked")) {
                $.each($(".company-check"), function (i, e) {
                    $(e).prop("checked", true);
                    var index = $.inArray($(e).attr('value'), companies_to_send);
                    if (index != -1) {
                        companies_to_send.splice(index, 1);
                    }
                    companies_to_send.push($(e).attr('value'));
                });
            } else {
                $.each($(".company-check"), function (i, e) {
                    $(e).prop("checked", false);
                    var index = $.inArray($(e).attr('value'), companies_to_send);
                    companies_to_send.splice(index, 1);
                });
            }
            hide_loader_top();
        }, 1000);
    });
    $("#btn-add-cc").on("click", function () {
        var e = $("#Copia").val();
        if (!isValidEmail(e)) {
            show_alert("warning", section_text['sas_emailValido']);
            return false;
        }
        var c = '<div class="chip"><span class="cc-text">' + e + '</span> <i class="close material-icons">close</i></div>';
        $("#cc-zone").append(c);
        $("#Copia").val('');
    });
}

function isValidEmail(mail)
{
    return /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(mail);
}

function saveEmarketing() {
    if (!validateFieldsEMarketing()) {
        return;
    }
    show_loader_wrapper();
    var post = {
        idEMarketing: $("#idEMarketing").val(),
        Asunto: $("#Asunto").val(),
        Cuerpo: tinymce.activeEditor.getContent()
    }
    $.ajax({
        type: "post",
        url: url_insert,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_wrapper();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            emarketing['Asunto'] = post.Asunto;
            emarketing['Cuerpo'] = post.Cuerpo;
            show_alert("success", general_text.sas_guardoExito);
            $("#btn-show-companies-list").removeClass("disabled");
            $("#Asunto").attr("disabled", "disabled");
            $("#Cuerpo").attr("disabled", "disabled");
            $("#save-emarketing").hide();
            $("#edit-emarketing").fadeIn();
            tinymce.activeEditor.setMode('readonly');
        },
        error: function (request, status, error) {
            show_modal_error(request.responseText);
            hide_loader_wrapper();
        }
    });
}

function validateFieldsEMarketing() {
    if ($("#Nombre").val() == '') {
        show_alert("warning", section_text['sas_asegureseIngresarNombre']);
        return false;
    }
    if ($("#Asunto").val() == '') {
        show_alert("warning", section_text['sas_asegureseIngresarAsunto']);
        return false;
    }
    if (tinymce.activeEditor.getContent() == '') {
        show_alert("warning", section_text['sas_asegureseIngresarCuerpo']);
        return false;
    }
    return true;
}

function sendEMarketing() {
    if (!validateFieldsEMarketing()) {
        return;
    }
    show_loader_wrapper();
    if (companies_to_send.length) {
        sender = [];
        $.each(companies_to_send, function (i, e) {
            sender[i] = list_empresa[e];
        });
    }
    var cc = "";
    $.each($(".cc-text"), function (i, e) {
        cc += $(e).text() + ",";
    });
    cc = cc.substring(0, cc.length - 1);
    var pointer = 0;
    queue = setInterval(function () {
        if (sending_flag == false && pointer < sender.length) {
            sending_flag = true;
            var c_sender = [];
            c_sender[0] = sender[pointer];
            var post = {
                marketing: emarketing,
                send: c_sender,
                cc: cc,
                montaje: ($("#montaje-desmontaje").is(":checked")) ? 1 : 0
            };
            sending(post);
            showPercent(pointer + 1);
            pointer++;
        } else {
            if (pointer >= sender.length) {
                hide_loader_wrapper();
                show_alert("success", general_text.sas_guardoExito);
                $("#percent").remove();
                clearInterval(queue);
            }
        }
    }, 2000);
}

function showPercent(p) {
    if ($("#loader-wrapper #percent").length == 0) {
        $("#loader-wrapper").prepend('<div id="percent"></div>');
    }
    var percent = calculatePercent(p, sender.length);
    $("#percent").text(number_format(percent, 2) + "%");
}

function calculatePercent(piece, total) {
    return (piece * 100) / total;
}

function number_format(amount, decimals) {

    amount += ''; // por si pasan un numero en vez de un string
    amount = parseFloat(amount.replace(/[^0-9\.]/g, '')); // elimino cualquier cosa que no sea numero o punto

    decimals = decimals || 0; // por si la variable no fue fue pasada

    // si no es un numero o es igual a cero retorno el mismo cero
    if (isNaN(amount) || amount === 0)
        return parseFloat(0).toFixed(decimals);

    // si es mayor o menor que cero retorno el valor formateado como numero
    amount = '' + amount.toFixed(decimals);

    var amount_parts = amount.split('.'),
            regexp = /(\d+)(\d{3})/;

    while (regexp.test(amount_parts[0]))
        amount_parts[0] = amount_parts[0].replace(regexp, '$1' + ',' + '$2');

    return amount_parts.join('.');
}

function sending(post) {
    $.ajax({
        type: "post",
        url: url_send,
        dataType: 'json',
        data: post,
        success: function (response) {
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            emarketing['NumeroEnvio'] = response['data']['NumeroEnvio'];
            emarketing['TotalEnvios'] = response['data']['TotalEnvios'];
//            $("#NumeroEnvio").text(response['data']['NumeroEnvio']);
            $("#TotalEnvios").text(response['data']['TotalEnvios']);
            $.each(response.data.Detalle, function (i, e) {
                hTable.row.add([
                    e['DC_NombreComercial'],
                    e['Email'],
                    e['CopiaOculta'],
                    (parseInt(e['Estatus']) == 1) ? general_text.sas_enviado : general_text.sas_falloEnvio,
                    e['FechaEnvio'],
                    section_text.sas_no,
                    '',
                    '0',
                    '',
                    '',
                    '',
                    '',
                    '',
                ]).draw('full-hold');
            });
            sending_flag = false;
        },
        error: function (request, status, error) {
            show_modal_error(request.responseText);
            hide_loader_wrapper();
            sending_flag = false;
            clearInterval(queue);
        }
    });
}