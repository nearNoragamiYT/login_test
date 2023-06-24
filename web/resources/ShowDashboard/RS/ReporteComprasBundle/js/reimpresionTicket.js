$(document).ready(function () {
    validateTicket();

    $(function () {
        var txt = $("#idCompra");
        var func = function () {
            txt.val(txt.val().replace(/\s/g, ''));
        }
        txt.keyup(func).blur(func);
    });
});

$("#cancelar").on('click', function (e) {
    e.preventDefault();
    $('#modal1').modal("close");
    resetForm();
    document.getElementById("idCompra").value = "";

});

$(".btn").on('click', function (e) {
    e.preventDefault();
    $("#generaTicket").submit();
});

function anular(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    return (tecla != 13);
}

function validateTicket() {
    $("#generaTicket").validate({
        rules: {
            'idCompra': {
                required: true,
                digits: true
            }
        },
        messages: {
            'idCompra': {
                required: general_text.sas_requeridoCampo,
                digits: general_text.rs_numeros
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
                $(placement).append(error)
            } else {
                if ($(element).attr('type') === "checkbox") {
                    element = $(element).parents('p');
                }
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            var data = $(form).serialize();
            consultaCompra(data);
        }
    });
}

function resetForm() {
    $("#idcompra-Comprobante-caja")[0].innerText = "";
    $("#Referencia-Comprobante-caja")[0].innerText = "";
    $("#fechaCompra-Comprobante-caja")[0].innerText = "";
    $("#cantidad-Comprobante-caja")[0].innerText = "";
    $("#producto-Comprobante-caja")[0].innerText = "";
    $("#precio-Comprobante-caja")[0].innerText = "";
    $("#subTotal-Comprobante-caja")[0].innerText = "";
    $("#iva-Comprobante-caja")[0].innerText = "";
    $("#monedaT-Comprobante-caja")[0].innerText = "";
    $("#formaPago-Comprobante-caja")[0].innerText = "";

    $("#idcompra-Comprobante-pre-registro")[0].innerText = "";
    $("#Referencia-Comprobante-pre-registro")[0].innerText = "";
    $("#fechaCompra-Comprobante-pre-registro")[0].innerText = "";
    $("#cantidad-Comprobante-pre-registro")[0].innerText = "";
    $("#producto-Comprobante-pre-registro")[0].innerText = "";
    $("#precio-Comprobante-pre-registro")[0].innerText = "";
    $("#subTotal-Comprobante-pre-registro")[0].innerText = "";
    $("#iva-Comprobante-pre-registro")[0].innerText = "";
    $("#monedaT-Comprobante-pre-registro")[0].innerText = "";
    $("#formaPago-Comprobante-pre-registro")[0].innerText = "";

    $("#idcompra-Comprobante-visitante")[0].innerText = "";
    $("#Referencia-Comprobante-visitante")[0].innerText = "";
    $("#fechaCompra-Comprobante-visitante")[0].innerText = "";
    $("#cantidad-Comprobante-visitante")[0].innerText = "";
    $("#producto-Comprobante-visitante")[0].innerText = "";
    $("#precio-Comprobante-visitante")[0].innerText = "";
    $("#subTotal-Comprobante-visitante")[0].innerText = "";
    $("#iva-Comprobante-visitante")[0].innerText = "";
    $("#monedaT-Comprobante-visitante")[0].innerText = "";
    $("#formaPago-Comprobante-visitante")[0].innerText = "";
}

function consultaCompra(val) {
    $.ajax({
        type: "post",
        dataType: 'json',
        data: val,
        url: url_datos,
        success: function (response) {

            var comp = val.split("=");
            var compra = comp[1];
            if (response.data == null || response.data == "") {
                show_toast("warning", 'No existe la compra con el numero: ' + compra);
                return;
            }

            if (response.status) {
                $("#modal1").modal({dismissible: false}).modal("open");

                let idcompra = response.data[0].idCompra;
                var formaPago = response.data[0].idFormaPago;

                var pago;
                if (formaPago == 3) {
                    pago = "Efectivo";
                } else if (formaPago == 1) {
                    pago = "Tarjeta de credito";
                }

                let numeroReferencia = response.data[0].TicketFacturacion;
                var fecha = response.data[0].FechaCreacion;
                var Fecha = fecha.split('.');
                var fechaHora = Fecha[0];
                let moneda = response.data[0].MonedaTipo;
                var subtotal = response.data[0].SubTotal;
                var sub = subtotal.split('.');
                var subt = sub[0];
                var iva = response.data[0].IVA;
                var iv = iva.split('.');
                var i = iv[0];
                let total = response.data[0].Total;

                /*-------CAJA-----*/
                var idComp = general_text.rs_compra + idcompra;
                var cajadiv = document.getElementById("idcompra-Comprobante-caja");
                var texto = document.createTextNode(idComp);
                cajadiv.appendChild(texto);

                var cajadiv = document.getElementById("Referencia-Comprobante-caja");
                var texto = document.createTextNode(numeroReferencia);
                cajadiv.appendChild(texto);

                var element = general_text.rs_fecha + fechaHora;
                var cajadiv = document.getElementById("fechaCompra-Comprobante-caja");
                var texto = document.createTextNode(element);
                cajadiv.appendChild(texto);

                var cajaS = document.getElementById("subTotal-Comprobante-caja");
                var texto = document.createTextNode("$ " + subt + " " + moneda);
                cajaS.appendChild(texto);

                var cajaI = document.getElementById("iva-Comprobante-caja");
                var texto = document.createTextNode("$ " + i + " " + moneda);
                cajaI.appendChild(texto);

                var cajaMon = document.getElementById("monedaT-Comprobante-caja");
                var texto = document.createTextNode("$ " + total + " " + moneda);
                cajaMon.appendChild(texto);

                var cajaPago = document.getElementById("formaPago-Comprobante-caja");
                var texto = document.createTextNode(pago);
                cajaPago.appendChild(texto);

                /*-------PRE-REGISTRO-----*/
                var idComp = general_text.rs_compra + idcompra;
                var cajadiv = document.getElementById("idcompra-Comprobante-pre-registro");
                var texto = document.createTextNode(idComp);
                cajadiv.appendChild(texto);

                var cajadiv = document.getElementById("Referencia-Comprobante-pre-registro");
                var texto = document.createTextNode(numeroReferencia);
                cajadiv.appendChild(texto);

                var element = general_text.rs_fecha + fechaHora;
                var cajadiv = document.getElementById("fechaCompra-Comprobante-pre-registro");
                var texto = document.createTextNode(element);
                cajadiv.appendChild(texto);

                var cajaS = document.getElementById("subTotal-Comprobante-pre-registro");
                var texto = document.createTextNode("$ " + subt + " " + moneda);
                cajaS.appendChild(texto);

                var cajaI = document.getElementById("iva-Comprobante-pre-registro");
                var texto = document.createTextNode("$ " + i + " " + moneda);
                cajaI.appendChild(texto);

                var cajaMon = document.getElementById("monedaT-Comprobante-pre-registro");
                var texto = document.createTextNode("$ " + total + " " + moneda);
                cajaMon.appendChild(texto);

                var cajaPago = document.getElementById("formaPago-Comprobante-pre-registro");
                var texto = document.createTextNode(pago);
                cajaPago.appendChild(texto);
                /*-------VISITANTE-----*/
                var idComp = general_text.rs_compra + idcompra;
                var cajadiv = document.getElementById("idcompra-Comprobante-visitante");
                var texto = document.createTextNode(idComp);
                cajadiv.appendChild(texto);

                var cajadiv = document.getElementById("Referencia-Comprobante-visitante");
                var texto = document.createTextNode(numeroReferencia);
                cajadiv.appendChild(texto);

                var element = general_text.rs_fecha + fechaHora;
                var cajadiv = document.getElementById("fechaCompra-Comprobante-visitante");
                var texto = document.createTextNode(element);
                cajadiv.appendChild(texto);

                var cajaS = document.getElementById("subTotal-Comprobante-visitante");
                var texto = document.createTextNode("$ " + subt + " " + moneda);
                cajaS.appendChild(texto);

                var cajaI = document.getElementById("iva-Comprobante-visitante");
                var texto = document.createTextNode("$ " + i + " " + moneda);
                cajaI.appendChild(texto);

                var cajaMon = document.getElementById("monedaT-Comprobante-visitante");
                var texto = document.createTextNode("$ " + total + " " + moneda);
                cajaMon.appendChild(texto);

                var cajaPago = document.getElementById("formaPago-Comprobante-visitante");
                var texto = document.createTextNode(pago);
                cajaPago.appendChild(texto);

                $.each(response.data, function (index, value) {
                    let cantidad = response.data[index].Cantidad;
                    let producto = response.data[index].ProductoES;
                    var precio = response.data[index].Precio;
                    var pu = precio.split('.');
                    var PU = pu[0];
                    /*----CAJA----*/
                    var cant = cantidad;
                    var cajaCant = document.getElementById("cantidad-Comprobante-caja");
                    var texto1 = document.createTextNode(cant);
                    cajaCant.appendChild(document.createElement("br"));
                    cajaCant.appendChild(texto1);

                    var prod = producto;
                    var cajaProd = document.getElementById("producto-Comprobante-caja");
                    var texto2 = document.createTextNode(prod);
                    cajaProd.appendChild(document.createElement("br"));
                    cajaProd.appendChild(texto2);

                    var pre = PU;
                    var cajaPre = document.getElementById("precio-Comprobante-caja");
                    var texto3 = document.createTextNode(pre);
                    cajaPre.appendChild(document.createElement("br"));
                    cajaPre.appendChild(texto3);
                    /*----PRE-REGISTRO----*/
                    var cant = cantidad;
                    var cajaCant = document.getElementById("cantidad-Comprobante-pre-registro");
                    var texto1 = document.createTextNode(cant);
                    cajaCant.appendChild(document.createElement("br"));
                    cajaCant.appendChild(texto1);

                    var prod = producto;
                    var cajaProd = document.getElementById("producto-Comprobante-pre-registro");
                    var texto2 = document.createTextNode(prod);
                    cajaProd.appendChild(document.createElement("br"));
                    cajaProd.appendChild(texto2);

                    var pre = PU;
                    var cajaPre = document.getElementById("precio-Comprobante-pre-registro");
                    var texto3 = document.createTextNode(pre);
                    cajaPre.appendChild(document.createElement("br"));
                    cajaPre.appendChild(texto3);
                    /*----VISITANTE----*/
                    var cant = cantidad;
                    var cajaCant = document.getElementById("cantidad-Comprobante-visitante");
                    var texto1 = document.createTextNode(cant);
                    cajaCant.appendChild(document.createElement("br"));
                    cajaCant.appendChild(texto1);

                    var prod = producto;
                    var cajaProd = document.getElementById("producto-Comprobante-visitante");
                    var texto2 = document.createTextNode(prod);
                    cajaProd.appendChild(document.createElement("br"));
                    cajaProd.appendChild(texto2);

                    var pre = PU;
                    var cajaPre = document.getElementById("precio-Comprobante-visitante");
                    var texto3 = document.createTextNode(pre);
                    cajaPre.appendChild(document.createElement("br"));
                    cajaPre.appendChild(texto3);
                });
            }
        },
    });
}

function imprSelec(ejemplo) {
    var ficha = document.getElementById(ejemplo);
    var ventimp = window.open(' ', 'popimpr');
    ventimp.document.write(ficha.innerHTML);
    ventimp.document.close();
    ventimp.print( );
    ventimp.close();
}