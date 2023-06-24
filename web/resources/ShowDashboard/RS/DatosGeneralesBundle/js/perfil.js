$(init);

function init() {
    $("#visitante-perfil").attr("class", "active");
    hide_loader_wrapper();
    setProfile();
}

function setProfile() {
    console.log(preguntas);
    console.log(respuestas);
    console.log(profile);
    if (profile.length != 0) {
        $.each(profile, function (index, value) {
            $("#cover-perfil-data").append(
                    $("<div>", {
                        id: 'p_' + index,
                        class: 'pregunta question'
                    }).append(
                    $("<div>", {
                        class: 'pregunta_header'
                    }).append(
                    $('<h2>', {
                        text: preguntas[index]['Pregunta' + lang.toUpperCase()]
                    }))));
            $.each(value, function (i, x) {

                if (x) {
                    $("#p_" + index).append(
                            $("<div>", {
                                id: 'pr_' + index,
                                class: 'pregunta_respuesta'
                            }).append(
                            $("<div>", {
                                id: 'div_r_' + index + '_' + i,
                                class: 'respuesta inline-block'
                            }).append(
                            $("<div>", {
                                id: 'div_r_' + index + '_' + i,
                                class: 'respuesta_label'
                            }).append(
                            $("<i>", {
                                class: 'material-icons',
                                text: 'remove'
                            }),
                            $("<label>", {
                                for : 'r_' + index + '_' + i,
                                text: respuestas[i]['Respuesta' + lang.toUpperCase()]
                            }),
                            $("<input>", {
                                type: 'text',
                                id: 'a_' + index + '_' + i,
                                name: 'a_' + index + '_' + i,
                                class: 'form-control',
                                readonly: true,
                                value: x,
                            })))));
                } else {
                    $("#p_" + index).append(
                            $("<div>", {
                                id: 'pr_' + index,
                                class: 'pregunta_respuesta'
                            }).append(
                            $("<div>", {
                                id: 'div_r_' + index + '_' + i,
                                class: 'respuesta inline-block'
                            }).append(
                            $("<div>", {
                                id: 'div_r_' + index + '_' + i,
                                class: 'respuesta_label'
                            }).append(
                            $("<i>", {
                                class: 'material-icons',
                                text: 'remove'
                            }),
                            $("<label>", {
                                for : 'r_' + index + '_' + i,
                                text: respuestas[i]['Respuesta' + lang.toUpperCase()]
                            })))));
                }
            })
        });
    } else {
        $("#cover-perfil-data").append(
                $("<span>", {
                }).append(
                $("<h5>", {
                    class: 'center-align',
                    text: 'Visitante sin encuesta'
                })));
    }
}

$(document).on("click", ".load", function () {
    show_loader_wrapper();
});
