var oTable = "", tr = "", itemToUpdate = "", itemToDelete = "", action = "", addContactoEdicion = 0, typeToUpdate = "";

$(document).ready(function () {
    initContacts();
});

function initContacts() {
    if (menu == "general")
        $("#empresa-contacto-general").attr("class", "active");
    if (menu == "edicion")
        $("#empresa-contacto-edicion").attr("class", "active");

    if (idEtapa != "")
        $("#change-contact").show();
    else
        $("#change-contact").hide();


    generateContactsTable('contacts-table');

    validateAddContactForm();
    validateChangeContactForm();

    $(document).on("click", ".company-menu>div>ul>li", function () {
        show_loader_wrapper();
    });

    $("#add-edition-contact-modal>div.modal-footer").hide();

    $("#add-contact").on('click', function () {
        $("#contact-head").html(section_text["sas_agregarContacto"]);
        clearForm('add-contact-form');

        if (menu == "general") {
            action = "insert";
            $("#add-contact-form").show();
            $('#add-general-contact-modal').modal({dismissible: false}).modal("open");
        }
        if (menu == "edicion") {
            $("#select-action").show();
            $("#add-contact-form").hide();
            $("#add-edition-contact-modal>div.modal-footer").hide();
            $('#add-edition-contact-modal').modal({dismissible: false}).modal("open");
        }
    });

    $("#btn-add-contact").on("click", function () {
        $("#add-contact-form").submit();
    });

    $(document).on("click", ".edit-record", function () {
        itemToUpdate = $(this).attr("data-id");
        typeToUpdate = $(this).attr("data-type");
        tr = $(this).parents("tr");
        action = "update";
        setContactData();
        $("#contact-head").html(section_text["sas_editarContacto"]);
        $("#select-action").hide();
        $("#add-contact-form").show();

        if (menu == "general")
            $('#add-general-contact-modal').modal({dismissible: false}).modal("open");
        if (menu == "edicion") {
            $("#add-edition-contact-modal>div.modal-footer").show();
            $('#add-edition-contact-modal').modal({dismissible: false}).modal("open");
        }
        $.each($(".toUpper"), function (i, ele) {
            $(ele).val($(ele).val().toUpperCase().trim());
        });
    });

    $("#btn-change-contact").on("click", function () {
        $("#change-contact-form").submit();
    });

    $(document).on("click", ".delete-record", function () {
        itemToDelete = $(this).attr("data-id");
        tr = $(this).parents("tr");
        action = "delete";
        $("#deleteText").html(section_text["sas_textoEliminarRegistro"] + ' ' + contacts[itemToDelete]["Nombre"] + ' ' + contacts[itemToDelete]["ApellidoPaterno"] + "?");
        $("#delete-record-modal").modal({dismissible: false}).modal("open");
    });

    $("#delete-record").on("click", function () {
        $("#delete-record-modal").modal("close");
        deleteContact();
    });

    $("#change-contact").on('click', function () {
        setPrincipalContactView();
        $("#contact-head2").html(section_text["sas_cambiarContactoPrincipal"]);
        $('#change-contact-modal').modal({dismissible: false}).modal("open");
    });

    $("#changePassword").on("click", function () {
        var last_generated_pass = "RX" + generateRandomString(6);
        $("#add-contact-form").find("#Password").val(last_generated_pass);
        $("#add-contact-form").find("#Password").removeClass('valid').next().addClass('active');
    });

    $("#generalContacts").on("change", function () {
        var contact = $(this).val();
        action = "insert";

        if (contact != 0) {
            var select = $("#idContactoTipo");
            select.empty();

            var keys_types = Object.keys(contact_types);
            var total_types = keys_types.length;

            var keys_ce = Object.keys(contacts);
            var total_ce = keys_ce.length;

            var type = "", con = "", types = [], aux = "", option;

            for (var i = 0; i < total_ce; i++) {
                con = contacts[keys_ce[i]];
                if (con["idContacto"] == contact) {
                    types.push(con["idContactoTipo"]);
                }
            }

            if (types.indexOf(0) == -1 && types.length == 5) {
                alert("Mal tipo");
            }

            for (var i = 0; i < total_types; i++) {
                type = contact_types[keys_types[i]];

                if (types.indexOf(type["idContactoTipo"]) == -1) {
                    option = document.createElement("option");
                    option.setAttribute("value", type["idContactoTipo"]);
                    option.innerHTML = type["ContactoTipoES"];
                    select.append(option);
                }
            }
            itemToUpdate = contact;
            addContactoEdicion = 1;
            action = "update";
            setContactData();
        } else {
            var select = $("#idContactoTipo");
            select.empty();

            var keys_types = Object.keys(contact_types);
            var total_types = keys_types.length;

            for (var i = 0; i < total_types; i++) {
                type = contact_types[keys_types[i]];

                option = document.createElement("option");
                option.setAttribute("value", type["idContactoTipo"]);
                option.innerHTML = type["ContactoTipoES"];
                select.append(option);
            }
        }


        $("#select-action").slideUp();
        $("#add-contact-form").slideDown();
        $("#add-edition-contact-modal>div.modal-footer").show();
    });
}

function generateContactsTable(id) {
    var btn, span;
    oTable = $('#' + id).DataTable({
        "language": {
            "url": url_lang
        }
    });
}
function validateAddContactForm() {
    $("#add-contact-form").validate({
        rules: {
            'idContactoTipo': {
                required: true
            },
            'Nombre': {
                required: true,
                maxlength: 100
            },
            'ApellidoPaterno': {
                required: true,
                maxlength: 100
            },
            'Email': {
                required: true,
                email: true,
                maxlength: 100
            }
        },
        messages: {
            'idContactoTipo': {
                required: general_text.sas_requerido,
            },
            'Nombre': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'ApellidoPaterno': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'Email': {
                required: general_text.sas_requerido,
                email: general_text.sas_ingresaCorreoValido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
        },
        errorElement: "div",
        errorClass: "invalid",
        errorPlacement: function (error, element) {
            if ($(element).parent('div').find('i.material-icons').length > 0) {
                $(error).attr('icon', true);
            }

            var placement = $(element).data('error');
            if (placement) {
                $(placement).append(error)
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            $('#add-general-contact-modal').modal("close");
            $('#add-edition-contact-modal').modal("close");

            var post = $('#add-contact-form').serialize();

            if (action == "insert")
                addContact(post);
            if (action == "update")
                updateContact(post);

            return;
        }
    });
}
function addContact(post) {
    post += "&Principal=false";
    $.ajax({
        type: "post",
        url: url_contact_add,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            contacts[response.data["idContacto"]] = response.data;
            setRow(response.data, "insert");
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}
function updateContact(post) {
    post += "&idContacto=" + itemToUpdate;
    post += "&idContactoTipoActual=" + typeToUpdate;
    if (typeof contacts[itemToUpdate] === "undefined")
        post += "&Principal=false";
    else
        post += "&Principal=" + contacts[itemToUpdate]["Principal"];

    $.ajax({
        type: "post",
        url: url_contact_update,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            contacts[response.data["idContacto"]] = response.data;
            if (addContactoEdicion)
                setRow(response.data, "insert");
            else
                setRow(response.data, "update");
            addContactoEdicion = 0;
            show_alert("success", general_text.sas_guardoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}
function deleteContact() {
    $.ajax({
        type: "post",
        url: url_contact_delete,
        dataType: 'json',
        data: {
            idContacto: itemToDelete,
            idEmpresa: idEmpresa
        },
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            delete contacts[response.data["idContacto"]]
            setRow(response.data, "delete");
            show_alert("success", general_text.sas_eliminoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function validateChangeContactForm() {
    $("#change-contact-form").validate({
        rules: {
            'idActual': {
                required: true
            },
            'idNuevo': {
                required: true
            }
        },
        messages: {
            'idActual': {
                required: general_text.sas_requerido,
            },
            'idNuevo': {
                required: general_text.sas_requerido,
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
            $('#change-contact-modal').modal("close");
            var post = $('#change-contact-form').serialize();
            changeContact(post);
            return;
        }
    });
}
function changeContact(post) {
    post += "&idEmpresa=" + idEmpresa;
    post += "&idContactoTipo=" + $("#idNuevo option:selected").attr("data-type");
    $.ajax({
        type: "post",
        url: url_contact_change,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            var keys = Object.keys(contacts);
            var total = keys.length;
            var contact = "", currentPrincipal = "", newPrincipal = "";

            for (var i = 0; i < total; i++) {
                contact = contacts[keys[i]];

                if (contact["idContacto"] == response.data["idActual"] && contact["idContactoTipo"] == response.data["idTipoActual"])
                    currentPrincipal = contact;

                if (contact["idContacto"] == response.data["idNuevo"] && contact["idContactoTipo"] == response.data["idContactoTipo"])
                    newPrincipal = contact;
            }

            currentPrincipal["Principal"] = false;
            tr = $('tr[data-row="' + response.data["idActual"] + '-' + response.data["idTipoActual"] + '"]').find("td:last-child").text("No");
            //setRow(currentPrincipal, "update");

            newPrincipal["Principal"] = true;
            tr = $('tr[data-row="' + response.data["idNuevo"] + '-' + response.data["idContactoTipo"] + '"]').find("td:last-child").text("Si");
            //setRow(newPrincipal, "update");

            show_alert("success", general_text.sas_eliminoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function setContactData() {
    var keys = Object.keys(contacts);
    var total = keys.length;
    var contactAux = "", contact = "";

    for (var i = 0; i < total; i++) {
        contactAux = contacts[keys[i]];
        if (contactAux["idContacto"] == itemToUpdate && contactAux["idContactoTipo"] == typeToUpdate)
            contact = contactAux;
    }
    $("#Nombre").val(contact["Nombre"]);
    $("#ApellidoPaterno").val(contact["ApellidoPaterno"]);
    $("#ApellidoMaterno").val(contact["ApellidoMaterno"]);
    $("#Email").val(contact["Email"]);
    $("#EmailAlterno").val(contact["EmailAlterno"]);

    if (contact['idContactoTipo'] != "" && contact['idContactoTipo'] != null) {
        $("#idContactoTipo").val(contact['idContactoTipo']).change();
    }
    if (contact['Password'] == "" || contact['Password'] == null) {
        var last_generated_pass = "RX" + generateRandomString(6);
        $("#add-contact-form").find("#Password").val(last_generated_pass);
        $("#add-contact-form").find("#Password").removeClass('valid').next().addClass('active');
    } else {
        $("#Password").val(contact['Password']);
    }
    $("#Puesto").val(contact["Puesto"]);
    $("#Telefono").val(contact["Telefono"]);
    $("#Celular").val(contact["Celular"]);
    $("#add-contact-form input[type='text'], textarea").removeClass('valid').next().addClass('active');
    $("#complete-contact-form input[type='text'], textarea").removeClass('valid').next().addClass('active');
}
function setPrincipalContactView() {
    var keys = Object.keys(contacts);
    var total = keys.length;

    for (var i = 0; i < total; i++) {
        if (contacts[keys[i]]["Principal"] == true || contacts[keys[i]]["Principal"] == "Si") {
            var principal = contacts[keys[i]];
        }
    }
    if (typeof principal === 'undefined') {
        $("#idActual").val("null");
        $("#idTipoActual").val("null");
        $("#NombreCompleto").val("");
    } else {
        $("#idActual").val(principal["idContacto"]);
        $("#idTipoActual").val(principal["idContactoTipo"]);
        $("#NombreCompleto").val(principal["Nombre"] + " " + principal["ApellidoPaterno"] + " - " + contact_types[principal["idContactoTipo"]]["ContactoTipo" + lang.toUpperCase()]);
    }
    $("#idNuevo").val("").change();
    $("#change-contact-form input[type='text']").removeClass('valid').next().addClass('active');
}
function clearForm(idForm) {
    $('#' + idForm).find('input').each(function (index, element) {
        if (!$(element).is(':disabled')) {
            $(element).removeClass('valid').next().removeClass('active');
        }
    });
    $('#' + idForm).find('input[type="text"]').not('input[type="text"]:disabled').val("");
    $('#' + idForm).find('input[type="email"]').not('input[type="email"]:disabled').val("");
    $('#' + idForm).find('input[type="tel"]').not('input[type="tel"]:disabled').val("");
    $('#' + idForm).find('textarea').not('textarea:disabled').val("");
    $('#' + idForm).find('select').not('select:disabled').val("");
    $('#' + idForm).find('input[type="checkbox"] input[type="radio"]').not('input[type="checkbox"]:disabled input[type="radio"]:disabled').prop('checked', false);
}
function setRow(data, action) {
    var logos = "", insertRow = "";
    if (data["Principal"] == "false" || data["Principal"] == "" || data["Principal"] == false || data["Principal"] == 0 || data["Principal"] == "null")
        data["Principal"] = "No";
    if (data["Principal"] == "true" || data["Principal"] == true || data["Principal"] == 1)
        data["Principal"] = "Si";

    if (menu == "edicion")
        logos = '<i class="material-icons edit-record" data-id="' + data.idContacto + '">mode_edit</i>';
    else
        logos = '<i class="material-icons edit-record" data-id="' + data.idContacto + '">mode_edit</i>' +
                '<i class="material-icons delete-record" data-id="' + data.idContacto + '">delete_forever</i>';

    if (action != "delete") {
        if (menu == "general") {
            var psw = $("tr#" + data.idContacto).find(":nth-child(3)").text().trim();
            insertRow = [
                data.Nombre + " " + data.ApellidoPaterno,
                data.Email,
                psw,
                /*data.Puesto,
                 data.Telefono,
                 data.Celular,*/
                logos
            ];
        } else
            insertRow = [
                contact_types[data.idContactoTipo]["ContactoTipo" + lang.toUpperCase()],
                data.Nombre + " " + data.ApellidoPaterno,
                data.Email,
                data.Puesto,
                data.Telefono,
                data.Celular,
                data.Principal,
                logos
            ];
    }

    switch (action) {
        case 'insert':
            oTable.row.add(insertRow).draw('full-hold');
            break;
        case 'update':
            oTable.row(tr).data(insertRow).draw();
            break;
        case 'delete':
            oTable.row(tr).remove().draw();
            break;
    }
}


function generateRandomString(l) {
    var c = "",
            str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for (var i = 0; i < l; i++)
        c += str.charAt(Math.floor(Math.random() * str.length));

    return c.toUpperCase();
}