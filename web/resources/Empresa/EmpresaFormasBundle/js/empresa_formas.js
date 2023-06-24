/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(init);
function init() {
    $('#empresa-formas').addClass('active');
    $('#table-forms').DataTable({
        "language": {
            "url": url_lang
        }
    });
    $(document).on("click", ".company-menu>div>ul>li", function () {
        show_loader_wrapper();
    });
    $('td a.disabled').on('click', function () {
        return false;
    }).tooltip({
        delay: 50,
        tooltip: section_text.sas_noEditarFormaPago,
        position: "top"
    });
    $(document).on("click", ".lock-status", function () {
        show_loader_top();
        var id = $(this).attr('data-id'), block = (parseInt($(this).attr('data-value')) == 1) ? 0 : 1;
        $.ajax({
            url: url_update_status,
            type: "POST",
            dataType: 'json',
            data: {"idForma": id, "Bloqueado": block},
            success: function (response) {
                hide_loader_top();
                if (block == 1) {
                    $('#status-' + id).removeClass('green-text fa-unlock-alt').addClass('amber-text fa-lock"');
                } else {
                    $('#status-' + id).removeClass('amber-text fa-lock"').addClass('green-text fa-unlock-alt');
                }
                $('#status-' + id).attr('data-value', block);
            },
            error: function (request) {
                hide_loader_top();
                show_modal_error(general_text.sas_errorInterno + "<br>" + request.responseText);
            }

        });
    });
}

