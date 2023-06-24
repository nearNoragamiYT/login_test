function Observable(attrs) {
  var subscribers = [];

  return {
    attrs: attrs,

    get: function(property) {
      return attrs[property];
    },

    set: function(property, value, update) {
      if (update === undefined) {
        update = true;
      }
      attrs[property] = value;
      if (update) {
        subscribers.map
          (function(f) {
            f.call(null);
          });
      }
    },

    subscribe: function(f) {
      subscribers.push(f);
    }
  };
}
