var oTable = "", tr = "", itemToUpdate = "", itemToDelete = "", disabled = "", action = "", license_to_send = [], licenses_list;
;
$(document).ready(function () {
    initEmpresaLectoras();
});
function initEmpresaLectoras() {
    $("#modal-show-qr").children(".modal-footer").hide();
    $("#empresa-lectoras").attr("class", "active");
    generateEmpresaLectorasTable('empresa-lectoras-table');
    validateEmpresaLectoraForm();
    $(document).on("click", ".company-menu>div>ul>li", function () {
        show_loader_wrapper();
    });
    $("#btn-add-lectora-empresa").on("click", function () {
        $("#add-empresa-lectora-form").submit();
    });
    $(document).on("click", ".edit-record", function () {
        itemToUpdate = $(this).attr("data-id");
        tr = $(this).parents("tr");
        action = "update";
        setEmpresaLectoraData();
        $("#empresa-lectora-head").html(section_text["sas_editarLectora"]);
        $('#add-lectora-empresa-modal').modal({dismissible: false}).modal("open");
    });
    /***** Mostrar Detalle Licencias *****/
    $("#mostrar-licencias").on('click', async function () {
        show_loader_top();
        let licencias = true;
        try {
            licencias = await getLicenses($("#idEmpresa").val());
        } catch (e) {
            hide_loader_top();
            show_modal_error(e.responseText);
            return;
        }
        if (licencias) {
            $('#modal-show-detalle').modal({dismissible: false}).modal("open");
            $("#btn-select-all").click();
        }
        hide_loader_top();
    });
    $(document).on('click', ".show-qr", async function () {
        show_loader_top();
        var licencia = $(this).attr("licencia");
        var idScanner = $(this).parent().parent().attr("id");
        let qr = true;
        if ($("#img-qry-" + idScanner).length === 0) {
            try {
                qr = await getQr(licencia, idScanner);
            } catch (e) {
                hide_loader_top();
                show_modal_error(e.responseText);
                return;
            }
        }
        if (qr) {
            $("#modal-show-qr").modal({dismissible: false}).modal("open");
            $("#img-qry-" + idScanner).show();
        }
        hide_loader_top();
    });
    $('#modal-show-qr .modal-close').on('click', function () {
        $("#tbl-qr tbody").find('tr').each(function (i, val) {
            $(val).hide();
        });
    });
    $('#modal-show-detalle .modal-close').on('click', function () {
        if ($("#btn-select-all").is(":checked")) {
            $("#btn-select-all").click();
        }
    });
    $("#btn-select-all").on("change", function (open = false) {
        show_loader_top();
        if ($("#btn-select-all").is(":checked")) {
            $.each($(".licencia-check"), function (i, e) {
                if (licenses_list[e.value].estadoActivacion || licenses_list[e.value].fechaExpiracion === null) {
                    $(e).prop("checked", true);
                    var index = $.inArray($(e).attr('value'), license_to_send);
                    if (index != -1) {
                        license_to_send.splice(index, 1);
                    }
                    license_to_send.push($(e).attr('value'));
                }
            });
        } else {
            $.each($(".licencia-check"), function (i, e) {
                $(e).prop("checked", false);
                var index = $.inArray($(e).attr('value'), license_to_send);
                license_to_send.splice(index, 1);
            });
        }
        hide_loader_top();
    });
    $(document).on("change", ".licencia-check", function () {
        var value = $(this).val();
        var index = $.inArray(value, license_to_send);
        if ($(this).is(":checked")) {
            if (index === -1) {
                license_to_send.push(value);
            }
        } else {
            license_to_send.splice(index, 1);
        }
    });
    $("#btn-send-licences").on("click", function () {
        $("#btn-confirm-send-licenses").removeAttr("idScanner");
        if (license_to_send.length == 0) {
            show_toast('warning', section_text.sas_asegureseSeleccionarLicencia);
            return false;
        }
        $("#selected-licenses").html(section_text.sas_notaEnvioLicencias.replace("%licencias%", "<b>" + license_to_send.length + "</b>"));
        $("#mdl-confirm-sending").modal({dismissible: false}).modal("open");
    });
    $(document).on("click", ".mail-license", function () {
        var idScanner = $(this).parent().parent().attr('id');
        var license_to_send_temp = license_to_send;
        var nota = section_text.sas_notaEnvioLicencia.replace("%etiqueta%", "<b>" + licenses_list[idScanner]['etiquetaUsuario'] + "</b>");
        nota = nota.replace("%licencia%", "<b>" + licenses_list[idScanner]['textoLicencia'] + "</b>");
        $("#selected-licenses").html(nota);
        $("#mdl-confirm-sending").modal({dismissible: false}).modal("open");
        $("#btn-confirm-send-licenses").attr("idScanner", idScanner);
    });
    $("#btn-confirm-send-licenses").on("click", function () {
        $("#mdl-confirm-sending").modal('close');
        sendLicenses($(this).attr('idScanner'));
    });
    $(document).on("click", ".liberation", function () {
        var idScanner = $(this).parent().parent().attr('id');
        var nota = section_text.sas_notaLiberarLicencia.replace("%etiqueta%", "<b>" + licenses_list[idScanner]['etiquetaUsuario'] + "</b>");
        nota = nota.replace("%licencia%", "<b>" + licenses_list[idScanner]['textoLicencia'] + "</b>");
        $("#liberation-license").html(nota);
        $("#mdl-confirm-liberation").modal({dismissible: false}).modal("open");
        $("#btn-confirm-liberation-license").attr("idScanner", idScanner);
    });
    $("#btn-confirm-liberation-license").on("click", function () {
        $("#mdl-confirm-liberation").modal('close');
        licenseLiberation($(this).attr('idScanner'));
    });


    $(document).on("click", ".change-date", function () {
        var nowDate = new Date();
        if (lang == 'es') {
            $('.datepicker').pickadate({
                selectMonths: true,
                selectYears: 1,
                monthsFull: ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
                monthsShort: ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'],
                weekdaysFull: ['domingo', 'lunes', 'martes', 'miÃ©rcoles ', 'jueves', 'viernes', 'sÃ¡bado'],
                weekdaysShort: ['dom', 'lun', 'mar', 'mie', 'jue', 'vie', 'sab'],
                today: false,
                clear: false,
                close: 'guardar',
                format: 'yyyy-mm-dd',
                min: new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate() + 1),
                onClose: function () {
                    var id = $(this)[0].$node.parent().parent().attr("id");
                    var date = licenses_list[id]['fechaExpiracion'].split(" ");
                    var newDate = $("#FechaExpiracion-" + id).val().split(" ");
                    if ($("#FechaExpiracion-" + id).val() !== "") {
                        $("#FechaExpiracion-" + id).val(newDate[0] + " " + date[1].substring(0, 8));
                        $("#mdl-confirm-date .modal-content").find('p').find('span').text($("#FechaExpiracion-" + id).val());
                        $("#selected-licenses-date").html("Se modificara la fecha limite de la licencia" + "<br><b>" + licenses_list[id]['textoLicencia'] + "</b>");
                        $("#btn-confirm-change-date").attr('id-date', id);
                        $("#btn-confirm-change-date").attr('new-date', $("#FechaExpiracion-" + id).val());
                        $("#mdl-confirm-date").modal({dismissible: false}).modal("open");
                        //$("#FechaExpiracion-" + id).click();
                    }
                }
                /*max: '07,09,2017'*/
            });
        } else {
            $(".datepicker").pickadate({
                selectMonths: true, // Creates a dropdown to control month
                selectYears: 1, // Creates a dropdown of 15 years to control year
                format: 'yyyy-mm-dd',
                min: '25,05,2017'/*,
                 max: '07,09,2017'*/
            });
        }
        var id = $(this).parent().parent().attr("id");
        $("#FechaExpiracion-" + id).trigger("click");
    });


    $(document).on("click", "#btn-confirm-change-date", function () {
        var idDate = $(this).attr("id-date");
        var newDate = $(this).attr("new-date");
        UpdateDateExpiry(idDate, newDate);
        $("#mdl-confirm-date").modal("close");
    });
    /***** Mostrar Detalle Licencias FIN *****/

    $("#add-empresa-lectora").on('click', function () {
        clearForm('add-empresa-lectora-form');
        action = "insert";
        $("#empresa-lectora-head").html(section_text["sas_agregaLectora"]);
        /* Ponemos en Status 1(En Uso) Automaticamente al guardar la lectora */
        $("#idStatusScanner").val(1).change();
        // $("#idScannerTipo").val(1).change();
        $('#add-lectora-empresa-modal').modal({dismissible: false}).modal("open");
    }
    );
    $("#delete-record").on("click", function () {
        $("#delete-record-modal").modal('close');
        deleteEmpresaLectora();
    });
    $(document).on("click", ".delete-record", function () {
        itemToDelete = $(this).attr("data-id");
        tr = $(this).parents("tr");
        action = "delete";
        var text = section_text["sas_textoEliminarRegistro"].replace('%lectora%', '<u>' + lectoras_empresa[itemToDelete]["ScannerTipo"] + '</u>');
        text = text.replace('%CodigoScanner%', '<u>' + lectoras_empresa[itemToDelete]["CodigoScanner"] + '</u>') + " ?";
        $("#deleteText").html(text);
        $("#delete-record-modal").modal({dismissible: false}).modal("open");
    });
    $("#idScannerTipo").on("change", function () {
        var nota = "";
        if ($("option:selected", this).attr("AppIxpo")) {
            var nota = section_text["sas_notaAppIxpo"];
            $("#CodigoScanner").attr("disabled", true);

        } else {
            var nota = section_text["sas_notaCodigoScanner"];
            $("#CodigoScanner").attr("disabled", false);
            nota = nota.replace("%lectora%", "<b>" + $("option:selected", this).text() + "</b>");
            if ($("option:selected", this).attr("RequierePassport")) {
                nota = nota.replace("%codigo%", " <b>" + section_text['sas_passportLectora'] + ".</b>");
            } else {
                nota = nota.replace("%codigo%", " <b>" + section_text['sas_NoSerieLectora'] + ".</b>");
            }
        }
        $("#NotaCodigoScanner").html("*" + nota);
    });
}


/* Administrador de Lectoras ABC*/
function generateEmpresaLectorasTable(id) {
    oTable = $('#' + id).DataTable({
        "language": {
            "url": url_lang
        }
    });
}

function validateEmpresaLectoraForm() {
    $("#add-empresa-lectora-form").validate({
        rules: {
            'CodigoScanner': {
                required: true,
                maxlength: 100
            },
            'EtiquetaApp': {
                required: true,
                maxlength: 100
            },
            'idScannerTipo': {
                required: true
            },
            'idStatusScanner': {
                required: true
            }
        },
        messages: {
            'CodigoScanner': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'EtiquetaApp': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'idScannerTipo': {
                required: general_text.sas_requerido
            },
            'idStatusScanner': {
                required: general_text.sas_requerido
            }
        },
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
            $('#add-lectora-empresa-modal').modal('close');
            disabled = $("#add-empresa-lectora-form select:disabled").removeAttr("disabled");
            disabled = $("#add-empresa-lectora-form input:disabled").removeAttr("disabled");
            $("#ScannerTipo").val($('#idScannerTipo option:selected').text());
            $("#Status").val($('#idStatusScanner option:selected').text());
            var post = $('#add-empresa-lectora-form').serialize();
            show_loader_top();
            if (action == "insert")
                addEmpresaLectora(post);
            if (action == "update")
                updateEmpresaLectora(post);
            return;
        }
    });
}


function addEmpresaLectora(post) {
    $.ajax({
        type: "post",
        url: url_add_empresa_lectora,
        dataType: 'json',
        data: post,
        success: function (response) {
            disabled.attr("disabled", "disabled");
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            response.data.Cortesia = (response.data.Cortesia === "TRUE");
            lectoras_empresa[response.data["idEmpresaScanner"]] = response.data;
            setRow(response.data, action);
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function updateEmpresaLectora(post) {
    post += "&idEmpresaScanner=" + itemToUpdate;
    $.ajax({
        type: "post",
        url: url_edit_empresa_lectora,
        dataType: 'json',
        data: post,
        success: function (response) {
            disabled.attr("disabled", "disabled");
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            response.data.Cortesia = (response.data.Cortesia === "TRUE");
            lectoras_empresa[response.data["idEmpresaScanner"]] = response.data;
            setRow(response.data, action);
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}


function deleteEmpresaLectora() {
    $.ajax({
        type: "post",
        url: url_delete_empresa_lectora,
        dataType: 'json',
        data: {idEmpresaScanner: itemToDelete},
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            delete lectoras_empresa[response.data["idEmpresaScanner"]];
            setRow(response.data, action);
            show_alert("success", general_text.sas_eliminoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function setRow(data, action) {
    var cortesiaTexto;
    if (data.Cortesia === true) {
        cortesiaTexto = general_text.sas_si;
    } else {
        cortesiaTexto = general_text.sas_no;
    }
    var Etiqueta;
    if (data.EtiquetaApp !== "") {
        Etiqueta = data.EtiquetaApp;
    } else {
        Etiqueta = section_text.sas_noAplica;
    }
    switch (action) {
        case 'insert':
            oTable.row.add([
                data.CodigoScanner,
                Etiqueta,
                data.ScannerTipo,
                data.Status,
                cortesiaTexto,
                '<i class="material-icons edit-record" data-id="' + data.idEmpresaScanner + '">edit</i>' +
                        '<i class="material-icons delete-record" data-id="' + data.idEmpresaScanner + '">delete_forever</i>'
            ]).draw('full-hold');
            break;
        case 'update':
            oTable.row(tr).data([
                data.CodigoScanner,
                Etiqueta,
                data.ScannerTipo,
                data.Status,
                cortesiaTexto,
                '<i class="material-icons edit-record" data-id="' + data.idEmpresaScanner + '">mode_edit</i>' +
                        '<i class="material-icons delete-record" data-id="' + data.idEmpresaScanner + '">delete_forever</i>'
            ]).draw();
            break;
        case 'delete':
            oTable.row(tr).remove().draw();
            break;
    }
}


function setEmpresaLectoraData(id) {
    var lectora = lectoras_empresa[itemToUpdate];
    $("#idScannerTipo").val(lectora['idScannerTipo']).change();
    $("#CodigoScanner").val(lectora['CodigoScanner']);
    $("#EtiquetaApp").val(lectora['EtiquetaApp']);
    $("#idStatusScanner").val(lectora['idStatusScanner']).change();
    $("#idStatusScanner").attr('disabled', false);
    $("#Cortesia").prop("checked", (lectora['Cortesia']));
    if (!lectora['EstadoDisponibilidad'] && lectora['AppIxpo']) {
        $("#idScannerTipo").attr('disabled', true);
    } else {
        $("#idScannerTipo").attr('disabled', false);
    }
    $("#add-empresa-lectora-form input[type='text'], textarea").removeClass('valid').next().addClass('active');
}


function clearForm(idForm) {
    $('#' + idForm).find('input').each(function (index, element) {
        if (!$(element).is(':disabled')) {
            $(element).removeClass('valid').next().removeClass('active');
        }
    });
    $("#idScannerTipo").attr('disabled', false);
    $("#idStatusScanner").attr('disabled', true);
    $("#Cortesia").prop("checked", false);
    $("#NotaCodigoScanner").html("");
    $('#' + idForm).find('input[type="text"]').not('input[type="text"]:disabled').val("");
    $('#' + idForm).find('input[type="email"]').not('input[type="email"]:disabled').val("");
    $('#' + idForm).find('input[type="tel"]').not('input[type="tel"]:disabled').val("");
    $('#' + idForm).find('textarea').not('textarea:disabled').val("");
    $('#' + idForm).find('select').not('select:disabled').val("");
    $('#' + idForm).find('input[type="checkbox"] input[type="radio"]').not('input[type="checkbox"]:disabled input[type="radio"]:disabled').prop('checked', false);
}
/* Administrador de Lectoras ABC FIN*/

/***** Mostrar Detalle Licencias *****/
async function getLicenses(idEmpresa) {
    let licencias;
    try {
        licencias = await $.ajax({
            type: "post",
            url: url_get_licencias,
            dataType: 'json',
            data: {idEmpresa: idEmpresa}
        });
    } catch (e) {
        return e;
    }
    if (!licencias['status']) {
        show_alert("danger", licencias['data']);
        return false;
    }
    buildLicensesTable(licencias.data['licencias']);
    return true;
}

function buildLicensesTable(licenses) {
    $("#tbl-detalle-licencia tbody").empty();
    licenses_list = [];
    $.each(licenses, function (index, licencia) {
        var fecha = section_text.sas_sinInicioSesion;
        var activacion = section_text.sas_expirada;
        var disponibilidad = section_text.sas_enUso;
        var dispositivo = section_text.sas_sinInicioSesion;
        var active_mail = [];
        var active_device = [];

        if (licencia.estadoDisponibilidad) {
            disponibilidad = section_text.sas_disponible;
            active_device["class"] = "disabled";
        } else {
            active_device["class"] = "liberation";
        }

        if (licencia.device !== null) {
            dispositivo = licencia.device;
        }
        licencia.device = dispositivo;
        if (validarEmail(licencia.etiquetaUsuario) && (licencia.estadoActivacion || licencia.fechaExpiracion === null)) {
            active_mail["class"] = "mail-license";
        } else {
            active_mail["class"] = "disabled";
        }

        if (licencia.estadoActivacion) {
            activacion = section_text.sas_vigente;
        } else if (licencia.fechaExpiracion === null) {
            activacion = section_text.sas_sinInicioSesion
        }

        if (licencia.fechaExpiracion !== null) {
            var fecha_split = licencia.fechaExpiracion.split(" ");
            fecha = fecha_split[0] + " " + fecha_split[1].substring(0, 8);
        }

        var tr = jQuery("<tr/>", {
            "id": licencia.idEmpresaScanner,
            "class": "licencia-info"
        }).appendTo("#tbl-detalle-licencia tbody");
        var td = jQuery("<td/>").appendTo(tr);
        jQuery("<input/>", {
            "type": "checkbox",
            "id": "licencia-" + licencia.idEmpresaScanner,
            "value": licencia.idEmpresaScanner,
            "class": "licencia-check"
        }).appendTo(td);
        td = jQuery("<td/>", {
        }).appendTo(tr);
        jQuery("<p/>", {
            "text": licencia.textoLicencia
        }).appendTo(td);
        td = jQuery("<td/>", {
        }).appendTo(tr);
        jQuery("<p/>", {
            "id": "activacion-" + licencia.idEmpresaScanner,
            "text": activacion
        }).appendTo(td);
        td = jQuery("<td/>", {
        }).appendTo(tr);
        jQuery("<p/>", {
            "id": "disponible-" + licencia.idEmpresaScanner,
            "text": disponibilidad
        }).appendTo(td);
        td = jQuery("<td/>", {
        }).appendTo(tr);
        jQuery("<p/>", {
            "text": licencia.device
        }).appendTo(td);
        td = jQuery("<td/>", {
        }).appendTo(tr);
        jQuery("<p/>", {
            "id": "fechaExp-" + licencia.idEmpresaScanner,
            "text": fecha
        }).appendTo(td);
        td = jQuery("<td/>", {
        }).appendTo(tr);
        jQuery("<p/>", {
            "text": licencia.etiquetaUsuario
        }).appendTo(td);
        td = jQuery("<td/>", {
        }).appendTo(tr);
        jQuery("<i/>", {
            "class": "material-icons show-qr",
            "text": "select_all",
            "licencia": licencia.textoLicencia
        }).appendTo(td);
        td = jQuery("<td/>", {
        }).appendTo(tr);
        jQuery("<i/>", {
            "class": "material-icons " + active_device["class"],
            "text": "phonelink_erase"
        }).appendTo(td);
        td = jQuery("<td/>", {
        }).appendTo(tr);
        jQuery("<i/>", {
            "class": "material-icons " + active_mail["class"],
            "text": "email"
        }
        ).appendTo(td);
        td = jQuery("<td/>", {
        }).appendTo(tr);
        jQuery("<i/>", {
            "class": "material-icons change-date",
            "text": "update"
        }
        ).appendTo(td);
        jQuery("<input/>", {
            "class": "datepicker",
            "hidden": true,
            "id": "FechaExpiracion-" + licencia.idEmpresaScanner,
            "name": "FechaExpiracion-" + licencia.idEmpresaScanner
        }).appendTo(td);
        licenses_list[licencia.idEmpresaScanner] = licencia;
    });
}


async function getQr(licencia, idScanner) {
    let qr;
    try {
        qr = await $.ajax({
            type: "post",
            url: url_get_qr,
            dataType: 'json',
            data: {licencia: licencia}
        });
    } catch (e) {
        return e;
    }
    if (!qr['status']) {
        show_toast("danger", qr['data']);
        return false;
    }
    buildQr(qr.data, idScanner);
    return true;
}

function buildQr(qr, idScanner) {
    var tr = jQuery("<tr/>", {
        "id": "img-qry-" + idScanner
    }).appendTo("#tbl-qr tbody");
    var td = jQuery("<td/>", {
        "style": "text-align: center;"
    }).appendTo(tr);
    jQuery("<p/>", {
        "html": "<b>" + section_text.sas_correoEtiqueta + "</b>: " + licenses_list[idScanner].etiquetaUsuario
    }).appendTo(td);
    jQuery("<p/>", {
        "html": "<b>" + section_text.sas_licencia + "</b>: " + qr.licence
    }).appendTo(td);
    jQuery("<img/>", {
        "src": url_generate_qr + "qrcode=" + qr.qrText + "&size=8"
    }).appendTo(td);
}

function validarEmail(mail) {
    if (/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(mail)) {
        return true;
    } else {
        return false;
    }
}


async function sendLicenses(idScanner) {
    show_loader_wrapper();
    var licencias = [];
    var mail = "";
    if (idScanner) {
        licencias[0] = licenses_list[idScanner];
        mail = licenses_list[idScanner]['etiquetaUsuario'];
    } else {
        $.each(license_to_send, function (i, e) {
            licencias[i] = licenses_list[e];
        });
    }
    try {
        await $.ajax({
            type: "post",
            url: url_send_email_licenses,
            dataType: 'json',
            data: {
                licencias: licencias,
                idEmpresa: $("#idEmpresa").val(),
                mail: mail,
                EmailEmpresa: $("#EmailEmpresa").val()
            },
            success: function (response) {
                hide_loader_wrapper();
                if (!response['status']) {
                    show_toast("danger", response['data']);
                    return;
                }
                show_toast('success', section_text.sas_correoEnviado);
            }
        });
    } catch (e) {
        show_modal_error(e.responseText);
    }
    hide_loader_wrapper();
}


async function licenseLiberation(idScanner) {
    show_loader_wrapper();
    var licencia = licenses_list[idScanner];
    try {
        await $.ajax({
            type: "post",
            url: url_license_liberation,
            dataType: 'json',
            data: {
                licencia: licencia,
                idEmpresa: $("#idEmpresa").val()
            },
            success: function (response) {
                hide_loader_wrapper();
                if (!response['status']) {
                    show_toast("danger", response['data']);
                    return;
                }
                var licencia = $("#" + idScanner + " td").find('.liberation');
                licencia.addClass('disabled');
                licencia.attr('disabled', true);
                licencia.removeClass('liberation');
                licenses_list[idScanner].estadoDisponibilidad = true;
                $("#disponible-" + idScanner).text(section_text.sas_disponible);
                show_toast('success', section_text.sas_licenciaLiberada);
            }
        });
    } catch (e) {
        show_modal_error(e.responseText);
    }
    hide_loader_wrapper();
}


async function UpdateDateExpiry(idEmpresaScanner, newDate) {
    show_loader_wrapper();
    var idScanner = licenses_list[idEmpresaScanner]["idScanner"];
    try {
        await $.ajax({
            type: "post",
            url: url_update_date_expiry,
            dataType: 'json',
            data: {
                idScanner: idScanner,
                newDate: newDate,
                idEmpresa: $("#idEmpresa").val()
            },
            success: function (response) {
                hide_loader_wrapper();
                if (!response['status']) {
                    show_toast("danger", response['data']);
                    return;
                }
                licenses_list[idEmpresaScanner]["fechaExpiracion"] = newDate;
                $("#fechaExp-" + idEmpresaScanner).text(newDate);
                $("#activacion-" + idEmpresaScanner).text(section_text.sas_vigente);
                $("#licencia-" + idEmpresaScanner).prop("checked", true);
                show_toast('success', response['data']['msg']);
            }
        });
    } catch (e) {
        show_modal_error(e.responseText);
    }
    hide_loader_wrapper();
}
/***** Mostrar Detalle Licencias FIN *****/


