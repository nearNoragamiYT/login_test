var fields = {
    id: 'idStand',
    pavilion: 'idPabellon',
    stand_type: 'idTipoStand',
    number: 'StandNumber',
    label: 'EtiquetaStand',
    status: 'StandStatus',
    x: 'Stand_X',
    y: 'Stand_Y',
    width: 'Stand_W',
    height: 'Stand_H'
};

// Chrome 1+
var isChrome = !!window.chrome;

(function () {
    if (!isChrome) {
        $("#mainFP").remove();
        $(".supported-browsers").show();
    } else {
        $("#mainFP").show();
        $(".supported-browsers").remove();
        // constants
        var defaultState = {
            pavilion: {},
            selected: undefined,
            origin: {},
            scale: 0
        };


        var canvas = document.getElementById('floorplan');
        var toolbar = document.getElementById('toolbar');
        var fp_tools = document.getElementById('fp-controls');
        var stand = document.getElementById('stand');
        var delete_stand = document.getElementById('delete-stand');
        var hall_select = toolbar.querySelector('#hall-select');
        var pavilion_select = stand.querySelector('#pavilion');
        var stand_type_select = stand.querySelector('#stand_type');
        var wrapper = $("#frm-booth");
        var form_stand = $("#booth-details");
        var canvas_state = $("#floorplan");
        var label = $("#etiqueta-label");
        var remove = $("#remove");
        var remove_stand = document.getElementById('text-modal');
        var save_button = stand.querySelector('#save');
        var inputs = {};
        for (var prop in fields) {
            var input = stand.querySelector('#' + prop);
            inputs[prop] = input;
        }
        var width_calc;
        var height_calc;
        canvas_dimension();

// mutable state
        var anchors = [];
        var state = new Observable(defaultState);
        var dragging = false;
        var resizing = false;

// Paint Floorplan on Resize
        $(window).on('resize', function () {
            canvas_dimension();
            canvas.width = width_calc;
            canvas.height = height_calc;
        });

// Canvas dimensions 
        function canvas_dimension() {
            width_calc = window.innerWidth;
            height_calc = window.innerHeight;
            if (width_calc > 992) {
                if (wrapper.hasClass('toggled')) {
                    width_calc -= $("#frm-booth").innerWidth();
                }
                width_calc -= $("#slide-out").innerWidth();
                height_calc -= $(".navbar-fixed").innerHeight();
                height_calc -= $("#toolbar").innerHeight();
                height_calc -= 40;
            } else {
                if (wrapper.hasClass('toggled')) {
                    width_calc -= $("#frm-booth").innerWidth();
                }
                height_calc -= $(".navbar-fixed").innerHeight();
                height_calc -= $("#toolbar").innerHeight();
                height_calc -= 40;
            }
        }
        ;

// submit [toolbar, fields, state, display]
        var form_booth = form_stand.validate({
            rules: {
                number: "required",
                width: {
                    required: true,
                    min: 1,
                    number: true
                },
                height: {
                    required: true,
                    min: 1,
                    number: true
                },
                label: "required"

            },
            messages: {
                number: language.fp_errorNumero,
                width: {
                    required: language.fp_errorFrente,
                    min: language.fp_errorCeroMetros
                },
                height: {
                    required: language.fp_errorLargo,
                    min: language.fp_errorCeroMetros
                },
                label: language.fp_errorCeroMetros
            },
            errorElement: "div",
            errorPlacement: function (error, element) {
                var placement = $(element).data('error');
                if (placement) {
                    $(placement).append(error);
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function () {
                saveBooth();
            }
        });
        //submit booth
        function saveBooth() {
            var selected = state.get('selected');
            for (var prop in fields) {
                var value = stand.querySelector('#' + prop).value;
                if (prop !== 'number' && prop !== 'status' && prop !== 'label' && prop !== 'pavilion' && prop !== 'stand_type') {
                    value = (+value);
                }
                selected.set(fields[prop], value, false);
            }
            if (selected.get(fields.number)) {
                var pavilion = state.get('pavilion');
                for (var len = pavilion.booths.length - 1; len >= 0; len--) {
                    var cur = pavilion.booths[len];
                    if (selected === cur)
                        continue;
                    if (selected.get(fields.number) === cur.get(fields.number)) {
                        selected.set(fields.number, '');
                        $("#number").focus();
                        return show_toast('warning', language.fp_notificacionStandDuplicado, 'top', 'right', '');
                    }
                }
                var url = selected.get(fields.id) ? url_update : url_create;
                show_toast('info', language.fp_notificacionGuardando, 'top', 'right', '');
                $.ajax({
                    method: "POST",
                    url: url,
                    data: JSON.stringify(selected.attrs)
                })
                        .done(function (response) {
                            if (url === url_create) {
                                if (typeof response === 'number') {
                                    selected.set(fields.id, response, false);
                                    display();
                                    $("#number").blur();
                                    $("#number").removeClass('valid');
                                    show_toast('success', language.fp_notificacionStandGuardado, 'top', 'right', '');
                                } else if (response === null) {
                                    show_toast('danger', language.fp_notificacionNoGuardado, 'top', 'right', '');
                                } else {
                                    window.location.href = url_dashboard;
                                }
                            } else {
                                if (typeof response === 'object') {
                                    display();
                                    $("#number").blur();
                                    $("#number").removeClass('valid');
                                    show_toast('success', language.fp_notificacionStandGuardado, 'top', 'right', '');
                                } else if (response === null) {
                                    show_toast('danger', language.fp_notificacionNoGuardado, 'top', 'right', '');
                                } else {
                                    window.location.href = url_dashboard;
                                }
                            }
                        })
                        .fail(function () {
                            show_toast('danger', language.fp_notificacionNoGuardado, 'top', 'right', '');
                        });
            } else {
                display();
            }
        }
        ;
// Delete button
        (function () {
            $('html').keyup(function (e) {
                if (e.keyCode == 46) {
                    if (wrapper.hasClass('toggled')) {
                        wrapper.toggleClass('toggled');
                        canvas_state.toggleClass('toggled');
                        wrapper.hide();
                    }
                    var selected = state.get('selected');
                    var flag = false;
                    if (selected === null) {
                        flag = true;
                    } else if (selected === undefined) {
                        flag = true;
                    }
                    if (flag === false) {
                        if (selected.get(fields.status) === 'contratado' || selected.get(fields.status) === 'reservado') {
                            canvas_dimension();
                            canvas.width = width_calc;
                            canvas.height = height_calc;
                            display();
                            return show_toast('danger', language.fp_notificacionNoEliminado, 'top', 'right', '');
                        } else if (selected.get(fields.status) === 'libre' && selected.get(fields.number) !== '') {
                            remove_stand.innerHTML = language.fp_confirmacionTexto + " <b>" + selected.get(fields.number) + "</b>?";
                            $('#mdl-delete-module').modal("open");
                            canvas_dimension();
                            canvas.width = width_calc;
                            canvas.height = height_calc;
                            display();
                        } else if (selected.get(fields.status) === 'libre' && selected.get(fields.number) === '') {
                            delete_stand.click();
                        }
                    }
                }
                if (e.keyCode == 13) {
                    var selected = state.get('selected');
                    var flag = false;
                    if (selected === null) {
                        flag = true;
                    } else if (selected === undefined) {
                        flag = true;
                    }
                    if (flag === false) {
                        if ($("#mdl-delete-module").is(':visible')) {
                            delete_stand.click();
                        }
                    }
                }
            });
        })();
// pavilion selection [state, loader, hall_select, pavilion_select, display, whole_floorplan]
        (function () {
            function paint_pavilion(pavilion) {
                var img = new Image();
                var booths = pavilion.booths;
                var abooths = pavilion.abooths;
                state.set('pavilion', pavilion, false);
                for (var i = 0; i < booths.length; i++) {
                    booths[i] = new Observable(booths[i]);
                    booths[i].subscribe(display);
                }
                for (var i = 0; i < abooths.length; i++) {
                    abooths[i] = new Observable(abooths[i]);
                    abooths[i].subscribe(display);
                }
                img.src = url_layout + pavilion.layout + "?" + new Date().getTime();
                pavilion.layout = img;
                pavilion.layout.addEventListener('load', function () {
                    whole_floorplan(pavilion);
                });
            }
            if (wrapper.hasClass('toggled')) {
                wrapper.toggleClass('toggled');
                canvas_state.toggleClass('toggled');
                wrapper.hide();
            } else {
                wrapper.hide();
            }
            state.subscribe(display);
            show_loader_wrapper();
            $.getJSON(url_halls, function (halls) {
                show_loader_wrapper();
                for (var i = 0; i < halls["hall"].length; i++) {
                    var hall = halls["hall"][i];
                    var hall_option = document.createElement('option');
                    hall_option.value = hall.idSala;
                    hall_option.innerHTML = hall.NombreES;
                    hall_select.appendChild(hall_option);
                }
                for (var i = 0; i < halls["pavilion"].length; i++) {
                    var pavilion = halls["pavilion"][i];
                    var pavilion_option = document.createElement('option');
                    pavilion_option.value = pavilion.idPabellon;
                    pavilion_option.innerHTML = pavilion.NombreES;
                    pavilion_select.appendChild(pavilion_option);
                }
                for (var i = 0; i < halls["stand_type"].length; i++) {
                    var stand = halls["stand_type"][i];
                    var stand_option = document.createElement('option');
                    stand_option.value = stand.idTipoStand;
                    stand_option.innerHTML = stand.TipoStand;
                    stand_type_select.appendChild(stand_option);
                }
                var edicion_id = halls["edition"]["0"]["idEdicion"];
                var hall_id = hall_select.value;
                $.getJSON(url_hall + '/' + edicion_id + '/' + hall_id, paint_pavilion);
                hall_select.addEventListener('change', function () {
                    show_loader_wrapper();
                    var edicion_id = halls["edition"]["0"]["idEdicion"];
                    var hall_id = hall_select.value;
                    state.set('selected', null, false);
                    if (wrapper.hasClass('toggled')) {
                        if (wrapper.hasClass('toggled')) {
                            wrapper.toggleClass('toggled');
                            canvas_state.toggleClass('toggled');
                            wrapper.hide();
                            canvas_dimension();
                            canvas.width = width_calc;
                            canvas.height = height_calc;
                            display();
                        }
                    }
                    $.getJSON(url_hall + '/' + edicion_id + '/' + hall_id, paint_pavilion);
                });
                pavilion_select.addEventListener('change', function () {
                    var booth = state.get('selected');
                    var pavilion_select_value = pavilion_select.value;
                    if (booth.get(fields.pavilion) != pavilion_select_value) {                        
                        booth.set(fields.pavilion, pavilion_select_value);
                    }
                });
                stand_type_select.addEventListener('change', function () {
                    var pavilion = state.get('pavilion');
                    var booth = state.get('selected');
                    var stand_type = stand_type_select.value;
                    var stand_size = pavilion.stand_size;
                    if (booth.get(fields.stand_type) != stand_type) {
                        booth.set(fields.stand_type, stand_type);
                    }
                    for (var i = 0; i < stand_size.length; i++) {
                        if (stand_size[i]["idTipoStand"] == stand_type) {
                            var stand_width = stand_size[i]["AnchoStand"];
                            var stand_height = stand_size[i]["AltoStand"];
                            booth.set(fields.width, stand_width, false);
                            booth.set(fields.height, stand_height);
                        }
                    }
                });
            });
        })();

// resize window [display]
        (function () {
            window.addEventListener('resize', display);
        })();

// select booth [canvas, state, getPosition, formatBooth, intersects]
        (function () {
            var mousedown;
            canvas.addEventListener('mousedown', on_down);
            canvas.addEventListener('touchstart', on_down);
            function on_down(event) {
                mousedown = getCoordinates(event);
            }

            canvas.addEventListener('click', on_click);
            canvas.addEventListener('touchenter', on_click);
            function on_click(event) {
                var coordinates = getCoordinates(event);
                if (!samePosition(mousedown, coordinates))
                    return;
                var pavilion = state.get('pavilion');
                var origin = state.get('origin');
                var scale = state.get('scale');
                var position = getPosition(coordinates, scale, origin);
                var clicked = pavilion.booths.filter(function (booth) {
                    return pointAtBooth(position, booth);
                });
                form_booth.resetForm();
                var selected = clicked.last();
                for (prop in fields) {
                    $('#' + prop).blur();
                    $('#' + prop).removeClass('valid');
                }
                if (selected === undefined) {
                    remove.addClass('disabled');
                    Materialize.updateTextFields();
                    for (prop in fields) {
                        if (prop !== "pavilion" && prop !== "stand_type") {
                            var input = inputs[prop];
                            input.disabled = false;
                            input.value = '';
                            $('#' + prop).removeClass('black-text valid');
                        }
                    }
                    if (wrapper.hasClass('toggled')) {
                        wrapper.toggleClass('toggled');
                        canvas_state.toggleClass('toggled');
                        wrapper.hide();
                        canvas_dimension();
                        canvas.width = width_calc;
                        canvas.height = height_calc;
                        display();
                    }
                    state.set('selected', null, false);
                    var scale = state.get('scale');
                    var origin = state.get('origin');
                    startPosition = getPosition(coordinates, scale, origin);
                    var position = getPosition(coordinates, scale, startPosition);
                    state.set('origin', position);
                } else {
                    if (!wrapper.hasClass('toggled')) {
                        if (event.layerX <= canvas.width && event.layerX >= canvas.width - $("#frm-booth").width()) {
                            var origin = state.get('origin');
                            var scale = state.get('scale');
                            var coordinates = getCoordinates(event);
                            startPosition = getPosition(coordinates, scale, origin);
                            var temp_coordinates = {x: event.layerX - 330, y: event.layerY};
                            var position = getPosition(temp_coordinates, scale, startPosition);
                            state.set('origin', position);
                        }
                    }
                    if (selected.get(fields.status) === 'libre') {
                        state.set('selected', selected);
                        if (!wrapper.hasClass('toggled')) {
                            wrapper.toggleClass('toggled');
                            canvas_state.toggleClass('toggled');
                            wrapper.show();
                            canvas_dimension();
                            canvas.width = width_calc;
                            canvas.height = height_calc;
                            display();
                        }
                        for (prop in fields) {
                            var input = inputs[prop];
                            input.disabled = false;
                            $('#' + prop).addClass('black-text');
                            $('#' + prop).removeClass('valid');
                        }
                        Materialize.updateTextFields();
                        label.hide();
                        if (selected.get(fields.number) == "") {
                            $("#number").focus();
                        }
                    }
                    if (selected.get(fields.status) === 'contratado' || selected.get(fields.status) === 'reservado') {
                        state.set('selected', selected);
                        if (!wrapper.hasClass('toggled')) {
                            wrapper.toggleClass('toggled');
                            canvas_state.toggleClass('toggled');
                            wrapper.show();
                            canvas_dimension();
                            canvas.width = width_calc;
                            canvas.height = height_calc;
                            display();
                        }
                        label.show();
                        for (prop in fields) {
                            if (prop !== 'label' && prop !== 'pavilion' && prop !== 'stand_type') {
                                var input = inputs[prop];
                                input.disabled = true;
                                $('#' + prop).removeClass('black-text valid');
                            } else {
                                var input = inputs[prop];
                                input.disabled = false;
                                $('#' + prop).addClass('black-text');
                            }
                        }
                        Materialize.updateTextFields();
                        if (selected.get(fields.label) == "") {
                            $("#label").focus();
                        }
                    }
                }
            }
        })();

// search by number [toolbar, search]
        (function () {
            var search_form = toolbar.querySelector('#search-form');
            var input = search_form.querySelector('#number2search');
            var list = search_form.querySelector('#search-list');
            var booth;
            var booths;
            search_form.addEventListener('submit', function (event) {
                event.preventDefault();
            });
            input.addEventListener('keyup', function () {
                var term = input.value;
                booths = search(term);
                list.innerHTML = '';
                booths.forEach(function (booth) {
                    var li = document.createElement('li');
                    if (hidden(booth))
                        return;
                    li.id = booth.get(fields.number);
                    if (booth.get(fields.label) === null && booth.get(fields.status) === 'libre') {
                        li.innerHTML = booth.get(fields.number) + ' - Libre';
                    } else {
                        li.innerHTML = booth.get(fields.number) + ' - ' + booth.get(fields.label);
                    }
                    list.appendChild(li);
                    $('.dropdown-button').dropdown('open');
                });
                if (term.length) {
                    list.className = 'dropdown-content';
                } else {
                    list.className = 'dropdown-content empty';
                }
            });
            list.addEventListener('click', function (event) {
                var pavilion = state.get('pavilion');
                var number = event.target.id;
                var found = booths.filter(function (booth) {
                    return booth.get(fields.number) == number;
                });
                booth = found[0];
                if (hall_select.value == booth.attrs.idSala) {
                    zoomSearch(booth);
                } else {
                    show_loader_wrapper();
                    hall_select.value = booth.attrs.idSala;
                    var edicion_id = pavilion.edicion;
                    var hall_id = hall_select.value;
                    state.set('selected', null, false);
                    if (wrapper.hasClass('toggled')) {
                        wrapper.toggleClass('toggled');
                        canvas_state.toggleClass('toggled');
                        wrapper.hide();
                    }
                    $.getJSON(url_hall + '/' + edicion_id + '/' + hall_id, paint_pavilion);
                }
            });
            function paint_pavilion(pavilion) {
                var img = new Image();
                var booths = pavilion.booths;
                var abooths = pavilion.abooths;
                state.set('pavilion', pavilion, false);
                for (var i = 0; i < booths.length; i++) {
                    booths[i] = new Observable(booths[i]);
                    booths[i].subscribe(display);
                }
                for (var i = 0; i < abooths.length; i++) {
                    abooths[i] = new Observable(abooths[i]);
                    abooths[i].subscribe(display);
                }
                img.src = url_layout + pavilion.layout + "?" + new Date().getTime();
                pavilion.layout = img;
                pavilion.layout.addEventListener('load', function () {
                    whole_floorplan(pavilion);
                    zoomSearch(booth);
                });
            }

            function zoomSearch(booth) {
                var position = {};
                state.set('selected', booth, false);
                whole_floorplan(state.get('pavilion'));
                var origin = state.get('origin');
                var scale = state.get('scale');
                var start_W = width_calc / 2;
                var start_H = height_calc / 2;
                position = seekBooth(scale, origin, booth);
                var coordinates = {x: start_W, y: start_H};
                startPosition = getPosition(position, scale, origin);
                var position_move = getPosition(coordinates, scale, startPosition);
                state.set('origin', position_move);
                var temp_zoom = {x: start_W, y: start_H};
                set_zoom(temp_zoom, 5);
                input.value = '';
                list.className = 'dropdown-content empty';
                if (!wrapper.hasClass('toggled')) {
                    wrapper.toggleClass('toggled');
                    canvas_state.toggleClass('toggled');
                    wrapper.show();
                    canvas_dimension();
                    canvas.width = width_calc;
                    canvas.height = height_calc;
                    display();
                }
                Materialize.updateTextFields();
            }
        })();

// move floorplan [state, canvas, getPosition, dragging]
        (function () {
            var startPosition;
            var mousedown = false;

            canvas.addEventListener('mousedown', on_down);
            canvas.addEventListener('touchstart', on_down);
            function on_down(event) {
                var origin = state.get('origin');
                var scale = state.get('scale');
                var coordinates = getCoordinates(event);
                event.preventDefault();
                mousedown = true;
                startPosition = getPosition(coordinates, scale, origin);
            }

            canvas.addEventListener('mousemove', on_move);
            canvas.addEventListener('touchmove', on_move);
            function on_move(event) {
                event.preventDefault();
                if (!mousedown)
                    return;
                if (dragging || resizing)
                    return;
                var coordinates = getCoordinates(event);
                var scale = state.get('scale');
                var origin = state.get('origin');
                var position = getPosition(coordinates, scale, startPosition);
                state.set('origin', position);
            }
            canvas.addEventListener('mouseup', on_up);
            canvas.addEventListener('touchend', on_up);
            function on_up(event) {
                mousedown = false;
            }
        })();

// drag booth [canvas, state, fields, getPosition, formatBooth, dragging, snap]
        (function () {
            var booth_position;
            var selected;
            var mousedown = false;
            canvas.addEventListener('mousedown', function (event) {
                selected = state.get('selected');
                if (!selected)
                    return;
                var scale = state.get('scale');
                var origin = state.get('origin');
                var position = getPosition(getCoordinates(event), scale, origin);
                mousedown = true;
                booth_position = {
                    x: position.x - selected.get(fields.x),
                    y: position.y - selected.get(fields.y),
                };
            });
            canvas.addEventListener('mousemove', function (event) {
                if (!mousedown)
                    return;
                if (resizing)
                    return;
                selected = state.get('selected');
                form_booth.resetForm();
                if (!selected)
                    return;
                var scale = state.get('scale');
                var origin = state.get('origin');
                var position = getPosition(getCoordinates(event), scale, origin);
                var shape = formatBooth(selected.attrs);
                if (dragging || intersects(shape, position)) {
                    dragging = true;
                    selected.set(fields.x, snap(position.x - booth_position.x), false);
                    selected.set(fields.y, snap(position.y - booth_position.y));
                }
            });
            canvas.addEventListener('mouseup', function (event) {
                mousedown = false;
                if (dragging) {
                    if (selected.get(fields.id)) {
                        show_toast('fp', language.fp_notificacionGuardando, 'top', 'right', '');
                        $.ajax({
                            method: "POST",
                            url: url_update,
                            data: JSON.stringify(selected.attrs)
                        })
                                .done(function (response) {
                                    if (typeof response === 'object') {
                                        show_toast('fp', language.fp_notificacionStandGuardado, 'top', 'right', '');
                                    } else if (response === null) {
                                        show_toast('danger', language.fp_notificacionNoGuardado, 'top', 'right', '');
                                    } else {
                                        window.location.href = url_dashboard;
                                    }
                                })
                                .fail(function () {
                                    show_toast('danger', language.fp_notificacionNoGuardado, 'top', 'right', '');
                                });
                    }
                    dragging = false;
                }
            });
        })();

// remove [toolbar, state, fields]
        (function () {
            toolbar.querySelector('#remove').addEventListener('click', function () {
                if (wrapper.hasClass('toggled')) {
                    wrapper.toggleClass('toggled');
                    canvas_state.toggleClass('toggled');
                    wrapper.hide();
                }
                var selected = state.get('selected');

                if (selected.get(fields.status) === 'contratado' || selected.get(fields.status) === 'reservado') {
                    canvas_dimension();
                    canvas.width = width_calc;
                    canvas.height = height_calc;
                    display();
                    return show_toast('danger', language.fp_notificacionNoEliminado, 'top', 'right', '');
                } else if (selected.get(fields.status) === 'libre' && selected.get(fields.number) !== '') {
                    remove_stand.innerHTML = language.fp_confirmacionTexto + " <b>" + selected.get(fields.number) + "</b>?";
                    $('#mdl-delete-module').modal("open");
                    canvas_dimension();
                    canvas.width = width_calc;
                    canvas.height = height_calc;
                    display();
                } else {
                    delete_stand.click();
                }
            });
        })();

// delete button
        (function () {
            delete_stand.addEventListener('click', function () {
                var selected = state.get('selected');
                var data = {idStand: selected.get(fields.id)};

                function remove(selected) {
                    var pavilion = state.get('pavilion');
                    var booths = pavilion.booths;
                    booths.splice(booths.indexOf(selected), 1);
                    state.set('selected', null, false);
                    state.set('booths', booths);
                }

                if (selected.get(fields.id)) {
                    $('#mdl-delete-module').modal("close");
                    show_toast('info', language.fp_notificacionEliminando, 'top', 'right', '');
                    $.ajax({
                        method: "POST",
                        url: url_destroy,
                        data: JSON.stringify(data)
                    })
                            .done(function (response) {
                                if (typeof response == 'object') {
                                    remove(selected);
                                    show_toast('success', language.fp_notificacionStandEliminado, 'top', 'right', '');
                                } else if (response === null) {
                                    show_toast('danger', language.fp_notificacionNoGuardado, 'top', 'right', '');
                                } else {
                                    window.location.href = url_dashboard;
                                }
                            })
                            .fail(function () {
                                show_toast('danger', language.fp_notificacionNoGuardado, 'top', 'right', '');
                            });
                } else {
                    remove(selected);
                }
                Materialize.updateTextFields();
                canvas_dimension();
                canvas.width = width_calc;
                canvas.height = height_calc;
                display();
            });
        })();


// new booth [toolbar, state, unitsToPixels, fields]
        (function () {
            toolbar.querySelector('#new-booth').addEventListener('click', function () {
                if (!wrapper.hasClass('toggled')) {
                    wrapper.toggleClass('toggled');
                    canvas_state.toggleClass('toggled');
                    wrapper.show();
                    canvas_dimension();
                    canvas.width = width_calc;
                    canvas.height = height_calc;
                    display();
                }
                var num = document.getElementById('number');
                label.hide();
                var origin = state.get('origin');
                var scale = state.get('scale');
                var pavilion = state.get('pavilion');
                var booths = pavilion.booths;
                var padding = unitsToPixels(5);
                var new_booth = {};
                new_booth[fields.number] = '';
                new_booth[fields.label] = '';
                new_booth[fields.x] = 6 * padding / scale - (+origin.x);
                new_booth[fields.y] = 3 * padding / scale - (+origin.y);
                var stand_type = stand_type_select.value;
                var stand_size = pavilion.stand_size;
                for (var i = 0; i < stand_size.length; i++) {
                    if (stand_size[i]["idTipoStand"] == stand_type) {
                        var stand_width = stand_size[i]["AnchoStand"];
                        var stand_height = stand_size[i]["AltoStand"];
                        new_booth[fields.width] = stand_width;
                        new_booth[fields.height] = stand_height;
                    }
                }
                new_booth[fields.status] = 'libre';
                new_booth[fields.pavilion] = $("#pavilion").val();
                new_booth[fields.stand_type] = $("#stand_type").val();
                new_booth.idEdicion = pavilion.edicion;
                new_booth.idEvento = pavilion.evento;
                new_booth.idSala = pavilion.hallId;
                new_booth = new Observable(new_booth);
                new_booth.subscribe(display);
                booths.push(new_booth);
                state.set('selected', new_booth);
                Materialize.updateTextFields();
                num.focus();
            });
        })();

// print PNG
//        (function () {
//            toolbar.querySelector('#print').addEventListener('click', function () {
//                var PAPER_WIDTH = 297;
//                var pavilion = state.get('pavilion');
//                var origin = {x: 0, y: 0};
//                var scale = 0.75;
//                state.set('origin', origin, false);
//                state.set('scale', scale, false);
//                floorplan(pavilion.layout.width * 0.75, pavilion.layout.height * 0.75, state);
//                var dataUrl = canvas.toDataURL();
//                window.open(dataUrl);
//                whole_floorplan(pavilion);
//            });
//        })();

// print PDF
        (function () {
            toolbar.querySelector('#pdf').addEventListener('click', function () {
                var PAPER_WIDTH = 297;
                var PAPER_HEIGHT = 210;
                var pavilion = state.get('pavilion');
                var origin = {x: 0, y: 0};
                var scale = 0.75;
                state.set('origin', origin, false);
                state.set('scale', scale, false);
                floorplan(pavilion.layout.width * 0.75, pavilion.layout.height * 0.75, state);
                var dataUrl = canvas.toDataURL();
                var orientation = pavilion.layout.width > pavilion.layout.height
                        ? 'l'
                        : 'p';
                var factor = PAPER_WIDTH / pavilion.layout[orientation === 'landscape'
                        ? 'width'
                        : 'height'
                ];
                var w = orientation === 'landscape' ? PAPER_WIDTH : factor * pavilion.layout.width;
                var h = orientation === 'portrait' ? PAPER_HEIGHT : factor * pavilion.layout.height;
                var doc = new jsPDF(orientation, 'mm', [w, h]);
                doc.addImage(dataUrl, 'PNG', 0, 0, w, h);
                doc.save('floorplan.pdf');
                whole_floorplan(pavilion);
            });
        })();

// zoom through wheel [set_zoom]
        (function () {
            canvas.addEventListener('wheel', do_zoom);
            canvas.addEventListener('mousewheel', do_zoom);

            function do_zoom(event) {
                event.preventDefault();
                var position = {
                    x: event.layerX,
                    y: event.layerY
                };
                var deltaY = event.deltaY === undefined ? -event.wheelDeltaY : event.deltaY;
                var delta = deltaY < 0 ? 1.25 : 0.75;
                set_zoom(position, delta);
            }
        })();
// Boton Cancelar
        (function () {
            $('#cancel').click(function () {
                if (wrapper.hasClass('toggled')) {
                    wrapper.toggleClass('toggled');
                    canvas_state.toggleClass('toggled');
                    wrapper.hide();
                    canvas_dimension();
                    canvas.width = width_calc;
                    canvas.height = height_calc;
                    display();
                }
            });
        })();

// zoom buttons [set_zoom]
        (function () {
            var cursor = {
                x: width_calc / 2,
                y: height_calc / 2
            };

            fp_tools.querySelector('#zoom-in').addEventListener('click', function (event) {
                var delta = 1.25;
                set_zoom(cursor, delta);
            });

            fp_tools.querySelector('#zoom-out').addEventListener('click', function (event) {
                var delta = 0.75;
                set_zoom(cursor, delta);
            });
        })();

// resizing
        (function () {
            var anchor;
            var selected;

            canvas.addEventListener('mousedown', function (event) {
                selected = state.get('selected');
                var cursor = getCoordinates(event);
                var scale = state.get('scale');
                var origin = state.get('origin');
                var position = getPosition(cursor, scale, origin);
                var i = 0;
                while (i < anchors.length) {
                    if (intersects(anchors[i], position)) {
                        anchor = anchors[i];
                        break;
                    }
                    i++;
                }
                if (anchor)
                    resizing = true;
            });

            canvas.addEventListener('mousemove', function (event) {
                this.style.cursor = 'move';
                if (!anchor)
                    return;
                if (selected.get(fields.status) === 'contratado' || selected.get(fields.status) === 'reservado')
                    return;
                var type = anchor.type;
                var scale = state.get('scale');
                var origin = state.get('origin');
                var position = getPosition(getCoordinates(event), scale, origin);

                switch (type) {
                    case "ul":
                        this.style.cursor = 'nwse-resize';
                        newX = snap(position.x);
                        newY = snap(position.y);
                        newW = +selected.get(fields.width) + (selected.get(fields.x) - newX) / 10;
                        newH = +selected.get(fields.height) + (selected.get(fields.y) - newY) / 10;
                        if (newW < 0.9)
                            return;
                        if (newH < 0.9)
                            return;
                        selected.set(fields.x, newX.toFixed(1), false);
                        selected.set(fields.y, newY.toFixed(1), false);
                        selected.set(fields.width, +newW.toFixed(1), false);
                        selected.set(fields.height, +newH.toFixed(1));
                        break;
                    case "ur":
                        this.style.cursor = 'nesw-resize';
                        newY = snap(position.y);
                        newW = (snap(position.x) - selected.get(fields.x)) / 10;
                        newH = +selected.get(fields.height) + (selected.get(fields.y) - newY) / 10;
                        if (newW < 0.9)
                            return;
                        if (newH < 0.9)
                            return;
                        selected.set(fields.y, newY.toFixed(1), false);
                        selected.set(fields.width, +newW.toFixed(1), false);
                        selected.set(fields.height, +newH.toFixed(1));
                        break;
                    case "bl":
                        this.style.cursor = 'nesw-resize';
                        newX = snap(position.x);
                        newH = (snap(position.y) - selected.get(fields.y)) / 10;
                        newW = +selected.get(fields.width) + (selected.get(fields.x) - newX) / 10;
                        if (newW < 0.9)
                            return;
                        if (newH < 0.9)
                            return;
                        selected.set(fields.x, newX.toFixed(1), false);
                        selected.set(fields.width, +newW.toFixed(1), false);
                        selected.set(fields.height, +newH.toFixed(1));
                        break;
                    case "br":
                        this.style.cursor = 'nwse-resize';
                        newH = (snap(position.y) - selected.get(fields.y)) / 10;
                        newW = (snap(position.x) - selected.get(fields.x)) / 10;
                        if (newW < 0.9)
                            return;
                        if (newH < 0.9)
                            return;
                        selected.set(fields.width, +newW.toFixed(1), false);
                        selected.set(fields.height, +newH.toFixed(1));
                        break;
                    default:
                        this.style.cursor = 'move';
                }
            });

            canvas.addEventListener('mouseup', function (event) {
                if (resizing && selected.get(fields.id) && selected.get(fields.status) === 'libre') {
                    show_toast('info', language.fp_notificacionGuardando, 'top', 'right', '');
                    $.ajax({
                        method: "POST",
                        url: url_update,
                        data: JSON.stringify(selected.attrs)
                    })
                            .done(function (response) {
                                if (typeof response == 'object') {
                                    show_toast('success', language.fp_notificacionStandGuardado, 'top', 'right', '');
                                } else if (response === null) {
                                    show_toast('danger', language.fp_notificacionNoGuardado, 'top', 'right', '');
                                } else {
                                    window.location.href = url_dashboard;
                                }
                            })
                            .fail(function () {
                                show_toast('danger', language.fp_notificacionNoGuardado, 'top', 'right', '');
                            });
                } else if (resizing && selected.get(fields.id) && (selected.get(fields.status) === 'contratado' || selected.get(fields.status) === 'reservado')) {
                    show_toast('danger', language.fp_notificacionNoModificar, 'top', 'right', '');
                }
                resizing = false;
                anchor = undefined;
            });
        })();

        function set_zoom(position, delta) {
            var scale = state.get('scale');
            var origin = state.get('origin');
            state.set('scale', scale * delta, false);
            var o = zoom(position, scale, origin, state.get('scale') - scale);
            state.set('origin', o);
        }

        function display() {
            floorplan(width_calc, height_calc, state);
        }

        function floorplan(w, h, state) {
            var origin = state.get('origin');
            var scale = state.get('scale');
            var pavilion = state.get('pavilion');
            for (var prop in fields) {
                if (prop !== "pavilion" && prop !== "stand_type") {
                    var input = inputs[prop];
                    input.disabled = true;
                    input.value = '';
                }
            }
            remove.addClass('disabled')
            save_button.disabled = true;
            canvas.width = w;
            canvas.height = h;
            context = canvas.getContext('2d');
            context.strokeStyle = 'gray';
            context.globalAlpha = 0.6;
            context.scale(scale, scale);
            context.translate(origin.x, origin.y);
            context.drawImage(pavilion.layout, 0, 0);
            context.globalAlpha = 1.0;
            pavilion.booths.map(function (booth) {
                var shape = formatBooth(booth.attrs);
                if (hidden(booth))
                    return;
                if (booth === state.get('selected'))
                    return;

                paintBooth(shape);
            });

            function paintBooth(shape) {
                var padding = 1;
                var px = 0.1 * Math.min(shape.height, shape.width);
                var label;
                var colores = pavilion.color;
                context.save();
                if (!shape.number) {
                    context.fillStyle = 'rgba(85, 85, 85, 0.85)';
                } else if (shape.status === 'reservado') {
                    context.fillStyle = 'rgba(220, 220, 220, 1)';
                } else if (shape.status === 'libre') {
                    context.fillStyle = 'rgba(255, 255, 255, 1)';
                } else if (shape.status === 'contratado' && colores.length !== 0) {
                    for (var i = 0; i < colores.length; i++) {
                        var color = colores[i];
                        if (shape.pavilion === color.idPabellon && shape.status === 'contratado' && color.Color !== null && color.Color !== "") {//pavilion.edicion shape.pabellon == color.idPabellon
                            var rgb = hexToRgb(color.Color);
                            context.fillStyle = "rgba(" + rgb["r"] + "," + rgb["g"] + "," + rgb["b"] + ",0.85)";
                            i = colores.length;
                        } else {
                            context.fillStyle = 'rgba(100, 100, 100, 1.0)';
                        }
                    }
                }

                context.strokeRect(shape.x, shape.y, shape.width, shape.height);
                context.fillRect(shape.x, shape.y, shape.width, shape.height);
                context.restore();

                // Number
                var size = shape.label.length ? 8 : 12;
                context.font = size + 'px sans-serif';
                context.textAlign = 'left';
                if (shape.status === 'contratado') {
                    context.fillStyle = 'white';
                } else {
                    context.fillStyle = 'black';
                }
                context.fillText(shape.number, shape.x + padding, shape.y + shape.height - padding);
                context.font = px + 'px sans-serif';
                context.textAlign = 'center';

                // Label
                if (shape.label.length) {
                    label = wrap_text(shape.label, shape.width / 2);
                    label.map(function (l, i) {
                        context.fillText(
                                l.trim(),
                                shape.x + shape.width / 2.0,
                                shape.y + (i + 1) * px,
                                shape.width
                                );
                    });
                }
            }


            // Border of Selected
            anchors = [];
            if (state.get('selected')) {
                var booth = state.get('selected');
                var shape = formatBooth(booth.attrs);
                paintBooth(shape);
                anchors = create_anchors(shape);
                var color = 'rgba(255, 204, 153, 1)';
                remove.removeClass('disabled')
                save_button.disabled = false;
                for (prop in fields) {
                    var input = inputs[prop];
                    if (prop !== 'label' && prop !== 'pavilion' && prop !== 'stand_type') {
                        if (booth.get("StandStatus") === 'contratado' || booth.get("StandStatus") === 'reservado') {
                            input.disabled = true;
                            input.value = booth.get(fields[prop]);
                            $("#" + prop).removeClass('black-text');
                            $('#' + prop).removeClass('valid');
                        } else {
                            input.disabled = false;
                            input.value = booth.get(fields[prop]);
                            $("#" + prop).addClass('black-text');
                        }
                    } else {
                        if (booth.get("StandStatus") === 'contratado' || booth.get("StandStatus") === 'reservado') {
                            input.disabled = false;
                            input.value = booth.get(fields[prop]);
                            $("#" + prop).addClass('black-text');
                            $('#' + prop).removeClass('valid');
                            label.show();
                        } else {
                            if (prop !== "pavilion" && prop !== "stand_type") {
                                input.disabled = true;
                                input.value = booth.get(fields[prop]);
                                $("#" + prop).addClass('black-text');
                            } else {
                                input.value = booth.get(fields[prop]);
                            }
                            label.hide();
                        }
                    }
                }
                context.strokeStyle = color;
                context.strokeRect(shape.x, shape.y, shape.width, shape.height);
                anchors.map(function (a) {
                    context.fillStyle = 'rgba(255, 153, 51, 1)';
                    context.fillRect(a.x, a.y, a.width, a.height);
                });
            }
        }

        function whole_floorplan(pavilion) {
            var height = height_calc;
            var width = width_calc;
            var scale = Math.min(height / pavilion.layout.height, width / pavilion.layout.width);
            var origin = {
                x: ((width - (pavilion.layout.width * scale)) / scale) / 2,
                y: ((height - (pavilion.layout.height * scale)) / scale) / 2
            };
            state.set('scale', scale, false);
            state.set('origin', origin, false);
            floorplan(width, height, state);
            hide_loader_wrapper();
        }

        function search(id) {
            var booths = state.get('pavilion').abooths;
            var filtered_booths = booths.filter(function (booth) {
                var regex = new RegExp(id, 'i');
                if (booth.get(fields.number) !== null)
                    var number = booth.get(fields.number).toString().search(regex) !== -1;
                if (booth.get(fields.label) !== null)
                    var label = booth.get(fields.label).search(regex) !== -1;
                return number || label;
            });
            return filtered_booths;
        }
        function hexToRgb(hex) {
            var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
            hex = hex.replace(shorthandRegex, function (m, r, g, b) {
                return r + r + g + g + b + b;
            });

            var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        }
    }
})();
