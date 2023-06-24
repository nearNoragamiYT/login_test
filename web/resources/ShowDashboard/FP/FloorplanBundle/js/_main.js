(function() {
// constants
var fields = {
  id: '_id_Stand',
  number: 'StandNumber',
  label: 'pf_EtiquetaStand',
  status: 'StandStatus',
  x: 'pf_Stand_X',
  y: 'pf_Stand_Y',
  width: 'pf_Stand_W',
  height: 'pf_Stand_H'
};
var defaultState = {
  pavilion: {},
  selected: undefined,
  origin: {},
  scale: 0
};
var canvas = document.getElementById('floorplan');
var toolbar = document.getElementById('toolbar');
var pavilion_select = toolbar.querySelector('#pavilion-select');
var remove_button = toolbar.querySelector('#remove');
var save_button = toolbar.querySelector('#save');
var loader = $('#loader');
var notification = $('#notification');
var inputs = {};
for (var prop in fields) {
  var input = toolbar.querySelector('#' + prop);
  inputs[prop] = input;
}

// mutable state
var state = new Observable(defaultState);
var dragging = false;

// pavilion selection [state, loader, pavilion_select, display, whole_floorplan]
(function() {
  var paint_pavilion = function (pavilion) {
    var img = new Image();
    var booths = pavilion.booths;
    state.set('pavilion', pavilion, false);
    for (var i = 0; i < booths.length; i++) {
      booths[i] = new Observable(booths[i]);
      booths[i].subscribe(display);
    }
    img.src = url_layout + pavilion.layout + "?" + new Date().getTime();
    pavilion.layout = img;
    pavilion.layout.addEventListener('load', function() {
      whole_floorplan(pavilion);
    });
  };

  state.subscribe(display);
  loader.show();
  $.getJSON(url_pavilions, function(pavilions) {
    for (var i = 0; i < pavilions.length; i++) {
      var pavilion = pavilions[i];
      var pavilion_option = document.createElement('option');
      pavilion_option.value = pavilion['_id_EventoEdicion'];
      pavilion_option.innerHTML = pavilion['EventoEdicion'];
      pavilion_select.appendChild(pavilion_option);
    }
    var pavilion_id = pavilion_select.value;
    $.getJSON(url_pavilion + '/' + pavilion_id, paint_pavilion);
  });

  pavilion_select.addEventListener('change', function() {
    loader.show();
    var pavilion_id = pavilion_select.value;
    $.getJSON(url_pavilion + '/' + pavilion_id, paint_pavilion);
  });
})();

// resize window [display]
(function() {
  window.addEventListener('resize', display);
})();

// select booth [canvas, state, get_position, format_booth, intersects]
(function() {
  var mousedown = undefined;

  canvas.addEventListener('mousedown', on_down);
  canvas.addEventListener('touchstart', on_down);
  function on_down(event) {
    var coordinates = event.touches ? event.touches[0] : event;
    mousedown = {
      x: coordinates.layerX, y: coordinates.layerY
    };
  }

  canvas.addEventListener('click', on_click);
  canvas.addEventListener('touchenter', on_click);
  function on_click(event) {
    var coordinates = event.touches ? event.touches[0] : event;
    if (mousedown.x !== coordinates.layerX ||
        mousedown.y !== coordinates.layerY)
      return;
    var pavilion = state.get('pavilion');
    var origin = state.get('origin');
    var scale = state.get('scale');
    var position = get_position(coordinates, scale, origin);
    var clicked = pavilion.booths.filter(function(booth) {
      var shape = format_booth(booth.attrs);
      return intersects(shape, position);
    });
    state.set('selected', clicked[clicked.length - 1]);
  }
})();

// search by number [toolbar, search]
(function() {
  var search_form = toolbar.querySelector('#search-form');
  var input = search_form.querySelector('#number2search');
  var list = search_form.querySelector('#search-list');
  var booths;
  search_form.addEventListener('submit', function(event) {
    event.preventDefault();
  });
  input.addEventListener('keyup', function() {
    var term = input.value;
    booths = search(term);
    list.innerHTML = '';
    booths.map(function(booth) {
      var li = document.createElement('li');
      if (hidden(booth)) return;
      li.id = booth.get(fields.number);
      li.innerHTML = booth.get(fields.number) + ' - ' + booth.get(fields.label);
      list.appendChild(li);
    });
    if (term.length) {
      list.className = '';
    } else {
      list.className = 'hidden';
    }
  });
  list.addEventListener('click', function(event) {
    var number = event.target.id;
    var found = booths.filter(function(booth) {
      return booth.get(fields.number) === number;
    });
    var booth = found[0];
    var position = {};
    state.set('selected', booth, false);
    whole_floorplan(state.get('pavilion'));
    var scale = state.get('scale');
    var origin = state.get('origin');
    position.x = scale * (origin.x + parseInt(booth.get(fields.x)) + units_to_pixels(booth.get(fields.width)));
    position.y = (origin.y + parseInt(booth.get(fields.y)) + units_to_pixels(booth.get(fields.height))) * scale;
    set_zoom(position, 5);
    input.value = '';
    list.className = 'hidden';
  });
})();

// move floorplan [state, canvas, get_position, dragging]
(function() {
  var startPosition = undefined;
  var mousedown = false;

  canvas.addEventListener('mousedown', on_down);
  canvas.addEventListener('touchstart', on_down);
  function on_down(event) {
    var origin = state.get('origin');
    var scale = state.get('scale');
    var coordinates = event.touches ? event.touches[0] : event;
    event.preventDefault();
    mousedown = true;
    startPosition = get_position(coordinates, scale, origin);
  }

  canvas.addEventListener('mousemove', on_move);
  canvas.addEventListener('touchmove', on_move);
  function on_move(event) {
    event.preventDefault();
    if (!mousedown) return;
    if (dragging) return;
    var coordinates = event.touches ? event.touches[0] : event;
    var scale = state.get('scale');
    var origin = state.get('origin');
    var position = get_position(coordinates, scale, startPosition);
    state.set('origin', position);
  }

  canvas.addEventListener('mouseup', on_up);
  canvas.addEventListener('touchend', on_up);
  function on_up(event) {
    mousedown = false;
  }
})();

// drag booth [canvas, state, fields, get_position, format_booth, dragging, snap]
(function() {
  var booth_position = undefined;
  var selected = undefined;
  var mousedown = false;
  canvas.addEventListener('mousedown', function(event) {
    selected = state.get('selected');
    if (!selected) return;
    var scale = state.get('scale');
    var origin = state.get('origin');
    var position = get_position(event, scale, origin);
    mousedown = true;
    booth_position = {
      x: position.x - selected.get(fields.x),
      y: position.y - selected.get(fields.y),
    };
  });
  canvas.addEventListener('mousemove', function(event) {
    if (!mousedown) return;
    selected = state.get('selected');
    if (!selected) return;
    if (selected.get(fields.status) === 'contratado') return;
    var scale = state.get('scale');
    var origin = state.get('origin');
    var position = get_position(event, scale, origin);
    var shape = format_booth(selected.attrs);
    if (dragging || intersects(shape, position)) {
      dragging = true;
      selected.set(fields.x, snap(position.x - booth_position.x), false);
      selected.set(fields.y, snap(position.y - booth_position.y));
    }
  });
  canvas.addEventListener('mouseup', function(event) {
    mousedown = false;
    if (dragging) {
      if (selected.get(fields.id)) {
        showNotification(2, 'Guardando...');
        $.post(url_update, JSON.stringify(selected.attrs), function(data) {
          showNotification(1, '¡Stand guardado exitosamente!');
        });
      }
      dragging = false;
    }
  });
})();

// submit [toolbar, fields, state, display]
(function() {
  toolbar.querySelector('#booth-details').addEventListener('submit', function(event) {
    event.preventDefault();
    var selected = state.get('selected');
    if (selected.get(fields.status) === 'contratado') {
      return showNotification(4, 'No es posible modificar stands contratados');
    }
    var data = {};
    for (var prop in fields) {
      var value = toolbar.querySelector('#' + prop).value;
      if (prop !== 'number' && prop !== 'status' && prop !== 'label') {
        value = parseFloat(value);
      }
      selected.set(fields[prop], value, false);
    }
    if (selected.get(fields.number)) {
      var pavilion = state.get('pavilion');
      for (var len = pavilion.booths.length - 1; len >= 0; i--) {
        var cur = pavilion.booths[len];
        if (selected.get(fields.number) === cur.get(fields.number)) {
          return showNotification(4, 'Número de stand duplicado');
        }
      }
      var url = selected.get(fields.id) ? url_update : url_create;
      showNotification(2, 'Guardando...');
      $.post(url, JSON.stringify(selected.attrs), function(data) {
        if (data === null) {
          showNotification(4, 'Error al crear el stand');
          return;
        }
        if (url === url_create) {
          selected.set(fields.id, data, false);
        }
        display();
        showNotification(1, '¡Stand guardado exitosamente!');
      });
    } else {
      display();
    }
  });
})();

// remove [toolbar, state, fields]
(function() {
  toolbar.querySelector('#remove').addEventListener('click', function() {
    var selected = state.get('selected');

    if (selected.get(fields.status) === 'contratado') {
      return showNotification(4, 'No es posible eliminar stands contratados');
    }

    var c = confirm('¿Está seguro?');
    if (!c) {
      return;
    }

    var data = { idStand: selected.get(fields.id) };

    function remove(selected) {
      var pavilion = state.get('pavilion');
      var booths = pavilion.booths;
      booths.splice(booths.indexOf(selected), 1);
      state.set('selected', null, false);
      state.set('booths', booths);
    }

    if (selected.get(fields.id)) {
      showNotification(2, 'Eliminando...');
      $.post(url_destroy, JSON.stringify(data), function(response) {
        if (data.error) {
          showNotification(4, 'Error al eliminar el stand');
        } else {
          remove(selected);
          showNotification(1, '¡Stand eliminado exitosamente!');
        }
      });
    } else {
      remove(selected);
    }
  });
})();

// new booth [toolbar, state, units_to_pixels, fields]
(function() {
  toolbar.querySelector('#new-booth').addEventListener('click', function() {
    var origin = state.get('origin');
    var scale = state.get('scale');
    var pavilion = state.get('pavilion');
    var booths = pavilion.booths;
    var padding = units_to_pixels(5);
    var new_booth = {}
    new_booth[fields.number] = '';
    new_booth[fields.x] = padding / scale - parseInt(origin.x);
    new_booth[fields.y] = 3*padding / scale - parseInt(origin.y);
    new_booth[fields.width] = 3;
    new_booth[fields.height] = 3;
    new_booth[fields.status] = 'libre';
    new_booth._id_Pabellon = pavilion.id;
    new_booth._id_Sala = pavilion.hallId;
    new_booth._id_EventoEdicion = pavilion.id;
    new_booth._id_Edicion = pavilion._id_Edicion;
    new_booth = new Observable(new_booth);
    new_booth.subscribe(display);
    console.log(new_booth);
    booths.push(new_booth);
    state.set('selected', new_booth);
  });
})();

// print [toolbar, state, canvas, whole_floorplan]
(function() {
  toolbar.querySelector('#print').addEventListener('click', function() {
    var pavilion = state.get('pavilion');
    var origin = { x: 0, y: 0 };
    var scale = 0.75;
    state.set('origin', origin, false);
    state.set('scale', scale, false);
    floorplan(pavilion.layout.width * 0.75, pavilion.layout.height * 0.75, state);
    var dataUrl = canvas.toDataURL();
    window.open(dataUrl);
    whole_floorplan(pavilion);
  });
})();

// zoom through wheel [set_zoom]
(function() {
  window.addEventListener('wheel', do_zoom);
  window.addEventListener('mousewheel', do_zoom);

  function do_zoom(event) {
    event.preventDefault();
    var position = {
      x: event.layerX,
      y: event.layerY
    };
    var deltaY = event.deltaY === undefined ? -event.wheelDeltaY : event.deltaY;
    var delta = deltaY < 0 ? 1.25 : 0.75;
    set_zoom(position, delta);
  };
})();

// zoom buttons [set_zoom]
(function() {
  var cursor = {
    x: window.innerWidth / 2,
    y: window.innerHeight / 2
  };

  toolbar.querySelector('#zoom-in').addEventListener('click', function(event) {
    var delta = 1.25;
    set_zoom(cursor, delta);
  });

  toolbar.querySelector('#zoom-out').addEventListener('click', function(event) {
    var delta = 0.75;
    set_zoom(cursor, delta);
  });
})();

// resizing
(function() {
})();

function set_zoom(position, delta) {
  var scale = state.get('scale');
  var origin = state.get('origin');
  state.set('scale', scale * delta, false);
  var o = zoom(position, scale, origin, state.get('scale') - scale);
  state.set('origin', o);
}

function display() {
  floorplan(window.innerWidth, window.innerHeight, state);
}

function floorplan(w, h, state) {
  var origin = state.get('origin');
  var scale = state.get('scale');
  var pavilion = state.get('pavilion');
  for (var prop in fields) {
    var input = inputs[prop];
    input.disabled = true;
    input.value = '';
  }
  remove_button.disabled = true;
  save_button.disabled = true;
  canvas.width = w; canvas.height = h;
  context = canvas.getContext('2d');
  context.strokeStyle = 'gray';
  context.scale(scale, scale);
  context.translate(origin.x, origin.y);
  context.drawImage(pavilion.layout, 0, 0);
  pavilion.booths.map(function(booth) {
    var shape = format_booth(booth.attrs);
    var padding = 1;
    var px = 0.1 * Math.min(shape.height, shape.width)
    var label;

    if (hidden(booth)) return;

    // Shape
    context.save();
    if (!shape.number.length) {
      context.fillStyle = 'rgba(85, 85, 85, 1)';
    } else if (shape.status === 'libre') {
      context.fillStyle = 'rgba(255, 255, 255, 1)';
    } else if (shape.status === 'contratado') {
      context.fillStyle = 'rgba(75, 185, 200, 1)';
    } else {
      context.fillStyle = 'rgba(215, 215, 215, 1)';
    }
    context.strokeRect(shape.x, shape.y, shape.width, shape.height);
    context.fillRect(shape.x, shape.y, shape.width, shape.height);
    context.restore();

    // Number
    var size = shape.label.length ? 8 : 12;
    context.font = size + 'px sans-serif';
    context.textAlign = 'left';
    context.fillText(shape.number, shape.x + padding, shape.y + shape.height - padding);
    context.font = px + 'px sans-serif';
    context.textAlign = 'center';

    // Label
    if (shape.label.length) {
      label = wrap_text(shape.label, shape.width / 2);
      label.map(function(l, i) {
        context.fillText(
          l.trim(),
          shape.x + shape.width / 2.0,
          shape.y + (i + 1)*px,
          shape.width
        );
      });
    }
  });

  // Border of Selected
  if (state.get('selected')) {
    var booth = state.get('selected');
    var shape = format_booth(booth.attrs);
    var anchors = create_anchors(shape);
    remove_button.disabled = false;
    save_button.disabled = false;
    for (var prop in fields) {
      var input = inputs[prop];
      input.disabled = false;
      input.value = booth.get(fields[prop]);
    }

    context.strokeStyle = 'rgba(200, 100, 0, 1)';
    context.strokeRect(shape.x, shape.y, shape.width, shape.height);
    /*
    anchors.map(function(a) {
      context.fillStyle = 'rgba(0, 0, 255, 1)';
      context.fillRect(a.x, a.y, a.width, a.height);
      context.strokeStyle = 'white';
      context.strokeRect(a.x, a.y, a.width, a.height);
    });
    */
  }
}

function wrap_text(str, width) {
  var regex = '.{1,' + width + '}(\\s|$)' + (false ? '|.{' + width + '}|.+$' : '|\\S+?(\\s|$)');
  try {
    var x = str.match(RegExp(regex, 'g')).join('<br>');
  } catch (e) {
    return [];
  }
  return x.split('<br>');
}

function intersects(shape, position) {
  return (position.x > shape.x &&
          position.x < shape.x + shape.width &&
          position.y > shape.y &&
          position.y < shape.y + shape.height)
}

function format_booth(booth) {
  return {
    number: booth[fields.number],
    label: booth[fields.label] || '',
    status: booth[fields.status],
    x: parseInt(booth[fields.x]),
    y: parseInt(booth[fields.y]),
    width: parseFloat(units_to_pixels(booth[fields.width])),
    height: parseFloat(units_to_pixels(booth[fields.height]))
  };
}

function units_to_pixels(x) {
  return 10 * x;
}

function zoom(cursor, scale, origin, delta) {
  function offset(dir) {
    return origin[dir] * scale;
  }
  function absCursor(dir) {
    return (cursor[dir] - offset(dir)) / scale;
  }
  function scaledCursor(dir) {
    return absCursor(dir) * (scale + delta);
  }
  function scaledOrigin(dir) {
    return -(scaledCursor(dir) - cursor[dir]) / (scale + delta);
  }
  return {
    x: scaledOrigin('x'),
    y: scaledOrigin('y')
  };
}

function get_position(evt, scale, origin) {
  return {
    x: (evt.layerX || evt.clientX) / scale - origin.x,
    y: (evt.layerY || evt.clientY) / scale - origin.y
  };
}

function snap(x) {
  var grid = 3;
  return Math.round(x / grid) * grid;
}

function whole_floorplan(pavilion) {
  var height = window.innerHeight;
  var width = window.innerWidth;
  var scale = Math.min(height / pavilion.layout.height, width / pavilion.layout.width);
  var origin = {
    x: ((width - (pavilion.layout.width * scale)) / scale) / 2,
    y: ((height - (pavilion.layout.height * scale)) / scale) / 2
  };
  state.set('scale', scale, false);
  state.set('origin', origin, false);
  floorplan(width, height, state);
  loader.hide();
}

function search(id) {
  var booths = state.get('pavilion').booths;
  var filtered_booths = booths.filter(function(booth) {
    var regex = new RegExp(id, 'i');
    var number = booth.get(fields.number).search(regex) !== -1;
    var label = booth.get(fields.label).search(regex) !== -1;
    return number || label;
  });
  return filtered_booths;
}

function hidden(booth) {
  var x = parseInt(booth.get(fields.x));
  var y = parseInt(booth.get(fields.y));
  var w = parseFloat(booth.get(fields.width));
  var h = parseFloat(booth.get(fields.height));
  return !w || !h || !x || !y;
}

function create_anchors(shape) {
  var xw = parseInt(shape.x) + parseInt(shape.width),
      yh = parseInt(shape.y) + parseInt(shape.height),
      verts = [
    [shape.x, shape.y],
    [xw, shape.y],
    [shape.x, yh],
    [xw, yh]
  ];
  return verts.map(function(point) {
    return {
      x: point[0] - 2,
      y: point[1] - 2,
      width: 4,
      height: 4
    };
  });
}
})();
