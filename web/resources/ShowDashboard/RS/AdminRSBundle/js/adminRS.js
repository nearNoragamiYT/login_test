var table = "";
var campo = "";
var form = "";
var nodo = {};

$(document).ready(function () {
    $('select').material_select();
    table = $('#table').DataTable({
        dom: 'Bfrtip',
        select: true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "NingÃºn dato disponible en esta tabla",
            "sInfo": "Registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Ãšltimo",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
    $("#rowNombreNodo").hide();
    jQuery.extend(jQuery.validator.messages, {
        required: general_text.sas_campoRequerido,
        number: general_text.sas_soloNumeros,
        maxlength: general_text.sas_caracteresMaximos,
        minlength: general_text.sas_caracterosMinimos,
        lettersonly: general_text.sas_soloTexto
    });
});

$(document).ready(function () {
    validateAddEstado();
    $('#btn-actualizar').prop('disabled', true);
    $('.btn-si-no').hide();
    $('.inp-res').hide();
    $('.btn-contingencia').hide();
    $('.btn-nombreEdicion').hide();
    $('.btn-nombreEvento').hide();
    $('#add-estado-modal').modal();
    $("#btn-add-estado").on('click', function () {
        $("#EstadoSistema").submit();
    });
    $('.reset').click(function () {
        $("#myForm")[0].reset();
    });
});

/* BOTON-ACTUALIZAR LOS CAMPOS SELECCIONADOS */
$("#btn-actualizar").on('click', function () {
    reset_form();
    if ($("#inp-res").val() != "") {
        UpdateGeneral($("#inp-res").val());
    } else if ($("#btn-contingencia").val() != "") {
        UpdateGeneral($("#btn-contingencia").val());
    } else if ($("#btn-si-no").val() != "") {
        UpdateGeneral($("#btn-si-no").val());
    } else if ($("#btn-nombreEdicion").val() != "") {
        UpdateGeneral($("#btn-nombreEdicion").val());
    } else if ($("#btn-nombreEvento").val() != "") {
        UpdateGeneral($("#btn-nombreEvento").val());
    }
});

/* GUARDAR EL "ID" DEL CHECKBOX SELECCIONADO EN UN ARRAY */
$(document).on('change', 'input[type="checkbox"]', function() {
    var id = $(this).attr("id");
    var input = $(this);
    var id = $(this).attr("id");
    var res = id.split("_");
    var res = res[1];
    if ($(input).prop('checked') == true) {
        nodo[JSON.parse(res)] = res;
    } else {
        delete nodo[JSON.parse(res)];
    }
    if (Object.keys(nodo).length == 0) {
        $('#btn-actualizar').prop('disabled', true);
    } else {
        $('#btn-actualizar').prop('disabled', false);
    }
});

/* CREACION DEL SWITCH QUE PERMITE OCULTAR Y MOSTRAR CAMPOS DE SELECCION */
    $('#btn-select').change(function () {
    campo = $(this).val();
    switch (campo) {
        case "idEdicion":
            $('.btn-nombreEdicion').show();
            $('.btn-nombreEvento').hide();
            $('.btn-contingencia').hide();
            $('.btn-si-no').hide();
            $('.inp-res').hide();
            $('.idNodo').hide();
            break;
        case "idEvento":
            $('.btn-nombreEdicion').hide();
            $('.btn-nombreEvento').show();
            $('.btn-contingencia').hide();
            $('.btn-si-no').hide();
            $('.inp-res').hide();
            $('.idNodo').hide();
            break;
        case "idCaptura":
            $('.btn-nombreEdicion').hide();
            $('.btn-nombreEvento').hide();
            $('.btn-contingencia').show();
            $('.btn-si-no').hide();
            $('.inp-res').hide();
            $('.idNodo').hide();
            break;
        case "ClubElite":
            $('.btn-nombreEdicion').hide();
            $('.btn-nombreEvento').hide();
            $('.btn-contingencia').hide();
            $('.btn-si-no').show();
            $('.inp-res').hide();
            $('.idNodo').hide();
            break;
        case "Tienda":
            $('.btn-nombreEdicion').hide();
            $('.btn-nombreEvento').hide();
            $('.btn-contingencia').hide();
            $('.btn-si-no').show();
            $('.inp-res').hide();
            $('.idNodo').hide();
            break;
        case "AutoRegistro":
            $('.btn-nombreEdicion').hide();
            $('.btn-nombreEvento').hide();
            $('.btn-contingencia').hide();
            $('.btn-si-no').show();
            $('.inp-res').hide();
            $('.idNodo').hide();
            break;
        case "GafeteMultiple":
            $('.btn-nombreEdicion').hide();
            $('.btn-nombreEvento').hide();
            $('.btn-contingencia').hide();
            $('.btn-si-no').show();
            $('.inp-res').hide();
            $('.idNodo').hide();
            break;
        case "Preregistro":
            $('.btn-nombreEdicion').hide();
            $('.btn-nombreEvento').hide();
            $('.btn-contingencia').hide();
            $('.btn-si-no').show();
            $('.inp-res').hide();
            $('.idNodo').hide();
            break;
    }
});

$("#btnAgregarEstado").on('click', function () {
    $('#add-estado-modal').modal({dismissible: false}).modal('open');
//    clearForm('beneficiario-form');
    action = "insert";
});
$("#btnAgregarEstado").on('click', function () {
    reset_form();
    $('#add-estado-modal').modal({dismissible: false}).modal('open');
});
$(document).on('click', '.edit-record', function () {
    var idConfiguracion = $(this).attr('id-edit');
    fillForm(idConfiguracion);
    $('#add-estado-modal').modal({dismissible: false}).modal('open');
    $('#btn-add-estado').attr('idConfiguracion', idConfiguracion);
    action = "update";
});
$(document).on('click', '.delete-record', function () {
    $("#deleteText").html('¿Esta seguro de borrar este nodo? ' + $(this).attr('ip'));
    var idConfiguracion = $(this).attr('id-delete');
    $('#btn-delete-estado').attr('id-delete', idConfiguracion);
    $("#delete-estado-modal").modal({dismissible: false});
    $('#delete-estado-modal').modal("open");
});
$('#btn-delete-estado').on('click', function () {
    var idConfiguracion = $(this).attr('id-delete');
    $('#delete-estado-modal').modal("close");
    DeleteSistema(idConfiguracion);
});
$("#idNodo").change(function () {
    var val = $("#idNodo").val();
    if (val == 1000) {
        $("#rowNombreNodo").show();
    } else {
        $("#rowNombreNodo").hide();
    }
});

function fillForm(idConfiguracion) {
    $("#Estado").val(configuraciones[idConfiguracion]["idCaptura"]).next().addClass("active");
    switch (parseInt(configuraciones[idConfiguracion]["idCaptura"])) {
        case 1:
            $("#EstadoVerde").prop('checked', true);
            break;
        case 2:
            $("#EstadoAzul").prop('checked', true);
            break;
        case 3:
            $("#EstadoRojo").prop('checked', true);
            break;
    }
    var clubElite = "", tienda = "", AutoRegistro = "", Preregistro = "", GafetesMultiple = "";
    if (configuraciones[idConfiguracion]["ClubElite"]) {
        clubElite = "True";
    } else {
        clubElite = "False";
    }

    if (configuraciones[idConfiguracion]["Tienda"]) {
        tienda = "True";
    } else {
        tienda = "False";
    }

    if (configuraciones[idConfiguracion]["AutoRegistro"]) {
        AutoRegistro = "True";
    } else {
        AutoRegistro = "False";
    }

    if (configuraciones[idConfiguracion]["GafeteMultiple"]) {
        GafetesMultiple = "True";
    } else {
        GafetesMultiple = "False";
    }

    if (configuraciones[idConfiguracion]["Preregistro"]) {
        Preregistro = "True";
    } else {
        Preregistro = "False";
    }

    $("#ip").val(configuraciones[idConfiguracion]["ip"]);
    $("#ClubElite").val(clubElite);
    $("#Tienda").val(tienda);
    $("#AutoRegistro").val(AutoRegistro);
    $("#GafeteMultiple").val(GafetesMultiple);
    $("#Preregistro").val(Preregistro);
    $("#idNodo").val(configuraciones[idConfiguracion]["idNodo"]);
    $("#idEvento").val(configuraciones[idConfiguracion]["idEvento"]);
    $("#idEdicion").val(configuraciones[idConfiguracion]["idEdicion"]);
    $('select').material_select();
}

$("#confirm-cancel").on('click', function () {
    reset_form();
});

function reset_form() {
    /* Resetea el formulario sin validaciones */
    var validator = $("#EstadoSistema").validate();
    validator.resetForm();
    /*----------------------------------------*/
    $("#EstadoSistema")[0].reset();
    $("#EstadoVerde").prop('checked', false);
    $("#EstadoAzul").prop('checked', false);
    $("#EstadoRojo").prop('checked', false);
    //$('#' + idForm).find('input[type="checkbox"] input[type="radio"]').not('input[type="checkbox"]:disabled input[type="radio"]:disabled').prop('checked', false);
    $('select').material_select();
}

function validateAddEstado() {
    $.validator.addMethod('IP4Checker', function (value) {
        var ip = "^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\." +
                "([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\." +
                "([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\." +
                "([01]?\\d\\d?|2[0-4]\\d|25[0-5])$";
        return value.match(ip);
    }, general_text.sas_ipInvalida);
    $("#EstadoSistema").validate({
        rules: {
            'idCaptura': {
                required: true
            },
            'EstadoVerde': {
                required: {
                    depends: function () {
                        if ($("#EstadoAzul").is(":checked")) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                }
            },
            'EstadoAzul': {
                required: {
                    depends: function () {
                        if ($("#EstadoVerde").is(":checked")) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                }
            },
            'EstadoRojo': {
                required: true
            },
            'TipoNodo': {
                required: {
                    depends: function () {
                        if ($("#TipoNodo").val() == "100") {
                            return false;
                        } else {
                            return true;
                        }
                    }
                }/*true*/
            },
            'NombreNodo': {
                required: {
                    depends: function () {
                        if ($("#TipoNodo").val() != "100") {
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                lettersonly: true
            },
            'idEvento': {
                required: true
            },
            'idEdicion': {
                required: true
            },
            'ClubElite': {
                required: true
            },
            'Tienda': {
                required: true
            },
            'AutoRegistro': {
                required: true
            },
            'GafeteMultiple': {
                required: true
            },
            'Preregistro': {
                required: true
            },
            'idNodo': {
                required: true
            },
            'ip': {
                required: true,
                IP4Checker: true
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
                if ($(element).attr('type') === "radio") {
                    element = $(element).parents('p');
                }
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            var post = $("#" + form.id).serialize();
            if (action === 'insert') {
                EstadoSistema(action);
            }
            if (action === 'update') {
                post = post + "&idConfiguracion=" + $("#btn-add-estado").attr('idConfiguracion');
                UpdateSistema(post, action);
            }
            return;
        }
    });
}

function DeleteSistema(idConfiguracion) {
    show_loader_wrapper();
    let data = new URLSearchParams({idConfiguracion});
    fetch(url_delete_estado, {
        method: 'POST',
        body: data
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    setRow(json.data, "delete");
                    $('.tooltipped').tooltip({delay: 50});
                    show_alert("success", 'Datos eliminados correctamente.');
                    hide_loader_wrapper();
                } else {
                    show_notification("error", json.message);
                    hide_loader_processing();
                }
            })
}

function UpdateSistema(post, action) {
    show_loader_wrapper();
    let data = new URLSearchParams(post, action);
    fetch(url_update_estado, {
        method: 'POST',
        body: data
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    $('#add-estado-modal').modal("close");
                    if ($("#idNodo").val() == "1000") {
                        Nodo[json.data.nodo.idNodo] = json.data.nodo;
                        json.data.idNodo = json.data.nodo.idNodo
                    }
                    reset_form();
                    setRow(json.data, action);
                    show_alert("success", general_text.sas_guardoExito);
                    hide_loader_wrapper();
                } else {
                    show_notification("error", json.message);
                    hide_loader_processing();
                }
            })
}

function EstadoSistema(action) {
    show_loader_wrapper();
    let data = new URLSearchParams($("#EstadoSistema").serialize(), );
    fetch(url_estado_sistema, {
        method: 'POST',
        body: data
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    if ($("#idNodo").val() == "1000") {
                        Nodo[json.data.nodo.idNodo] = json.data.nodo;
                    }
                    $('#add-estado-modal').modal('close');
                    setRow(json.data, action);
                    hide_loader_wrapper();
                    show_toast('success', general_text.sas_guardoExito);
                } else {
                    show_notification("error", json.message);
                    hide_loader_processing();
                }
            })
}

/* ajax para la actualizacion del datatable*/
function UpdateGeneral(val) {
    show_loader_wrapper();
    var post = "";
    $.each(nodo, function (key, value) {
        post = {"Campo": campo, "value": val, "where": value};
        $.ajax({

            type: "post",
            url: url_update_general,
            dataType: 'json',
            data: post,
            success: function (response) {
                if (!response['status']) {
                    show_alert('error', response.data);
                }
                /* CONSULTA DE UNA FUNCION ASINCRONA, DUVUELVE LOS DATOS O CAMPOS ACTUALIZADOS*/
                reset_form();
                form = $.ajax({
                    url: url_get_configuracion,
                    dataType: 'json',
                    async: false
                }).responseJSON;
                $('input[type="checkbox"]').prop('checked', false);
                setRowsCon(form.data[JSON.parse(response.data.where)]);
                delete nodo[JSON.parse(JSON.parse(response.data.where))];
                show_alert("success", general_text.sas_guardoExito);
                hide_loader_wrapper();
            },
            error: function (request, status, error) {
            }
        });
    });
}

function setRow(data, action) {
    configuraciones[data.idConfiguracion] = data;
    $("#idEvento").val(data.idEvento);
    $("#idEdicion").val(data.idEdicion);
    $("#Tienda").val(data.Tienda);
    var estado = "", tienda = "", clubElite = "", autoregistro = "", preregistro = "", gafetesMultiples = "";
    switch (data.idCaptura) {
        case "1":
            estado = "Verde"
            break;
        case "2":
            estado = "Azul"
            break;
        case "3":
            estado = "Rojo"
            break;
    }

    if (data.ClubElite) {
        clubElite = "Si";
    } else {
        clubElite = "No";
    }

    if (data.Tienda) {
        tienda = "Si";
    } else {
        tienda = "No";
    }

    if (data.AutoRegistro) {
        autoregistro = "Si";
    } else {
        autoregistro = "No";
    }

    if (data.GafeteMultiple) {
        gafetesMultiples = "Si";
    } else {
        gafetesMultiples = "No";
    }

    if (data.Preregistro) {
        preregistro = "Si";
    } else {
        preregistro = "No";
    }

    if (typeof data.nodo != "undefined") {
        Nodo[data.nodo.idNodo] = {
            "idNodo": data.nodo.idNodo,
            "NombreNodo": data.nodo.NombreNodo
        }
    }

    if (action === 'insert') {
        var row = table.row.add([
            '<input type="checkbox" class="select-record che_ck" id="cb_' + data.idConfiguracion + '" /><label for="cb_' + data.idConfiguracion + '"></label>',
            $("#idEdicion option:selected").text(),
            $("#idEvento option:selected").text(),
            estado,
            clubElite,
            tienda,
            autoregistro,
            gafetesMultiples,
            preregistro,
            Nodo[data.idNodo]["NombreNodo"],
            data.ip,
            '<i class="material-icons edit-record tooltipped" id-edit="' + data.idConfiguracion + '" data-position="left" data-tooltip="Editar">mode_edit</i>' +
                    '<i class="material-icons delete-record tooltipped" id-delete="' + data.idConfiguracion + '" data-position="left" data-tooltip="Eliminar">delete_forever</i>'
        ]).draw().node();
        $(row).attr('id', data.idConfiguracion);
    }
    if (action === 'update') {
        var row = table.row('#' + data.idConfiguracion).node();
        '<input type="checkbox" class="select-record che_ck" id="cb_' + data.idConfiguracion + '" /><label for="cb_' + data.idConfiguracion + '"></label>';
        $(row).find('td:nth-child(2)').text($("#idEdicion option:selected").text());
        $(row).find('td:nth-child(3)').text($("#idEvento option:selected").text());
        $(row).find('td:nth-child(4)').text(estado);
        $(row).find('td:nth-child(5)').text(clubElite);
        $(row).find('td:nth-child(6)').text(tienda);
        $(row).find('td:nth-child(7)').text(autoregistro);
        $(row).find('td:nth-child(8)').text(gafetesMultiples);
        $(row).find('td:nth-child(9)').text(preregistro);
        $(row).find('td:nth-child(10)').text(Nodo[data.idNodo]["NombreNodo"]);
        $(row).find('td:nth-child(11)').text(data.ip);
    }
    if (action === 'delete') {
        table.row('#' + data.idConfiguracion).remove().draw();
    }
}

function setRowsCon(data) {
    form[data.idConfiguracion] = data;
    $("#idEvento").val(data.idEvento);
    $("#idEdicion").val(data.idEdicion);
    $("#Tienda").val(data.Tienda);
    var estado = "", tienda = "", clubElite = "", autoregistro = "", preregistro = "", gafetesMultiples = "";
    switch (data.idCaptura) {
        case 1:
            estado = "Verde"
            break;
        case 2:
            estado = "Azul"
            break;
        case 3:
            estado = "Rojo"
            break;
    }

    if (data.ClubElite) {
        clubElite = "Si";
    } else {
        clubElite = "No";
    }

    if (data.Tienda) {
        tienda = "Si";
    } else {
        tienda = "No";
    }

    if (data.AutoRegistro) {
        autoregistro = "Si";
    } else {
        autoregistro = "No";
    }

    if (data.GafeteMultiple) {
        gafetesMultiples = "Si";
    } else {
        gafetesMultiples = "No";
    }

    if (data.Preregistro) {
        preregistro = "Si";
    } else {
        preregistro = "No";
    }

    if (typeof data.nodo != "undefined") {
        Nodo[data.nodo.idNodo] = {
            "idNodo": data.nodo.idNodo,
            "NombreNodo": data.nodo.NombreNodo
        }
    }
    var row = table.row('#' + data.idConfiguracion).node();
    '<input type="checkbox" class="select-record che_ck" id="cb_' + data.idConfiguracion + '" /><label for="cb_' + data.idConfiguracion + '"></label>';
    $(row).find('td:nth-child(2)').text($("#idEdicion option:selected").text());
    $(row).find('td:nth-child(3)').text($("#idEvento option:selected").text());
    $(row).find('td:nth-child(4)').text(estado);
    $(row).find('td:nth-child(5)').text(clubElite);
    $(row).find('td:nth-child(6)').text(tienda);
    $(row).find('td:nth-child(7)').text(autoregistro);
    $(row).find('td:nth-child(8)').text(gafetesMultiples);
    $(row).find('td:nth-child(9)').text(preregistro);
    $(row).find('td:nth-child(10)').text(Nodo[data.idNodo]["NombreNodo"]);
    $(row).find('td:nth-child(11)').text(data.ip);
}
