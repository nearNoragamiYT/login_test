var oTable = "";
$(init);
function init() {
    initDataTable();
    $(document).on("click", ".approved-recod", function () {
        var id = $(this).attr('data-id');
        viewDetails(id);
    });
    $(document).on("click", "#reject-request", function () {
        if ($('#MotivoCancelacion').val() == "") {
            show_toast("warning", section_text.sas_describirMotivo);
            return;
        }
        var id = $(this).attr('data-id');
        cancelRequest(id);
    });
    $("#accept-request").on("click", function () {
        var id = $(this).attr('data-id');
        acceptRequest(id);
    });
    $(document).on("click", ".email-record", function () {
        var id = $(this).attr('data-id');
        $("#subject").val("");
        $("#company-name").text(requests[id]["DC_NombreComercial"]);
        $("#company-country").text(requests[id]["DC_Pais"]);
        var span = document.getElementById("status-send");
        setStatus(span, parseInt(requests[id]['Status']));
        tinymce.get("email").setContent('');
        $("#send-email").attr("data-id", id);
        $("#modal-email").modal("open");
        ;
    });
    $("#send-email").on("click", function () {
        var id = $(this).attr('data-id');
        sendEmail(id);
    });
}

function viewDetails(id) {
    var request = requests[id];
    var content = document.getElementById("details-request");
    content.innerHTML = "";
    var p = document.createElement("p");
    var span = document.createElement('span');
    span.className = "margin-right";
    span.textContent = section_text.sas_nombreComercial + ":";
    p.appendChild(span);
    $(p).append(request['DC_NombreComercial']);
    span = document.createElement('span');
    setStatus(span, parseInt(request['Status']));
    p.appendChild(span);
    content.appendChild(p);
    p = document.createElement("p");
    span = document.createElement('span');
    span.className = "margin-right";
    span.textContent = section_text.sas_pais + ":";
    p.appendChild(span);
    $(p).append(request['DC_Pais']);
    content.appendChild(p);
    p = document.createElement("p");
    span = document.createElement('span');
    span.className = "margin-right";
    span.textContent = section_text.sas_nombreContacto + ":";
    p.appendChild(span);
    $(p).append(request['NombreCompleto']);
    content.appendChild(p);
    p = document.createElement("p");
    span = document.createElement('span');
    span.className = "margin-right";
    span.textContent = section_text.sas_cargo + ":";
    p.appendChild(span);
    $(p).append(request['Puesto']);
    content.appendChild(p);
    p = document.createElement("p");
    span = document.createElement('span');
    span.className = "margin-right";
    span.textContent = section_text.sas_fechaSolicitud + ":";
    p.appendChild(span);
    $(p).append(returnDate(request['FechaSolicitud']));
    content.appendChild(p);
    p = document.createElement("p");
    span = document.createElement('span');
    span.className = "margin-right";
    span.textContent = section_text.sas_paqueteAsignado + ":";
    p.appendChild(span);
    $(p).append(packages[request['PaqueteActual']]['Paquete' + lang.toUpperCase()]);
    content.appendChild(p);
    div = document.createElement("div");
    div.className = "input-field select";
    var select = document.createElement("select");
    select.id = "idPaquete";
    select.name = "idPaquete";
    $.each(packages, function (i, v) {
        var opt = document.createElement("option");
        opt.value = i;
        opt.textContent = v["Paquete" + lang.toUpperCase()];
        if (i == parseInt(request["idPaquete"])) {
            opt.setAttribute("selected", true);
        }
        select.appendChild(opt);
    })
    div.appendChild(select);
    var label = document.createElement('label');
    label.textContent = section_text.sas_paqueteSolicitado;
    div.appendChild(label);
    content.appendChild(div);
    $(select).material_select();
    p = document.createElement("p");
    span = document.createElement('span');
    span.className = "margin-right";
    span.textContent = section_text.sas_fechaCancelacion + ":";
    p.appendChild(span);
    $(p).append(returnDate(request['FechaCancelacion']));
    content.appendChild(p);
    div = document.createElement("div");
    div.className = "input-field col s12";
    var textArea = document.createElement("textarea");
    textArea.id = "MotivoCancelacion";
    textArea.name = "MotivoCancelacion";
    textArea.className = "materialize-textarea";
    textArea.value = request['MotivoCancelacion'];
    label = document.createElement('label');
    label.textContent = section_text.sas_motivoCancelacion;
    label.setAttribute("for", "MotivoCancelacion");
    if (request['MotivoCancelacion'] != null && request['MotivoCancelacion'] != "") {
        label.className = "active";
    }
    div.appendChild(textArea);
    div.appendChild(label);
    content.appendChild(div);
    switch (request['Status']) {
        case 1:
            div = document.createElement('div');
            div.className = "col s12";
            var a = document.createElement('a');
            a.id = "reject-request";
            a.className = "waves-effect waves-light btn red right";
            a.textContent = section_text.sas_rechazarSolicitud;
            $(a).attr("data-id", id);
            div.appendChild(a);
            content.appendChild(div);
            $("#accept-request").attr('data-id', id).show();
            break;
        case 2:
            div = document.createElement('div');
            div.className = "col s12";
            var a = document.createElement('a');
            a.id = "reject-request";
            a.className = "waves-effect waves-light btn red right";
            a.textContent = section_text.sas_rechazarSolicitud;
            $(a).attr("data-id", id);
            div.appendChild(a);
            content.appendChild(div);
            $("#accept-request").attr('data-id', id).show();
            break;
        case 3:
            $("#accept-request").hide();
            break;
        case 4:
            $("#accept-request").attr('data-id', id).show();
            break;
        default:
            show_modal_error(general_text.sas_errorInterno);
            break;
    }
    $("#modal-show-details").modal("open");
}

function cancelRequest(id) {
    show_loader_wrapper();
    $.ajax({
        type: "post",
        url: url_cancel_package + "/" + id,
        dataType: 'json',
        data: {"MotivoCancelacion": $("#MotivoCancelacion").val()},
        success: function (data) {
            hide_loader_wrapper();
            oTable.destroy();
            requests[id]['Status'] = data.Status;
            requests[id]['MotivoCancelacion'] = data.MotivoCancelacion;
            requests[id]['FechaCancelacion'] = data.FechaCancelacion;
            $("#request-status-" + id).attr("data-badge-caption", section_text.sas_rechazada);
            $("#request-status-" + id).removeClass("blue-grey green deep-orange").addClass("red");
            $("#request-status-" + id).parent().attr('data-search', section_text.sas_rechazada);
            initDataTable();
            $("#modal-show-details").modal("close");
        },
        error: function (response) {
            hide_loader_wrapper();
            show_modal_error(general_text.sas_errorGuardado + "<br>" + response.responseText);
        }
    });
}

function acceptRequest(id) {
    show_loader_wrapper();
    $.ajax({
        type: "post",
        url: url_accept_package + "/" + id,
        dataType: 'json',
        data: {"idEmpresa": requests[id]['idEmpresa'], "idPaquete": $("#idPaquete").val()},
        success: function (data) {
            hide_loader_wrapper();
            oTable.destroy();
            requests[id]['Status'] = data.Status;
            requests[id]['PaqueteActual'] = data.PaqueteActual;
            $("#package-" + id).text(packages[$("#idPaquete").val()]['Paquete' + lang.toUpperCase()]);
            $("#request-status-" + id).attr("data-badge-caption", section_text.sas_aprobada);
            $("#request-status-" + id).removeClass("blue-grey red deep-orange").addClass("green");
            $("#request-status-" + id).parent().attr('data-search', section_text.sas_aprobada);
            initDataTable();
            $("#modal-show-details").modal("close");
        },
        error: function (response) {
            hide_loader_wrapper();
            show_modal_error(general_text.sas_errorGuardado + "<br>" + response.responseText);
        }
    });
}

function sendEmail(id) {
    show_loader_wrapper();
    $.ajax({
        type: "post",
        url: url_send_mail,
        dataType: 'json',
        data: {
            "Email": requests[id]["Email"],
            "Asunto": $("#subject").val(),
            "Correo": tinyMCE.get('email').getContent()
        },
        success: function (data) {
            hide_loader_wrapper();
            $("#modal-email").modal("close");
            show_toast("success", section_text.sas_exitoEnvioIndividual);
        },
        error: function (response) {
            hide_loader_wrapper();
            show_modal_error(general_text.sas_errorGuardado + "<br>" + response.responseText);
        }
    });
}

function setStatus(span, status) {
    switch (status) {
        case 1:
            span.className = "new badge blue-grey";
            span.setAttribute("data-badge-caption", section_text.sas_nueva);
            break;
        case 2:
            span.className = "new badge green";
            span.setAttribute("data-badge-caption", section_text.sas_aprobada);
            break;
        case 3:
            span.className = "new badge deep-orange";
            span.setAttribute("data-badge-caption", general_text.sas_cancelada);
            break;
        default:
            span.className = "new badge red";
            span.setAttribute("data-badge-caption", section_text.sas_rechazada);
            break;
    }
}

function initDataTable() {
    oTable = $("#requests").DataTable({
        "language": {
            "url": url_lang
        },
        responsive: true
    });
}

function returnDate(dt) {
    var date = dt.split(" ");
    return date[0];
}