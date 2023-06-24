var oTable = "", slide = 0;

$(document).ready(function () {
    init();
});

function init() {
    initTable();
    validateFilters();
    createFilterChips();

    $(document).on("click", "#search-icon", function () {
        createFilters(this);
        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: 9,
            format: 'yyyy-mm-dd'
        });
        setFiltersFields();
    });

    $(document).on("dblclick", ".view-exhibitor", function () {
        show_loader_wrapper();
        var link = url_edit_empresa_data + "/" + $(this).attr('id');
        window.location = link;
    });

    $(document).on("click", "#cancel", function () {
        $("#search-icon").css("background", "");
        $("#filter-combo").slideUp();
        slide = 0;
    });

    $(document).on("click", "#applyFilters", function () {
        $("#search-icon").css("background", "");
        slide = 0;
        $("#filters-form").submit();
    });

    $(document).on("click", "#clearFilters", function () {
        clearFilters();
    });


}


function initTable() {
    if (oTable != "") {
        oTable.destroy();
        $("#exhibitors-table thead").html("");
        $("#exhibitors-table tbody").html("");
        $('.tooltipped').tooltip();
    }
    constructTableHeader();
    constructTableBody();
    oTable = $('#exhibitors-table').DataTable({
        "language": {
            "url": url_lang
        },
        "order": [[0, "asc"]],
        "lengthMenu": [[25, 50, 100], [25, 50, 100]]
    });
    $('.tooltipped').tooltip();
    oTable.column(0).visible(false);
}

function constructTableHeader() {
    var tr = "", td = "", item = "";

    $("#exhibitors-table thead").html("");

    var keys = Object.keys(exhibitors_metadata);
    var total = keys.length;

    tr = $('<tr/>');
    for (var i = 0; i < total; i++) {
        item = exhibitors_metadata[keys[i]];
        if (item["is_visible"]) {
            td = $('<th/>', {
                'html': item["text"]
            });

            $(tr).append(td);
        }
    }
    $("#exhibitors-table thead").append(tr);
}

function constructTableBody() {
    var tr = "", td = "", item = "", lb = "";

    $("#exhibitors-table tbody").html("");
    if (exhibitors == null || exhibitors == "")
        return;

    var keys = Object.keys(exhibitors);
    var total = keys.length;
    var keys_item = Object.keys(exhibitors[keys[0]]);
    var keys_item_total = keys_item.length;

    for (var i = 0; i < total; i++) {
        item = exhibitors[keys[i]];
        tr = $("<tr/>", {
            'id': item["idEmpresa"],
            'class': 'view-exhibitor'
        });

        for (var j = 0; j < keys_item_total; j++) {
            if (exhibitors_metadata[keys_item[j]]["is_visible"]) {
                td = $("<td/>", {
                    'id': keys_item[j],
                    'html': item[keys_item[j]]
                });
                $(tr).append(td);
            }
        }
        $("#exhibitors-table tbody").append(tr);
    }
    hide_loader_top();
}

function setFiltersFields() {
    if (active_filters == "")
        return;

    var keys = Object.keys(active_filters);
    var total = keys.length;

    for (var i = 0; i < total; i++) {
        $('input#' + keys[i]).val(active_filters[keys[i]]);
        if (active_filters[keys[i]] != "")
            $('input#' + keys[i]).removeClass('valid').next().addClass('active');
    }

}

function createFilters(e) {
    $('#filters-form').html("");
    var div = "", lb = "", column1 = "", column2 = "", column3 = "", item = "", data = "";

    var keys = Object.keys(exhibitors_metadata);
    var total = keys.length;

    column1 = $('<div/>', {'class': 'col s4'});
    column2 = $('<div/>', {'class': 'col s4'});
    column3 = $('<div/>', {'class': 'col s4'});

    var count = 1;
    for (var i = 0; i < total; i++) {
        item = exhibitors_metadata[keys[i]];
        if (item['is_filter']) {
            div = $('<div/>', {'class': 'input-field col s12'});
            switch (item['data-type']) {
                case 'text':
                    var input = $('<input/>', {
                        'id': keys[i],
                        'name': keys[i],
                        'class': 'validate',
                        'type': 'text'
                    });
                    lb = $('<label/>', {
                        'for': keys[i],
                        'html': item['text']
                    });

                    $(div).append(input);
                    $(div).append(lb);
                    break;
                case 'numeric':
                    var input = $('<input/>', {
                        'id': keys[i],
                        'name': keys[i],
                        'class': 'validate',
                        'type': 'text'
                    });
                    lb = $('<label/>', {
                        'for': keys[i],
                        'html': item['text']
                    });

                    $(div).append(input);
                    $(div).append(lb);
                    break;
                case 'select':
                    var keys_item = Object.keys(item['data']);
                    var total_item = keys_item.length;

                    lb = $('<label/>', {
                        'for': keys[i],
                        'class': "active space-label",
                        'html': item['text']
                    });
                    var select = $('<select/>', {
                        'id': keys[i],
                        'name': keys[i],
                        'class': 'browser-default validate'
                    });

                    for (var j = 0; j < total_item; j++) {
                        data = item['data'][keys_item[j]];
                        var option = $('<option/>', {
                            'value': keys_item[j],
                            'html': data
                        });
                        $(select).append(option);
                    }
                    $(div).append(lb);
                    $(div).append(select);
                    break;
                case 'date':
                    var keys_item = Object.keys(item['data']);
                    var total_item = keys_item.length;

                    for (var j = 0; j < total_item; j++) {
                        data = item['data'][keys_item[j]];
                        var div_in = $("<div/>", {'class': 'input-field col s6 date-div'});
                        var input = $('<input/>', {
                            'id': keys_item[j],
                            'name': keys_item[j],
                            'class': 'datepicker',
                            'type': 'date'
                        });
                        lb = $('<label/>', {
                            'for': keys_item[j],
                            'html': data
                        });

                        $(div_in).append(lb);
                        $(div_in).append(input);
                        $(div).append(div_in);
                    }
                    break;
                default:
                    break;
            }

            if (count == 1) {
                $(column1).append(div);
                count++;
            } else if (count == 2) {
                $(column2).append(div);
                count++;
            } else {
                $(column3).append(div);
                count = 1;
            }
        }
    }
    $('#filters-form').append(column1);
    $('#filters-form').append(column2);
    $('#filters-form').append(column3);

    var width = $('#search-icon').width();
    var height = $('#search-icon').height();
    var pos = $(e).position();
    var top = pos.top + height + 5;
    var left = pos.left;

    $("#filter-combo").css("top", top);
    $("#filter-combo").css("left", left);
    $("#filter-combo").css("width", width);
    if (slide == 0) {
        $("#search-icon").css("background", "lightgrey");
        $("#filter-combo").slideDown();
        slide = 1;
    } else {
        $("#search-icon").css("background", "");
        $("#filter-combo").slideUp();
        slide = 0;
    }
}

function validateFilters() {
    $("#filters-form").validate({
        rules: {
            'idEmpresa': {
                digits: true
            },
            'LectorasSolicitadas': {
                digits: true
            },
            'LectorasAsignadas': {
                digits: true
            },
            'LectorasDevueltas': {
                digits: true
            }
        },
        messages: {
            'idEmpresa': {
                digits: general_text.sas_soloDigitos
            },
            'LectorasSolicitadas': {
                digits: general_text.sas_soloDigitos
            },
            'LectorasAsignadas': {
                digits: general_text.sas_soloDigitos
            },
            'LectorasDevueltas': {
                digits: general_text.sas_soloDigitos
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
            $("#filter-combo").toggle("slow");
            var post = $('#filters-form').serialize();
            applyFilters(post);
            return;
        }
    });
}

function applyFilters(post) {
    show_loader_top();
    $.ajax({
        type: "post",
        url: url_exhibitors_filters,
        dataType: 'json',
        data: post,
        success: function (response) {
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            exhibitors = response.data;
            active_filters = response.filters_post;
            initTable();
            createFilterChips();
            hide_loader_top();
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
    hide_loader_wrapper();
}


function clearFilters() {
    $("#filters-form input[type='text'], textarea").val("").removeClass('active').next().addClass('valid');
    $("#filters-form .datepicker").pickadate({clear: 'Clear'}).removeClass('active').next().addClass('valid');
    $("#filters-form select option[value='0']").prop("selected", true);
}


function createFilterChips() {
    $("#active-filters").html("");
    var chip = "";
    var keys = Object.keys(active_filters);
    var total = keys.length;

    for (var i = 0; i < total; i++) {
        if (active_filters[keys[i]] != "" && active_filters[keys[i]] != null) {
            chip = $('<div/>', {'class': 'active-filter'});
            $(chip).html('<strong>' + exhibitors_metadata[keys[i]]['text'] + '</strong>: ' + active_filters[keys[i]]);
            $("#active-filters").append(chip);
        }
    }

    if ($("#active-filters div").length == 0) {
        chip = $('<div/>', {
            'class': 'active-filter',
            'html': "<strong>" + general_text["sas_mostrandoTodosRegistros"] + "</strong>"
        });

        $("#active-filters").append(chip);
    }
    $('.chips').material_chip();
}









