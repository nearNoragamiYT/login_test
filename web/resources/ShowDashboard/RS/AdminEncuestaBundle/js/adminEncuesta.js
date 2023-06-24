$(document).ready(function () {

    $.each(questions, function (index, value) {
        if (!value.Activa || value.Activa == "0") {
            let idPregunta = value.idPregunta;

            $('[id-preg=' + idPregunta + ']').children().css({"opacity": ".4"});
            $('[id-preg=' + idPregunta + ']').children().last().css({"opacity": "1"});
            $('[id-preg=' + idPregunta + ']').children().last().html('<i class="material-icons unlock-record tooltipped" id-unlock ="'
                    + idPregunta + '" data-position="left" data-tooltip="Desbloquear Producto">lock_open</i>');
        }
    });

    table = $('#table').DataTable({
        responsive: true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
});

$(document).on('click', '.edit-record', function () {//mostrar pregunta-respuestas
    $("#respuestas").empty();
    $("#question").empty();
    var idPregunta = $(this).attr('id-edit');
    var pregunta = document.getElementById("question");
    var etiqueta = document.createElement("label");
    etiqueta.innerHTML = questions[idPregunta]['PreguntaES'];
    etiqueta.setAttribute("id", "lbl_question");
    pregunta.appendChild(etiqueta);
    getQuestionAnswers(idPregunta);
    $('#preguntas-respuestas').modal({dismissible: false}).modal('open');
    $('#btn-add-pregunta').attr('idPregunta', idPregunta);
});

$(document).on('click', '#btn-add-pregunta', function (e) {//guardar respuestas activadas/desactivadas
    e.preventDefault();
    updateAnswers();
});

$(document).on('click', '.block-record', function () {//boton desactivar pregunta
    var row = $('#table').DataTable()
            .row(this)
            .index();
    table
            .cell(row, 0)
            .data('Oculta');
    $('.tooltipped').tooltip('remove');
    var idPregunta = $(this).attr('id-block');
    $('[id-preg=' + idPregunta + ']').children().css({"opacity": ".4"});
    $('[id-preg=' + idPregunta + ']').children().last().css({"opacity": "1"});
    $('[id-preg=' + idPregunta + ']').children().last().html('<i class="material-icons unlock-record tooltipped" id-unlock ="'
            + idPregunta + '" data-position="left" data-tooltip="Desbloquear Producto">lock_open</i>');
    deactivateQuestion(idPregunta);
});

$(document).on('click', '.unlock-record', function () {//activar pregunta
    var row = $('#table').DataTable()
            .row(this)
            .index();
    table
            .cell(row, 0)
            .data('Mostrando');
    $('.tooltipped').tooltip('remove');
    var idPregunta = $(this).attr('id-unlock');
    $('[id-preg=' + idPregunta + ']').children().css({"opacity": "1"});
    $('[id-preg=' + idPregunta + ']').children().last().html('<i class="material-icons block-record tooltipped" id-block ="'
            + idPregunta + '" data-position="left" data-tooltip="Desbloquear Producto">lock</i><i class="material-icons edit-record tooltipped " id-edit="'
            + idPregunta + '" data-position="left" data-tooltip="Editar">mode_edit</i>');
    deactivateQuestion(idPregunta);
});

function deactivateQuestion(idPregunta) {//desactivar pregunta
    show_loader_wrapper();
    let data = new URLSearchParams({"idPregunta": idPregunta});

    fetch(deactivate_Question, {
        method: 'POST',
        body: data
    })
            .then(response => response.json())
            .then(json => {
                if (json.status) {
                    if (json.action == "Activada") {
                        hide_loader_wrapper();
                        show_toast('success', general_text.sas_Activada);

                    }
                    if (json.action == "Desactivada") {
                        hide_loader_wrapper();
                        show_toast('success', general_text.sas_Desactivada);
                    }
                } else {
                    show_notification("error", json.message);
                    hide_loader_processing();
                }
            });
}

function updateAnswers() {//activar-desactivar respuestas
    show_loader_wrapper();
    var answers = [], data = [];
    var idPregunta = $("#btn-add-pregunta").attr('idpregunta');
    $("#questions_answers input:checkbox").each(function (key, value) {
        var name = value.name;
        if ($(this).prop('checked')) {
            var valor = "1";
        } else {
            var valor = "0";
        }
        position = {'name': name, 'value': valor};
        answers.push(position);
    });
    data = {'respuestas': answers, 'pregunta': idPregunta}

    $.ajax({
        type: "post",
        url: update_Answers,
        dataType: 'json',
        data: data,
        success: function (response) {
            if (response.status) {
                $('#preguntas-respuestas').modal('close');
                hide_loader_wrapper();
                show_toast('success', general_text.sas_guardoExito);
            } else {
                show_notification("error", json.message);
                hide_loader_processing();
            }
        },
        error: function (request, status, error) {
        }
    });
}


function getQuestionAnswers(idPregunta) {//consulto respuestas de preguntas
    $('.preloader-background').delay(500).fadeIn();
    let data = new URLSearchParams({"idPregunta": idPregunta});

    fetch(getPollAnswers, {
        method: 'POST',
        body: data
    })
            .then(response => response.json())
            .then(json => {
                if (json.status) {
                    for (i in json.data) {
                        let respuestas = json.data[i];
                        inputCheck(respuestas);
                    }
                    $('.preloader-background').delay(500).fadeOut();
                } else {
                    show_notification("error", json.message);
                    hide_loader_processing();
                }
            });
}

var elemento = document.getElementById("respuestas");

function inputCheck(respuestas) {//se pintan las respuestas
    var parrafo = document.createElement("p");
    var input = document.createElement("input");
    input.setAttribute("type", "checkbox");
    input.setAttribute("id", respuestas.idRespuesta);
    input.setAttribute("name", respuestas.idRespuesta);
    input.setAttribute("class", "select-record che_ck" + respuestas.idRespuesta);

    if (respuestas.Activa) {
        input.setAttribute("checked", true);
    }

    input.setAttribute('idRespuesta', respuestas.idRespuesta);
    parrafo.appendChild(input);
    var label = document.createElement("label");
    label.setAttribute("for", respuestas.idRespuesta);
    label.innerHTML = respuestas.RespuestaES;
    parrafo.appendChild(label);
    elemento.appendChild(parrafo);
}