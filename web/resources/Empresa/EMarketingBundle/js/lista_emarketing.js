oTable = "";
$(init);

function init() {
    oTable = $("#tbl-emarketing").DataTable({
        "language": {
            "url": url_lang
        }
    });

    $("#btn-add-emarketing").on("click", function () {
        $("#frm-emarketing input").val("");
        $("#mdl-emarketing").modal({dismissible: false}).modal("open");
    });

    $("#btn-save-emarketing").on("click", function () {
        $("#frm-emarketing").submit();
    });

    validateEmarketing();
}

function validateEmarketing() {
    $("#frm-emarketing").validate({
        rules: {
            'NombreEmarketing': {
                required: true,
                maxlength: 100
            }
        },
        messages: {
            'NombreEmarketing': {
                required: general_text.sas_requerido,
                maxlength: general_text.sas_ingresaMaxCaracteres,
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
            $('#mdl-emarketing').modal("close");
            addEmarketing();
            return;
        }
    });
}

function addEmarketing() {
    $.ajax({
        type: "post",
        url: url_insert,
        dataType: 'json',
        data: $('#frm-emarketing').serialize(),
        success: function (response) {
            if (!response['status']) {
                hide_loader_wrapper();
                show_alert("danger", response['data']);
                return;
            }
            window.location.replace(url_emarketing + "/" + response.data["idEMarketing"]);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
            hide_loader_wrapper();
        }
    });
}