/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery.extend(jQuery.validator.messages, {
    required: general_text.sas_campoRequerido,
    email: general_text.sas_emailInvalido,
    number: general_text.sas_soloNumeros,
    digits: general_text.sas_soloDigitos,
    maxlength: jQuery.validator.format(general_text.sas_ingresaMaxCaracteres)
});

var validateForm = null;

$(init);

function init() {
    initDatatables();
    $("#show-modal-adviser").on('click', function () {
        clearForm("form-adviser");
        validateForm.resetForm();
        $("#idUsuario").val(0);
        $("#modal-add-adviser").modal({dismissible: false}).modal("open");
    });
    $("#add-adviser").on("click", function () {
        $("#form-adviser").submit();
    });
    $(document).on("click", ".edit-record", function () {
        var id = $(this).attr("data-id");
        serDataAdviser(id);
        validateForm.resetForm();
        $("#modal-add-adviser").modal({dismissible: false}).modal("open");
    });
    $(document).on("click", ".actived-adviser", function () {
        activeInactiveAdviser($(this).attr('data-id'), $(this).is(":checked"));
    });
    $("#edit-password").on("click", function () {
        $(this).parent().find("input[type=password]").val("").prop('disabled', false).focus();
        $(this).fadeOut();
    });
    validateAdviser();
}

function initDatatables() {
    $("#commertial-advisors-table").DataTable({
        "language": {
            "url": dataTablesLang
        },
        "order": [[1, 'asc']]
    });
}

function serDataAdviser(id) {
    var adviser = advisors[id];
    $("#Nombre").val(adviser['Nombre']).next().addClass('active');
    $("#Email").val(adviser['Email']).next().addClass('active');
    $("#Telefono").val(adviser['Telefono']).next().addClass('active');
    $("#Puesto").val(adviser['Puesto']).next().addClass('active');
    $("#idUsuario").val(id);
    $("#Password").val("****").prop('disabled', true).next().addClass('active');
    $("#edit-password").show();
}

function validateAdviser() {
    validateForm = $("#form-adviser").validate({
        errorClass: "col s8 offset-s2 invalid",
        validClass: "col s8 offset-s2 valid",
        errorElement: "div",
        errorPlacement: function (error, element) {
            if (($(element).tagName === "INPUT") && ($(element).attr('type') === "radio" || $(element).attr('type') === "checkbox")) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element.parent());
            }
        },
        highlight: function (element, errorClass, validClass) {
            if (($(element).tagName === "INPUT") && ($(element).attr('type') === "radio" || $(element).attr('type') === "checkbox")) {
                $(element).parent().addClass(errorClass).removeClass(validClass);
            } else {
                $(element).addClass(errorClass).removeClass(validClass);
            }
        },
        unhighlight: function (element, errorClass, validClass) {
            if (($(element).tagName === "INPUT") && ($(element).attr('type') === "radio" || $(element).attr('type') === "checkbox")) {
                $(element).parent().addClass(validClass).removeClass(errorClass);
            } else {
                $(element).addClass(validClass).removeClass(errorClass);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            addAdviser($(form).serializeArray());
        }
    });
}

function addAdviser(data) {
    var idAdviser = $("#idUsuario").val();
    if (idUsuario == "0") {
        data[data.length] = {name: "Activo", value: "t"};
    } else {
        var actived = ($(".actived-adviser[data-id=" + idAdviser + "]").is(":checked")) ? "t" : "f";
        data[data.length] = {name: "Activo", value: actived};
    }
    $.ajax({
        url: url_add_edit_advisor,
        type: "POST",
        dataType: "json",
        data: data,
        success: function (response) {
            hide_loader_wrapper();
            destroyDatatables();
            (idAdviser == "0") ? addRow(response.data) : setRowData(response.data);
            initDatatables();
            advisors[response.data['idUsuario']] = response.data;
            $("#modal-add-adviser").modal("close");
            show_toast('success', general_text.sas_guardoExito);
        },
        error: function (jqXHR) {
            hide_loader_wrapper();
            show_modal_error(general_text.sas_errorInterno + "<br>" + jqXHR.responseText);
        }
    });
}

function destroyDatatables() {
    $("#commertial-advisors-table").DataTable().destroy();
}

function addRow(data) {
    var tr = null, td = null, tbody = null, div = null, label = null, input = null, span = null;
    tbody = document.getElementById("commertial-advisors-table").getElementsByTagName("tbody");
    tr = document.createElement("tr");
    tr.id = "tr-" + data['idUsuario'];
    td = document.createElement("td");
    div = document.createElement('div');
    div.className = "switch";
    label = document.createElement('label');
    input = document.createElement('input');
    input.type = "checkbox";
    input.value = "1";
    input.setAttribute("data-id", data['idUsuario']);
    input.setAttribute("checked", 'checked');
    input.className = "actived-adviser";
    span = document.createElement('span');
    span.className = "lever";
    label.append(general_text.sas_no);
    label.appendChild(input);
    label.appendChild(span);
    label.append(general_text.sas_si);
    div.appendChild(label);
    td.appendChild(div);
    tr.appendChild(td);
    td = document.createElement("td");
    td.textContent = data['Nombre'];
    tr.appendChild(td);
    td = document.createElement("td");
    td.textContent = data['Email'];
    tr.appendChild(td);
    td = document.createElement("td");
    td.textContent = data['Telefono'];
    tr.appendChild(td);
    td = document.createElement("td");
    td.textContent = 0;
    tr.appendChild(td);
    td = document.createElement("td");
    td.innerHTML = '<i class="material-icons edit-record" data-id="' + data['idUsuario'] + '">edit</i>';
    tr.appendChild(td);
    $(tbody).append(tr);
}

function setRowData(data) {
    var tr = document.getElementById("tr-" + data['idUsuario']);
    var children = tr.children;
    children[1].textContent = data['Nombre'];
    children[2].textContent = data['Email'];
    children[3].textContent = data['Telefono'];
}

function activeInactiveAdviser(idUser, check) {
    show_loader_wrapper();
    $.ajax({
        url: url_actived_advisor,
        type: "POST",
        dataType: "json",
        data: {idUsuario: idUser, Activo: check},
        success: function () {
            hide_loader_wrapper();
            show_toast('success', general_text.sas_guardoExito);
        },
        error: function (jqXHR) {
            hide_loader_wrapper();
            $('.actived-adviser[data-id=' + idUser + ']').prop("checked", !check);
            show_modal_error(general_text.sas_errorInterno + "<br>" + jqXHR.responseText);
        }
    });
}

function clearForm(id) {
    $("#" + id).find("input[type=text]").val("").next().removeClass('active');
    $("#" + id).find("input[type=password]").val("").prop('disabled', false).next().removeClass('active');
    $("#" + id).find("#edit-password").hide();
}