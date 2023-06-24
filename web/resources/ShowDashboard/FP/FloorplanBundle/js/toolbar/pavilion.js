var toolbar = toolbar || {};

(function() {
  var pavilion_select = document.getElementById('pavilion-select');

  function create_option(pavilion) {
    var pavilion_option = document.createElement('option');
    pavilion_option.value = pavilion['_id_EventoEdicion'];
    pavilion_option.innerHTML = pavilion['Nombre_ES'];
    pavilion_select.appendChild(pavilion_option);
  }

  toolbar.pavilion = {
    load_pavilions: function(pavilions) {
      var pavilion_id;
      pavilions.map(create_option);
      pavilion_id = pavilion_select.value;
    },

    current: function() {
      return pavilion_select.value;
    },

    change: function(callback) {
      pavilion_select.addEventListener('change', callback);
    }
  };
})();
