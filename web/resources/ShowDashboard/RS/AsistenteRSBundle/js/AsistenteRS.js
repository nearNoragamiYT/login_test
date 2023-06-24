var action;
$(document).ready(function () {
    $('select').material_select();
    $('#Descripcion').val('');
    $('#Descripcion').trigger('autoresize');
    $('#rowEn').hide();
    $('#rowEn2').hide();
    $("#enCheckbox").trigger("change");
    $('.color').colorPicker();
//    document.getElementById("ColorHeader").value = "rgb(255, 255, 255)";
//    document.getElementById("ColorButton").value = "rgb(255, 255, 255)";
//    document.getElementById("ColorHeader").style.backgroundColor = "rgb(255, 255, 255)";
//    document.getElementById("ColorButton").style.backgroundColor = "rgb(255, 255, 255)";

    $('#ColorButton').val(ColorButton);
    $('#ColorButton').colorPicker(setColorConf('color', '.color-picker'));
    $('.color-picker').css('color', ColorButton);
    $('#ColorHeader').val(ColorHeader);
    $('#ColorHeader').colorPicker(setColorConf('background-color', '.background-picker'));
    $('.background-picker').css('background-color', ColorHeader);

    validateConfig();

    jQuery.extend(jQuery.validator.messages, {
        required: general_text.sas_campoRequerido,
        email: general_text.sas_emailInvalido,
        number: general_text.sas_soloNumeros,
        maxlength: general_text.sas_ingresaMaxCaracteres,
        digits: general_text.sas_soloDigitos,
        minlength: general_text.sas_ingresaMinimoCaracteres
    });

    $('#colorpickerField1, #colorpickerField2, #colorpickerField3').ColorPicker({
        onSubmit: function (hsb, hex, rgb, el) {
            $(el).val(hex);
            $(el).ColorPickerHide();
        },
        onBeforeShow: function () {
            $(this).ColorPickerSetColor(this.value);
        }
    })
            .bind('keyup', function () {
                $(this).ColorPickerSetColor(this.value);
            });

    $('.datepicker').pickadate({
        selectMonths: true, // Creates a dropdown to control month
        selectYears: 15, // Creates a dropdown of 15 years to control year,
        today: 'Today',
        clear: 'Clear',
        close: 'Ok',
        closeOnSelect: false,
        // Close upon selecting a date,
        container: undefined, // ex. 'body' will append picker to body
    });
});

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

$("#btnGuardarConfig").on('click', function () {
    event.preventDefault();
    action = "insert";
    $("#configuracionEdicion").submit();
});

$(".btnUpdateConfig").on('click', function () {
    //event.preventDefault();
    var idConfiguracion = $(this).attr('id');
    $("#configuracionEdicion").submit();
    action = "update";
});
$(':file').change(previewImage);

function previewImage() {
    var oFReader = new FileReader();
    var fileinput = $(this);
    if (isset(fileinput.val())) {
        oFReader.readAsDataURL(document.getElementById(fileinput.attr('id')).files[0]);
        oFReader.onload = function (oFREvent) {
            var dataURL = oFREvent.target.result;
            var mimeType = dataURL.split(",")[0].split(":")[1].split(";")[0];

            document.getElementById(fileinput.attr('rel-preview')).src = '';
            if (mimeType.match('image.*')) {
                document.getElementById(fileinput.attr('rel-preview')).src = dataURL;
            } else {
                document.getElementById(fileinput.attr('rel-preview')).src = url_image_fail;
            }

            $(fileinput).parents('.file-field').find('input[type="text"]').focus();
        };
    } else {
        document.getElementById(fileinput.attr('rel-preview')).src = url_no_image;
    }
}

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


$("#enCheckbox").change(function () {
    var val = $("#enCheckbox").val();
    if ($(this).is(":checked")) {
        $("#rowEn").show();
//        $("#rowEn2").show();
    } else {
        $("#rowEn").hide();
//        $("#rowEn2").hide();
    }
});



function validateConfig() {
    $("#configuracionEdicion").validate({
        rules: {
            'Abreviatura': {
                required: true
            },
            'ColorHeader': {
                required: true
            },
            'FechaInicio': {
                required: true
            },
            'FechaFin': {
                required: true
            },
            'ColorButton': {
                required: true
            },
            'Logo_Es_1_name': {
                required: true
            },
            'Logo_En_1_name': {
                required: true
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
                validaInsercion(action);
//                insertConfiguracionRS(action);
            }
            if (action === 'update') {
                post = post + "&idConfiguracion=" + $(".btnUpdateConfig").attr('id');
                updateConfiguracionRS(post, action);
            }
            return;
        }

    });
}

function validaInsercion(action) {
    let duplicados = false;
    $.each(Configuracion, function (index, value) {
        var configuracionRS = {};
        if (value === parseInt($("#idEdicion").val())) {
            hide_loader_top();
            show_alert("danger", "Solo puede haber un registro de configuración.");
            duplicados = true;
            return;
        }

    });
    if (!duplicados) {
        insertConfiguracionRS(action);
    }
}

function updateConfiguracionRS(post) {
    show_loader_wrapper();
    var formData = new FormData($("#configuracionEdicion")[0]);
    $.ajax({
        type: "post",
        url: update_configuracion,
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (!response['status']) {
                show_alert('error', response.data);
            }
            hide_loader_wrapper();
        },

        error: function (request, status, error) {

        }
    });
}

function insertConfiguracionRS(action) {
    show_loader_wrapper();
    var formData = new FormData($("#configuracionEdicion")[0]);

    $.ajax({
        type: "post",
        url: insert_configuracion,
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (!response['status']) {
                show_alert('error', response.data);
            }
            hide_loader_wrapper();
        },

        error: function (request, status, error) {
        }
    });
}