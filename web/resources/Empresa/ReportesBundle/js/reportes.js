/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var iCol = "";
var table = "";
$(init);

/**
 * funcion inical
 */
function init() {
    show_loader_wrapper();
    $("#colfixed").material_select();
    $("#colhide").material_select();

    /*$('#view-report').css('display', 'none');*/


    /*show_loader_wrapper();
     var head = document.getElementById('table-report').getElementsByTagName('thead');
     var body = document.getElementById('table-report').getElementsByTagName('tbody');
     var tr = document.createElement('tr');
     $.each(headers, function (index, value) {
     var th = document.createElement('th');
     th.textContent = value;
     tr.appendChild(th);
     });
     $(head).append(tr);
     $.each(data, function (index, value) {
     tr = document.createElement('tr');
     $.each(value, function (i, val) {
     var td = document.createElement('td');
     td.textContent = val;
     tr.appendChild(td);
     });
     $(body).append(tr);
     });*/

    table = $("#table-report").DataTable({
        "scrollX": true,
        "scrollY": '60vh',
        "pageLength": 25,
        "language": {
            "url": url_lang

        },
        fixedColumns: {
            leftColumns: $('#colfixed').val()
        },
        initComplete: function () {
            hide_loader_wrapper();
            $('#view-report').attr('style', 'display:block');
        }

    });
    $('#colfixed').change(function () {
        show_loader_wrapper();
        $('#view-report').attr('style', 'display:none');

        table.destroy();
        table = $("#table-report").DataTable({
            "scrollX": true,
            "scrollY": '60vh',
            "pageLength": 25,
            "language": {
                "url": url_lang
            },
            fixedColumns: {
                leftColumns: $('#colfixed').val()
            },
            initComplete: function () {
                hide_loader_wrapper();
                $('#view-report').attr('style', 'display:block');
            }

        });
    });


    /*

     setTimeout(
     function ()
     {
     $('#view-report').css('display', '');
     }, 100);*/
//    table.on('stateLoaded', function () {
//
//    });


}

