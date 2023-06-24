/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(init);
var validator;
function init() {
    initForm();
}

function initForm() {
    $("#frm-module").validate({
        rules: {
            'idPlataformaIxpo': {
                required: true
            },
            'idPadre': {
                required: true
            },
            'Modulo_ES': {
                required: true,
            },
            'Orden': {
                required: true,
                number: true
            }
        },
        messages: {
            'idPlataformaIxpo': {
                required: general_text.sas_campoRequerido,
            },
            'idPadre': {
                required: general_text.sas_campoRequerido
            },
            'Modulo_ES': {
                required: general_text.sas_campoRequerido,
            },
            'Orden': {
                required: general_text.sas_campoRequerido,
                number: general_text.sas_soloDigitos
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
                if ($(element).attr('type') === "file") {
                    element = $(element).parents('.file-field').find('input[type="text"]');
                }
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            show_loader_wrapper();
            form.submit();
        }
    });

}

var options_nestable = {};
options_nestable.maxDepth = 2;
options_nestable.callback = function (l, e) {
    var data = {};
    data.idModuloIxpo = e.attr("data-id");
    data.Orden = parseInt(e.index()) + 1;
    data.idPadre = 0;
    data.Nivel = 1;
    var padre = $(e).parents(".dd3-item")[0];

    if (isset($(padre).attr("data-id"))) {
        data.idPadre = $(padre).attr("data-id");
        data.Nivel = 2;
    }

    /* Todos los elementos deben tener valores */
    if (!issetGroup(data)) {
        return;
    }
    /* Si no han cambiado los valores, no hacemos la actualizacion */
    if (modulo_isEqual(data)) {
        return;
    }

    /* Mostramos loader */
    setLoader("li[data-id=" + data.idModuloIxpo + "] > .dd3-content");

    $.ajax({
        type: "post",
        url: url_update,
        dataType: 'json',
        data: data,
        success: function (result) {
            removeLoader("li[data-id=" + data.idModuloIxpo + "]");
            if (!result['status']) {
                show_alert("danger", result['data']);
                return;
            }
            modulos[data.idModuloIxpo]["idPadre"] = data.idPadre;
            modulos[data.idModuloIxpo]["Orden"] = data.Orden;
            modulos[data.idModuloIxpo]["Nivel"] = data.Nivel;
            show_alert("success", general_text['sas_exitoGuardado']);
        },
        error: function (request, status, error) {
            removeLoader("li[data-id=" + data.idModuloIxpo + "]");
            show_modal_error(request.responseText);
        }
    });
};

$('.dd').nestable(options_nestable);

function issetGroup(data) {
    var is_defined = true;
    $.each(data, function (id, value) {
        if (!isset(value)) {
            is_defined = false;
        }
    });
    return is_defined;
}

function modulo_isEqual(data) {
    var modulo = modulos[data.idModuloIxpo];
    if (parseInt(modulo.idPadre) === parseInt(data.idPadre) && parseInt(modulo.Orden) === parseInt(data.Orden)) {
        return true;
    }
    return false;
}

function setLoader(element) {
    var progress = $("<div/>", {"class": "progress"});
    $("<div/>", {"class": "indeterminate"}).appendTo(progress);
    $(element).before(progress);
}

function removeLoader(element) {
    $(element).find(".progress").remove();
}

$(document).on("click", ".add-record", function () {
    clearForm();
    $(".eliminar-modulo-block").css("display", "none");
    $("#mdl-module").modal("open");
    $(".modal-content").scrollTop(0);
});

function clearForm() {
    $('#frm-module')[0].reset();
    $('#frm-module input[type=hidden]').val("");
}

$(document).on("change", "#idPlataformaIxpo", cambioIdPlataformaIxpo);

function cambioIdPlataformaIxpo() {
    var idPlataformaIxpo = $("#idPlataformaIxpo").val();
    $("#idPadre").html("");
    $("<option/>", {
        "value": 0,
        "text": section_text['sas_sinModuloPadre']
    }).appendTo("#idPadre");

    if (!isset(items[idPlataformaIxpo])) {
        return;
    }
    var modulos = items[idPlataformaIxpo];

    if (isset(modulos["Modulos"])) {
        $.each(modulos["Modulos"], function (id, modulo) {
            $("<option/>", {
                "value": modulo['idModuloIxpo'],
                "text": modulo['ModuloIxpo']
            }).appendTo("#idPadre");
        });
    }

    $("#Orden").val(Object.keys(modulos["Modulos"]).length + 1).change();
    $("#Nivel").val("1");
}

$(document).on("change", "#idPadre", cambioIdPadre);

function cambioIdPadre() {
    var idPlataformaIxpo = $("#idPlataformaIxpo").val();
    var modulos = items[idPlataformaIxpo];
    var idPadre = $("#idPadre").val();
    if (isset(modulos['Modulos'][idPadre])) {
        var submodulos = modulos['Modulos'][idPadre];
        var orden = 1;
        if (isset(submodulos["SubModulos"])) {
            orden = Object.keys(submodulos["SubModulos"]).length + 1;
        }
        $("#Orden").val(orden).change();
        $("#Nivel").val("2");
    } else {
        $("#Orden").val(Object.keys(modulos["Modulos"]).length + 1).change();
        $("#Nivel").val("1");
    }
}

$(document).on("click", ".edit-modulo", showEditModulo);

function showEditModulo() {
    var idModuloIxpo = $(this).attr("id-modulo");
    if (!isset(modulos[idModuloIxpo])) {
        return;
    }
    var modulo = modulos[idModuloIxpo];
    clearForm();
    $("#idModuloIxpo").val(modulo['idModuloIxpo']);
    $("#Nivel").val(modulo['Nivel']).change();
    $("#idPlataformaIxpo").val(modulo['idPlataformaIxpo']).change();
    $("#idPadre").val(modulo['idPadre']).change();
    $("#Ruta").val(modulo['Ruta']).change();
    $("#Modulo_ES").val(modulo['Modulo_ES']).change();
    $("#Modulo_EN").val(modulo['Modulo_EN']).change();
    $("#Orden").val(modulo['Orden']).change();

    if (modulo['Publicado']) {
        $("#Publicado").prop("checked", true).change();
    } else {
        $("#Publicado").prop("checked", false).change();
    }

    $(".eliminar-modulo-block").css("display", "block");
    $("#mdl-module").modal("open");
    $(".modal-content").scrollTop(0);
}

$(document).on("click", ".ver-eliminar-modulo", advertenciaEliminarModulo);

function advertenciaEliminarModulo() {
    if (!isset(modulos[$("#idModuloIxpo").val()])) {
        return;
    }
    $("#frm-module-delete input[name='idModuloIxpo']").val($("#idModuloIxpo").val());
    $("#modulo-nombre").text(modulos[$("#idModuloIxpo").val()]['Modulo_' + lang.toUpperCase()]);
    $("#mdl-delete-module").modal("open");
}