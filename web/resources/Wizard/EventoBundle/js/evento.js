/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(init);

function init() {
    initFormEvento();
}

function initFormEvento() {
    var formID = "#frm-evento";
    $(formID).validate({
        rules: {
            'Evento_ES': {
                required: true
            },
        },
        messages: {
            'Evento_ES': {
                required: general_text.sas_requerido
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

$(".btn-new-ef").click(showNewEvent);
function showNewEvent() {
    $(".frm-evento #idEvento").val("");
    $(".frm-evento #Evento_ES").val("");
    $(".frm-evento #Evento_EN").val("");
    $(".frm-evento #Evento_FR").val("");
    $(".frm-evento #Evento_PT").val("");

    Materialize.updateTextFields();

    $(".eventos").css("display", "none");
    $(".frm-evento").fadeIn();
}

$(".btn-cancel-submit").click(cancelSubmit);
function cancelSubmit() {
    $(".frm-evento #idEvento").val("");
    $(".frm-evento #Evento_ES").val("");
    $(".frm-evento #Evento_EN").val("");
    $(".frm-evento #Evento_FR").val("");
    $(".frm-evento #Evento_PT").val("");
    Materialize.updateTextFields();

    $(".frm-evento").css("display", "none");
    $(".eventos").fadeIn();
}

$(".edit").click(showEditEvent);
function showEditEvent() {
    if (!isset(eventos[$(this).attr("id-evento")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var evento = eventos[$(this).attr("id-evento")];
    
    $(".frm-evento #idEvento").val(evento["idEvento"]);
    $(".frm-evento #Evento_ES").val(evento["Evento_ES"]);
    $(".frm-evento #Evento_EN").val(evento["Evento_EN"]);
    $(".frm-evento #Evento_FR").val(evento["Evento_FR"]);
    $(".frm-evento #Evento_PT").val(evento["Evento_PT"]);
    Materialize.updateTextFields();

    $(".eventos").css("display", "none");
    $(".frm-evento").fadeIn();
}

$(".delete").click(showDeleteEvent);

function showDeleteEvent() {
    if (!isset(eventos[$(this).attr("id-evento")])) {
        show_alert("warning", general_text['sas_errorPeticion']);
        return;
    }
    var evento = eventos[$(this).attr("id-evento")];

    $("#frm-evento-eliminar #idEvento").val(evento['idEvento']);
    $(".evento").text(evento['Evento_ES']);
    $("#modal-delete-evento").modal("open");
}

$(".click-to-toggle ul a").click(function () {
    $(this).parents(".click-to-toggle").find("a").first().trigger("click");
});