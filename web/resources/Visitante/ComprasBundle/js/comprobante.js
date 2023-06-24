var compraFacturada, FolioFactura;
$('select').material_select();

$("#modal-status-compra .procesar-status-compra").on("click", function () {
    show_loader_wrapper();
});

$("#modal-forma-pago .procesar-forma-pago").on("click", function () {
    show_loader_wrapper();
});

$("#modal-compra-facturada .procesar-compra-facturada").on("click", function () {
    show_loader_wrapper();
});

$(".modal-close").on("click", function () {
    $('#FolioFactura').val('');
    if (compras[0]['CompraFacturada'] == 0 || compras[0]['CompraFacturada'] == null) {
        $('#input-CompraFacturada').prop('checked', false);        
    } else {
        $('#input-CompraFacturada').prop('checked', true);
    }
});

$("#FolioFactura").on('keyup', function () {
    var stringFolioFactura = $("#FolioFactura").val();
    //Eliminamos espacios en blanco que se puedan haber escrito
    stringFolioFactura = stringFolioFactura.trim();
    stringFolioFactura = stringFolioFactura.replace(/\s/g, "");
    //Asignamos el valor limpio y evaluamos, para habilitar o deshabilitar el select
    $("#FolioFactura").val(stringFolioFactura);
    if (stringFolioFactura == "") {
        $(".procesar-compra-facturada").addClass('disabled');
    } else {
        $(".procesar-compra-facturada").removeClass('disabled');
    }
});

$(".procesar-compra-facturada").on("click", function () {

    //Si la compra se marca como Facturada se toma el valor del input para Folio de Factura
    if (compraFacturada == 1) {
        FolioFactura = $("#FolioFactura").val();
        $("#modal-compra-facturada .procesar-compra-facturada").attr("href", sas_status_compra + "?CompraFacturada=" + compraFacturada + "&FolioFactura=" + FolioFactura);
    } else {
        //Si la compra se marca como NO facturada, se limpia el dato para Folio de Factura
        FolioFactura = "";
        $("#modal-compra-facturada .procesar-compra-facturada").attr("href", sas_status_compra + "?CompraFacturada=" + compraFacturada + "&FolioFactura=" + FolioFactura);
    }
});

//Actualiza el Status de la Compra
$('#idCompraStatus').on('change', function () {
    if (this.value != compras[0]['idCompraStatus']) {
        $("#mensaje-statusCompra").html('¿Estás seguro que deseas cambiar el estatus de la compra <b>' + compras[0]['idCompra'] + '</b> de <b>' + compras[0]['StatusES']
                + '</b> a <b>' + $("#idCompraStatus option:selected").text() + '</b>?');
        $("#modal-status-compra .procesar-status-compra").attr("href", sas_status_compra + "?idStatus=" + this.value);
        $("#modal-status-compra").modal("open");
    }
});

//Actualiza la Froma de pago de la Compra
$('#idFormaPago').on('change', function () {
    if (this.value != compras[0]['idFormaPago']) {
        $("#mensaje-formaPago").html('¿Estás seguro que deseas cambiar la forma de pago de la compra <b>' + compras[0]['idCompra'] + '</b> de <b>' + compras[0]['FormaPagoES']
                + '</b> a <b>' + $("#idFormaPago option:selected").text() + '</b>?');
        $("#modal-forma-pago .procesar-forma-pago").attr("href", sas_status_compra + "?idFormaPago=" + this.value);
        $("#modal-forma-pago").modal("open");
    }
});
//Actualiza la Compra Facturada
$('#input-CompraFacturada').on('change', function () {
    $('#modal-compra-facturada').modal({dismissible: false});
    if (compras[0]['CompraFacturada'] == 0 || compras[0]['CompraFacturada'] == null) {
        compraFacturada = 1;
        $("#mensaje-compraFacturada").html('¿Desea marcar la compra <b>' + compras[0]['idCompra'] + '</b> como "Facturada" ?');
        $('#FolioFactura').parent().show();
        $("#modal-compra-facturada").modal("open");
        if ($("#FolioFactura").val() == 0) {
            $(".procesar-compra-facturada").addClass('disabled');
        }
    }
    if (compras[0]['CompraFacturada'] == 1) {
        compraFacturada = 0;
        $("#mensaje-compraFacturada").html('¿Desea marcar la compra <b>' + compras[0]['idCompra'] + '</b> como "No Facturada" ?');
        $('#FolioFactura').parent().hide();
        $("#modal-compra-facturada").modal("open");
    }
});

$(".reenviar").on("click", function () {
    show_loader_top();
    $.ajax({
        type: "post",
        url: url_sas_comprobante_reenviar,
        data: {
            idVisitante: visitante.idVisitante
        },
        dataType: 'json',
        success: function (result) {
            hide_loader_top();
            if (result['status']) {
                show_toast("success", template_text['ae_comprobanteEnviado'].replace("%email%", visitante['Email']));
                return;
            }
            show_toast("danger", 'Error');
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
});

$("#mostrar").on("click", function () {
    var idCompra = $(this).attr("id-compra");
    viewFile(idCompra);
});

function viewFile(idCompra) {
    show_loader_wrapper();
    $.ajax({
        type: "post",
        url: url_sas_view_file + "/" + idCompra,
        dataType: 'json',
        success: function (result) {
            hide_loader_top();
            if (!result['status']) {
                alert('Error al ver imagen');
            }
            var imagen = document.createElement("img");
            imagen.style.maxWidth = "100%";
            imagen.style.maxHeigth = "100%";
            imagen.src = 'https://congresounam.infoexpo.com.mx/2019/ae/web/Administrator/Comprobantes/' + result.data.NombreTicket;
            if (result.data.NombreTicket == null) {
                hide_loader_wrapper();
                show_toast("warning", "Lo sentimos, no tenemos una imagen almacenada para mostrar");

            } else {
                imagen.id = "ticket";
//            imagen.className = "logo";
                imagen.title = result.data.NombreTicket;
                //imagen.alt = "texto alternativo";
                //
                // definimos el elemento donde insertamos la imagen
                var div = document.getElementById("mostrarTicket");
                // agregamos la imagen
                div.appendChild(imagen);
                $('#modalView').modal("open");
                hide_loader_wrapper();
            }


        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
}
