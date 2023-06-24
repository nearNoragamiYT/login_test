/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(init);

function init() {
    initFormLogin();
    initRestablecerPassword();
}

function initFormLogin() {
    $("#frm-login").validate({
        rules: {
            'Email': {
                required: true,
                email: true
            },
            'Password': {
                required: true,
                minlength: 4
            },
        },
        messages: {
            'Email': {
                required: general_text.sas_requerido,
                email: general_text.sas_ingresaCorreoValido,
            },
            'Password': {
                required: general_text.sas_requerido,
                minlength: general_text.sas_ingresaMinCaracteres,
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
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            if ($('.alert:not(".alert-form")').length > 0) {
                $('.alert:not(".alert-form")').remove();
            }
            $("#Resolucion").val(screen.width + " x " + screen.height);
            show_loader_top();
            $.ajax({
                type: "post", // podría ser get, post, put o delete.
                url: $("#frm-login").attr('action'), // url del recurso
                dataType: 'json', // formato que regresa la respuesta
                data: $("#frm-login").serialize(), // datos a pasar al servidor, en caso de necesitarlo
                success: function (result) {
                    hide_loader_top();
                    if (!result['status']) {
                        show_modal_error(result['data']);
                        return;
                    }

                    if (!result['status_aux']) {
                        show_alert("warning", result['data']);
                        return;
                    }

                    $(location).attr('href', _target_path);
                },
                error: function (request, status, error) {
                    hide_loader_top();
                    show_modal_error(request.responseText);
                }
            });
        }
    });
}

function initRestablecerPassword() {
    $("#frm-restablecer-password").validate({
        rules: {
            'Email': {
                required: true,
                email: true
            }
        },
        messages: {
            'Email': {
                required: general_text.sas_requerido,
                email: general_text.sas_ingresaCorreoValido,
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
            if ($('.alert:not(".alert-form")').length > 0) {
                $('.alert:not(".alert-form")').remove();
            }
            var url = $("#frm-restablecer-password").attr('action');
            var data = $("#frm-restablecer-password").serialize();
            show_loader_top();
            $.ajax({
                type: "post", // podría ser get, post, put o delete.
                url: url, // url del recurso
                dataType: 'json', // formato que regresa la respuesta
                data: data, // datos a pasar al servidor, en caso de necesitarlo
                success: function (result) {
                    hide_loader_top();
                    $('#modal-password').modal("close");
                    if (!result['status']) {
                        show_modal_error(result['data']);
                        return;
                    }

                    if (!result['status_aux']) {
                        show_alert("warning", result['data']);
                        return;
                    }
                    show_alert("success", result['data']);
                },
                error: function (request, status, error) {
                    $('#modal-password').modal("close");
                    hide_loader_top();
                    show_modal_error(request.responseText);
                }
            });
        }
    });
}