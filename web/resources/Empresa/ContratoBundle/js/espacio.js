var oTable = "", stands_to_add = [], listado_stand = "", itemToDelete = {};
$(init);

function init() {
    if (!$("input[name=OpcionPago]").is(":checked")) {
        $("input[name=OpcionPago]").first().prop("checked", true);
    }
    $(".uncompleted").on("click", function (e) {
        hide_loader_top();
    });
    oTable = $('#tbl-free-stand').DataTable({
        "language": {
            "url": url_lang
        }
    });

    $(".edit-vendedor").on("click", function () {
        var i = $(this).attr("id-record");
        $("#vendedor-nombre").text(list_vendedor[i]["Nombre"]);
        $("#vendedor-email").text(list_vendedor[i]["Email"]);
        $("#vendedor").val(i);
    });

    $("select").material_select();

    $("#add-stand").on("click", function () {
        stands_to_add = [];
        $(".free-stand").prop("checked", false);
        $("#mdl-free-stand").modal('open');
    });

    $(document).on("change", ".free-stand", function () {
        var value = $(this).val();
        var index = $.inArray(value, stands_to_add);
        if ($(this).is(":checked")) {
            if (index === -1) {
                stands_to_add.push(value);
            }
        } else {
            stands_to_add.splice(index, 1);
        }
    });

    $("#save-free-stand").on("click", function () {
        var tipo_precio = $("#idTipoPrecioStand").val();
        //var tipo_stand = list_free_stand[e]['idTipoStand'];
        var moneda = $("#Moneda").val();

        var totalPrecios = Object.keys(list_tipo_precio_stand).length;
        var keysTipos = Object.keys(list_tipo_precio);
        var totalTipoPrecios = Object.keys(list_tipo_precio).length;
        var val = "";

        if (stands_to_add.length) {
            $.each(stands_to_add, function (i, e) {
                var row = '<tr class="unsaved">\n\
                                <td>\n\
                                    <input id="booth-label-' + e + '" type="text" class="validate booth-label col s11" value="' + empresa["DC_NombreComercial"] + '"/>\n\
                                </td>\n\
                                <td>' +
                        list_free_stand[e]['NombrePabellon'] +
                        '</td>\n\
                                <td>' +
                        list_free_stand[e]['StandNumber'] +
                        '</td>\n\
                                <td>' +
                        list_tipo_stand[list_free_stand[e]['idTipoStand']]['TipoStand'] +
                        '</td>\n\
                                <td>\n\
                                    <select id="select-type-' + e + '" name="idTipoPrecio" class="idTipoPrecio browser-default validate" data-id="' + e + '" style="width: 85%;">\n\
                                    <option value="">' + general_text.sas_seleccionaOpcion + '</option>';
                $.each(list_tipo_precio_stand, function (index, el) {
                    if (parseInt(el['idTipoStand']) == parseInt(list_free_stand[e]['idTipoStand'])) {
                        //$.each(list_tipo_precio, function (jindex, jel) {
                        //if (parseInt(jel['idTipoPrecioStand']) == parseInt(el['idTipoPrecioStand'])) {
                        row += '<option value="' + el["idTipoPrecioStand"] + '" data-id="' + el["idTipoPrecioTipoStand"] + '" data-mxn="' + el["PrecioES"] + '" data-usd="' + el["PrecioEN"] + '">' + el["TipoPrecioStand"] + '</option>';
                        //}
                        //});
                    }
                });
                row += '</select></td>\n\
                                <td>\n\
                                    <input id="booth-price-' + e + '" type="text" class="validate booth-price col s8" modify="false" booth-type="' + list_free_stand[e]['idTipoStand'] + '" value="';
                row += '"/><span class="new badge blue price-mod" style="display:none;margin-top:10px;">MOD</span>\n\
                                </td>\n\
                                <td>' +
                        list_free_stand[e]['Stand_W'] + '  x  ' + list_free_stand[e]['Stand_H'] + '  =  <span class="area">' + (parseFloat(list_free_stand[e]['Stand_W']) * parseFloat(list_free_stand[e]['Stand_H'])) + '</span>\n\
                                </td>\n\
                                <td>\n\
                                    <i class="material-icons delete-record" id-record="' + list_free_stand[e]['idStand'] + '">delete_forever</i>\n\
                                </td>\n\
                            </tr>';
                $("#tbl-stand").append(row);
                if ($("#idTipoPrecioStand option:selected").val() != "") {
                    $("#select-type-" + e).val($("#idTipoPrecioStand option:selected").val()).trigger("change");
                } else {
                    $("#select-type-" + e).val($($("#select-type-" + e + " option")[1]).val()).trigger("change");
                }

                var tr = $("#free-stand-" + e).parents('tr');
                oTable.row(tr).remove().draw();
            });
        }
        $("#mdl-free-stand").modal("close");
        $("#unsaved-note").fadeIn();
        total();
        calculaTotal();
    });

    $(document).on("click", ".delete-record", function () {
        if ($(this).parents("tr").hasClass("unsaved")) {
            var i = $(this).attr("id-record");
            oTable.row.add([
                '<input type="checkbox" id="free-stand-' + i + '" value="' + i + '" class="free-stand" /><label for="free-stand-' + i + '"></label>',
                list_free_stand[i]['StandNumber'],
                list_free_stand[i]['NombrePabellon'],
                list_tipo_stand[list_free_stand[i]['idTipoStand']]['TipoStand'],
                '<td>' + list_free_stand[i]['Stand_W'] + '  x  ' + list_free_stand[i]['Stand_H'] + '  =  <span class="area">' + (parseInt(list_free_stand[i]['Stand_W']) * parseInt(list_free_stand[i]['Stand_H'])) + '</span>'
            ]).draw('full-hold');
            $(this).parents("tr").remove();
        } else {
            if ($("#tbl-stand tbody tr[class!=unsaved]").length <= 1) {
                show_alert("warning", section_text.sas_noQuitarTodoStand);
                return;
            }
            itemToDelete = {
                "record": $(this).attr("id-record"),
                "asign": $(this).attr("id-asign"),
                "contract": $(this).attr("id-contract"),
            };
            $("#mdl-delete-stand").modal("open");

        }
        total();
        calculaTotal();
    });

    $(document).on("change", '.idTipoPrecio', function () {
        var id = $(this).attr("data-id");
        var moneda = $("#Moneda").val();
        var precio = $(this).find("option:selected").attr('data-' + moneda);
        var idTPTS = $(this).find("option:selected").attr('data-id');
        var tipo_stand = $(this).parents('tr').find('.booth-price').attr("booth-type");
        /*if (tipo_stand == 14) {
         var m2 = total();
         var t = 0;
         if (moneda == "MXN") {
         t = parseFloat(list_tipo_precio_stand[142]['PrecioES']) * m2;
         $("#booth-price-" + id).val(number_format(t, 2));
         }
         if (moneda == "USD") {
         t = parseFloat(list_tipo_precio_stand[142]['PrecioEN']) * m2;
         $("#booth-price-" + id).val(number_format(t, 2));
         }
         } else {*/
        var flag = false;
        $.each(list_tipo_precio_stand, function (i, e) {
            if (e['idTipoPrecioTipoStand'] == parseInt(idTPTS)) {
                flag = true;
                $("#booth-price-" + id).val(number_format(precio, 2));
            }
        });
        if (!flag) {
            $("#booth-price-" + id).val('0.00');
        }
        //}
        calculaTotal();
        $(this).parents('tr').find('.booth-price').attr('modify', 'false');
        $(this).parents('tr').find('.price-mod').hide();
    });

    $("#Moneda").on('change', function () {
        var moneda = $(this).val();
        $.each($('.idTipoPrecio'), function (i, ele) {
            var id = $(this).attr('data-id');
            var precio = $(this).find('option:selected').attr('data-' + moneda);
            $("#booth-price-" + id).val(number_format(precio, 2));
        });
        calculaTotal();
        $("#tbl-stand tr").find('.booth-price').attr('modify', 'false');
        $("#tbl-stand tr").find('.price-mod').hide();
    });

    $(document).on("focusout", ".booth-price", function () {
        if ($(this).val() == "") {
            $(this).val("0.00");
        } else {
            $(this).val(number_format($(this).val(), 2));
        }
        calculaTotal();
    });

    $(document).on("change", ".booth-price", function () {
        $(this).attr('modify', 'true');
        $(this).siblings('.price-mod').show();
    });

    $("#DescuentoCantidad").on("focusout", function () {
        if ($(this).val() == "") {
            $(this).val("0.00");
        } else {
            $(this).val(number_format($(this).val(), 2));
        }
        calculaTotal();
    });

    $("#DecoracionCantidad").on("focusout", function () {
        if ($(this).val() == "") {
            $(this).val("0.00");
        } else {
            $(this).val(number_format($(this).val(), 2));
        }
        calculaTotal();
    });

    $("#btnSaveContract").on("click", saveContract);

    $("#delete-stand").on("click", deleteData);

    $("input[name='OpcionPago']").on("change", calculaTotal);

    total();

    calculaTotal();

    $("#add-concept").on("click", function () {
        showConcept();
    });

    $("#save-concept").on("click", function () {
        addConcept($("#idConcept").val());
    });

    $(document).on("click", ".edit-concept", function () {
        var idC = $(this).attr('data-id');
        showConcept(idC);
    });

    $(document).on("click", ".delete-concept", function () {
        var idC = $(this).attr('data-id');
        delete concepts[idC];
        $("#other-concept-" + idC).remove();
        calcularOtrosConceptos();
        calculaTotal();
    });
}


function saveContract() {
    if ($("#tbl-stand tbody tr").length == 0) {
        $(".tbl-stand-panel").addClass('panel-highlight');
        $('html, body').animate({scrollTop: $(".tbl-stand-panel").offset().top}, 2000);
        setTimeout(function () {
            $(".tbl-stand-panel").removeClass("panel-highlight");
        }, 5000);
        show_alert("warning", section_text.sas_asegureseSeleccionarStand);
        return false;
    }
    if (!isBoothLabelFilled()) {
        return false;
    }
    if (!isTypePriceSelected()) {
        return false;
    }
    listado_stand = {};
    $.each($(".delete-record"), function (i, e) {
        var idStand = $(e).attr("id-record");
        var EtiquetaStand = $(e).parents("tr").find(".booth-label").val();
        var Precio = $(e).parents("tr").find(".booth-price").val();
        var PrecioStand = $(e).parents("tr").find(".idTipoPrecio").val();
        var PrecioModificado = $(e).parents("tr").find(".booth-price").attr('modify');
        listado_stand[idStand] = {
            "idStand": idStand,
            "EtiquetaStand": EtiquetaStand,
            "Precio": Precio.replace(/,/g, ""),
            "idTipoPrecioStand": PrecioStand,
            "PrecioModificado": PrecioModificado
        };
    });
    var post = {
        "idContrato": $("#idContrato").val(),
        "idEmpresa": $("#idEmpresa").val(),
        "idVendedor": $("#vendedor").val(),
        "idOpcionPago": $("input[name='OpcionPago']:checked").val(),
        "Moneda": $("#Moneda").val(),
        "ListadoStand": JSON.stringify(listado_stand),
        "SubTotal": $("#SubtotalOpcionPago").val().replace(/,/g, ""),
        "IVA": $("#IvaOpcionPago").val().replace(/,/g, ""),
        "Total": $("#TotalOpcionPago").val().replace(/,/g, ""),
        "SubtotalStand": $("#SubtotalStand").val().replace(/,/g, ""),
        "DescuentoCantidad": $("#DescuentoCantidad").val().replace(/,/g, ""),
        "DecoracionCantidad": $("#DecoracionCantidad").val().replace(/,/g, ""),
        "OtrosConceptosCantidad": $("#OtrosConceptosCantidad").val().replace(/,/g, ""),
        "OtrosConceptos": JSON.stringify(concepts)
    }
    show_loader_wrapper();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_save_espacio, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: post, // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            if (!result['status']) {
                show_alert("warning", result['data']);
                return;
            }
            show_alert("success", general_text.sas_guardoExito);
            $.each($(".unsaved").find('i'), function (i, e) {
                var id = $(e).attr("id-record");
                $.each(result.data, function (j, s) {
                    if (parseInt(s['_idStand']) == parseInt(id)) {
                        $(e).parents("tr").removeClass("unsaved");
                        $(e).attr("id-asign", s['_idEmpresaAsignada']);
                        $(e).attr("id-contract", s['_idEmpresaContrato']);
                        $(e).parents("tr").find('td:first').text(s['_EtiquetaStand']);
                        delete list_free_stand[id];
                    }
                });
            });
            if ($(".unsaved").length) {
                show_alert("warning", section_text.sas_asignacionesFallaron);
                return;
            }
            $("#unsaved-note").fadeOut();
            total();
            calculaTotal();
            $("#mdl-next-step").modal('open').modal({dismissible: false});
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}
function isBoothLabelFilled() {
    var flag = true;
    $.each($(".booth-label"), function (i, e) {
        if ($(e).val() == "") {
            flag = false;
        }
    });
    if (!flag) {
        $(".tbl-stand-panel").addClass('panel-highlight');
        $('html, body').animate({scrollTop: $(".tbl-stand-panel").offset().top}, 2000);
        setTimeout(function () {
            $(".tbl-stand-panel").removeClass("panel-highlight");
        }, 5000);
        show_alert("warning", section_text.sas_asegureseIngresarEtiquetaStand);
    }
    return flag;
}
function isOpcionPagoSelected() {
    if ($("input[name='OpcionPago']:checked").length > 0) {
        return true;
    }
    $(".opcion-pago-panel").addClass('panel-highlight');
    $('html, body').animate({scrollTop: $(".opcion-pago-panel").offset().top}, 2000);
    show_alert("warning", section_text.sas_asegureseSeleccionarSocioTipo);
    setTimeout(function () {
        $(".opcion-pago-panel").removeClass("panel-highlight");
    }, 5000);
    return false;
}
function isTypePriceSelected() {
    var flag = true;
    $.each($(".idTipoPrecio"), function (i, e) {
        if ($(e).val() == "") {
            flag = false;
        }
    });
    if (!flag) {
        $(".tbl-stand-panel").addClass('panel-highlight');
        $('html, body').animate({scrollTop: $(".tbl-stand-panel").offset().top}, 2000);
        setTimeout(function () {
            $(".tbl-stand-panel").removeClass("panel-highlight");
        }, 5000);
        show_alert("warning", section_text.sas_asegureseSeleccionarTipoPrecio);
    }
    return flag;
}
function deleteData() {
    if ($('.alert').length > 0) {
        $('.alert').remove();
    }
    var post = {
        "idContrato": $("#idContrato").val(),
        "idEmpresa": $("#idEmpresa").val(),
        "ListadoStand": itemToDelete,
    }
    show_loader_wrapper();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_delete_espacio, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: post, // datos a pasar al servidor, en caso de necesitarlo
        success: function (result) {
            hide_loader_wrapper();
            $("#mdl-delete-platform").modal("close");
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            if (Object.keys(result.data).length == 0) {
                show_alert("danger", section_text.sas_errorDesasignacionStand);
                return;
            }
            list_free_stand[result.data['idStand']] = result.data;
            $("i[id-record='" + result.data['idStand'] + "']").parents("tr").remove();
            $("#mdl-delete-stand").modal("close");
            oTable.row.add([
                '<input type="checkbox" id="free-stand-' + result.data['idStand'] + '" value="' + result.data['idStand'] + '" class="free-stand" /><label for="free-stand-' + result.data['idStand'] + '"></label>',
                result.data['StandNumber'],
                list_pabellon[result.data['idPabellon']]['NombreES'],
                list_tipo_stand[result.data['idTipoStand']]['TipoStand'],
                result.data['Stand_W'] + '  x  ' + result.data['Stand_H'] + '  =  <span class="area">' + (parseInt(result.data['Stand_W']) * parseInt(result.data['Stand_H'])) + '</span>'
            ]).draw('full-hold');
            show_alert("success", general_text.sas_eliminoExito);
            total();
            calculaTotal();
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function total() {
    var total = 0;
    $.each($("#tbl-stand .area"), function (i, e) {
        total += parseFloat($(e).text());
    });
    $("#area-total").text(total + " m");
    return total;
}

function calculaTotal() {
    calculaSubtotalTotalStand();
    var stands = parseFloat($("#SubtotalStand").val().replace(/,/g, ""));
    var descuento = parseFloat($("#DescuentoCantidad").val().replace(/,/g, ""));
    var decoracion = parseFloat($("#DecoracionCantidad").val().replace(/,/g, ""));
    var otrosConceptos = parseFloat($("#OtrosConceptosCantidad").val().replace(/,/g, ""));
    var subtotal = (stands - descuento) + decoracion + otrosConceptos;
    var iva = calculaIVA(subtotal);
    var total = parseFloat(subtotal) + parseFloat(iva);
    $("#SubtotalStandDescuento").val(number_format((stands - descuento), 2));
    $("#SubtotalOpcionPago").val(number_format(subtotal, 2));
    $("#IvaOpcionPago").val(number_format(iva, 2));
    $("#TotalOpcionPago").val(number_format(total, 2));

}

function calculaSubtotalTotalStand() {
    var subtotal = 0;
    $.each($(".booth-price"), function (i, e) {
        subtotal += parseFloat($(e).val().replace(/,/g, ""));
    });
    $("#SubtotalStand").val(number_format(subtotal, 2));
    return subtotal;
}

function showConcept(idC = "") {
    if (idC === "")
        clearConcept();
    else
        setConcept(idC);
    $("#modal-concept").modal("open");
}

function clearConcept() {
    $("#Price").val("").next().removeClass('active');
    $("#Name").val("").next().removeClass('active');
    $("#Description").val("").next().removeClass('active');
    $("#idConcept").val("");
}

function setConcept(idC) {
    $("#Price").val(concepts[idC]['Precio']).next().addClass('active');
    $("#Name").val(concepts[idC]['Nombre']).next().addClass('active');
    $("#Description").val(concepts[idC]['Descrpcion']).next().addClass('active');
    $("#idConcept").val(idC);
}

function addConcept(id) {
    if (id === "")
        insertConcept();
    else
        updateConcept(id);
    calcularOtrosConceptos();
    calculaTotal();
    $("#modal-concept").modal("close");
}
function insertConcept() {
    var idC = Object.keys(concepts).length;
    var priceC = parseFloat($("#Price").val()).toFixed(2);
    var nameC = $("#Name").val();
    var descriptionC = $("#Description").val();
    var tbody = document.getElementById("table-concepts").getElementsByTagName("tbody");
    var tr = document.createElement("tr");
    tr.id = "other-concept-" + idC;
    var td = document.createElement("td");
    td.id = "concept-name-" + idC;
    td.textContent = nameC;
    tr.appendChild(td);
    td = document.createElement("td");
    td.id = "concept-description-" + idC;
    td.textContent = descriptionC;
    tr.appendChild(td);
    td = document.createElement("td");
    td.id = "concept-price-" + idC;
    td.className = "concept-prices right-align";
    td.textContent = priceC;
    tr.appendChild(td);
    td = document.createElement("td");
    td.className = "center-align";
    var i = document.createElement('i');
    i.className = "material-icons table-icon edit-concept";
    i.textContent = "edit";
    i.setAttribute('data-id', idC);
    td.appendChild(i);
    tr.appendChild(td);
    td = document.createElement("td");
    td.className = "center-align";
    i = document.createElement('i');
    i.className = "material-icons table-icon delete-concept";
    i.textContent = "delete_forever";
    i.setAttribute('data-id', idC);
    td.appendChild(i);
    tr.appendChild(td);
    $(tbody).append(tr);
    concepts[idC] = {"Precio": priceC, "Nombre": nameC, "Descripcion": descriptionC};
}
function updateConcept(idC) {
    var priceC = parseFloat($("#Price").val()).toFixed(2);
    var nameC = $("#Name").val();
    var descriptionC = $("#Description").val();
    $("#concept-price-" + idC).text(priceC);
    $("#concept-name-" + idC).text(nameC);
    $("#concept-description-" + idC).text(descriptionC);
    concepts[idC] = {"Precio": priceC, "Nombre": nameC, "Descripcion": descriptionC};
}
function calcularOtrosConceptos() {
    var total = 0.00;
    $.each($(".concept-prices"), function (i, ele) {
        total = total + parseFloat($(this).text());
    });
    $("#OtrosConceptosCantidad").val(number_format(total, 2));
}