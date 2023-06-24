$(document).ready(function () {
    validateProducto();
    $('select').material_select();
    $('#add-producto-modal').modal();
    $('#DescripcionES').val();
    $('#DescripcionES').trigger('autoresize');
    $("#btn-add-producto").on('click', function () {
        $("#datosProducto").submit();
    });



    table = $('#table').removeAttr('width').DataTable({
        "Descripcion": [
            {"width": "10%", "targets": 0}
        ],
        "Costo": [
            {"width": "10%", "targets": 0}
        ],
        "Evento": [
            {"width": "20%", "targets": 0}
        ],
        fixedColumns: true
    });

    jQuery.extend(jQuery.validator.messages, {
        required: general_text.sas_campoRequerido,
        number: general_text.sas_soloNumeros,
        maxlength: general_text.sas_caracteresMaximos,
        minlength: general_text.sas_caracterosMinimos,
        lettersonly: general_text.sas_soloTexto
    });

});

$("#btnAgregarProducto").on('click', function () {
    resetForm();
    $('#add-producto-modal').modal({
        dismissible: false
    }).modal('open');
    action = "insert";
});

$(document).on('click', '.edit-record', function () {
    $('.tooltipped').tooltip('remove');
    var idProducto = $(this).attr('id-edit');
    fillForm(idProducto);
    $('#add-producto-modal').modal({dismissible: false}).modal('open');
    $('#btn-add-producto').attr('idProducto', idProducto);
    action = "update";
});

$(document).on('click', '.block-record', function () {
    $('.tooltipped').tooltip('remove');
    var idProducto = $(this).attr('id-block');
    $('[id-prod=' + idProducto + ']').children().css({"opacity": ".4"});
    $('[id-prod=' + idProducto + ']').children().last().css({"opacity": "1"});
    $('[id-prod=' + idProducto + ']').children().last().html('<i class="material-icons unlock-record tooltipped" id-unlock ="'
            + idProducto + '" data-position="left" data-tooltip="Desbloquear Producto">lock_open</i>');
    updateProductoStatus(idProducto);
});

$(document).on('click', '.unlock-record', function () {
    var idProducto = $(this).attr('id-unlock');
    $('[id-prod=' + idProducto + ']').children().css({"opacity": "1"});
    $('[id-prod=' + idProducto + ']').children().last().html('<i class="material-icons edit-record tooltipped " id-edit="' + idProducto
            + '" data-position="left" data-tooltip="Editar">mode_edit</i><i class="material-icons delete-record tooltipped" id-delete="' + idProducto
            + '" data-position="left" data-tooltip="Eliminar" >delete_forever</i><i class="material-icons block-record tooltipped" id-block="' + idProducto
            + '" data-position="left" data-tooltip="Bloquear Producto" >lock</i>');
    updateProductoStatus(idProducto);
});

$(document).on('click', '.delete-record', function () {
    $("#deleteText").html('¿Esta seguro de borrar el registro?');
    var idProducto = parseInt($(this).attr('id-delete'));
    $('#btn-delete-producto').attr('id-delete', idProducto);
    $("#delete-producto-modal").modal({dismissible: false});
    $('#delete-producto-modal').modal("open");

});

$('#btn-delete-producto').on('click', function () {
    var idProducto = parseInt($(this).attr('id-delete'));
    $('#delete-producto-modal').modal("close");
    deleteProducto(idProducto);
});

function fillForm(idProducto) {
    $("#ProductoES").val(productos[idProducto]["ProductoES"]);
    $("#DescripcionES").val(productos[idProducto]["DescripcionES"]);
    $("#Precio").val(productos[idProducto]["Precio"]);
    $("#PrecioSitio").val(productos[idProducto]["PrecioSitio"]);

}

function resetForm() {
    $("#ProductoES").val("");
    $("#DescripcionES").val("");
    $("#Precio").val("");
    $("#PrecioSitio").val("");
}

function validateProducto() {
    $("#datosProducto").validate({
        rules: {
            'ProductoES': {
                required: true,
                lettersonly: true,
            },
            'DescripcionES': {
                required: true,

            },
            'Precio': {
                required: true,
                minlength: 3,
                maxlength: 5,
                number: true,
            },
            'PrecioSitio': {
                minlength: 3,
                maxlength: 5,
                number: true,
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
            var post = $("#" + form.id).serialize();
            if (action === 'insert') {
                insertProducto(action);
            }
            if (action === 'update') {
                post = post + "&idProducto=" + $("#btn-add-producto").attr('idProducto');
                updateProducto(post, action);
            }

            return;
        }
    });
}
function updateProducto(post, action) {
    show_loader_wrapper();
    let data = new URLSearchParams(post, action);
    fetch(update_producto, {
        method: 'POST',
        body: data
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    $('#add-producto-modal').modal('close');
                    setRow(json.data, action);
                    resetForm();
                    hide_loader_wrapper();
                    show_toast('success', general_text.sas_modificoExito);
                } else {
                    show_notification("error", json.message);
                    hide_loader_processing();
                }
            })
}

function deleteProducto(idProducto) {
    show_loader_wrapper();
    let data = new URLSearchParams({idProducto});
    fetch(delete_producto, {
        method: 'POST',
        body: data
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    setRow(json.data, "delete");
                    hide_loader_wrapper();
                    show_toast('success', general_text.sas_eliminarExito);
                } else {
                    show_notification("error", json.message);
                    hide_loader_processing();
                }
            })
}

//function updateProductoStatus(idProducto) {
//    show_loader_wrapper();
//    let data = new URLSearchParams(idProducto);
//    fetch(update_status_producto, {
//        method: 'POST',
//        body: data
//    })
//            .then(response => response.json())
//            .then(json => {
//
//                if (json.status) {
//                    setRowStatus(json.data, "update");
//                    hide_loader_wrapper();
//                    show_toast('success', general_text.sas_modificoExito);
//                } else {
//                    show_notification("error", json.message);
//                    hide_loader_processing();
//                }
//            })
//}

function updateProductoStatus(idProducto) {
    show_loader_wrapper();
    $.ajax({
        type: "post",
        url: update_status_producto,
        dataType: 'json',
        data: {idProducto},

        success: function (response) {
            if (!response['status']) {
                show_alert('error', response.data);
            }
            setRowStatus(response.data, "update");
            hide_loader_wrapper();
            show_toast('success', general_text.sas_modificoExito);

        },
        error: function (request, status, error) {
        }
    });
}

function insertProducto(action) {
    show_loader_wrapper();
    let data = new URLSearchParams($("#datosProducto").serialize());
    fetch(insert_producto, {
        method: 'POST',
        body: data
    })
            .then(response => response.json())
            .then(json => {

                if (json.status) {
                    $('#add-producto-modal').modal('close');
                    setRow(json.data, action);
                    productos[json.data.idProducto] = json.data
                    hide_loader_wrapper();
                    show_toast('success', general_text.sas_guardoExito);
                } else {
                    show_notification("error", json.message);
                    hide_loader_processing();
                }
            })
}

function setRowStatus(data, action) {
    productos[data.idProducto] = data;
    if (action === 'update') {
        var row = table.row('#' + data.idProducto).node();
        $(row).find('td:nth-child(1)').text(data.ProductoES);
        $(row).find('td:nth-child(2)').text(data.DescripcionES);
        $(row).find('td:nth-child(3)').text(data.Precio);
        $(row).find('td:nth-child(4)').text(data.PrecioSitio);
    }
}
function setRow(data, action) {
    productos[data.idProducto] = data;
    if (action === 'insert') {
        var row = table.row.add([
            data.ProductoES,
            data.DescripcionES,
            data.Precio,
            data.PrecioSitio,
            '<i class="material-icons edit-record tooltipped" id-edit="' + data.idProducto + '" data-position="left" data-tooltip="Editar">mode_edit</i>' +
                    '<i class="material-icons delete-record tooltipped" id-delete="' + data.idProducto + '" data-position="left" data-tooltip="Eliminar">delete_forever</i>' +
                    '<i class="material-icons block-record tooltipped" id-block="' + data.idProducto + '" data-position="left"  data-tooltip="Bloquear Producto">lock</i>'
        ]).draw().node();
        $(row).attr('id', data.idProducto);
    }
    if (action === 'update') {
        var row = table.row('#' + data.idProducto).node();
        $(row).find('td:nth-child(1)').text(data.ProductoES);
        $(row).find('td:nth-child(2)').text(data.DescripcionES);
        $(row).find('td:nth-child(3)').text(data.Precio);
        $(row).find('td:nth-child(4)').text(data.PrecioSitio);
    }
    if (action === 'delete') {
        table.row('#' + data.idProducto).remove().draw();
    }
}
