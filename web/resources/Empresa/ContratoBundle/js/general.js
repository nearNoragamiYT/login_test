$(init);

function init() {
    $("#contract-status").on("change", function () {
        if ($(this).val() != "") {
            $("#mdl-confirm-status .modal-content").find('p').find('span').text($("#contract-status option:selected").text());
            $("#mdl-confirm-status").modal({dismissible: false}).modal("open");
        }
    });

    $("#change-status").on("click", changeStatus);
    $("#btn-next-step").on("click", function () {
        $("#mdl-next-step").modal("close");
        show_loader_wrapper();
        $(location).attr('href', url_next_step);
    });
    $("#mdl-next-step .modal-close").remove();
}

function changeStatus() {
    show_loader_wrapper();
    $("#mdl-confirm-status").modal("close");
    var post = {
        "status": $("#contract-status").val(),
        "idEmpresa": $("#idEmpresa").val(),
        "idContrato": $("#idContrato").val()
    };
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_save_status, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: post, // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            if (post.status == 4) {
                show_alert("success", section_text.sas_exitoContratoAutorizado);
                location.reload()
                show_loader_wrapper();
            } else {
                show_alert("success", section_text.sas_exitoContratoCancelado);
                $(location).attr('href', url_contract_list);
                show_loader_wrapper();
            }
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function calculaIVA(subtotal) {
    return (parseFloat(subtotal) * 0.16).toFixed(2);
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