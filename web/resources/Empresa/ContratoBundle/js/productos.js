var ca = {}, premios = {};
$(init);

function init() {
    $(".uncompleted").on("click", function (e) {
        hide_loader_top();
    });

    $(".ca").on("change", function () {
        if ($(this).is(":checked")) {
            $(this).parents(".ca-row").siblings(".ca-description").find("input").removeAttr('disabled');
        } else {
            $(this).parents(".ca-row").siblings(".ca-description").find("input").attr('disabled', 'disabled');
        }
        calculaTotal();
    });
    $("#btnSaveContract").on("click", saveContract);

    $(".ca-amount").focusout(calculaTotal);
}

function calculaTotal() {
    var subtotal = 0;
    $.each($(".ca:checked"), function (i, e) {
        var price = parseFloat($(e).attr('price'));
        var am = haveAmount(e);
        if (am != 0) {
            subtotal += price * $("#" + am).val();
        } else {
            subtotal += price;
        }
    });
    var iva = parseFloat(calculaIVA(subtotal));
    var total = parseFloat(subtotal) + parseFloat(iva);
    $("#SubtotalCostoAdicional").val(number_format(subtotal, 2));
    $("#IvaCostoAdicional").val(number_format(iva, 2));
    $("#TotalCostoAdicional").val(number_format(total, 2));

}

function haveAmount(e) {
    var result = 0;
    if ($(e).parents(".ca-row").siblings(".ca-description").find("input").length) {
        result = $(e).parents(".ca-row").siblings(".ca-description").find("input").attr("id");
    }
    return result;
}

function saveContract() {
    var ListaCostos = "";
    var flag = false;
    $.each($(".ca:checked"), function (i, e) {
        var id = $(e).val();
        var am = haveAmount(e);
        if (am != 0) {
            if ($("#" + am).val() == "") {
                show_alert("warning", section_text.sas_asegureseIngresarCantidad);
                $("#" + am).addClass("input-highlight");
                flag = true;
                setTimeout(function () {
                    $("#" + am).removeClass("input-highlight");
                }, 3000);
                return false;
            }
            ListaCostos += id + "-" + $("#" + am).val() + ",";
        } else {
            ListaCostos += id + ",";
        }
    });
    if (flag) {
        return;
    }
    premios = {};
    $.each($(".premio:checked"), function (i, e) {
        premios[$(e).val()] = {
            "idPremio": $(e).val(),
            "PremioES": $(e).attr('es'),
            "PremioEN": $(e).attr('es')
        }
    });
    $.each($(".premio-resp:checked"), function (i, e) {
        premios[$(e).attr('i')] = {
            "idPremio": $(e).attr('i'),
            "ResponsableSocial": $(e).val()
        }
    });

    var post = {
        "idContrato": $("#idContrato").val(),
        "ListaCostos": ListaCostos.substr(0, (ListaCostos.length - 1)),
        "SubTotal": $("#SubtotalCostoAdicional").val().replace(",", ""),
        "IVA": $("#IvaCostoAdicional").val().replace(",", ""),
        "Total": $("#TotalCostoAdicional").val().replace(",", ""),
        "Premios": JSON.stringify(premios)
    };
    show_loader_wrapper();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_save_productos, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: post, // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            show_alert("success", general_text.sas_guardoExito);
            $("#mdl-next-atep p").text(section_text.guardoExitoContinuar);
            if ($(".ca:checked").length == 0) {
                $("#mdl-next-step p").text(section_text.sas_productoAdicionalNoSeleccionContinuar);
            }
            $("#mdl-next-step").modal({dismissible: false}).modal("open");
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}
