/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(init);

function init() {
    initRestablecerPassword();
}

function initRestablecerPassword() {
    $("#frm-restablecer-password").validate({
        rules: {
            'Password': {
                required: true,
                minlength: 4
            },
            'Password2': {
                required: true,
                minlength: 4,
                equalTo: "#Password"
            }
        },
        messages: {
            'Password': {
                required: general_text.sas_requerido,
                minlength: general_text.sas_ingresaMinCaracteres,
            },
            'Password2': {
                required: general_text.sas_requerido,
                minlength: general_text.sas_ingresaMinCaracteres,
                equalTo: general_text.sas_passwordNoCoinciden
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
            form.submit();
        }
    });
}