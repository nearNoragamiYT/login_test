if (!Array.prototype.last){
    Array.prototype.last = function(){
        return this[this.length - 1];
    };
};

function getCoordinates (event) {
    var evt = event.touches ? event.touches[0] : event;
    return {
        x: evt.layerX || evt.clientX,
        y: evt.layerY || evt.clientY
    };
}

function getPosition (cursor, scale, origin) {
  function adjust (x) { return cursor[x] / scale - origin[x]; }
    return {
        x: adjust('x'),
        y: adjust('y')
    };
}

function samePosition (a, b) {
    return a.x === b.x && a.y === b.y;
}


function intersects(shape, position) {
    return (position.x > shape.x &&
            position.x < shape.x + shape.width &&
            position.y > shape.y &&
            position.y < shape.y + shape.height);
}

function unitsToPixels(x) { return 10 * x; }

function formatBooth(booth) {
    return {
        number: booth[fields.number],
        label: booth[fields.label] || '',
        pavilion: booth[fields.pavilion],
        stand_type: booth[fields.stand_type],
        status: booth[fields.status],
        x: parseInt(booth[fields.x]),
        y: parseInt(booth[fields.y]),
        width: parseFloat(unitsToPixels(booth[fields.width])),
        height: parseFloat(unitsToPixels(booth[fields.height]))
    };
}

function pointAtBooth (point, booth) {
    var shape = formatBooth(booth.attrs);
    return intersects(shape, point);
}

function seekBooth (scale, origin, booth) {
  function f (x, y) {
      return scale * (origin[x] + (booth.get(fields[x])) + unitsToPixels(booth.get(fields[y])/2));
        }
    return {
        x: f('x', 'width'),
        y: f('y', 'height')
    };
}

function snap(x) {
    var grid = 1;
    return Math.round(x / grid) * grid;
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

function hidden(booth) {
  var x = (+booth.get(fields.x));
  var y = (+booth.get(fields.y));
  var w = (+booth.get(fields.width));
  var h = (+booth.get(fields.height));
  return !w || !h || !x || !y;
}

function create_anchors(shape) {
    var xw = (+shape.x) + (+shape.width),
            yh = (+shape.y) + (+shape.height),
            verts = [
                [shape.x, shape.y, "ul"],
                [xw, shape.y, "ur"],
                [shape.x, yh, "bl"],
                [xw, yh, "br"]
            ];
  return verts.map(function(point) {
        return {
            x: point[0] - 2,
            y: point[1] - 2,
            width: 4,
            height: 4,
            type: point[2]
        };
    });
}
