jQuery.extend(jQuery.validator.messages, {
    required: general_text.sas_campoRequerido,
    email: general_text.sas_emailInvalido,
    number: general_text.sas_soloNumeros,
    digits: general_text.sas_soloDigitos,
    maxlength: jQuery.validator.format(general_text.sas_ingresaMaxCaracteres)
});
$.validator.addMethod("webPage",
        function (value, element, params) {
            var rgx = /^(?:(?:(?:https?):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[/?#]\S*)?$/i;
            if (rgx.test(value) && params) {
                return true;
            } else {
                return false;
            }
        }, $.validator.format(section_text.sas_linkInvalido));
/**
 * Agrega el header de la seccion en la tabla
 */
function constructSectionTable(section) {
    var tb = document.createElement('tbody');
    tb.id = "tbody-" + section['idSeccionFormatos'];
    var tr = document.createElement("tr");
    tr.id = "section-" + section['idSeccionFormatos'];
    tr.className = "section row section-" + section['idSeccionFormatos'];
    var td = document.createElement('td');
    td.colSpan = clspan;
    td.id = "header-" + section['idSeccionFormatos'];
    if (section['ColorFondo'] !== null) {
        td.style.backgroundColor = section['ColorFondo'];
    } else {
        td.style.backgroundColor = "#eeeeee";
    }
    var div = document.createElement('div');
    div.className = "col s11";
    if (section['ColorLetra']) {
        div.style.color = section['ColorLetra'];
    } else {
        div.style.color = "#000000";
    }
    if (section['VisibleWeb'] === 0) {
        div.innerHTML = '<h5>' + section['Nombre' + lang.toUpperCase()] + '<span style="font-size: 14px;" id="popover-section-' + section['idSeccionFormatos'] + '" class="fa fa-question-circle fa-x1 circle orange section-no-visible" ></span></h5>';
    } else {
        div.innerHTML = '<h5>' + section['Nombre' + lang.toUpperCase()] + '</h5>';
    }
    td.appendChild(div);
    div = document.createElement('div');
    div.className = 'col s1 edit-content';
    var spn = document.createElement('span');
    $(spn).attr("data-section-id", section['idSeccionFormatos']);
    $(spn).attr("class", "tooltipped waves-effect waves-ligh btn-floating blue edit-section right");
    $(spn).attr("data-tooltip", general_text['sas_editar']);
    if (section['CreacionSAS'] === 1) {
        $(spn).attr('data-delete', 1);
    }
    i = document.createElement("i");
    i.className = "fa fa-pencil fa-2x";
    spn.appendChild(i);
    div.appendChild(spn);
    td.appendChild(div);
    tr.appendChild(td);
    tb.appendChild(tr);
    return tb;
}
/**
 * Construye la columna por forma para la tabla
 */
function buildFormTable(form) {
    var tr = "", td = "", spn = "", cls = "";
    //----  Comienza el renglón   ----//
    tr = document.createElement("tr");
    tr.id = 'form-' + form['idForma'];
    $(tr).attr('data-id', form['idForma']);
    cls = getRowLabelClass(form['FechaLimite'], form['Bloqueado'], "row");
    tr.className = "section-" + form['idSeccionFormatos'] + " row-" + form['idSeccionFormatos'] + " " + cls;
    //----  orden de la forma  ----//
    td = document.createElement('td');
    spn = document.createElement('span');
    spn.className = "btn-flat waves-effect waves-grey grey-text text-darken-2 tooltipped ordered order-" + form['idSeccionFormatos'];
    spn.innerHTML = '<i class="fa fa-arrows-v fa-1x"></i> ' + form["OrdenDespliegue"];
    $(spn).attr("data-tooltip", section_text['sas_editarOrden']);
    td.appendChild(spn);
    tr.appendChild(td);
    //----  Nombre forma   ----//
    td = document.createElement('td');
    if (form['Identificador'] !== null) {
        td.innerHTML = form['Identificador'] + " - " + form['NombreForma' + lang.toUpperCase()];
    } else {
        td.innerHTML = form['NombreForma' + lang.toUpperCase()];
    }
    if (cls !== null) {
        spn = document.createElement('span');
        spn.style.cursor = "help";
        if (cls === "end red lighten-5") {
            $(spn).attr("data-tooltip", section_text['sas_formaAbiertaNoDisponible']);
            spn.className = "tooltipped info-open-form btn-flat waves-effect waves-gray red-text fa fa-question fa-1x";
        } else if (cls === "orange lighten-5") {
            $(spn).attr("data-tooltip", section_text['sas_formaCerradaDisponible']);
            spn.className = "tooltipped info-close-form btn-flat waves-effect waves-gray orange-text fa fa-question fa-1x";
        }
        td.appendChild(spn);
    }
    tr.appendChild(td);
    //----  Botón para ver las formas llenas   ----//
    td = document.createElement('td');
    td.className = "center";
    spn = document.createElement('span');
    spn.innerHTML = form['completed'];
    $(spn).attr("data-form-id", form['idForma']);
    $(spn).attr("data-form-link", form['LinkEDForma']);
    $(spn).attr("class", "btn blue waves-effect waves-light btn-status");
    $(spn).on('click', function () {
        var a = $(this).text();
        if (a === "0") {
            show_toast('warning', section_text['sas_sinExpositores']);
            return;
        }
        showExhibitorsByFormStatus(this, 1);
    });
    td.appendChild(spn);
    tr.appendChild(td);
    //----  Botón para ver las formas pendientes   ----//
    td = document.createElement('td');
    td.className = "center";
    spn = document.createElement('span');
    spn.innerHTML = form['incompleted'];
    $(spn).attr("data-form-id", form['idForma']);
    $(spn).attr("data-form-link", form['LinkEDForma']);
    $(spn).attr("class", "btn orange waves-effect waves-light btn-status");
    $(spn).on('click', function () {
        var a = $(this).text();
        if (a === "0") {
            show_toast('warning', section_text['sas_sinExpositores']);
            return;
        }
        showExhibitorsByFormStatus(this, 0);
    });
    td.appendChild(spn);
    tr.appendChild(td);
    //----  Botón para ver las formas que no interesan   ----//
    td = document.createElement('td');
    td.className = "center";
    spn = document.createElement('span');
    spn.innerHTML = form['no_interest'];
    $(spn).attr("data-form-id", form['idForma']);
    $(spn).attr("data-form-link", form['LinkEDForma']);
    $(spn).attr("class", "btn grey lighten-1 black-text waves-effect waves-light btn-status");
    $(spn).on('click', function () {
        var a = $(this).text();
        if (a === "0") {
            show_toast('warning', section_text['sas_sinExpositores']);
            return;
        }
        showExhibitorsNotInterest(form['idForma']);
    });
    td.appendChild(spn);
    tr.appendChild(td);
    //----  Fecha límite   ----//
    td = document.createElement('td');
    cls = getRowLabelClass(form['FechaLimite'], form['Bloqueado'], "label");
    spn = document.createElement('span');
    spn.id = "deadline-form-" + form["idForma"];
    $(spn).css('font-size', "14px");
    $(spn).text(form['FechaLimite']);
    $(spn).attr('onclick', 'changeDeadline(' + form["idForma"] + ', "' + form['NombreForma' + lang.toUpperCase()] + '")');
    $(spn).addClass('tooltipped waves-effect waves-light btn btn-deadline ' + cls);
    switch (cls) {
        case "amber":
            $(spn).attr("data-tooltip", section_text['sas_tresDias']);
            $(tr).addClass('next-deadline');
            break;
        case "blue":
            $(spn).attr("data-tooltip", section_text['sas_cincoDias']);
            $(tr).addClass('next-deadline');
            break;
        case "grey":
            $(spn).attr("data-tooltip", section_text['sas_formaClausurada']);
            $(tr).addClass('end');
            break;
        case "green":
            $(spn).attr("data-tooltip", section_text['sas_editarFecha']);
            $(tr).addClass('time');
            break;
        case "red":
            $(spn).attr("data-tooltip", section_text['sas_formaAbiertaNoDisponible']);
            $(tr).addClass('time');
            break;
        default:
            $(spn).attr("data-tooltip", section_text['sas_definirFecha']);
            $(tr).addClass('no-deadline');
            $(spn).text(general_text['sas_agregar']);
            break;
    }
    td.appendChild(spn);
    td.className = "center";
    tr.appendChild(td);
    //----  Botón de forma Obligatoria   ----//
    td = document.createElement('td');
    spn = document.createElement("span");
    $(spn).css("cursor", "pointer");
    spn.setAttribute("data-form-id", form['idForma']);
    $(spn).attr("id", "mandatory-" + form['idForma']);
    $(spn).attr("data-type", "mandatory");
    if (parseInt(form["ObligatorioOpcional"]) === 1) {
        $(spn).attr("data-status", "0");
        spn.className = "tooltipped red-text status mandatory fa fa-check-square-o fa-2x";
        $(spn).attr("data-tooltip", section_text['sas_noObligatoria']);
        $(tr).addClass('obligatory');
    } else {
        $(spn).attr("data-status", "1");
        spn.className = "tooltipped gray-text status no-mandatory fa fa-square-o fa-2x";
        $(spn).attr("data-tooltip", section_text['sas_hacerObligatoria']);
        $(tr).addClass('optional');
    }
    td.appendChild(spn);
    td.className = "center";
    tr.appendChild(td);
    //----  Bloquear la forma   ----//
    td = document.createElement('td');
    spn = document.createElement("span");
    $(spn).css("cursor", "pointer");
    spn.setAttribute("data-form-id", form['idForma']);
    $(spn).attr("id", "lock-" + form['idForma']);
    $(spn).attr("data-type", "lock");
    if (parseInt(form['Bloqueado']) === 0) {
        $(spn).attr("data-status", "1");
        $(spn).attr("data-tooltip", section_text['sas_cerrarForma']);
        spn.className = "tooltipped green-text status fa fa-unlock-alt fa-2x";
        $(tr).addClass('form-open');
    } else {
        $(spn).attr("data-status", "0");
        $(spn).attr("data-tooltip", section_text['sas_abrirForma']);
        spn.className = "tooltipped orange-text status fa fa-lock fa-2x";
        $(tr).addClass('form-close');
    }
    td.appendChild(spn);
    td.className = "center";
    tr.appendChild(td);
    //----  Ver iconos de los tipos de forma  ----//
    $.each(idioms, function (i, value) {
        var idiom = value.toLowerCase();
        td = document.createElement("td");
        td.className = "center";
        spn = document.createElement("span");
        spn.id = form['idForma'] + "-" + value;
        spn.setAttribute("data-id", form['idForma']);
        spn.setAttribute("data-lang", value);
        if (parseInt(form['TipoLink']) === 1) {
            $(spn).css("cursor", "pointer");
            $(spn).attr("data-link", form['rutaTextos' + value]);
            $(spn).attr("data-tooltip", section_text['sas_editarTextoForma']);
            spn.className = "tooltipped blue-text fa fa-file-text-o fa-2x";
            $(tr).addClass('smart');
        } else if (parseInt(form['TipoLink']) === 2) {
            $(spn).css("cursor", "pointer");
            spn.className = "tooltipped fa fa-file-pdf-o fa-2x";
            form['FechaActualizacion' + value] !== "" && form['FechaActualizacion' + value] !== null ? $(spn).attr("data-tooltip", section_text['sas_ultimaActulizacion'] + " " + form['FechaActualizacion' + value]).addClass('red-text') : $(spn).attr("data-tooltip", section_text['sas_actualizarPDF']).addClass('gray-text');
            $(tr).addClass('pdf');
        } else if (parseInt(form['TipoLink']) === 3) {
            $(spn).css("cursor", "pointer");
            spn.className = "tooltipped fa fa-external-link fa-2x";
            form['FechaActualizacion' + value] !== "" && form['FechaActualizacion' + value] !== null ? $(spn).attr("data-tooltip", section_text['sas_ultimaActulizacion'] + " " + form['FechaActualizacion' + value]).addClass('purple-text') : $(spn).attr("data-tooltip", section_text['sas_editarLink']).addClass('gray-text');
            $(tr).addClass('link');
        }
        td.appendChild(spn);
        tr.appendChild(td);
    });
    //----  Botón para los reportes   ----//
    td = document.createElement('td');
    td.className = "center hide";
    a = document.createElement('a');
    if (form['LinkReporte'] !== '') {
//a.setAttribute("href", links[form['LinkReporte']]);
        a.setAttribute("target", "_blank");
    } else {
//a.setAttribute("href", url_form_data + "/" + form['idForma']);
        a.setAttribute("target", "_blank");
    }
    spn = document.createElement("span");
    $(spn).css("cursor", "pointer");
    spn.setAttribute("data-id", form['idForma']);
    spn.className = " green-text fa fa-file-excel-o fa-2x";
    a.appendChild(spn);
    td.appendChild(a);
    tr.appendChild(td);
    //----  Botón para las gráficas   ----//
    td = document.createElement('td');
    spn = document.createElement("span");
    $(spn).css("cursor", "pointer");
    spn.setAttribute("data-id", form['idForma']);
    $(spn).attr("onclick", "showCharts(" + form['idForma'] + ",'" + form['NombreForma' + lang.toUpperCase()] + "')");
    spn.className = "cyan-text fa fa-line-chart fa-2x";
    td.appendChild(spn);
    td.className = "center hide";
    tr.appendChild(td);
    //----  Botón para editar la forma  ----//
    td = document.createElement('td');
    spn = document.createElement('span');
    $(spn).css("cursor", "pointer");
    $(spn).attr("data-form-id", form['idForma']);
    $(spn).attr("data-tooltip", general_text['sas_editar']);
    $(spn).attr("class", "tooltipped teal-text fa fa-pencil-square-o fa-2x edit-form");
    if (parseInt(form['CreacionSAS']) === 1) {
        $(spn).attr('data-delete', 1);
    }
    td.appendChild(spn);
    td.className = "center";
    tr.appendChild(td);
    return tr;
}
/**
 *Actualiza el orden de las formas
 */
function ajaxOrder() {
    $.each(sections, function (i, Section) {
        var fixHelperModified = function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            tr.parent().find('#form-' + $helper.attr('data-id')).attr('data-original', $helper.find('td:first-child').text());
            $helper.children().each(function (index) {
                $(this).width($originals.eq(index).width());
            });
            return $helper;
        },
                updateIndex = function (e, ui) {
                    var j = 1, items = {}, pass = true;
                    items["idSeccionFormatos"] = Section['idSeccionFormatos'];
                    $('tr.section-' + Section['idSeccionFormatos'] + ' td:first-child', ui.item.parent()).each(function (i) {
                        if ($(this).parent().hasClass('section-' + Section['idSeccionFormatos'])) {
                            if (pass && !($(this).parent().hasClass('section'))) {
                                show_loader_wrapper();
                                var spn = document.createElement('span');
                                spn.className = "tooltipped btn-flat waves-effect waves-grey ordered order-" + Section['idSeccionFormatos'];
                                spn.innerHTML = '<i class="fa fa-arrows-v fa-x1"></i> ' + j;
                                $(spn).attr("data-tooltip", section_text['sas_editarOrden']);
                                $(this).html("");
                                $(this).append(spn);
                                $(spn).tooltip({delay: 50})
                                items[j] = $(this).parent().attr('data-id');
                                j++;
                            }
                        }
                    });
                    $('tr.section-' + Section['idSeccionFormatos']).each(function (i, item) {
                        if (parseInt($(item).attr('data-original')) === i) {
                            pass = false;
                            hide_loader_wrapper();
                            return;
                        }
                    });
                    if (pass) {
                        $.ajax({
                            type: "POST",
                            dataType: 'json',
                            url: url_order_update,
                            data: items,
                            success: function (response, textStatus, jqXHR) {
                                hide_loader_wrapper();
                                $('tr.section-' + Section['idSeccionFormatos']).removeAttr('data-original');
                                if (response['status']) {
                                    show_toast('success', section_text.sas_ordenActualizado);
                                    return;
                                } else {
                                    $("#cover-forms-table #tbody-" + Section['idSeccionFormatos']).sortable('cancel');
                                    $("#cover-forms-table #tbody-" + Section['idSeccionFormatos'] + " tr").each(function (i, item) {
                                        if ($(this).hasClass('row-' + Section['idSeccionFormatos'])) {
                                            $(this).find('td:first-child').html('<spn class="btn-flat waves-effect waves-grey ordered order-' + Section['idSeccionFormatos'] + '" ><i class="fa fa-arrows-v fa-x1"></i> ' + (i) + '</spn>');
                                        }
                                    });
                                    show_modal_error(response.data);
                                }
                            },
                            error: function (jqXHR, textStatus, error) {
                                hide_loader_wrapper();
                                $("#cover-forms-table #tbody-" + Section['idSeccionFormatos']).sortable('cancel');
                                $("#cover-forms-table #tbody-" + Section['idSeccionFormatos'] + " tr").each(function (i, item) {
                                    if ($(this).hasClass('row-' + Section['idSeccionFormatos'])) {
                                        $(this).find('td:first-child').html('<spn class="btn-flat waves-effect waves-grey ordered order-' + Section['idSeccionFormatos'] + '" ><i class="fa fa-arrows-v fa-x1"></i> ' + (i) + '</spn>');
                                    }
                                });
                                show_modal_error(jqXHR.responseText);
                            }
                        });
                    }
                };
        $("#cover-forms-table #tbody-" + Section['idSeccionFormatos']).sortable({
            delay: 500,
            revert: 200,
            handle: ".ordered",
            items: ".row-" + Section['idSeccionFormatos'],
            opacity: "0.7",
            axis: "y",
            helper: fixHelperModified,
            stop: updateIndex
        }).disableSelection();
    });
}
/**
 *
 * @param {obeject} current_form recibe el botón
 * @param {integer} status recibe el status de la forma 1 o 0
 * @returns {undefined}
 */
function showExhibitorsByFormStatus(current_form, status) {
    show_loader_wrapper();
    var form_id = $(current_form).attr("data-form-id");
    var form_link = $(current_form).attr("data-form-link");
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url_get_exhibitors_by_form_status,
        data: {"idForma": form_id, "StatusForma": status},
        success: function (response) {
            hide_loader_wrapper();
            if (!response['status']) {
                show_modal_error("danger", section_text['sas_errorOptenerExpositores']);
                return;
            }
            $("#link-form").val(form_link);
            if (response.data.length > 0) {
                array_exhibitors = [];
            }
            drawExhibitorList(response.data, status, form_id);
        },
        error: function (request) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}
/*
 * dibuja la tabla de los expositores
 * @param {object} data los expositores encontrados
 * @param {string} status el status de los expositores
 */
function drawExhibitorList(data, status, id) {
    var tr = "", td = "", inpt = "", n = data.length, thead = '';
    changeItemsForms(parseInt(id), parseInt(status), n);
    $("#exhibitors-table thead tr").html(thead);
    /*if (status) {
     //--- header de la tabla para las formas llenas ---//
     thead += '<th>' + section_text.sas_codigoCliente + '</th>';
     thead += '<th>' + section_text.sas_razonSocial + '</th>';
     thead += '<th>' + section_text.sas_nombreEmpresa + '</th>';
     thead += '<th>' + section_text.sas_estado + '</th>';
     thead += '<th>' + section_text.sas_pais + '</th>';
     thead += '<th>' + section_text.sas_asesorComercial + '</th>';
     //--- Pinta la columna si la forma tinene opcion de pago en la forma por el lenguaje ---//
     if (formsAssoc[id]["FormaPago" + lang.toUpperCase()] !== null && formsAssoc[id]["FormaPago" + lang.toUpperCase()] !== "") {
     thead += '<th>' + section_text.sas_statusPago + '</th>';
     }
     thead += '<th>' + section_text.sas_idiomaLlenado + '</th>';
     //--- Pinta la columna de bloquear ---//
     thead += '<th>' + section_text.sas_desbloquearBloquear + '</th>';
     //--- Pinta la columna para editar la forma si es un smart link no se ve si la forma no esta llena ya que lo toma como proveedor ---//
     if ((parseInt(formsAssoc[id]["TipoLink"]) === 1 || (parseInt(status) === 1 && (parseInt(formsAssoc[id]["TipoLink"]) === 1))) && (formsAssoc[id]["FormaPago" + lang.toUpperCase() ] === null || formsAssoc[id]["FormaPago" + lang.toUpperCase()] === "")) {
     thead += '<th>' + section_text.sas_detalleForma + '</th>';
     }
     } else {*/
    //--- header de la tabla para las formas pendientes ---//
    thead = '<th class="center-align no-sort" style="max-width: 100px; width: 100px;"><input type="checkbox" id="select-all" class="select-all filled-in"><label for="select-all" class="white-text">' + section_text.sas_seleccionarTodos + '</label></th>';
    thead += '<th>' + section_text.sas_codigoCliente + '</th>';
    thead += '<th>' + section_text.sas_razonSocial + '</th>';
    thead += '<th>' + section_text.sas_nombreEmpresa + '</th>';
    thead += '<th>' + section_text.sas_estado + '</th>';
    thead += '<th>' + section_text.sas_pais + '</th>';
    thead += '<th>' + section_text.sas_asesorComercial + '</th>';
    //--- Pinta la columna si la forma tinene opcion de pago en la forma por el lenguaje ---//
    if (formsAssoc[id]["FormaPago" + lang.toUpperCase() ] !== null && formsAssoc[id]["FormaPago" + lang.toUpperCase()] !== "") {
        thead += '<th>' + section_text.sas_statusPago + '</th>';
    }
    thead += '<th>' + section_text.sas_idiomaLlenado + '</th>';
    //--- Pinta la columna de bloquear ---//
    thead += '<th >' + section_text.sas_desbloquearBloquear + '</th>';
    //--- Pinta la columna para editar la forma si es un smart link no se ve si la forma no esta llena ya que lo toma como proveedor ---//
    if ((parseInt(formsAssoc[id]["TipoLink"]) === 1 || (parseInt(status) === 1 && (parseInt(formsAssoc[id]["TipoLink"]) === 1))) && (formsAssoc[id]["FormaPago" + lang.toUpperCase() ] === null || formsAssoc[id]["FormaPago" + lang.toUpperCase()] === "")) {
        thead += '<th>' + section_text.sas_detalleForma + '</th>';
    }
    //}
    $("#exhibitors-table thead tr").html(thead);
    //--- Cuerpo de la tabla ---//
    $("#exhibitors-table tbody").html("");
    for (var i = 0; i < n; i++) {
        tr = document.createElement('tr');
        tr.id = "row_booth_" + data[i]['idEmpresa'];
        //if (!status) {
        var la = "";
        td = document.createElement('td');
        td.style.textAlign = "center";
        inpt = document.createElement('input');
        inpt.id = "check-" + data[i]['idEmpresa'];
        inpt.setAttribute("type", "checkbox");
        inpt.setAttribute("class", "select-me filled-in");
        inpt.value = data[i]['idEmpresa'];
        td.appendChild(inpt);
        la = document.createElement('label');
        la.setAttribute('for', "check-" + data[i]['idEmpresa']);
        td.appendChild(la);
        tr.appendChild(td);
        //}
        td = document.createElement('td');
        td.innerHTML = data[i]['CodigoCliente'];
        tr.appendChild(td);
        td = document.createElement('td');
        td.innerHTML = (data[i]['DF_RazonSocial'] != undefined) ? data[i]['DF_RazonSocial'] : section_text.sas_sinDefinir;
        tr.appendChild(td);
        td = document.createElement('td');
        td.innerHTML = data[i]['DC_NombreComercial'];
        tr.appendChild(td);
        td = document.createElement('td');
        td.innerHTML = data[i]['EmpresaPais'];
        tr.appendChild(td);
        td = document.createElement('td');
        td.innerHTML = data[i]['EmpresaEstado'];
        tr.appendChild(td);
        td = document.createElement('td');
        td.innerHTML = (data[i]['Vendedor'] != undefined) ? data[i]['Vendedor'] : section_text.sas_sinDefinir;
        tr.appendChild(td);
        if (formsAssoc[id]["FormaPago" + lang.toUpperCase()]) {
            var status_p = (data[i]['StatusPago'] !== undefined) ? parseInt(data[i]['StatusPago']) : 0;
            var status_payment = array_payment[status_p];
            td = document.createElement('td');
            td.innerHTML = status_payment;
            tr.appendChild(td);
        }
        var lang_filled = (data[i]['Lang'] !== null && data[i]['Lang'] !== undefined) ? array_lang[data[i]['Lang']] : array_lang[0];
        td = document.createElement('td');
        td.innerHTML = lang_filled;
        tr.appendChild(td);
        td = document.createElement('td');
        td.className = "center";
        if (parseInt(status)) {
            if (parseInt(data[i]['Bloqueado']) === 1) {
                td.innerHTML = '<span id="emfo-lock-' + data[i]['idEmpresa'] + '" class="btn btn-flat waves-effect waves-light emfo-lock" data-id="' + data[i]['idEmpresa'] + '" data-lock="0"><i class="fa fa-lock amber-text"></i></span>';
            } else {
                td.innerHTML = '<span id="emfo-lock-' + data[i]['idEmpresa'] + '" class="btn btn-flat waves-effect waves-light emfo-lock" data-id="' + data[i]['idEmpresa'] + '" data-lock="1"><i class="fa fa-unlock-alt green-text"></i></span>';
            }
        } else {
            if (parseInt(data[i]['Bloqueado']) === 1) {
                td.innerHTML = '<span id="emfo-lock-' + data[i]['idEmpresa'] + '" class="btn btn-flat waves-effect waves-light emfo-lock" data-id="' + data[i]['idEmpresa'] + '" data-lock="0"><i class="fa fa-lock amber-text"></i></span>';
            } else if (parseInt(data[i]['Bloqueado']) === 0) {
                td.innerHTML = '<span id="emfo-lock-' + data[i]['idEmpresa'] + '" class="btn btn-flat waves-effect waves-light emfo-lock" data-id="' + data[i]['idEmpresa'] + '" data-lock="1"><i class="fa fa-unlock-alt green-text"></i></span>';
            } else {
                if (parseInt(formsAssoc[id]['Bloqueado']) === 1) {
                    td.innerHTML = '<span id="emfo-lock-' + data[i]['idEmpresa'] + '" class="btn btn-flat waves-effect waves-light emfo-lock" data-id="' + data[i]['idEmpresa'] + '" data-lock="0"><i class="fa fa-lock amber-text"></i></span>';
                } else {
                    td.innerHTML = '<span id="emfo-lock-' + data[i]['idEmpresa'] + '" class="btn btn-flat waves-effect waves-light emfo-lock" data-id="' + data[i]['idEmpresa'] + '" data-lock="1"><i class="fa fa-unlock-alt green-text"></i></span>';
                }

            }
        }
        tr.appendChild(td);
        //--- Pinta la columna para editar la forma si es un smart link no se ve si la forma no esta llena ya que lo toma como proveedor ---//
        if ((parseInt(formsAssoc[id]["TipoLink"]) === 1 || (parseInt(status) === 1 && (parseInt(formsAssoc[id]["TipoLink"]) === 1))) && (formsAssoc[id]["FormaPago" + lang.toUpperCase() ] === null || formsAssoc[id]["FormaPago" + lang.toUpperCase()] === "")) {
            td = document.createElement('td');
            td.className = "center";
            td.innerHTML = "<a href='" + $("#link-form").val() + user['idUsuario'] + '/' + $("#form-id").val() + '/' + data[i]['Token'] + '/' + lang + "' target='_blank' data-company='" + data[i]['idEmpresa'] + "' data-name='" + data[i]['DC_NombreComercial'] + "' class='btn btn-flat waves-effect waves-light blue-text'><i class='fa fa-edit'></i></a>";
            tr.appendChild(td);
        }
        $("#exhibitors-table tbody").append(tr);
        array_exhibitors[data[i]['idEmpresa']] = data[i];
    }
    initEditMail();
    initChecksMail();
    initExhibitorsTable();
}
/**
 * Inicia las funciones para editar el mail que se le envía a los expositores
 */
function initEditMail() {
    $("#sendHtml").on('click', function () {
        show_loader_wrapper();
        var post = tinyMCE.get('correo').getContent();
        $.ajax({
            type: "POST",
            data: {correo: post},
            dataType: 'json',
            url: url_save_email_html,
            success: function (response) {
                hide_loader_wrapper();
                if (response.status) {
                    $('#ModalEditEmail').modal("close");
                    section_text.sas_emailFormaPendiente = post;
                    show_toast('success', section_text['sas_emailEditadoCorrectamente']);
                }
            },
            error: function (request) {
                hide_loader_wrapper();
                show_modal_error(request.responseText);
            }
        });
    });
}
/**
 * Inicia las opciones para los mails
 */
function initChecksMail() {
    $(document).on("change", ".select-all", function () {
        var checked = $(this).is(":checked");
        if (checked) {
            var rowsTable = exTable.rows({filter: 'applied'}).nodes();
            $.each(rowsTable, function (index, element) {
                var id = $(element).find("input[type=checkbox]").val();
                array_selected[id] = array_exhibitors[id];
                $(element).find("input[type=checkbox]").prop('checked', true);
            });
            $("#send-email-all").prop('disabled', false);
        } else {
            array_selected = [];
            var rowsTable = exTable.rows().nodes();
            $.each(rowsTable, function (index, element) {
                $(element).find("input[type=checkbox]").prop('checked', false);
            });
            $("#send-email-all").prop('disabled', true);
        }
        /*$('#exhibitors-table tbody input[type="checkbox"]').each(function () {
         var value = $(this).val();
         if (checked) {
         $(this).prop("checked", true);
         array_selected[value] = array_exhibitors[value];
         $("#send-email-all").prop('disabled', false);
         } else {
         $(this).prop("checked", false);
         delete array_selected[value];
         if (Object.keys(array_selected).length < 1) {
         array_selected = [];
         $("#send-email-all").prop('disabled', true);
         }
         }

         });*/
    });
    $(document).on("change", ".select-me", function () {
        var checked = $(this).is(":checked");
        var value = parseInt($(this).val());
        if (checked) {
            array_selected[value] = array_exhibitors[value];
        } else {
            delete array_selected[value];
            if (Object.keys(array_selected).length < 1) {
                array_selected = [];
            }
        }
        var cont = 0;
        $('#exhibitors-table tbody input[type="checkbox"]').each(function (key, val) {
            ($(this).is(":checked")) ? cont++ : '';
        });
        (cont > 0) ? $("#send-email-all").prop('disabled', false) : $("#send-email-all").prop('disabled', true);
    });
    $("#send-email-all").click(function () {
        var index = 0;
        exhibitors = new Array();
        var idForm = parseInt($('#form-id').val());
        var keys_companies = Object.keys(array_selected);
        var total_companies = keys_companies.length;
        for (var i = 0; i < total_companies; i++) {
            if (array_selected[keys_companies[i]]['EmailContacto'] !== "") {
                exhibitors[index] = {
                    "idEmpresa": array_selected[keys_companies[i]]['idEmpresa'],
                    "DC_NombreComercial": array_selected[keys_companies[i]]['DC_NombreComercial'],
                    "Email": array_selected[keys_companies[i]]['Email'],
                    "Password": array_selected[keys_companies[i]]['Password'],
                    "idPais": array_selected[keys_companies[i]]['EmpresaIdPais'],
                    "Pais": array_selected[keys_companies[i]]['EmpresaPais'],
                    "FechaLimite": formsAssoc[idForm]['FechaLimite'],
                    "idForma": idForm
                };
                $.each(idioms, function (id, idiom) {
                    exhibitors[index]['NombreForma' + idiom] = formsAssoc[idForm]['NombreForma' + idiom];
                });
                index++;
            }
        }
        var confirm_text = section_text['sas_preguntaAntesEnviaremail'].replace("%total_exhibitors%", Object.keys(exhibitors).length);
        confirm_text = confirm_text.replace("%form_name%", $("#form-name").text());
        $("#modal-send-email").find('#info-send').html(confirm_text);
        $("#modal-send-email").modal({dismissible: false}).modal("open");
    });
}
/**
 * Envía el mail a las empresas seleccionadas
 * @param {object} data el arreglo de empresas a las que se le va a mandar el mail
 */
function sendEmailPendingForm(data) {
    show_loader_wrapper();
    $("#modal-send-email").modal("close");
    $.ajax({
        type: "post",
        dataType: 'json',
        data: {
            exhibitors: data,
            idForma: $("#form-id").val()
        },
        url: url_send_email_form,
        success: function (response) {
            hide_loader_wrapper();
            if (!response.status) {
                show_modal_error(general_text.sas_errorPeticion);
                return;
            }
            if (response['band'] === 0) { //todos se enviaron
                show_toast("success", section_text.sas_correosEnviados);
            } else { //más de alguno no se envio
                var error = section_text.sas_errorEnviarCorreos.replace('%num%', response['band']);
                show_toast("danger", error);
            }
        },
        error: function (request) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}
/*
 * Bloque la empresa forma
 * @param {integer} id el id de la forma
 * @param {integer} lock el status del bloqueado
 */
function lockUnlockEMFO(id, lock) {
    $.ajax({
        type: "post",
        dataType: 'json',
        data: {
            idEmpresa: id,
            idForma: $("#form-id").val(),
            Bloqueado: lock
        },
        url: url_unlock_lock_form,
        success: function (response) {
            hide_loader_wrapper();
            var msj = section_text['sas_desbloquearBloquearExito'];
            var msj = msj.replace("%company%", array_exhibitors[id]['DC_NombreComercial']);
            if (parseInt(lock) === 1) {
                $("#emfo-lock-" + id).attr('data-lock', 0).find('i').removeClass('fa-unlock-alt green-text').addClass('fa-lock amber-text');
                msj = msj.replace("%lock%", section_text.sas_bloqueado);
            } else {
                $("#emfo-lock-" + id).attr('data-lock', 1).find('i').removeClass('fa-lock amber-text').addClass('fa-unlock-alt green-text');
                msj = msj.replace("%lock%", section_text.sas_desbloqueado);
            }
            show_toast("success", msj);
        },
        error: function (request) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}
/**
 * Inicia el datatables
 */
function initExhibitorsTable() {
    exTable = $('#exhibitors-table').DataTable({
        "language": {
            "url": table_lang,
        },
        "aaSorting": [[1, 'asc']],
        'aoColumnDefs': [{
                'bSortable': false,
                'aTargets': ['no-sort']
            }]
    });
    $(".dataTables_filter label").css("display", "block");
}
/**
 * Muestra la tabla de la forma a la cual las empresas no les interesaba
 * @param {int} idForma el id de la forma a mostrar
 */
function showExhibitorsNotInterest(idForma) {
    show_loader_wrapper();
    $.ajax({
        type: "POST",
        data: {idForma: idForma},
        dataType: 'json',
        url: url_show_not_interest_forms,
        success: function (response) {
            hide_loader_wrapper();
            drawTableNotInterestForms(response.data, idForma);
        },
        error: function (request) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}
/**
 *
 * @param {array} data todas las empresas que no tenian interes en las forma
 */
function drawTableNotInterestForms(data, id) {
    var tr = "", td = "", n = data.length, thead = '';
    changeItemsForms(parseInt(id), 2, n);
    $("#exhibitors-table thead tr").html(thead);
    //--- header de la tabla ---//
    thead = '<th class="center-align no-sort" style="max-width: 100px; width: 100px;"><input type="checkbox" id="select-all" class="select-all filled-in"><label for="select-all" class="white-text">' + section_text.sas_seleccionarTodos + '</label></th>';
    thead += '<th data-class="expand">ID</th>';
    thead += '<th data-class="expand">' + section_text.sas_nombreEmpresa + '</th>';
    thead += '<th>' + section_text.sas_responsableForma + '</th>';
    thead += '<th>' + section_text.sas_email + '</th>';
    thead += '<th>' + section_text.sas_telefono + '</th>';
    thead += '<th data-hide="phone,tablet">' + section_text.sas_desbloquearBloquear + '</th>';
    $("#exhibitors-table thead tr").html(thead);
    //--- cuerpo de la tabla ---//
    $("#exhibitors-table tbody").html("");
    for (var i = 0; i < n; i++) {
        tr = document.createElement('tr');
        tr.id = "row_booth_" + data[i]['idEmpresa'];
        var la = "";
        td = document.createElement('td');
        td.style.textAlign = "center";
        inpt = document.createElement('input');
        inpt.id = "check-" + data[i]['idEmpresa'];
        inpt.setAttribute("type", "checkbox");
        inpt.setAttribute("class", "select-me filled-in");
        inpt.value = data[i]['idEmpresa'];
        td.appendChild(inpt);
        la = document.createElement('label');
        la.setAttribute('for', "check-" + data[i]['idEmpresa']);
        td.appendChild(la);
        tr.appendChild(td);
        td = document.createElement('td');
        td.innerHTML = data[i]['idEmpresa'];
        tr.appendChild(td);
        td = document.createElement('td');
        td.innerHTML = data[i]['DC_NombreComercial'];
        tr.appendChild(td);
        td = document.createElement('td');
        td.innerHTML = data[i]['NombreCompleto'];
        tr.appendChild(td);
        td = document.createElement('td');
        td.innerHTML = data[i]['Email'];
        tr.appendChild(td);
        td = document.createElement('td');
        td.innerHTML = data[i]['Telefono'];
        tr.appendChild(td);
        td = document.createElement('td');
        td.className = "center";
        if (data[i]['Bloqueado'] !== undefined && parseInt(data[i]['Bloqueado']) === 1) {
            td.innerHTML = '<span id="emfo-lock-' + data[i]['idEmpresa'] + '" class="btn btn-flat waves-effect waves-light emfo-lock" data-id="' + data[i]['idEmpresa'] + '" data-lock="0"><i class="fa fa-lock amber-text"></i></span>';
        } else {
            td.innerHTML = '<span id="emfo-lock-' + data[i]['idEmpresa'] + '" class="btn btn-flat waves-effect waves-light emfo-lock" data-id="' + data[i]['idEmpresa'] + '" data-lock="1"><i class="fa fa-unlock-alt green-text"></i></span>';
        }
        tr.appendChild(td);
        $("#exhibitors-table tbody").append(tr);
        array_exhibitors[data[i]['idEmpresa']] = data[i];
    }
    initEditMail();
    initChecksMail();
    initExhibitorsTable();
}
/**
 * Cambia los iconos y textos cuando se muestran los detalles por forma para pendiente, llenadas y las que no interesan
 * @param {integer} id el id de la forma a mostrar
 * @param {integer} status 0 pendientes, 1 completas y 2 no interesan
 * @param {integer} total el total de expositores
 */
function changeItemsForms(id, status, total) {
    if (exTable != '') {
        exTable.destroy();
        exTable = false;
    }
    var NameForma = formsAssoc[id]['NombreForma' + lang.toUpperCase()];
    $("#form-name").html(NameForma);
    $("#form-id").val(id);
    //--- iconos según la forma ---//
    $("#obligatory-detail").find('i').removeClass("fa-square-check-o fa-square-o").addClass(array_obligarory[formsAssoc[id]["ObligatorioOpcional"]]['icon']);
    $("#form-detail").find('i').removeClass("fa-file fa-file-text fa-file-excel-o").addClass(array_status[status]['icon']);
    $("#close-detail").find('i').removeClass("fa-lock fa-unlock-alt").addClass(array_close[formsAssoc[id]["Bloqueado"]]['icon']);
    $("#type-detail").find('i').removeClass("fa-link fa-file-excel-o fa-file-text-o").addClass(array_type[formsAssoc[id]["TipoLink"]]['icon']);
    //--- textos según la forma ---//
    var deadline_form = (formsAssoc[id]['FechaLimite'] === null || formsAssoc[id]['FechaLimite'] === "") ? section_text.sas_sinDefinir : formsAssoc[id]['FechaLimite'];
    $("#deadline-status").text(deadline_form);
    $("#obligatory-status").html(array_obligarory[formsAssoc[id]['ObligatorioOpcional']]['label']);
    $("#form-status").html(array_status[status]['label']);
    $("#close-status").html(array_close[formsAssoc[id]["Bloqueado"]]['label']);
    $("#type-status").html(array_type[formsAssoc[id]["TipoLink"]]['label']);
    $("#form-count-exhibitors").html(total);
    $("#form-status").attr("data-status", status);
    $('#send-email-all').show();
    $('#edit-email').show();
    if (status === 2) {
        $("#form-status").removeClass('amber-text green-text');
    } else if (status === 1) {
        //$('#send-email-all').hide();
        //$('#edit-email').hide();
        $("#form-status").addClass('green-text').removeClass('amber-text');
    } else {
        $("#form-status").addClass('amber-text').removeClass('green-text');
    }
    formsList("hide");
}
/**
 * acciones para cambiar la fecha límite
 */
function changeDeadline(idForm, form) {
    var title = $('#modal-deadline').find('.modal-title').text();
    $('#modal-deadline').find('.modal-title').text(title.replace('%form%', form));
    $('#modal-deadline').find('label').addClass('active');
    $('#send-deadline').attr('disabled', true).addClass('disabled');
    $('#send-deadline').attr('data-id-form', idForm);
    if (formsAssoc[idForm]['FechaLimite'] === null || formsAssoc[idForm]['FechaLimite'] === "") {
        $('#delete-deadline').attr('data-id-form', idForm).hide();
    } else {
        $('#delete-deadline').attr('data-id-form', idForm).show();
    }
    //--- conf para ver el plugin de la fecha ---//
    var $input = $('.datepicker').pickadate();
    picker = $input.pickadate('picker');
    picker.set('min', true);
    picker.set('select', $('#deadline-form-' + idForm).text(), {format: 'yyyy-mm-dd'});
    picker.on({
        set: function () {
            $('#send-deadline').attr('disabled', false).removeClass('disabled');
            $('#send-deadline').attr('data-deadline', picker.get('select', 'yyyy-mm-dd'));
        }
    });
    $('#modal-deadline').modal({dismissible: false}).modal("open");
}
function updateDeadline(idForm, dl) {
    show_loader_wrapper();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url_deadline_update + "/" + idForm,
        data: {FechaLimite: dl},
        success: function (response) {
            hide_loader_wrapper();
            if (response['status']) {
                $('#modal-deadline').modal("close");
                formsAssoc[idForm]['FechaLimite'] = dl;
                var cls = getRowLabelClass(dl, formsAssoc[idForm]['Bloqueado'], 'row');
                changeClassRowLabel('row', cls, idForm);
                cls = getRowLabelClass(dl, formsAssoc[idForm]['Bloqueado'], 'label');
                changeClassRowLabel('label', cls, idForm);
                if (dl === null || dl === "") {
                    $('#deadline-form-' + idForm).text(general_text['sas_agregar']);
                    show_toast('success', section_text.sas_fechaEliminada);
                } else {
                    $('#deadline-form-' + idForm).text(dl);
                    show_toast('success', section_text.sas_fechaActualizada);
                }
            } else {
                $('#error-deadline').text(general_text.sas_errorPeticion).show();
            }
        },
        error: function (jqXHR) {
            hide_loader_wrapper();
            show_modal_error(jqXHR.responseText);
        }
    });
}
/**
 * cambia el status de las formas para hecerlas obligatorias o no y también para cerrarlas o abrirlas
 */
function changeStatusForm(idForm, status, type) {
    show_loader_wrapper();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url_status_update,
        data: {idForma: idForm, status: status, type: type},
        success: function (response, textStatus, jqXHR) {
            if (response['status']) {
                hide_loader_wrapper();
                var rowForm = $('#form-' + idForm);
                if (type === "mandatory") {
                    formsAssoc[idForm]['ObligatorioOpcional'] = parseInt(status);
                    if (status === "1") {//forma obligatoria
                        rowForm.removeClass('optional').addClass('obligatory');
                        $('#mandatory-' + idForm).removeClass('no-mandatory fa-square-o gray-text').addClass('mandatory fa-check-square-o red-text').attr('data-tooltip', section_text['sas_noObligatoria']).attr('data-status', "0");
                        show_toast('success', section_text['sas_formaYaObligatoria']);
                    } else {//forma opcional
                        rowForm.removeClass('obligatory').addClass('optional');
                        $('#mandatory-' + idForm).removeClass('mandatory fa-check-square-o red-text').addClass('no-mandatory no-mandatory fa-square-o gray-text').attr('data-tooltip', section_text['sas_hacerObligatoria']).attr('data-status', "1");
                        show_toast('success', section_text['sas_formaYaNoObligatoria']);
                    }
                } else if (type === "lock") {
                    formsAssoc[idForm]['Bloqueado'] = parseInt(status);
                    if (status === "1") {//forma cerrada
                        rowForm.removeClass('form-open').addClass('form-close');
                        show_toast('success', section_text['sas_formaYaCerrada']);
                        $('#lock-' + idForm).removeClass('fa-unlock-alt geen-text').addClass('fa-lock orange-text').attr('data-tooltip', section_text['sas_abrirForma']).attr('data-status', "0");
                    } else {//forma abierta
                        rowForm.removeClass('form-close').addClass('form-open');
                        show_toast('success', section_text['sas_formaYaAbierta']);
                        $('#lock-' + idForm).removeClass('fa-lock orange-text').addClass('fa-unlock-alt green-text').attr('data-tooltip', section_text['sas_cerrarForma']).attr('data-status', "1");
                    }
                    if (!rowForm.hasClass('no-deadline')) {
                        var dl = $('#deadline-form-' + idForm).text();
                        var cls = getRowLabelClass(dl, formsAssoc[idForm]['Bloqueado'], 'row');
                        changeClassRowLabel('row', cls, idForm);
                        rowForm.addClass(cls);
                        cls = getRowLabelClass(dl, formsAssoc[idForm]['Bloqueado'], 'label');
                        changeClassRowLabel('label', cls, idForm);
                    }
                }
            } else {
                hide_loader_wrapper();
                show_modal_error(response.data);
            }
        },
        error: function (jqXHR, textStatus, error) {
            hide_loader_wrapper();
            show_modal_error(jqXHR.responseText);
        }
    });
}
/**
 * Acciones para subir el PDF
 */
function PDFActions(e) {
    var id = parseInt(e.attr('data-id'));
    var idiom = e.attr('data-lang');
    var d = new Date();
    var url_pdf = viewer + "../../../" + "/administrador/sin_pdf_" + lang + ".pdf";
    if (formsAssoc[id]["Link" + idiom] !== null && formsAssoc[id]["Link" + idiom] !== "") {
        url_pdf = viewer + "../../../" + formsAssoc[id]["Link" + idiom] + "?" + d.getMinutes();
    }
    var last = (formsAssoc[id]['FechaActualizacion' + idiom] !== null && formsAssoc[id]['FechaActualizacion' + idiom] !== "") ? section_text.sas_fechaActualizacion + " " + formsAssoc[id]['FechaActualizacion' + idiom] : section_text.sas_sinPDF;
    var link = formsAssoc[id]["Link" + idiom];
    $('#show-pdf').attr('src', url_pdf).show();
    $('#download-excel').hide();
    if (link !== null && link !== "") {
        var rute = link.split(".");
        if (rute.slice(-1)[0] === "pdf") {
            $('#show-pdf').show();
            $('#download-power').hide();
            $('#download-excel').hide();
        } else if (rute.slice(-1)[0] === "xls" || rute.slice(-1)[0] === "xlsx") {
            $('#show-pdf').hide();
            $('#download-power').hide();
            $('#download-excel').attr('href', url_public + link).show();
        } else {
            $('#show-pdf').hide();
            $('#download-excel').hide();
            $('#download-power').attr('href', url_public + link).show();
        }
    }
    $('#last-update-pdf').text(last);
    $('#update-pdf').attr('data-id', id);
    $('#update-pdf').attr('data-lang', idiom);
    var title = section_text.sas_actualizarPDFTitulo;
    title = title.replace('{{idiom}}', section_text['sas_idioma' + e.attr('data-lang')]);
    title = title.replace('{{form}}', formsAssoc[id]['NombreForma' + lang.toUpperCase()]);
    $('#modal-update-pdf').find('.modal-title').html(title);
    $('#modal-update-pdf').modal("open");
}
/**
 * Acciones para actualizar los links
 */
function linkActions(e) {
    validateLink('form-link');
    var id = e.attr('data-id');
    var idiom = e.attr('data-lang');
    var url_link = formsAssoc[id]["Link" + idiom] !== null ? formsAssoc[id]["Link" + idiom] : "";
    var last = formsAssoc[id]['FechaActualizacion' + idiom] !== null ? section_text.sas_fechaActualizacion + " " + formsAssoc[id]['FechaActualizacion' + idiom] : section_text.sas_sinLink
    $('#last-update-link').text(last);
    $('#link').val(url_link);
    if (url_link !== "") {
        $('label[for="link"]').addClass('active');
        $('#form-link').valid();
    } else {
        $('label[for="link"]').removeClass('active');
    }
    $('#update-link').attr('data-id', id);
    $('#update-link').attr('data-lang', idiom);
    var title = section_text.sas_actualizarLinkTitulo;
    title = title.replace('{{idiom}}', section_text['sas_idioma' + e.attr('data-lang')]);
    title = title.replace('{{form}}', formsAssoc[id]['NombreForma' + lang.toUpperCase()]);
    $('#modal-update-link').find('.modal-title').html(title);
    $('#modal-update-link').modal({dismissible: false}).modal("open");
}
/**
 * valida la forma para el link externo
 */
function validateLink(form) {
    $('#' + form).validate({
        errorClass: "invalid",
        validClass: "valid",
        errorElement: "span",
        errorPlacement: function (error, element) {
            error.insertAfter(element.parent()).addClass('right');
        },
        highlight: function (element, errorClass, validClass) {
            $(element).parent().addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).parent().addClass(validClass).removeClass(errorClass);
        },
        rules: {
            link: {
                required: true,
                webPage: true
            }
        },
        success: function (label) {
            label.html('<i class="fa fa-check fa-1x green-text"></i>');
        },
        submitHandler: function () {
            show_loader_wrapper();
            linkSubmitAjax();
        }
    });
}
/**
 * ajax para actualizar el link externo
 */
function linkSubmitAjax() {
    $.ajax({
        url: url_save_link,
        type: "POST",
        dataType: "json",
        data: {
            idForma: $('#update-link').attr('data-id'),
            idioma: $('#update-link').attr('data-lang'),
            Link: $('#link').val(),
            FechaActualizacion: getDateUp()
        },
        success: function (response, textStatus, jqXHR) {
            hide_loader_wrapper();
            if (response.status) {
                $('.open').each(function (i, ele) {
                    $(ele).modal("close");
                });
                formsAssoc[response.data['idForma']]['Link' + response.data['idioma']] = response.data['Link'];
                formsAssoc[response.data['idForma']]['FechaActualizacion' + response.data['idioma']] = response.data['FechaActualizacion'];
                $('#' + response.data['idForma'] + '-' + response.data['idioma']).attr('data-tooltip', section_text.sas_ultimaActulizacion + " " + response.data['FechaActualizacion']).removeClass('gray-text').addClass('purple-text');
                $('#last-update-link').text(section_text.sas_fechaActualizacion + " " + response.data['FechaActualizacion']);
                show_toast('success', general_text.sas_guardoExito);
            }

        },
        error: function (jqXHR, textStatus, error) {
            hide_loader_wrapper();
            show_modal_error(jqXHR.responseText);
        }
    });
}
/**
 * Accione para mostrar las gráficas
 */
function chartsActions(e) {
    var id = e.attr('data-id');
    ajaxChart(id);
}
/**
 * Acciones para editar la seccion
 */
function editSection(id) {
    $('#idSeccionFormatos').val(id);
    var section = sections[id];
    var sect = [];
    $.each(idioms, function (i, idiom) {
        $('#Nombre' + idiom).val(section['Nombre' + idiom]);
        sect[idiom] = document.getElementById('Nombre' + idiom + '_ifr');
        sect[idiom].contentDocument.body.innerHTML = section['Nombre' + idiom];
    });
    $('#ColorLetra').val(section['ColorLetra']);
    $('#ColorLetra').colorPicker(setColorConf('color', '.color-picker'));
    $('.color-picker').css('color', section['ColorLetra']);
    $('#ColorFondo').val(section['ColorFondo']);
    $('#ColorFondo').colorPicker(setColorConf('background-color', '.background-picker'));
    $('.background-picker').css('background-color', section['ColorFondo']);
    $('#VisibleWeb').prop('checked', false);
    $('#CreacionSAS').val(section['CreacionSAS']);
    if (parseInt(section['VisibleWeb']) === 1) {
        $('#VisibleWeb').prop('checked', true);
    } else {
        $('#VisibleWeb').prop('checked', false);
    }
    if (parseInt(section['HabilitarSeccion']) === 1) {
        $('#HabilitarSeccion').prop('checked', true);
    } else {
        $('#HabilitarSeccion').prop('checked', false);
    }
    var d = new Date();
    var img = (section['Imagen'] !== null && section['Imagen'] !== "") ? section['Imagen'] : "images/no-image.png";
    $('#content-dropzone img').attr('src', url_public + img + "?" + d.getTime());
    $('#card-demo .card-title').html(section['Nombre' + lang.toLocaleUpperCase()]);
    $('#modal-add-section').modal({dismissible: false}).modal("open");
    $('#CreacionSAS').val(section['CreacionSAS']);
    $('#Orden').val(section['Orden']);
    if (parseInt(section['CreacionSAS']) === 1) {
        $('#btn-delete-section').show().attr('data-id', section['idSeccionFormatos']);
    } else {
        $('#btn-delete-section').hide();
    }
}
/**
 * Acciones que se ejecutan al abrir una nueva sección
 */
function newSectionActions() {
    $('#idSeccionFormatos').val(0);
    var sect = [];
    $.each(idioms, function (i, idiom) {
        $('#Nombre' + idiom).val(general_text.sas_titulo);
        sect[idiom] = document.getElementById('Nombre' + idiom + '_ifr');
        sect[idiom].contentDocument.body.innerHTML = general_text.sas_titulo;
    });
    $('#ColorLetra').val('rgb(0, 0, 0)');
    $('.color-picker').css('color', 'rgb(0, 0, 0)');
    $('#ColorLetra').colorPicker(setColorConf('color', '.color-picker'));
    $('#ColorFondo').val('rgba(235, 235, 235, 1)');
    $('.background-picker').css('background-color', 'rgba(235, 235, 235, 1)');
    $('#ColorFondo').colorPicker(setColorConf('background-color', '.background-picker'));
    $('#VisibleWeb').prop('checked', true);
    $('#HabilitarSeccion').prop('checked', true);
    var d = new Date();
    $('#content-dropzone img').attr('src', url_public + "images/no-image.png" + "?" + d.getTime());
    $('#card-demo .card-title').html(general_text.sas_titulo);
    $('#Orden').val(orderSections);
    $('#CreacionSAS').val(1);
    $('#btn-delete-section').hide();
    $('#modal-add-section').modal({dismissible: false}).modal("open");
}
/**
 * Valida el formulario de la seccion
 */
function validateSection(url) {
    $("#form-add-section").validate({
        errorClass: "invalid left",
        validClass: "valid right",
        errorElement: "span",
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
        success: function (label) {
            label.html('<i class="fa fa-check fa-1x green-text"></i>');
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            sectionSubmitAjax($(form).serializeArray(), url);
        }
    });
}
/**
 * Envia el formulario por medio de ajax
 */
function sectionSubmitAjax(form, url) {
    $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        data: form,
        success: function (response) {
            hide_loader_wrapper();
            if (response.status) {
                $('.open').modal("close");
                var section = response.data;
                sections[section['idSeccionFormatos']] = section;
                if ($('#idSeccionFormatos').val() === "0") {
                    var tb = constructSectionTable(section);
                    $("#cover-forms-table").append(tb);
                    orderSections++;
                } else {
                    $("#header-" + section['idSeccionFormatos']).css('background-color', $('#ColorFondo').val());
                    $("#header-" + section['idSeccionFormatos']).find('h5').html($("#Nombre" + lang.toUpperCase()).val());
                }
                show_toast('success', general_text.sas_guardoExito);
            }

        },
        error: function (jqXHR, textStatus, error) {
            hide_loader_wrapper();
            show_modal_error(jqXHR.responseText);
        }
    });
}
/**
 * Elimina la seccion que se creo desde el sal
 */
function deleteSection(id) {
    show_loader_wrapper();
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: url_delete_section + "/" + id,
        success: function (response) {
            hide_loader_wrapper();
            $('#tbody-' + response['idSeccionFormatos']).remove();
            delete sections[response['idSeccionFormatos']];
            //$('select').material_select('destroy');
            $('#filter-option option[value=section-' + response['idSeccionFormatos'] + ']').remove();
            $('#FO_idSeccionFormatos option[value=' + response['idSeccionFormatos'] + ']').remove();
            //$('select').material_select();
            $('#modal-add-section').modal("close");
            show_toast("success", general_text.sas_eliminoExito);
        },
        error: function (request) {
            hide_loader_wrapper();
            show_toast("danger", general_text.sas_errorInterno + "<br>" + request.responseText);
        }
    });
}
/**
 * Acciones que se realizan al agregar una forma
 */
function newFormActions() {
    clearForm('form-add');
    $('#FO_OrdenDespliegue').val(0);
    $('#FO_CreacionSAS').val(1);
    $('#FO_FormaVisibleWeb').prop('checked', true);
    $('#FO_Habilitado').prop('checked', true);
    $('#FO_idSeccionFormatos').on('change', function () {
        var id = $(this).val();
        $('#FO_OrdenDespliegue').val($('.section-' + id).length);
    });
    var $input = $('#fecha-limite').pickadate();
    picker = $input.pickadate('picker');
    picker.set('min', true);
    picker.on({
        set: function () {
            $('#FO_FechaLimite').val(picker.get('select', 'yyyy-mm-dd'));
        }
    });
    $('#modal-add-form').modal({dismissible: false}).modal("open");
    $('#form-add').validate().resetForm();
}
/**
 * Valida el formulario para una forma nueva
 */
function validateNewForm() {
    $("#form-add").validate({
        errorClass: "invalid",
        focusInvalid: true,
        errorElement: "div",
        ignore: ":hidden:not(select)",
        rules: {
            FO_TipoLink: {
                required: true
            },
            FO_ObligatorioOpcional: {
                required: true
            },
            FO_DescripcionEN: {
                maxlength: 250
            },
            FO_DescripcionES: {
                maxlength: 250
            },
            FO_DescripcionFR: {
                maxlength: 250
            },
            FO_DescripcionPT: {
                maxlength: 250
            }
        },
        errorPlacement: function (error, element) {
            $(error).addClass('col s12');
            if ($(element).attr('type') === "radio") {
                error.css({'margin-top': "-15px", "margin-bottom": "10px"});
                error.css('opacity', 1);
                element.parent().parent().addClass('error-checked');
                error.appendTo(element.parent().parent());
                return;
            }
            error.css({'margin-top': "-20px", "margin-bottom": "10px"});
            if ($(element).prop('tagName') === "SELECT") {
                error.insertAfter($(element).parent());
                return;
            }
            error.insertAfter(element);
        },
        unhighlight: function (element, errorClass, validClass) {
            if ($(element).attr('type') === "radio") {
                $(element).parent().parent().removeClass('error-checked');
            }
            $(element).removeClass(errorClass);
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            formSubmitAjax($(form).serializeArray());
        }
    });
}
/**
 * Envia los datos del formulario
 */
function formSubmitAjax(post) {
    $.ajax({
        url: url_add_form,
        type: "post",
        dataType: "json",
        data: post,
        success: function (response) {
            hide_loader_wrapper();
            $('#modal-add-form').modal("close");
            $("#modal-update-form").modal("close");
            response.data['Bloqueado'] = 0;
            response.data['completed'] = 0;
            response.data['incompleted'] = 0;
            response.data['no_interest'] = 0;
            formsAssoc[response.data['idForma']] = response.data;
            var tr = buildFormTable(formsAssoc[response.data['idForma']]);
            var idSeccionFormatos = $('#FO_idSeccionFormatos').val();
            $(tr).find('.tooltipped').tooltip({"delay": 50, "position": "top"});
            $(tr).appendTo('#tbody-' + idSeccionFormatos);
            show_toast('success', general_text.sas_guardoExito);
        },
        error: function (jqXHR) {
            hide_loader_wrapper();
            show_modal_error(general_text.sas_errorInterno + "<br>" + jqXHR.responseText);
        }
    });
}
/**
 * Edita el nombre de la forma
 */
function editFormaAction(element) {
    var id = element.attr('data-form-id');
    var form = formsAssoc[id];
    var title = section_text.sas_editarForma.replace('%form%', form['NombreForma' + lang.toLocaleUpperCase()]);
    $("#modal-update-form .modal-title").html(title);
    $('#Identificador').val(form['Identificador']).next().addClass('active');
    $.each(idioms, function (i, idiom) {
        $('#NombreForma' + idiom).val(form['NombreForma' + idiom]).next().addClass('active');
        $('#Descripcion' + idiom).val(form['Descripcion' + idiom]).next().addClass('active');
    });
    $('#FormaVisibleWeb').prop('checked', true);
    if (isNaN(parseInt(form['FormaVisibleWeb'])) === false && parseInt(form['FormaVisibleWeb']) === 0) {
        $('#FormaVisibleWeb').prop('checked', false);
    }
    $('#Habilitado').prop('checked', true);
    if (isNaN(parseInt(form['Habilitado'])) === false && parseInt(form['Habilitado']) === 0) {
        $('#Habilitado').prop('checked', false);
    }
    if (parseInt(form['CreacionSAS']) === 1) {
        $('#btn-delete-form').show().attr('data-id', id);
    } else {
        $('#btn-delete-form').hide();
    }
    $('#idForma').val(id);
    $("#modal-update-form").modal({dismissible: false}).modal("open");
}
/**
 * Valida el formulario para editar una forma
 */
function validateFormEdit() {
    $('#form-update').validate({
        errorClass: "invalid left",
        validClass: "valid right",
        errorElement: "span",
        errorPlacement: function (error, element) {
            error.insertAfter(element.parent());
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).addClass(validClass).removeClass(errorClass);
        },
        rules: {
            DescripcionEN: {
                maxlength: 250
            },
            DescripcionES: {
                maxlength: 250
            },
            DescripcionFR: {
                maxlength: 250
            },
            DescripcionPT: {
                maxlength: 250
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            formAjax($(form).serializeArray());
        }
    });
}
/**
 * Envia los datos de formulario para guardar los datos de la forma
 */
function formAjax(post) {
    $.ajax({
        url: url_edit_form,
        type: "post",
        dataType: "json",
        data: post,
        success: function (response) {
            hide_loader_wrapper();
            $("#modal-update-form").modal("close");
            $('#form-' + response.data['idForma']).find('td:nth-child(2)').html(response.data['Identificador'] + " - " + response.data['NombreForma' + lang.toLocaleUpperCase()])
            $.each(idioms, function (i, idiom) {
                formsAssoc[response.data['idForma']]['NombreForma' + idiom] = response.data['NombreForma' + idiom];
                formsAssoc[response.data['idForma']]['Descripcion' + idiom] = response.data['Descripcion' + idiom];
            });
            formsAssoc[response.data['idForma']]['Identificador'] = response.data['Identificador'];
            formsAssoc[response.data['idForma']]['FormaVisibleWeb'] = response.data['FormaVisibleWeb'];
            show_toast('success', general_text.sas_guardoExito);
        },
        error: function (jqXHR) {
            hide_loader_wrapper();
            show_modal_error(general_text.sas_errorInterno + "<br>" + jqXHR.responseText);
        }
    });
}
/**
 * Ajax para eliminar una forma por el metodo Get con pasando el id
 */
function deleteForm(id) {
    show_loader_wrapper();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url_delete_form + "/" + id,
        success: function (response) {
            hide_loader_wrapper();
            $("#modal-update-form").modal("close");
            $('#form-' + response['idForma']).remove();
            delete formsAssoc[response['idForma']];
            show_toast("success", general_text.sas_eliminoExito);
        },
        error: function (request) {
            hide_loader_wrapper();
            show_toast("danger", general_text.sas_errorInterno + "<br>" + request.responseText);
        }
    });
}
/**
 * Agrega la clase que le corresponde a la fila
 */
function getRowLabelClass(deadline, lock, type) {
    if ((deadline === null || deadline === "") && type === "row") {
        return "no-deadline";
    }
    if ((deadline === null || deadline === "") && type === "label") {
        return 'blue-grey';
    }
    var d = new Date();
    var day = (d.getDate() < 10 ? '0' : '') + d.getDate();
    var month = ((d.getMonth() + 1) < 10 ? '0' : '') + (d.getMonth() + 1);
    var year = d.getFullYear();
    var date = year + "-" + month + "-" + day;
    var fl = deadline.split('-');
    if (type === "row") {
        if (date > deadline && (lock === 0)) {//cuando ya esta cerrada y paso su fecha limite
            return "end red lighten-5";
        }
        if (date <= deadline && lock === 1) {//cuando esta cerrada y aún no pasa su fecha limite
            return "orange lighten-5";
        }
    } else if (type === "label") {
        if (date > deadline && lock === 0) {
            return 'red';
        }
        if (date > deadline && lock === 1) {
            return 'grey';
        }
        if ((d.getDate() + 2) > daysInMonth(month, year)) {//son diferentes meses para tres días o menos
            var dayNextMonth = (d.getDate() + 2) - daysInMonth(month, year);
            if (year === parseInt(fl[0]) && (parseInt(month) + 1) === parseInt(fl[1]) && dayNextMonth >= parseInt(fl[2])) {
                return 'amber';
            }
        }
        if ((d.getDate() + 2) <= daysInMonth(month, year)) {//es el del mismo mes
            if (year === parseInt(fl[0]) && parseInt(month) === parseInt(fl[1]) && (parseInt(fl[2]) - 3) <= parseInt(day) && parseInt(day) <= parseInt(fl[2])) {
                return 'amber';
            }
        }
        if ((d.getDate() + 4) > daysInMonth(month, year)) {//son diferentes meses para cinco días o menos
            var dayNextMonth = (d.getDate() + 4) - daysInMonth(month, year);
            if (year === parseInt(fl[0]) && (parseInt(month) + 1) === parseInt(fl[1]) && dayNextMonth >= parseInt(fl[2])) {
                return 'blue';
            }
        }
        if ((d.getDate() + 4) <= daysInMonth(month, year)) {//es el del mismo mes
            if (year === parseInt(fl[0]) && parseInt(month) === parseInt(fl[1]) && (parseInt(fl[2]) - 5) <= parseInt(day) && parseInt(day) <= parseInt(fl[2])) {
                return 'blue';
            }
        }
        return 'green';
    }
}
function daysInMonth(month, year) {
    return new Date(year, month, 0).getDate();
}
/**
 * Cambia el contenido de la celda según lo que sea
 */
function changeClassRowLabel(type, cls, idForm) {
    var row = $('#form-' + idForm);
    if (type === "row") {
        row.removeClass('end red orange lighten-5 no-deadline next-deadline time');
        row.addClass(cls);
        switch (cls) {
            case "end red lighten-5":
                var cell = row.find('td:nth-child(2)');
                var spn = '<span class="tooltipped info-open-form btn-flat waves-effect waves-gray red-text fa fa-question fa-1x" data-tooltip="' + section_text['sas_formaAbiertaNoDisponible'] + '" style="cursor:help"></span>';
                cell.html(cell.text() + spn);
                $(cell).find('.tooltipped').tooltip({delay: 50});
                break;
            case "orange lighten-5":
                var cell = row.find('td:nth-child(2)');
                var spn = '<span class="tooltipped info-open-form btn-flat waves-effect waves-gray orange-text fa fa-question fa-1x" data-tooltip="' + section_text['sas_formaCerradaDisponible'] + '" style="cursor:help"></span>';
                cell.html(cell.text() + spn);
                $(cell).find('.tooltipped').tooltip({delay: 50});
                break;
            default:
                row.find('td:nth-child(2)').text(formsAssoc[idForm]['NombreForma' + lang.toUpperCase()]);
                break;
        }
    } else if (type === "label") {
        var spn = $('#deadline-form-' + idForm).removeClass('red amber blue green blue-grey green');
        spn.addClass(cls);
        $(spn).tooltip('remove');
        switch (cls) {
            case "red":
                spn.attr('data-tooltip', section_text['sas_formaCerradaDisponible']);
                break;
            case "grey":
                spn.attr('data-tooltip', section_text['sas_formaClausurada']);
                row.addClass('end');
                break;
            case "blue-grey":
                spn.attr('data-tooltip', section_text['sas_definirFecha']);
                row.addClass('no-deadline');
                $(spn).text(general_text['sas_agregar']);
                break;
            case "amber":
                row.addClass('next-deadline');
                spn.attr('data-tooltip', section_text['sas_tresDias']);
                break;
            case "blue":
                row.addClass('next-deadline');
                $('#deadline-form-' + idForm).attr("data-tooltip", section_text['sas_cincoDias']);
                break;
            case "green":
                row.addClass('time');
                $('#deadline-form-' + idForm).attr("data-tooltip", section_text['sas_editarFecha']);
                break;
            default:
                show_modal_error('Error to change class at deadline :(');
                break;
        }
        $(spn).tooltip({delay: 50});
    }
}
/**
 * regresa el json con las configuraciones necesarias para el plugin
 */
function setColorConf(style, elements) {
    return    {
        customBG: '#222',
        margin: '4px -2px 0',
        doRender: 'div div',
        preventFocus: true,
        animationSpeed: 150,
        // demo on how to make plugins... mobile support plugin
        buildCallback: function ($elm) {
            this.$colorPatch = $elm.prepend('<div class="cp-disp">').find('.cp-disp');
            $(this).on('click', function (e) {
                e.preventDefault && e.preventDefault();
            });
        },
        cssAddon: // could also be in a css file instead
                '.cp-disp{padding:10px; margin-bottom:6px; font-size:19px; height:40px; line-height:20px}' +
                '.cp-xy-slider{width:200px; height:200px;}' +
                '.cp-xy-cursor{width:16px; height:16px; border-width:2px; margin:-8px}' +
                '.cp-z-slider{height:200px; width:40px; cursor: n-resize;}' +
                '.cp-z-cursor{border-width:8px; margin-top:-8px;}' +
                '.cp-alpha{height:40px; cursor: e-resize;}' +
                '.cp-alpha-cursor{border-width: 8px; margin-left:-8px;}',
        renderCallback: function ($elm, toggled) {
            if (!toggled) {
                $(elements).css(style, $elm.val());
            }
        }
    };
}
/**
 * limpia el formulario
 */
function clearForm(idForm) {
    details = {};
    $("#" + idForm).find('input, select, textarea').each(function (index, value) {
        switch (this.tagName) {
            case "INPUT":
                var typeInput = $(this).attr('type');
                switch (typeInput) {
                    case "radio":
                        if ($(this).is(':checked')) {
                            $(this).prop('checked', false);
                        }
                        break;
                    case "checkbox" :
                        if ($(this).is(':checked')) {
                            $(this).prop('checked', false);
                        }
                        break;
                    case "hidden" :
                        break;
                    default:
                        $(this).val('').next().removeClass('active');
                        break;
                }
                break;
            case "TEXTAREA":
                $(this).val('').next().removeClass('active');
                break;
            case "SELECT":
                //$(this).val("").change().material_select();
                break;
            default:
                console.log('No find element ' + this);
        }
    });
    return JSON.stringify(details);
}
/**
 * regresa el día con la hora del navegador en que se modificó
 */
function getDateUp() {
    var d = new Date();
    var day = (d.getDate() < 10 ? '0' : '') + d.getDate();
    var month = ((d.getMonth() + 1) < 10 ? '0' : '') + (d.getMonth() + 1);
    var year = d.getFullYear();
    var hours = d.getHours();
    var minutes = d.getMinutes();
    var seconds = d.getSeconds();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;
    var strTime = hours + ':' + minutes + ':' + seconds + " " + ampm;
    var date = year + "-" + month + "-" + day + " " + strTime;
    return date;
}
/**
 *
 * @param {string} action muesta o esconde las tablas
 */
function formsList(action) {
    if (action !== undefined) {
        if (action == "show") {
            $("#form-general-detail").hide();
            $("#forms-list").fadeIn();
        } else if (action == "hide") {
            $("#form-general-detail").fadeIn();
            $("#forms-list").hide();
        }
    }
}