$(document).ready(function () {
    validateConfig();
//    document.getElementById('tipArchivoBD').checked = false;
//    document.getElementById('tipArchivoVis').checked = false;
});

$("#path").change(function () {
    var base_path_asset = (asset_path_img.replace("app_dev.php","")) + '/images/';
    var val = $("#path").val();

    if (val != "") {
        document.getElementById('archivo').src =  base_path_asset + "text.png";
    } else {
        document.getElementById('archivo').src = base_path_asset + "no-file.png";
    }
});

//$("#tipArchivoBD").change(function () {
//    if (document.getElementById('tipArchivoBD').checked) {
//        document.getElementById('tipArchivoVis').checked = false;
//    }
//});
//
//$("#tipArchivoVis").change(function () {
//    if (document.getElementById('tipArchivoVis').checked) {
//        document.getElementById('tipArchivoBD').checked = false;
//    }
//});

$("#btnSubirArchivo").on('click', function () {
    event.preventDefault();
    action = "insert";
    $("#archivosRS").submit();
});

function validateConfig() {
    $("#archivosRS").validate({
        rules: {
            'fileSelected': {
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
            if (action === 'insert') {
                insertArchivoRS(action);
            }

            return;
        }

    });
}

function insertArchivoRS(action) {
    show_loader_wrapper();
    var formData = new FormData($("#archivosRS")[0]);

    $.ajax({
        type: "post",
        url: insert_archivo,
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,

        success: function (response) {
            if (!response['status']) {
                show_alert('error', response.data);
            }
            document.getElementById("path").value = "";
            document.getElementById('archivo').src = base_path_asset + "no-file.png";
            hide_loader_wrapper();
            show_toast('success', general_text.sas_guardoExito);
        },

        error: function (request, status, error) {
        }
    });
}