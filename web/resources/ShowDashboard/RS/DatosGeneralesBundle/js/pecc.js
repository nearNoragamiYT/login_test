function showProgressBar(progressBar) {
    $('[loader-element="' + progressBar + '"]').attr('disabled', 'disabled');
    $(progressBar + " .progress").fadeIn("fast");
}

function hideProgressBar(progressBar) {
    setTimeout(function () {
        $(progressBar + " .progress").fadeOut("fast");
    }, 250);
    $('[loader-element="' + progressBar + '"]').removeAttr('disabled');
}

$("select#DE_idPais").change(getEstados);

function getEstados() {
    var idPais = $("select#DE_idPais").val();
    var url = url_get_estados.replace("0000", idPais);
    var loader_element = $(this).attr('loader-element');
    showProgressBar(loader_element);
    $.ajax({
        type: "get",
        url: url,
        dataType: 'json',
        success: function (result) {
            if (!result['status']) {
                show_modal_error(result['data']);
                hideProgressBar(loader_element);
                return;
            }
            var estados = result['data'];
            if (Object.keys(estados).length === 0) {
                hideProgressBar(loader_element);
                var html_estado = '<option value="">' + general_text['sas_sinOpciones'] + '</option>';
                $("select#DE_idEstado").html(html_estado);
                return;
            }

            var html_estado = '<option value="">' + general_text['sas_seleccionaOpcion'] + '</option>';
            $.each(estados, function (index, value) {
                html_estado += '<option value="' + value['idEstado'] + '">';
                html_estado += value['Estado'];
                html_estado += '</option>';
            });
            $("select#DE_idEstado").html(html_estado);
            hideProgressBar(loader_element);
        },
        error: function (request, status, error) {
            hideProgressBar(loader_element);
            show_modal_error(request.responseText);
        }
    });
}

/* Busqueda CP */
var searchInterval;
$("#DE_CP").keyup(function () {
    clearTimeout(searchInterval);
    searchInterval = setTimeout(function () {
        if ($("#DE_CP").val().length < 4) {
            return;
        }
        getPECC();
    }, 1000);
});

$("#DE_CP").focusout(removePECC);

function removePECC() {
    $('.autocomplete-content').slideDown("fast", function () {
        $('.autocomplete-content').remove();
    });
}

function getPECC() {
    $('.autocomplete-content').remove();
    var codigoPostal = $("#DE_CP").val().trim();
    var url = url_get_pecc.replace("00000", codigoPostal);
    var loader_element = $("#DE_CP").attr('loader-element');
    showProgressBar(loader_element);
    $.ajax({
        type: "get",
        url: url,
        dataType: 'json',
        success: function (result) {
            if (!result['status']) {
                show_modal_error(result['data']);
                hideProgressBar(loader_element);
                return;
            }
            var codigos = result['data'];
            if (codigos.length === 0) {
                hideProgressBar(loader_element);
                return;
            }

            var ul = $('<ul/>', {
                //"src": $('#img_back').val(),
                "class": 'dropdown-content autocomplete-content'
            });
            $.each(codigos, function (index, value) {
                var li = $('<li/>').data('pecc', value).click(setPECCValues).appendTo(ul);
                $('<span/>', {"text": value['label']}).appendTo(li);
            });
            $("label[for='DE_CP']").after(ul);
            hideProgressBar(loader_element);
        },
        error: function (request, status, error) {
            hideProgressBar(loader_element);
            show_modal_error(request.responseText);
        }
    });
}

function setPECCValues() {
    if (!isset($(this).data('pecc'))) {
        return;
    }
    $('#DE_CP').focus();
    var pecc = $(this).data('pecc');
    console.log(pecc);
    if (parseInt($("#DE_idPais").val()) !== parseInt(pecc['idPais'])) {
        $('#DE_idPais').val(pecc['idPais']).change();
    }
    setTimeout(function () {
        $('#DE_idEstado').val(pecc['idEstado']).change().focusout();
        $('#DE_Direccion').focus();
    }, 1500);
    $('#DE_CP').val(pecc['CodigoPostal']).change().focusout();
    $('#DE_Ciudad').val(pecc['Ciudad']).change().focusout();
    $('#DE_Colonia').val(pecc['Colonia']).change().focusout();
    $('#DE_Direccion').focus();
    removePECC();
}