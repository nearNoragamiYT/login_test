var listado_categorias = "";
$(document).ready(function () {
    initComercialCompany();
});

function initComercialCompany() {
    $("#empresa-comercial").attr("class", "active");

    setComercialData();

    validateSaveComercialDataForm();

    $(document).on("click", ".company-menu>div>ul>li", function () {
        show_loader_wrapper();
    });

    $(".progress-estado").hide();

    $("#saveComercialData").on("click", function () {
        $("#save-comercial-form").submit();
    });

    $("#primary-category").on("change", function () {
        var parent = $(this).val();
        $('#second-category').prop('disabled', false);
        var html = "<option value=''>" + general_text['sas_seleccionaOpcion'] + "</option>";
        $.each(list_categoies, function (key, value) {
            if (parseInt(value['idPadre']) === parseInt(parent)) {
                html += '<option value="' + value['idCategoria'] + '">' + value['NombreCategoria' + lang.toUpperCase()] + '</option>';
            }
        });
        $('#second-category').html(html);
        $('#second-category').trigger('change', {
            value: $('#principal').val()
        });
    });

    $("#add-category").on("click", function () {
        if ($("#second-category").val()) {
            if ($(".chip").length >= 5) {
                show_alert("warning", section_text.sas_limiteCategorias);
                return;
            }
            if ($(".chip[id-record= " + $("#second-category").val() + "]").length > 0) {
                show_alert("warning", section_text.sas_categoriaDuplicada);
                return;
            }
            if (list_categoies[$("#second-category").val()]['ActivaOtro'] == "1") {
                $("#otro").show();
                $("#other").attr("record-id", $("#second-category").val());
                $("#other").attr("padres", $("#primary-category").val() + ',' + $("#second-category").val());
            } else {
                var valor = $("#second-category option:selected").text();
                var row = '<div id-record = "' + $("#second-category").val() + '" class="chip" padres="' + $("#primary-category").val() + ',' + $("#second-category").val() + '">' + valor + '<i class="close material-icons" >close</i></div>';
                $("#category-wrapper").append(row);
            }
        } else {
            show_alert("warning", section_text.sas_asegureseSeleccionarCategoria);
        }
    });

    $("#other-category").on("click", function () {
        if ($(".chip").length >= 5) {
            show_alert("warning", section_text.sas_limiteCategorias);
            return;
        }
        if ($(".chip[id-record= " + $("#other").attr("record-id") + "]").length > 0) {
            show_alert("warning", section_text.sas_categoriaDuplicada);
            return;
        }
        var valor = $("#other").val();
        var row = '<div id-record = "' + $("#other").attr("record-id") + '" class="chip" active-other="1" padres="' + $("#other").attr("padres") + '">' + section_text.sas_otro + ': ' + valor + '<i class="close material-icons" >close</i></div>';
        $("#category-wrapper").append(row);
        $("#otro").hide();
    });

    $('#parent').select2({
        placeholder: general_text.sas_seleccionaOpcion
    });
}

$("#DC_idPais").on("change", function () {
    $(".progress-estado").show();
    var id, loader = $(this).attr('loader-element');
    getEstados(id, loader);
});

function setComercialData() {
    $("#idEmpresa").val(comercialData["idEmpresa"]);
    $("#DC_NombreComercial").val(comercialData["DC_NombreComercial"]);
    $("#idEmpresaUUID").val(comercialData["idEmpresaUUID"]);
    $("#CodigoCliente").val(comercialData["CodigoCliente"]);

    $("#idEvento").val(comercialData["idEvento"]).change();
    $("#idPabellon").val(comercialData["idPabellon"]).change();
    $("#idEmpresaTipo").val(comercialData["idEmpresaTipo"]).change();
    $("#DC_idPais").val(comercialData["DC_idPais"]).change();
    setTimeout(function () {
        $("#DC_idEstado").val(comercialData['DC_idEstado']).change();
    }, 1500);

    $("#DC_Ciudad").val(comercialData["DC_Ciudad"]);
    $("#DC_CodigoPostal").val(comercialData["DC_CodigoPostal"]);
    $("#DC_Colonia").val(comercialData["DC_Colonia"]);
    $("#DC_CalleNum").val(comercialData["DC_CalleNum"]);
    $("#DC_TelefonoAreaPais").val(comercialData["DC_TelefonoAreaPais"]);
    $("#DC_TelefonoAreaCiudad").val(comercialData["DC_TelefonoAreaCiudad"]);
    $("#DC_Telefono").val(comercialData["DC_Telefono"]);
    $("#DC_TelefonoExtension").val(comercialData["DC_TelefonoExtension"]);
    $("#DC_PaginaWeb").val(comercialData["DC_PaginaWeb"]);
    $("#DC_DescripcionES").val(comercialData["DC_DescripcionES"]);
    $("#DC_DescripcionEN").val(comercialData["DC_DescripcionEN"]);
    $("#DC_DescripcionEN").val(comercialData["DC_DescripcionEN"]);
    if (comercialData["VisibleDirectorio"])
        $("#VisibleDirectorio").prop("checked", true);
    else
        $("#VisibleDirectorio").prop("checked", false);
    $("#save-comercial-form input[type='text'], textarea").removeClass('valid').next().addClass('active');
}

function validateSaveComercialDataForm() {
    $("#save-comercial-form").validate({
        rules: {
            'DC_NombreComercial': {
                required: true,
                maxlength: 100
            },
            'idEvento': {
                required: true,
            },
            'DC_idPais': {
                required: true
            },
            'DC_CodigoPostal': {
                required: true,
                maxlength: 15,
            },
            'DC_Telefono': {
                required: true
            },
            'DC_DescripcionES': {
                maxlength: 300,
            },
            'DC_DescripcionEN': {
                maxlength: 300,
            }
        },
        messages: {
            'DC_NombreComercial': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'idEvento': {
                required: general_text.sas_requerido,
            },
            'DC_idPais': {
                required: general_text.sas_requerido,
            },
            'DC_CodigoPostal': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
            },
            'DC_Telefono': {
                required: general_text.sas_requerido
            },
            'DC_DescripcionES': {
                maxlength: general_text.sas_ingresaMaxCaracteres
            },
            'DC_DescripcionEN': {
                maxlength: general_text.sas_ingresaMaxCaracteres
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
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            var post = $('#save-comercial-form').serialize();
            saveComercialData(post);
            return;
        }
    });
}
function allReplace(s) {
    s = s.replace("close", "");
    s = s.replace("Otra:", "");
    s = s.replace("Otro:", "");
    s = s.replace("otro:", "");
    s = s.replace("Other", "");
    return s;
}
function saveComercialData(post) {
    listado_categorias = {};
    $.each($("#category-wrapper .chip"), function (i, e) {
        var c = $(e).attr("id-record");
        if ($(e).attr("active-other") == "1") {
            listado_categorias[c] = {
                "idCategoria": c,
                "Padres": $(e).attr("padres"),
                "Otro": allReplace($(e).text())
            }
        } else {
            listado_categorias[c] = {
                "idCategoria": c,
                "Padres": $(e).attr("padres"),
                "Otro": ""
            }
        }
    });
    post += '&ListadoCategorias=' + JSON.stringify(listado_categorias);
    if ($("#VisibleDirectorio").is(":checked"))
        post += "&VisibleDirectorio=1"
    else
        post += "&VisibleDirectorio=0"
    $.ajax({
        type: "post",
        url: url_comercial_company_save,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            show_alert("success", general_text.sas_guardoExito);
            if (!response['categorias']['status']) {
                show_alert("danger", response['categorias']['data'][0]['fn_sas_InsertaCategorias']);
                return;
            }
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function getEstados(id, loader) {
    var idPais = $("select#DC_idPais").val();
    var url = url_get_estados.replace("0000", idPais);
    var loader_element = loader;
    showProgressBar(loader_element);
    $.ajax({
        type: "get",
        url: url,
        dataType: 'json',
        beforeSend: function (xhr) {
            setProgressBar(loader_element, "50%");
        },
        success: function (result) {
            setProgressBar(loader_element, "70%");
            if (!result['status']) {
                show_modal_error(result['data']);
                hideProgressBar(loader_element);
                return;
            }
            var estados = result['data'];
            if (Object.keys(estados).length === 0) {
                hideProgressBar(loader_element);
                var html_estado = '<option value="">' + general_text['sas_sinOpcion'] + '</option>';
                $("select#DC_idEstado").html(html_estado);
                return;
            }

            var html_estado = '<option value="">' + general_text['sas_seleccionaOpcion'] + '</option>';

            $.each(estados, function (index, value) {
                html_estado += '<option value="' + value['idEstado'] + '">';
                html_estado += value['Estado'];
                html_estado += '</option>';
            });

            $("select#DC_idEstado").html(html_estado);

            hideProgressBar(loader_element);
        },
        error: function (request, status, error) {
            hideProgressBar(loader_element);
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}
function setProgressBar(progressBar, progress) {
    $(progressBar + " .determinate").attr("style", "width: " + progress);
}
function showProgressBar(progressBar) {
    $('[loader-element="' + progressBar + '"]').attr('disabled', 'disabled');
    $(progressBar + " .determinate").attr("style", "width: 0%");
    $(progressBar + " .progress").fadeIn("fast");
}
function hideProgressBar(progressBar) {
    $(progressBar + " .determinate").attr("style", "width: 100%");
    setTimeout(function () {
        $(progressBar + " .progress").fadeOut("fast");
    }, 250);
    $('[loader-element="' + progressBar + '"]').removeAttr('disabled');
}