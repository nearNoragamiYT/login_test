/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            return $('#seller').find('option:selected').attr('data-seller');
        }
);
var table = "";
$(init);

function init() {
    table = $('#tabla-gafetes').DataTable({
        "order": [[($('#tabla-gafetes th').length - 1), "desc"]],
        "language": {
            "url": url_lang
        },
        initComplete: function () {
            this.api().columns([0]).every(function () {
                var column = this;
                var select = $('<select><option value=""></option></select>')
                        .appendTo($(column.footer()).empty())
                        .on('change', function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                    $(this).val()
                                    );

                            column
                                    .search(val ? '^' + val + '$' : '', true, false)
                                    .draw();
                        });

                column.data().unique().sort().each(function (d, j) {
                    select.append('<option value="' + d + '">' + d + '</option>');
                });
            });
        }
    });
    $('#generate-pdf').on('click', function () {
        var id = $(this).attr('data-id');
    });
    $('#seller').on('change', function () {
        var seller = sellersAssc[$(this).val()];
        $("#idVendedor").val(seller['idUsuario']).next().addClass('active');
        $("#Nombre").val(seller['Nombre']).next().addClass('active');
        $("#Email").val(seller['Email']).next().addClass('active');
        $("#generate-pdf").attr("href", url_show_seller_badges + "/" + seller['idUsuario']);
    });
}