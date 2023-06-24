/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(init);

function init() {
    $('#tbl-usuarios').DataTable({
        "language": {
            "url": url_lang,
            "scrollX": true
        }});
}

$(".delete-record").on("click", notificacionDesactivarUsuario);

function notificacionDesactivarUsuario() {
    var idUsuario = $(this).attr("id-record");
    if (!isset(usuarios[idUsuario])) {
        return;
    }

    var usuario = usuarios[idUsuario];
    $("#modal-delete-usuario #idUsuario").val(usuario['idUsuario']);
    $("#modal-delete-usuario .usuario").text(usuario['Nombre']);
    $("#modal-delete-usuario").modal("open");
}

$(".reactivar-cuenta").on("click", notificacionReactivarUsuario);

function notificacionReactivarUsuario() {
    var idUsuario = $(this).attr("id-record");
    if (!isset(usuarios[idUsuario])) {
        return;
    }

    var usuario = usuarios[idUsuario];
    $("#modal-reactivar-usuario #idUsuario").val(usuario['idUsuario']);
    $("#modal-reactivar-usuario .usuario").text(usuario['Nombre']);
    $("#modal-reactivar-usuario").modal("open");
}