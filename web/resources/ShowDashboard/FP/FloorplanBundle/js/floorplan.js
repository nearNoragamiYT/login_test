var floorplan = floorplan || {};

function whole_floorplan(pavilion) {
  var height = window.innerHeight;
  var width = window.innerWidth;
  var scale = Math.min(height / pavilion.layout.height, width / pavilion.layout.width);
  var origin = {
    x: ((width - (pavilion.layout.width * scale)) / scale) / 2,
    y: ((height - (pavilion.layout.height * scale)) / scale) / 2
  };
}

(function() {
  floorplan.paint_pavilion = function(pavilion) {
    var img = new Image();
    var booths = pavilion.booths;
    state.set('pavilion', pavilion, false);
    for (var i = 0, l = booths.length; i < l; i++) {
      booths[i] = new Observable(booths[i]);
      booths[i].subscribe(display);
    }
    img.src = url_layout + pavilion.layout + "?" + new Date().getTime();
    pavilion.layout = img;
    pavilion.layout.addEventListener('load', function() {
      whole_floorplan(pavilion);
    });
  };

  floorplan.scene = function(w, h, state) {
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
      var padding = 2;
      var px = 0.1 * Math.min(shape.height, shape.width)
      var label;

      // Shape
      context.save();
      if (shape.number.length === 0) {
        context.fillStyle = 'rgba(85, 85, 85, 0.85)';
      } else if (shape.free) {
        context.fillStyle = 'rgba(215, 215, 215, 0.85)';
      } else {
        context.fillStyle = 'rgba(85, 170, 85, 0.85)';
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
      remove_button.disabled = false;
      save_button.disabled = false;
      for (var prop in fields) {
        var input = inputs[prop];
        input.disabled = false;
        input.value = booth.get(fields[prop]);
      }
      context.strokeStyle = 'blue';
      context.strokeRect(shape.x, shape.y, shape.width, shape.height);
    }
  };
});
