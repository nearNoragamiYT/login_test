var editionTable = "", tr = "", itemToDelete = "";

$(document).ready(init);

function init() {      
    

    $("#delete-record").on("click", deleteData);        
    $(document).on("click", ".delete-record", function () {
        itemToDelete = $(this).attr("id-record");
        tr = $(this).parents("tr");
        $("#mdl-delete-edition").modal("open");
    });
    
    initFormEdicion();
    
}

function initFormEdicion() {
    var formID = "#frm-edition";
    var frmEdition = $(formID).validate({
        rules: {
            'idComiteOrganizador':{
              required: true  
            },
            'idEvento':{
                required: true
            },
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
            'Logo_ES_2': {
                required: {
                    depends: function (element) {
                        if (isset($("#Logo_ES_2_name").val())) {
                            return false;
                        }
                        return true;
                    }
                },
                accept: "image/jpeg, image/png",
                maxheight: 150,
                filesize: 3 //Megas
            },
            'Logo_ES_3': {
                required: {
                    depends: function (element) {
                        if (isset($("#Logo_ES_3_name").val())) {
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
            'KeyEncriptacion': {
                required: true
            }
        },
        messages: {
             'idComiteOrganizador':{
              required: general_text.sas_requerido  
            },
            'idEvento':{
                required: general_text.sas_requerido
            },
            'Edicion_ES': {
                required: general_text.sas_requerido
            },
            'Logo_ES_1': {
                required: general_text.sas_requerido,
                accept: general_text.sas_archivoInvalidoJs,
                filesize: general_text.sas_tamanoArchivoJs,
                maxheight: general_text.sas_maxAlto
            },
            'Logo_ES_2': {
                required: general_text.sas_requerido,
                accept: general_text.sas_archivoInvalidoJs,
                filesize: general_text.sas_tamanoArchivoJs,
                maxheight: general_text.sas_maxAlto
            },
            'Logo_ES_3': {
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
            'KeyEncriptacion': {
                required: general_text.sas_requerido
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
            show_loader_wrapper()
            submitFormFilesEdition();
        }

    });

    $('input[id="lang-en"]').change();
    $('input[id="lang-fr"]').change();
    $('input[id="lang-pt"]').change();

    if (!isset($("#Edicion_ES").val())) {
        $("#KeyEncriptacion").val(generateKeyEncriptacion());
    }
}

var files_edition = {};
$('#frm-edition input[type=file]').change(prepareUploadEdition);
function prepareUploadEdition(event) {
    if (isset(event.target.files[0])) {
        files_edition[$(this).attr('id')] = event.target.files[0];
    } else {
        delete files_edition[$(this).attr('id')];
    }
}

function submitFormFilesEdition() {
    if (files_edition === null) {
        submitFormEdition(null);
        return;
    }
    var data = new FormData();
    $.each(files_edition, function (key, value) {
        data.append(key, value);
    });

    $.ajax({
        url: url_logos_edicion,
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function (result, textStatus, jqXHR) {
            if (!result['status']) {
                show_modal_error(result['data']);
                return;
            }

            submitFormEdition(result['data']);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}

function submitFormEdition(files) {
    var formID = "#frm-edition";
    var data = $(formID).serialize();

    if (isset(files)) {
        $.each(files, function (key, value) {
            data += "&" + value['field'] + "=" + value['name'];
        });
    }
    $.ajax({
        type: "post",
        url: $(formID).attr('action'),
        dataType: 'json',
        data: data,
        success: function (result) {
            hide_loader_wrapper();            
            
            files_edition = {};
            $(formID + " #idEdicion").val(edition['idEdicion']);
            show_alert("success", general_text['sas_guardoExito']);            
        },
        error: function (request, status, error) {
            hide_loader_wrapper();
            show_modal_error(request.responseText);
        }
    });
}


$('input[id="lang-en"]').change(enableDisableLangEdition);
$('input[id="lang-fr"]').change(enableDisableLangEdition);
$('input[id="lang-pt"]').change(enableDisableLangEdition);


function enableDisableLangEdition() {
    var lang = $(this).val();
    if ($(this).is(":checked")) {
        $('.edition-section[lang=' + lang + ']').slideDown();
    } else {
        //$('.edition-section[lang=' + lang + '] input').val("");
        //Materialize.updateTextFields();
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


//funciones para la el logotipo al insertar una edición
$(':file').change(previewImage);

function previewImage() {
    var oFReader = new FileReader();
    var fileinput = $(this);
    oFReader.readAsDataURL(document.getElementById(fileinput.attr('id')).files[0]);
    oFReader.onload = function (oFREvent) {
        var dataURL = oFREvent.target.result;
        var mimeType = dataURL.split(",")[0].split(":")[1].split(";")[0];

        document.getElementById(fileinput.attr('rel-preview')).src = '';
        if (mimeType.match('image.*')) {
            document.getElementById(fileinput.attr('rel-preview')).src = dataURL;
        } else {
            console.log("ooppsss")
            //document.getElementById(fileinput.attr('rel-preview')).src = src_fail;
        }
    };
}

//función para dar formato en datepicker
 var $inputdate = $('.datepicker').pickadate({
    selectMonths: true, // Creates a dropdown to control month
    selectYears: 15, // Creates a dropdown of 15 years to control year
    format: 'yyyy-mm-dd' 
  });


//función para eliminar la edición
function deleteData(){
    if ($('.alert').length > 0) {
        $('.alert').remove();
    }
    show_loader_top();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_delete, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: {idEdicion: itemToDelete}, // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_top();
            $("#mdl-delete-edition").modal("close");
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            delete  edition[response.data.idEdicion];             
            show_alert("success", general_text.sas_eliminoExito);
            /*Recargamos desde caché*/
            location.reload();
            /*Forzamos la recarga*/
            location.reload(true);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
}

