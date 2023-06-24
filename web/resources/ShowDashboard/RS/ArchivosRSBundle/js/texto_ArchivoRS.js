$(document).ready(function () {
    validateConfig();
    $('select').material_select();
    $("#rowPuerta").hide();

    jQuery.extend(jQuery.validator.messages, {
        required: general_text.sas_campoRequerido,
    });
});

var base_path_asset = (asset_path_img.replace("app_dev.php", "")) + 'images/';
$("#path_rsTexto").change(function () {
    var val = $("#path_rsTexto").val();

    if (val != "") {
        document.getElementById('archivo_rsTexto').src = base_path_asset + "text.png";
    } else {
        document.getElementById('archivo_rsTexto').src = base_path_asset + "no-file.png";
    }
});

$("#idPuerta").change(function () {
    var val = $("#idPuerta").val();
    if (val == 10000) {
        $("#rowPuerta").show();
    } else {
        $("#rowPuerta").hide();
    }
});

$("#btnSubirArchivo_rsTexto").on('click', function () {
    event.preventDefault();
    action = "insert";
    $("#texto_archivosRS").submit();
});

$("#btnSubirArchivoAPP").on('click', function () {
    event.preventDefault();
    action = "";
    $("#texto_archivosRS").submit();
});

function validateConfig() {
    $("#texto_archivosRS").validate({
        rules: {
            'path_rsTexto': {
                required: true
            },
            'idPuerta': {
                required: true
            },
            'NombrePuerta': {
                required: {
                    depends: function () {
                        if ($("#idPuerta").val() == "10000") {
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            },
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
            if (action === 'insert') {
                texto_insertArchivoRS(action);
            } else {
                texto_AppRS();
            }
            return;
        }
    });
}

function texto_insertArchivoRS(action) {
    show_loader_wrapper();
    var formData = new FormData($("#texto_archivosRS")[0]);

    $.ajax({
        type: "post",
        url: insert_archivoTexto_rs,
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,

        success: function (response) {

            if (!response['status']) {
                show_alert('error', response.data);
            }
            if ($("#idPuerta").val() == "10000") {
                Puerta[response.data.Puerta.idPuerta] = response.data.Puerta;
                var option = new Option(response.data.Puerta.NombrePuerta, response.data.Puerta.idPuerta);
                $(option).html(response.data.Puerta.NombrePuerta);
                $("#idPuerta").append(option);
            }
            document.getElementById("texto_archivosRS").reset();
            document.getElementById("fileSelected_rsTexto").value = "";
            document.getElementById("path_rsTexto").value = "";
            document.getElementById('archivo_rsTexto').src = base_path_asset + "no-file.png"
            hide_loader_wrapper();
            show_toast('success', general_text.sas_guardoExito);
        },

        error: function (request, status, error) {
        }
    });
}

//function texto_AppRS(action) {
//    show_loader_wrapper();
//    let formData = new FormData($("#texto_archivosRS")[0]);
//    fetch(insert_archivoTextoAPP_rs, {
//        method: 'POST',
//        body: formData
//    })
//            .then(response => response.json())
//            .then(json => {
//
//                if (json.status) {
//
//                    if ($("#idPuerta").val() == "10000") {
//                        Puerta[json.data.Puerta.idPuerta] = json.data.Puerta;
//                        var option = new Option(json.data.Puerta.NombrePuerta, json.data.Puerta.idPuerta);
//                        $(option).html(json.data.Puerta.NombrePuerta);
//                        $("#idPuerta").append(option);
//                    }
//
//                    document.getElementById("texto_archivosRS").reset();
//                    document.getElementById("fileSelected_rsTexto").value = "";
//                    document.getElementById("path_rsTexto").value = "";
//                    document.getElementById('archivo_rsTexto').src = base_path_asset + "no-file.png";
//                    hide_loader_wrapper();
//                    show_toast('success', general_text.sas_guardoExito);
//
//                } else {
//                    show_notification("error", json.message);
//                    hide_loader_processing();
//                }
//            })
//}