/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(init);

function init() {
    initFormComiteOrganizador();
}

function initFormComiteOrganizador() {
    var formID = "#frm-comite-organizador";
    $(formID).validate({
        rules: {
            'ComiteOrganizador': {
                required: true
            },
            'Licencias': {
                required: true,
                number: true,
                min: 1
            },
            'Logo': {
                required: {
                    depends: function (element) {
                        if (isset($("#Logo_name").val())) {
                            return false;
                        }
                        return true;
                    }
                },
                accept: "image/jpeg, image/png",
                maxheight: 200,
                filesize: 3 //Megas
            }
        },
        messages: {
            'ComiteOrganizador': {
                required: general_text.sas_requerido
            },
            'Licencias': {
                required: general_text.sas_requerido,
                number: section_text.sas_soloNumero,
                min: section_text.sas_minLicencias
            },
            'Logo': {
                required: general_text.sas_requerido,
                accept: general_text.sas_archivoInvalidoJs,
                maxheight: general_text.sas_maxAlto,
                filesize: general_text.sas_tamanoArchivoJs
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
            show_loader_wrapper();
            form.submit();
        }
    });
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

$('.delete-record').click(deleteCO);

function deleteCO() {
    var idCO = $(this).attr('id-record');
    if (!isset(co[idCO])) {
        show_alert("error", general_text['sas_errorPeticion']);
    }
    $(".co-nombre").text(co[idCO]['ComiteOrganizador']);
    $("#idComiteOrganizador").val(idCO);
    $('#modal-delete-co').modal("open");
}