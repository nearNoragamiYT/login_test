var oTable = "", GeneralTable = "", tr = "", itemToUpdate = "", itemToDelete = "", action = "", disabled = "", empToUpdate="";
var InvitationsNumber,createdCodes = 0,sendedCodes = 0,rejectedCodes = 0,canceledCodes = 0,freeCodes = 0,usedCodes = 0 ;
$(document).ready(function () {
    initElectronicInvitations();
});

function initElectronicInvitations() {
    console.log(url_lang);
    totalInvitaciones = Object.keys(codes).length;
    keys = Object.keys(codes);
    for (var i = 0; i < totalInvitaciones; i++) {
//        if (codes[i]['idCuponStatus'] == 1) {
//
//        }
        switch (codes[keys[i]]['idCuponStatus']) {
            case 1:
                createdCodes = createdCodes + 1;
                break;
            case 2:
                sendedCodes = sendedCodes + 1;
                break;
            case 3:
                usedCodes = usedCodes + 1;
                break;
            case 4:
                rejectedCodes = rejectedCodes + 1;                
                break;
            case 5:
                canceledCodes = canceledCodes + 1;
                break;            
            default:
                totalInvitaciones = totalInvitaciones;
        }
    }
    freeCodes = totalInvitaciones - sendedCodes - usedCodes - rejectedCodes - canceledCodes;
    $('#created-codes').html('Total '+totalInvitaciones);
    $('#sended-codes').html('Enviadas '+sendedCodes);
    $('#rejected-codes').html('Rechazadas '+rejectedCodes);
    $('#used-codes').html('Usadas  '+usedCodes);
    $('#canceled-codes').html('Canceladas '+canceledCodes);
    $('#free-codes').html('Disponibles '+freeCodes);
    
    
    //console.log(codes.length);
    $("#empresa-invitaciones").attr("class","active");
    if (idEmpresa != "") {                
        generateInvitationsTable('invitations-table');
    }               
    $("#generate-invitation").on('click', function () {        
        $('#input-invitations-number').removeAttr('readonly');
        $('#message-generate-invitations').html('El expositor cuenta con <b>'+totalInvitaciones+'</b> invitaciones, ingrese en el campo el número de invitaciones adicionales que desee generar.');
        $('#modal-generate-invitation').modal({dismissible: false}).modal("open");                 
    });                         
    
    $("#input-invitations-number").on('change', function () {
        InvitationsNumber = $("#input-invitations-number").val();
        console.log(InvitationsNumber);
        if (InvitationsNumber > 15) {
            $(".btn-generate").addClass('disabled');
        } else {
            $(".btn-generate").removeClass('disabled');
            $(".btn-generate").attr("href", route_generate_invitations  + "?InvitationsNumber=" + InvitationsNumber);             
        }
    });
    
    $("#btn-generate-invitations").on("click", function () {
       show_loader_wrapper();     
    });
        
    $(".cancel-invitation").on('click', function () {
        var id = $(this).attr("data-id");
        console.log(id);
        var codeToUpdate =id;
        $("#message-cancel-invitation").html('¿Estás seguro que deseas Cancelar la Invitación <b>' + codeToUpdate  + '</b> ?');        
        $(".btn-cancel").attr("href", route_cancel_invitation  + "?idCupon=" + codeToUpdate + "&idCuponStatus=" + 5); 
        $('#modal-cancel-invitation').modal({dismissible: false}).modal("open");
    });
            
    $("#btn-cancel-invitation").on("click", function () {
       show_loader_wrapper();     
    });
    
    $(document).on("click", ".delete-record", function () {
        itemToDelete = $(this).attr("data-id");
        tr = $(this).parents("tr");
        action = "delete";
        $("#message-delete-invitation").html('¿Estás seguro que deseas Eliminar la Invitación <b>' + itemToDelete  + '</b> ?');
        $('#modal-delete-invitation').modal({dismissible: false}).modal("open");
    });

    $("#btn-delete-invitation").on("click", function () {
        //show_loader_wrapper();    
        $("#modal-delete-invitation").modal("close");
        deleteInvitation();
    });

    //validateGenerateInvitationForm();        
}

//tabla de modulo interno
function generateInvitationsTable(id) {      
    var btn, span;
    oTable = $('#' + id).DataTable({
        "language": {
            "url": url_lang
        },
         "order": [[4, "desc"]]
    });
}

function generateInvitations(post){
    post += "&idEmpresa=".idEmpresa;   
    
    $.ajax({
        type: "post",
        url: url_invitation_generate,
        dataType: 'json',
        data: post,
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }            
            // setRow(response.data, action);            
            show_alert("success", general_text.sas_guardoExito);
            location.reload();
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();           
}

function deleteInvitation() {
    $.ajax({
        type: "post",
        url: route_delete_invitation,
        dataType: 'json',
        data: {
            idCupon: itemToDelete,
            idEmpresa: idEmpresa
        },
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            delete codes[response.data["idCupon"]]
            setRow(response.data, "delete");
            show_alert("success", general_text.sas_eliminoExito);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}

function validateGenerateInvitationForm() {
    $("#form-generate-invitation").validate({        
        rules: {
            'numero': {
                required: true,
                max: 10
            },                        
        },
        messages: {
            'numero': {
                required: general_text.sas_requerido,
                max: general_text.sas_ingresaNumeroMenor, 
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
            $('#modal-generate-invitation').closeModal();
            disabled = $("#form-generate-invitation input:disabled").removeAttr("disabled");                        
            var post = $('#form-generate-invitation').serialize();            
            if (action == "insert")
                generateInvitations(post);
            return;            
        }
    });
}



function clearForm(idForm) {
    $('#' + idForm).find('input').each(function (index, element) {
        if (!$(element).is(':disabled')) {
            $(element).removeClass('valid').next().removeClass('active');
        }
    });
    $('#' + idForm).find('input[type="number"]').not('input[type="number"]:disabled').val("");
  }
  
  function setRow(data, action) {     

    switch (action) {
        case 'insert':
            oTable.row.add(insertRow).draw('full-hold');
            break;
        case 'update':
            oTable.row(tr).data(insertRow).draw();
            break;
        case 'delete':
            oTable.row(tr).remove().draw();
            break;
    }
}
  
  

