/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(init);

function init() {
    initFormEdicion();
    $('input[name="lang"]').change();
    if (!isset($("#Edicion_ES").val())) {
        $("#KeyEncriptacion").val(generateKeyEncriptacion());
    }
}

$(".character-counter textarea").characterCounter();

function initFormEdicion() {
    var formID = "#frm-edicion";
    $(formID).validate({
        rules: {
            'Edicion_ES': {
                required: true
            },
            'Logo_ES_1': {
                required: {
                    depends: function (element) {
                        if (isset($("#Logo_ES_1_name").val())) {
                            return false;
                        }
                        return true;
                    }
                },
                accept: "image/jpeg, image/png",
                maxheight: 150,
                filesize: 3 //Megas
            },
            'FechaInicio': {
                required: true
            },
            'FechaFin': {
                required: true
            },
            'Abreviatura': {
                required: true
            },
            'KeyEncriptacion': {
                required: true
            },
            'Edicion_EN': {
                required: true
            },
            'Logo_EN_1': {
                required: {
                    depends: function (element) {
                        if (isset($("#Logo_EN_1_name").val())) {
                            return false;
                        }
                        return true;
                    }
                },
                accept: "image/jpeg, image/png",
                maxheight: 150,
                filesize: 3 //Megas
            },
            'Edicion_FR': {
                required: true
            },
            'Logo_FR_1': {
                required: {
                    depends: function (element) {
                        if (isset($("#Logo_FR_1_name").val())) {
                            return false;
                        }
                        return true;
                    }
                },
                accept: "image/jpeg, image/png",
                maxheight: 150,
                filesize: 3 //Megas
            },
            'Edicion_PT': {
                required: true
            },
            'Logo_PT_1': {
                required: {
                    depends: function (element) {
                        if (isset($("#Logo_PT_1_name").val())) {
                            return false;
                        }
                        return true;
                    }
                },
                accept: "image/jpeg, image/png",
                maxheight: 150,
                filesize: 3 //Megas
            },
        },
        messages: {
            'Edicion_ES': {
                required: general_text.sas_requerido
            },
            'Logo_ES_1': {
                required: general_text.sas_requerido,
                accept: general_text.sas_archivoInvalidoJs,
                filesize: general_text.sas_tamanoArchivoJs,
                maxheight: general_text.sas_maxAlto
            },
            'FechaInicio': {
                required: general_text.sas_requerido
            },
            'FechaFin': {
                required: general_text.sas_requerido
            },
            'Abreviatura': {
                required: general_text.sas_requerido
            },
            'KeyEncriptacion': {
                required: general_text.sas_requerido
            },
            'Edicion_EN': {
                required: general_text.sas_requerido
            },
            'Logo_EN_1': {
                required: general_text.sas_requerido,
                accept: general_text.sas_archivoInvalidoJs,
                filesize: general_text.sas_tamanoArchivoJs,
                maxheight: general_text.sas_maxAlto
            },
            'Edicion_FR': {
                required: general_text.sas_requerido
            },
            'Logo_FR_1': {
                required: general_text.sas_requerido,
                accept: general_text.sas_archivoInvalidoJs,
                filesize: general_text.sas_tamanoArchivoJs,
                maxheight: general_text.sas_maxAlto
            },
            'Edicion_PT': {
                required: general_text.sas_requerido
            },
            'Logo_PT_1': {
                required: general_text.sas_requerido,
                accept: general_text.sas_archivoInvalidoJs,
                filesize: general_text.sas_tamanoArchivoJs,
                maxheight: general_text.sas_maxAlto
            },
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
                if ($(element).attr('type') === "file") {
                    element = $(element).parents('.file-field').find('input[type="text"]');
                }
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            if ($('.alert').length > 0) {
                $('.alert').remove();
            }
            show_loader_wrapper()

            form.submit();
        }
    });
}

var pickerInicio = $('#FechaInicio').pickadate({
    clear: "",
    format: 'yyyy-mm-dd',
    onSet: function (context) {
        if (isset(context["select"])) {
            this.close();
            Materialize.updateTextFields();
            $('#FechaInicio').removeClass("invalid").addClass("valid");
        }
    }
});

var pickerFin = $('#FechaFin').pickadate({
    clear: "",
    format: 'yyyy-mm-dd',
    onSet: function (context) {
        if (isset(context["select"])) {
            this.close();
            Materialize.updateTextFields();
            $('#FechaFin').removeClass("invalid").addClass("valid");
        }
    }
});

$('input[name="lang"]').change(enableDisableLangEdition);

function enableDisableLangEdition() {
    var lang = $(this).val();
    if ($(this).is(":checked")) {
        $('.edition-section[lang=' + lang + ']').slideDown();
    } else {
        $('.edition-section[lang=' + lang + '] input').val("");
        Materialize.updateTextFields();
        $('.edition-section[lang=' + lang + ']').slideUp();
    }
}

$(".btn-generate-encryption-key").click(function () {
    $("#KeyEncriptacion").val(generateKeyEncriptacion());
    $("#KeyEncriptacion").focus();
});
function generateKeyEncriptacion() {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < 40; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}

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

$(".btn-new-edi").click(showNewEdition);
function showNewEdition() {
    resetForm();
    $(".ediciones").css("display", "none");
    $(".frm-edicion").fadeIn();
}

$(".btn-cancel-submit").click(cancelSubmit);
function cancelSubmit() {
    resetForm();
    $(".frm-edicion").css("display", "none");
    $(".ediciones").fadeIn();
}

function resetForm() {
    $("input[name='lang']").prop("checked", false).change();
    $(".frm-edicion #idEvento").val($(".frm-edicion #idEvento option:first").val());
    $(".frm-edicion #idEdicion").val("");
    $(".frm-edicion #Abreviatura").val("");
    $(".frm-edicion #FechaInicio").val("");
    $(".frm-edicion #FechaFin").val("");
    $(".frm-edicion #KeyEncriptacion").val("");
    $(".frm-edicion #Descripcion").val("");
    $(".frm-edicion #Edicion_ES").val("");
    $(".frm-edicion #Slogan_ES").val("");

    $(".frm-edicion #Logo_ES_1_name").val("");
    $('#' + $(".frm-edicion #Logo_ES_1").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_ES_2_name").val("");
    $('#' + $(".frm-edicion #Logo_ES_2").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_ES_3_name").val("");
    $('#' + $(".frm-edicion #Logo_ES_3").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_EN_1_name").val("");
    $('#' + $(".frm-edicion #Logo_EN_1").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_EN_2_name").val("");
    $('#' + $(".frm-edicion #Logo_EN_2").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_EN_3_name").val("");
    $('#' + $(".frm-edicion #Logo_EN_3").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_FR_1_name").val("");
    $('#' + $(".frm-edicion #Logo_FR_1").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_FR_2_name").val("");
    $('#' + $(".frm-edicion #Logo_FR_2").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_FR_3_name").val("");
    $('#' + $(".frm-edicion #Logo_FR_3").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_PT_1_name").val("");
    $('#' + $(".frm-edicion #Logo_PT_1").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_PT_2_name").val("");
    $('#' + $(".frm-edicion #Logo_PT_2").attr('rel-preview')).attr("src", path_no_image);

    $(".frm-edicion #Logo_PT_3_name").val("");
    $('#' + $(".frm-edicion #Logo_PT_3").attr('rel-preview')).attr("src", path_no_image);
    Materialize.updateTextFields();
}

$(".edit").click(showEditEdition);
function showEditEdition() {
    if (!isset(ediciones[$(this).attr("id-edicion")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var edicion = ediciones[$(this).attr("id-edicion")];

    $("input[name='lang']").prop("checked", false).change();
    $(".frm-edicion #idEvento").val(edicion["idEvento"]);
    $(".frm-edicion #idEdicion").val(edicion["idEdicion"]);
    $(".frm-edicion #Abreviatura").val(edicion["Abreviatura"]);
    $(".frm-edicion #FechaInicio").val(edicion["FechaInicio"]);
    $(".frm-edicion #FechaFin").val(edicion["FechaFin"]);
    $(".frm-edicion #KeyEncriptacion").val(edicion["KeyEncriptacion"]);
    $(".frm-edicion #Descripcion").val(edicion["Descripcion"]);
    $(".frm-edicion #Edicion_ES").val(edicion["Edicion_ES"]);
    $(".frm-edicion #Slogan_ES").val(edicion["Slogan_ES"]);
    $(".frm-edicion #Logo_ES_1_name").val(edicion["Logo_ES_1"]);
    $('#' + $(".frm-edicion #Logo_ES_1").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_ES_1"]);

    $(".frm-edicion #Logo_ES_2_name").val(edicion["Logo_ES_2"]);
    $('#' + $(".frm-edicion #Logo_ES_2").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_ES_2"]);

    $(".frm-edicion #Logo_ES_3_name").val(edicion["Logo_ES_3"]);
    $('#' + $(".frm-edicion #Logo_ES_3").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_ES_3"]);

    if (isset(edicion["Edicion_EN"])) {
        $("input[name='lang'][value='en']").prop("checked", true).change();
        $(".frm-edicion #Edicion_EN").val(edicion["Edicion_EN"]);
        $(".frm-edicion #Slogan_EN").val(edicion["Slogan_EN"]);
        $(".frm-edicion #Logo_EN_1_name").val(edicion["Logo_EN_1"]);
        $('#' + $(".frm-edicion #Logo_EN_1").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_EN_1"]);

        $(".frm-edicion #Logo_EN_2_name").val(edicion["Logo_EN_2"]);
        $('#' + $(".frm-edicion #Logo_EN_2").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_EN_2"]);

        $(".frm-edicion #Logo_EN_3_name").val(edicion["Logo_EN_3"]);
        $('#' + $(".frm-edicion #Logo_EN_3").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_EN_3"]);
    }

    if (isset(edicion["Edicion_FR"])) {
        $("input[name='lang'][value='fr']").prop("checked", true).change();
        $(".frm-edicion #Edicion_FR").val(edicion["Edicion_FR"]);
        $(".frm-edicion #Slogan_FR").val(edicion["Slogan_FR"]);
        $(".frm-edicion #Logo_FR_1_name").val(edicion["Logo_FR_1"]);
        $('#' + $(".frm-edicion #Logo_FR_1").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_FR_1"]);

        $(".frm-edicion #Logo_FR_2_name").val(edicion["Logo_FR_2"]);
        $('#' + $(".frm-edicion #Logo_FR_2").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_FR_2"]);

        $(".frm-edicion #Logo_FR_3_name").val(edicion["Logo_FR_3"]);
        $('#' + $(".frm-edicion #Logo_FR_3").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_FR_3"]);
    }

    if (isset(edicion["Edicion_PT"])) {
        $("input[name='lang'][value='pt']").prop("checked", true).change();
        $(".frm-edicion #Edicion_PT").val(edicion["Edicion_PT"]);
        $(".frm-edicion #Slogan_PT").val(edicion["Slogan_PT"]);
        $(".frm-edicion #Logo_PT_1_name").val(edicion["Logo_PT_1"]);
        $('#' + $(".frm-edicion #Logo_PT_1").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_PT_1"]);

        $(".frm-edicion #Logo_PT_2_name").val(edicion["Logo_PT_2"]);
        $('#' + $(".frm-edicion #Logo_PT_2").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_PT_2"]);

        $(".frm-edicion #Logo_PT_3_name").val(edicion["Logo_PT_3"]);
        $('#' + $(".frm-edicion #Logo_PT_3").attr('rel-preview')).attr("src", path_logo_base + edicion["Logo_PT_3"]);
    }

    Materialize.updateTextFields();

    $(".ediciones").css("display", "none");
    $(".frm-edicion").fadeIn();
}

$(".delete").click(showDeleteEdition);

function showDeleteEdition() {
    if (!isset(ediciones[$(this).attr("id-edicion")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var edicion = ediciones[$(this).attr("id-edicion")];

    $("#frm-edicion-eliminar #idEdicion").val(edicion['idEdicion']);
    $(".edicion").text(edicion['Edicion_ES']);
    $("#modal-delete-edicion").modal("open");
}

$(".click-to-toggle ul a").click(function () {
    $(this).parents(".click-to-toggle").find("a").first().trigger("click");
});