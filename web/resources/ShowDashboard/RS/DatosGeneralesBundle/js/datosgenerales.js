$(init);

function init() {    
    $('select').material_select();
    $("#visitante-datosgenerales").attr("class", "active");
    hide_loader_wrapper();   
    initFrmDatosGenerales();
}

function initFrmDatosGenerales() {
    $("#frm-datos-generales").validate({
        rules: {
            'Email': {
                required: true,
                email: true
            },
            'Nombre': {
                required: true
            },
            'ApellidoPaterno': {
                required: true
            },
            'EmailOpcional':{
              email: true  
            }
        },
        messages: {
            'Email': {
                required: general_text.sas_requeridoCampo,
                email: general_text.sas_mailInvalido
            },            
            'Nombre': {
                required: general_text.sas_requeridoCampo
            },
            'ApellidoPaterno': {
                required: general_text.sas_requeridoCampo
            },
            'EmailOpcional':{
              email: general_text.sas_mailInvalido 
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
                if ($(element).attr('type') === "checkbox") {
                    element = $(element).parents('p');
                }
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            var data = $(form).serialize();
            updateGeneralData(data);
        }
    });
}

function updateGeneralData(data) {
    $.ajax({
        type: "post",
        dataType: 'json',
        data: data,
        url: url_update_datosgenerales,
        success: function (response) {
            hide_loader_wrapper();  
            if (!response.status) {
                show_toast("danger", general_text.sas_errorGuardado);
                return;
            }
            show_toast("success", general_text.sas_exitoGuardado);
        },
        error: function (request, status, error) {
            hide_loader_wrapper();  
            show_toast("danger", general_text.sas_errorGuardado);
        }
    });
}

$(document).on("click", ".load", function () {
    show_loader_wrapper();
});
