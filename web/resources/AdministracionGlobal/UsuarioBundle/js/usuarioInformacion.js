/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(init);
function init() {
    initFormUsuario();
    $("input[name='idEdicion[]']").change();
    initEdicionesUsuario();
}

function initFormUsuario() {
    var formID = "#frm-usuario";
    $(formID).validate({
        rules: {
            'Nombre': {
                required: true
            },
            'Puesto': {
                required: true
            },
            'Email': {
                required: true
            },
            'Password': {
                required: true
            },
            'idComiteOrganizador': {
                required: true
            },
            'idTipoUsuario': {
                required: true
            },
        },
        messages: {
            'Nombre': {
                required: general_text.sas_requerido
            },
            'Puesto': {
                required: general_text.sas_requerido
            },
            'Email': {
                required: general_text.sas_requerido
            },
            'Password': {
                required: general_text.sas_requerido
            },
            'idComiteOrganizador': {
                required: general_text.sas_requerido
            },
            'idTipoUsuario': {
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
            show_loader_wrapper();
            form.submit();
        }
    });
}

$("input[name='idEdicion[]']").on("change", activarDesactivarEdicion);

function activarDesactivarEdicion() {
    var edicion = ediciones[$(this).val()];
    if (!$(this).is(":checked")) {
        ocultarEdicionActiva(edicion['idEdicion']);
        $(".tab-edicion[id-edicion=" + edicion['idEdicion'] + "] .permisos-edicion").html("");
        return;
    }

    construirEdicionPermisos(edicion['idEdicion']);
    mostrarEdicionActiva(edicion['idEdicion']);
}

function construirEdicionPermisos(idEdicion) {
    var table = $("<table>", {
        "id-edicion": idEdicion,
        "class": "bordered highlight responsive-table"
    }).appendTo(".tab-edicion[id-edicion=" + idEdicion + "] .permisos-edicion");
    construirThead(idEdicion);
    construirTbody(idEdicion);
    if (isset(usuario["Ediciones"]) && isset(usuario["Ediciones"][idEdicion])) {
        initPermisosEdicionUsuario(idEdicion, usuario["Ediciones"][idEdicion]);
    }

    /* efecto header fixed */
    table.floatThead({
        scrollContainer: function (table) {
            return table.closest('.permisos-edicion');
        },
        position: 'fixed',
        zIndex: 995
    });
}

function construirThead(idEdicion) {
    var thead = $("<thead>").appendTo("table[id-edicion=" + idEdicion + "]");
    var tr = $("<tr>").appendTo(thead);
    $("<th>", {"text": "Modulo"}).appendTo(tr);
    $("<th>", {"class": "center-align th-check", "id-edicion": idEdicion, "text": "Ver", "permiso": "ver"}).appendTo(tr);
    $("<th>", {"class": "center-align th-check", "id-edicion": idEdicion, "text": "Editar", "permiso": "editar"}).appendTo(tr);
    $("<th>", {"class": "center-align th-check", "id-edicion": idEdicion, "text": "Borrar", "permiso": "borrar"}).appendTo(tr);

    $("table[id-edicion=" + idEdicion + "] .th-check").on("click", checkUncheckAll);
}

function construirTbody(idEdicion) {
    $("<tbody>").appendTo("table[id-edicion=" + idEdicion + "]");
    contruirPlataformasEdicion(idEdicion);
}

function contruirPlataformasEdicion(idEdicion) {
    var tbody = $("table[id-edicion=" + idEdicion + "] tbody");

    $.each(plataformas, function (idPlataformaIxpo, plataforma) {
        var tr = $("<tr>", {
            "class": "plataforma-ixpo",
            "id-plataforma-ixpo": plataforma['idPlataformaIxpo']
        }).appendTo(tbody);

        $("<td>", {
            "colspan": "4",
            "text": plataforma['PlataformaIxpo'],
        }).appendTo(tr);

        /* Modulos de la plataforma */
        contruirModulosPlataforma(modulos[idPlataformaIxpo], idEdicion);
    });
}

function contruirModulosPlataforma(modulos, idEdicion, submodulo) {
    if (!isset(modulos)) {
        return;
    }

    if (submodulo === undefined) {
        submodulo = false;
    }

    var tbody = $("table[id-edicion=" + idEdicion + "] tbody");

    $.each(modulos, function (key, modulo) {
        var tr = null;
        var td = null;
        var p = null;

        var clase = (!submodulo) ? "modulo-ixpo" : "submodulo-ixpo";
        tr = $("<tr>", {
            "class": clase,
            "id-modulo-ixpo": modulo['idModuloIxpo']
        }).appendTo(tbody);

        // td nombre del modulo
        td = $("<td>").appendTo(tr);
        $('<label/>', {
            "class": "lbl-modulo",
            "for": "id-modulo-ixpo-" + idEdicion + "-" + modulo['idModuloIxpo'] + "-v",
            "text": modulo['Modulo_' + lang.toUpperCase()]
        }).appendTo(td);

        // td permiso ver
        td = $("<td>", {"class": "center-align"}).appendTo(tr);
        //p = $('<p>').appendTo(td);
        $('<input/>', {
            "type": "checkbox",
            "permiso": "ver",
            "id-edicion": idEdicion,
            "id-modulo-ixpo": modulo['idModuloIxpo'],
            "id": "id-modulo-ixpo-" + idEdicion + "-" + modulo['idModuloIxpo'] + "-v",
            "name": "idModuloIxpo[" + idEdicion + "][" + modulo['idModuloIxpo'] + "][v]",
            "value": true
        }).appendTo(td);
        /*$('<label/>', {
         "for": "id-modulo-ixpo-" + idEdicion + "-" + modulo['idModuloIxpo'] + "-v",
         }).appendTo(p)*/;

        // td permiso editar
        td = $("<td>", {"class": "center-align"}).appendTo(tr);
        //p = $('<p>').appendTo(td);
        $('<input/>', {
            "type": "checkbox",
            "permiso": "editar",
            "id-edicion": idEdicion,
            "id-modulo-ixpo": modulo['idModuloIxpo'],
            "id": "id-modulo-ixpo-" + idEdicion + "-" + modulo['idModuloIxpo'] + "-e",
            "name": "idModuloIxpo[" + idEdicion + "][" + modulo['idModuloIxpo'] + "][e]",
            "value": true
        }).appendTo(td);
        /*$('<label/>', {
         "for": "id-modulo-ixpo-" + idEdicion + "-" + modulo['idModuloIxpo'] + "-e",
         }).appendTo(p);*/

        // td permiso borrar
        td = $("<td>", {"class": "center-align"}).appendTo(tr);
        //p = $('<p>').appendTo(td);
        $('<input/>', {
            "permiso": "borrar",
            "id-edicion": idEdicion,
            "id-modulo-ixpo": modulo['idModuloIxpo'],
            "type": "checkbox",
            "id": "id-modulo-ixpo-" + idEdicion + "-" + modulo['idModuloIxpo'] + "-b",
            "name": "idModuloIxpo[" + idEdicion + "][" + modulo['idModuloIxpo'] + "][b]",
            "value": true
        }).appendTo(td);
        /*$('<label/>', {
         "for": "id-modulo-ixpo-" + idEdicion + "-" + modulo['idModuloIxpo'] + "-b",
         }).appendTo(p);*/

        /* Si tiene submodulos, lo pintamos */
        if (isset(modulo['SubModulos'])) {
            contruirModulosPlataforma(modulo['SubModulos'], idEdicion, true);
        }
    });
    $("input[permiso='ver']").on("change", controlPermisoVer);
    $("input[permiso='editar'], input[permiso='borrar']").on("change", controlPermisoEditarBorrar);
}

function controlPermisoVer() {
    var idEdicion = $(this).attr("id-edicion");
    var idModuloIxpo = $(this).attr("id-modulo-ixpo");
    if (!$(this).is(":checked")) {
        $("input[id-edicion='" + idEdicion + "'][id-modulo-ixpo='" + idModuloIxpo + "']:not([permiso='ver']):checked").trigger("click");
    }
}

function controlPermisoEditarBorrar() {
    var idEdicion = $(this).attr("id-edicion");
    var idModuloIxpo = $(this).attr("id-modulo-ixpo");

    if ($(this).is(":checked") && !$("input[id-edicion='" + idEdicion + "'][id-modulo-ixpo='" + idModuloIxpo + "'][permiso='ver']").is(":checked")) {
        $("input[id-edicion='" + idEdicion + "'][id-modulo-ixpo='" + idModuloIxpo + "'][permiso='ver']").trigger("click");
        return;
    }
}

$("#chk-cambiar-password").on("change", activarCambioPassword);

function activarCambioPassword() {
    if ($(this).is(":checked")) {
        $("#Password").removeAttr("disabled");
        $("#Password").attr("type", "text");
        $("#Password").val("");
        $("#Password").focus();
        $(".btn-generate-password").fadeIn();
        return;
    }
    $("#Password").attr("type", "password");
    $("#Password").val("____");
    $("#Password").attr("disabled", "disabled");
    $("label[for='Password']").addClass("active");
    $(".btn-generate-password").fadeOut();
}

$(".btn-generate-password").click(function () {
    $("#Password").val(generatePassword());
    $("#Password").focus();
});
function generatePassword() {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < 6; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}

function initEdicionesUsuario() {
    if (!isset(usuario['Ediciones'])) {
        return;
    }
    $.each(usuario['Ediciones'], function (idEdicion, permisos) {
        //$("input[name='idEdicion[]'][value=" + idEdicion + "]:not('checked')").trigger("click");
        $("input[name='idEdicion[]'][value=" + idEdicion + "]:not('checked')").prop("checked", true).change();
    });
    $(".collection-item.active .edicion-item").trigger("click");
}

function initPermisosEdicionUsuario(idEdicion, permisos) {
    if (!isset(permisos)) {
        return;
    }
    $.each(permisos, function (idModulo, permiso) {
        if (permiso["Ver"]) {
            document.getElementById("id-modulo-ixpo-" + idEdicion + "-" + idModulo + "-v").checked = true;
        }
        if (permiso["Editar"]) {
            document.getElementById("id-modulo-ixpo-" + idEdicion + "-" + idModulo + "-e").checked = true;
        }
        if (permiso["Borrar"]) {
            document.getElementById("id-modulo-ixpo-" + idEdicion + "-" + idModulo + "-b").checked = true;
        }
    });
}

$(".ediciones .edicion-item").on("click", mostrarPermisosEdicion);

function mostrarPermisosEdicion() {
    var collectionItem = $(this).parent(".collection-item");
    var idEdicion = collectionItem.attr("id-edicion");
    if (collectionItem.hasClass("active")) {
        ocultarEdicionActiva(idEdicion);
        return;
    }
    mostrarEdicionActiva(idEdicion);
}

function ocultarEdicionActiva(idEdicion) {
    $(".ediciones a[id-edicion=" + idEdicion + "].active").removeClass("active");
    $(".tab-edicion[id-edicion=" + idEdicion + "]").css("display", "none");
}

function mostrarEdicionActiva(idEdicion) {
    if (!isset(ediciones[idEdicion])) {
        show_alert("danger", general_text['sas_errorInterno']);
        return;
    }
    var edicion = ediciones[idEdicion];

    if (!$("input[name='idEdicion[]'][value=" + idEdicion + "]").is(":checked")) {
        show_alert("info", section_text['sas_activaEdicion'].replace("%edicion%", edicion['Edicion_' + lang.toUpperCase()]));
        return;
    }
    $(".ediciones a.active").removeClass("active");
    $(".tab-edicion").css("display", "none");
    $(".tab-edicion[id-edicion=" + idEdicion + "]").fadeIn();
    $(".ediciones a[id-edicion=" + idEdicion + "]").addClass("active");
    $('table[id-edicion=' + idEdicion + ']').floatThead('reflow');
}

function checkUncheckAll() {
    var permiso = $(this).attr("permiso");
    var idEdicion = $(this).attr("id-edicion");
    if ($('table[id-edicion=' + idEdicion + '] input[permiso="' + permiso + '"]:checked').length === $('table[id-edicion=' + idEdicion + '] input[permiso="' + permiso + '"]').length) {
        $('table[id-edicion=' + idEdicion + '] input[permiso="' + permiso + '"]:checked').prop('checked', false).change();
    } else {
        $('table[id-edicion=' + idEdicion + '] input[permiso="' + permiso + '"]:not(":checked")').prop('checked', true).change();
    }
}