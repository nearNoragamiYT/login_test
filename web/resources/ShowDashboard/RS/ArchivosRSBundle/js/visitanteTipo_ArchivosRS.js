$(document).ready(function () {
    validateConfig();
});

$("#path_visitanteTipo").change(function () {
    var base_path_asset = (asset_path_img.replace("app_dev.php","")) + '/images/';
    var val = $("#path_visitanteTipo").val();

    if (val != "") {
        document.getElementById('archivo_visitanteTipo').src = base_path_asset + "file.png";
    } else {
        document.getElementById('archivo_visitanteTipo').src = base_path_asset + "no-file.png";
    }
});

$("#btnSubirArchivo_visitanteTipo").on('click', function () {
    event.preventDefault();
    action = "insert";
    $("#visitantetipo_archivosRS").submit();
});

function validateConfig() {
    $("#visitantetipo_archivosRS").validate({
        rules: {
            'fileSelected_visitanteTipo': {
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
                visitanteTipo_insertArchivoRS(action);
            }

            return;
        }

    });
}

function visitanteTipo_insertArchivoRS(action) {
    show_loader_wrapper();
    var formData = new FormData($("#visitantetipo_archivosRS")[0]);

    $.ajax({
        type: "post",
        url: insert_archivo_visitanteTipo,
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,

        success: function (response) {
            if (!response['status']) {
                show_alert('error', response.data);
            }
            document.getElementById("path_visitanteTipo").value = "";
            document.getElementById('archivo_visitanteTipo').src = base_path_asset + "no-file.png";
            hide_loader_wrapper();
            show_toast('success', general_text.sas_guardoExito);
        },

        error: function (request, status, error) {
        }
    });
}
