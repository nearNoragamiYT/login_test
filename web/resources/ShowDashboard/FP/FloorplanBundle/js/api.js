
var Api = {
  load_pavilions: function(url) {
    $.getJSON(url, function(pavilions) {
      var pavilion_id;
      pavilions.map(create_option);
      pavilion_id = pavilion_select.value;
      $.getJSON(url_pavilion + '/' + pavilion_id, paint_pavilion);
    });
  }
};
