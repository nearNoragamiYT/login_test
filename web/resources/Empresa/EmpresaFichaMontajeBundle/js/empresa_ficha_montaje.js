var idVendedor = "";
var idEmpresa = "";
$(document).ready(function () {
    initFichaMontaje();
});

function initFichaMontaje() {
    //console.log(sellers[2]["Email"]);
    //$("#ficha-montaje").find("#btnGenerarFicha").hide();
    $('#tabla-fichas').DataTable({
        "order": [[($('#tabla-fichas th').length - 1), "desc"]],
        "language": {
            "url": url_lang
        }
    });
    setFichaMontajeData();

    //Envio Ficha Vendedor Montaje
    $(document).on('click', '#btnSendFichaVendedorMontaje', function () {
        idEmpresa = $(this).attr("data-id");
        idUsuario = $(this).attr("data-user");
        envioFichaVendedorMontaje(idEmpresa, idUsuario);

    });

//Envio Ficha Vendedor Desmontaje
    $(document).on('click', '#btnSendFichaVendedorDesmontaje', function () {
        //$("#btnSendFichaVendedorDesmontaje").on("click", function () {
        idEmpresa = $(this).attr("data-id");
        idUsuario = $(this).attr("data-user");
        envioFichaVendedorDesmontaje(idEmpresa, idUsuario);

    });
}

function setFichaMontajeData() {
    $("#SellersInput").on("change", function () {
        $("#btnGenerarFicha").removeClass("disabled");
        $("#btnGenerarFichaDesmontaje").removeClass("disabled");
        $("#btnEnviarFichaMontaje").removeClass("disabled");
        $("#btnEnviarFichaDesmontaje").removeClass("disabled");
        idVendedor = $(this).val();
        //console.log(idVendedor);
        $("#save-seller-form").find("label").addClass('active');

        if (idVendedor > 0) {
            $("#idVendedor").val(sellers[idVendedor]["idUsuario"]);
            $("#NombreCompletoES").val(sellers[idVendedor]["Nombre"]);
            $("#Email").val(sellers[idVendedor]["Email"]);
        } else {
            $("#idVendedor").val('');
            $("#NombreCompletoES").val('');
            $("#Email").val('');
        }

    });

    $("#btnGenerarFicha").on("click", function () {
        if (idVendedor != "") {
            $("#ficha-montaje").find("#btnGenerarFicha").attr("href", url_pdf_ficha + "/" + idVendedor);
            //window.print();
        }
    });

    $("#btnGenerarFichaDesmontaje").on("click", function () {
        if (idVendedor != "") {
            $("#ficha-montaje").find("#btnGenerarFichaDesmontaje").attr("href", url_pdf_ficha_desmontaje + "/" + idVendedor);
            //window.print();
        }
    });
    $("#btnEnviarFichaDesmontaje").on("click", function () {
        if (idVendedor != "") {
            envioFichaDesmontaje();
        }
    });

    $("#btnEnviarFichaMontaje").on("click", function () {
        if (idVendedor != "") {
            envioFichaMontaje();
        }

    });

}

function envioFichaMontaje() {
    show_loader_wrapper();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_send_ficha, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: {idVendedor: idVendedor}, // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_wrapper();
            if (!response['status']) {
                show_alert('error', response.data);
            }
            show_alert('success', "Ficha enviada con exito");
        },
        error: function (request, status, error) {
            show_alert('error', error);
            hide_loader_wrapper();
        }
    });
}

function envioFichaDesmontaje() {
    show_loader_wrapper();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_send_ficha_desmontaje, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: {idVendedor: idVendedor}, // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_wrapper();
            if (!response['status']) {
                show_alert('error', response.data);
            }
            show_alert('success', "Ficha enviada con exito");

        },
        error: function (request, status, error) {
            show_alert('error', error);
            hide_loader_wrapper();
        }
    });
}

function envioFichaVendedorDesmontaje(idEmpresa, idUsuario) {
    show_loader_wrapper();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_send_ficha_vendedor_desmontaje, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: {idEmpresa: idEmpresa, idUsuario: idUsuario}, // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_wrapper();
            if (!response['status']) {
                show_alert('error', response.data);
            }
            show_alert('success', "Ficha enviada con exito");
        },
        error: function (request, status, error) {
            show_alert('error', error);
            hide_loader_wrapper();
        }
    });

}
function envioFichaVendedorMontaje(idEmpresa, idUsuario) {
    show_loader_wrapper();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_send_ficha_vendedor_montaje, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: {idEmpresa: idEmpresa, idUsuario: idUsuario}, // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_wrapper();
            if (!response['status']) {
                show_alert('error', response.data);
            }
            show_alert('success', "Ficha enviada con exito");

        },
        error: function (request, status, error) {
            show_alert('error', error);
            hide_loader_wrapper();
        }

    });

}
