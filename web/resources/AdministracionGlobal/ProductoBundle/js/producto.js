$(init);

function init() {
    $(".container").css("width", "90%");
    $('select').material_select();
    $("#add-record").on("click", addProduct);
    $(document).on("click", ".mode-edit-record", function () {
        var active = $(".products-panel input").length - $(".products-panel input[disabled]").length;
        if (active != 0) {
            show_alert("warning", section_text.sas_aseguraSeleccionarModulos);
            return;
        }
        var id = $(this).attr("id-record");
        $(this).parents('ul').siblings('input[type="text"]').removeAttr("disabled").focus();
        $(this).parents('ul').siblings('a.blue').hide();
        $(this).parents('ul').siblings('a.green').show();
        loadModules(id);
    });
    $(document).on("click", ".duplicate-record", function () {
        var active = $(".products-panel input").length - $(".products-panel input[disabled]").length;
        if (active != 0) {
            show_alert("warning", section_text.sas_aseguraSeleccionarModulos);
            return;
        }
        var id = $(this).attr("id-record");
        duplicateData(id);
    });
    $(document).on("click", ".edit-record", function () {
        updateData(this)
    });
    $(document).on("click", ".save-record", function () {
        saveData(this);
    })
    $(".module-check").on("change", function () {
        if ($(this).attr("is-parent") == "1") {
            var parent = this;
            $.each($(".module-check[id-parent='" + $(parent).attr("id-record") + "']"), function (i, e) {
                if ($(parent).is(":checked")) {
                    $(e).prop("checked", true);
                } else {
                    $(e).prop("checked", false);
                }
                if ($(e).attr("is-parent") == "1") {
                    $(e).trigger("change");
                }
            });
        }
    });
}

function addProduct() {
    var active = $(".products-panel input").length - $(".products-panel input[disabled]").length;
    var l = $(".products-panel input").length + 1;
    if (active != 0) {
        show_alert("warning", "Recuerda guardar todos los productos antes de intentar agregar uno nuevo.");
        return;
    }
    var p = '<li class="collection-item dismissable" ><div>' +
            '<input id-record="" type="text" class="validate nombre" value="">' +
            '<a style="display:none;" class="secondary-content dropdown-button btn-floating waves-effect waves-light btn-sm blue" data-activates="dropdown' + l + '"><i class="tiny material-icons">mode_edit</i></a>' +
            '<ul id="dropdown' + l + '" class="dropdown-content">' +
            '<li><a id-record="" class="mode-edit-record">Editar</a></li>' +
            '<li><a id-record="" class="duplicate-record">Duplicar</a></li></ul>' +
            '<a id-record="" class="secondary-content dropdown-button btn-floating waves-effect waves-light btn-sm green save-record">' +
            '<i class="tiny material-icons">done</i></a></div></li>';
    $(".products-panel .collection").append($(p));
    $(".products-panel .collection input").last().addClass("valid").focus();
    $(".collection").scrollTop(100000000);
    $("#modules-overlay").fadeOut();
    $(".dropdown-button").dropdown();
}

function saveData(e) {
    show_loader_top();
    var mds = "";
    var name = $(e).siblings("input[type='text']").val();
    $.each($(".module-check:checked"), function (i, e) {
        mds += $(e).attr("id-record") + ",";
    });
    mds = mds.substring(0, mds.length - 1);
    if (name == "" || mds == "") {
        show_alert("warning", section_text.sas_aseguraSeleccionarModulos);
        $(".modules-panel").addClass("modules-panel-highlight");
        $(e).siblings("input[type='text']").addClass("input-highlight");
        setTimeout(function () {
            $(".modules-panel").removeClass("modules-panel-highlight");
            $(e).siblings("input[type='text']").removeClass("input-highlight");
        }, 5000);
        hide_loader_top();
        return;
    }
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_insert, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: {
            modulos: mds,
            Nombre: name
        }, // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            $("#modules-overlay").fadeIn();
            $(e).siblings("input[type='text']").attr("disabled", "disabled").removeClass("valid");
            $(e).hide();
            $(e).siblings("a.blue").show();
            $(e).siblings(".dropdown-content").find(".mode-edit-record").attr("id-record", response['data']['idProductoIxpo']);
            $(e).siblings(".dropdown-content").find(".duplicate-record").attr("id-record", response['data']['idProductoIxpo']);
            $.each($(".module-check[id-parent='0']"), function (i, e) {
                $(this).prop("checked", false);
                $(this).trigger("change");
            });
            show_alert("success", general_text.sas_guardoExito);
            pr[response['data']['idProductoIxpo']] = response['data'];
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
}

function updateData(e) {
    show_loader_top();
    var id = $(e).attr("id-record");
    var mds = "";
    var name = $(e).siblings("input[type='text']").val();
    $.each($(".module-check:checked"), function (i, e) {
        mds += $(e).attr("id-record") + ",";
    });
    mds = mds.substring(0, mds.length - 1);
    if (name == "" || mds == "") {
        show_alert("warning", "Recuerda ingresar el nombre del producto y seleccionar los módulos asignados.");
        $(".modules-panel").addClass("modules-panel-highlight");
        $(e).siblings("input[type='text']").addClass("input-highlight");
        setTimeout(function () {
            $(".modules-panel").removeClass("modules-panel-highlight");
            $(e).siblings("input[type='text']").removeClass("input-highlight");
        }, 5000);
        hide_loader_top();
        return;
    }
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_update, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: {
            idProducto: id,
            modulos: mds,
            Nombre: name
        }, // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            $("#modules-overlay").fadeIn();
            $(e).siblings("input[type='text']").attr("disabled", "disabled").removeClass("valid");
            $(e).hide();
            $(e).siblings("a.blue").show();
            $.each($(".module-check[id-parent='0']"), function (i, e) {
                $(this).prop("checked", false);
                $(this).trigger("change");
            });
            show_alert("success", general_text.sas_guardoExito);
            pr[response['data']['idProductoIxpo']] = response['data'];
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
}

function duplicateData(i) {
    show_loader_top();
    $.ajax({
        type: "post", // podría ser get, post, put o delete.
        url: url_duplicate, // url del recurso
        dataType: 'json', // formato que regresa la respuesta
        data: {
            id: i
        }, // datos a pasar al servidor, en caso de necesitarlo
        success: function (response) {
            hide_loader_top();
            if (!response['status']) {
                show_alert("danger", response['data']);
                return;
            }
            show_alert("success", general_text.sas_guardoExito);
            addProduct();
            var e = $(".products-panel input:last");
            $(e).val(response['data']['ProductoIxpo']);
            //$(e).siblings("a.blue").show();
            //$(e).siblings("a.hide").green();
            $(e).siblings(".dropdown-content").find(".mode-edit-record").attr("id-record", response['data']['idProductoIxpo']);
            $(e).siblings(".dropdown-content").find(".duplicate-record").attr("id-record", response['data']['idProductoIxpo']);
            $(e).parents(".collection-item").addClass("highlight-bg");
            pr[response['data']['idProductoIxpo']] = response['data'];
            setTimeout(function () {
                 $(e).parents(".collection-item").removeClass("highlight-bg");
            }, 3000);
            loadModules(response['data']['idProductoIxpo']);
        },
        error: function (request, status, error) {
            hide_loader_top();
            show_modal_error(request.responseText);
        }
    });
}

function loadModules(i) {
    $("#modules-overlay").fadeOut();
    $.each(pr[i]['Modulos'], function (i, e) {
        $(".module-check[id-record='" + e + "']").prop("checked", true);
    });
}