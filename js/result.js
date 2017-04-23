(function ( $ ) {
  $(document).ready(function() {

    Chart.defaults.global.defaultFontSize = 14;

    // Displays donut charts
    $("canvas").each(function(idx, ctx) {
      var data = getDataFromDOM($(ctx).data("sgqa"));

      new Chart(ctx, {
        type:   'doughnut',
        data:   {
          labels : data.labels,
          datasets :  [{
            label :   "Vote de la résolution",
            data :    data.data,
            backgroundColor: getRGBColors(data.labels.length, 1),
            hoverBackgroundColor: getRGBColors(data.labels.length, 0.8),
            //borderWidth : [2, 2, 2],
          }],
        },
      });

      $(ctx).html(data);
    });


    // Parse DOM to return relevant data
    function getDataFromDOM(sgqa) {
      var data = {
        labels :    [],
        data :      [],
      };

      // Grabbing labels
      $(".label-"+ sgqa).each(function(idx, el) {
        data.labels.push(el.innerHTML);
      });

      // Grabbing results
      $(".data-"+ sgqa).each(function(idx, el) {
        data.data.push(Number(el.innerHTML));
      });

      return data;
    }


    // Generate an array of hex colors of the correct size
    function getHexColors(length) {
      var colors = ["#FF6384", "#36A2EB", "#FFCE56", "#cc00cc", "#00cccc", "#66cc99", "#c65353", "#ffff66", "#bf8040", "#6666ff"];

      for(var i=0; i<10; i++) {
        colors = colors.concat(colors);
      }

      return colors.splice(0, length);
    }


    // Generate an array of RGB colors of the correct size
    function getRGBColors(length, opacity) {
      var hexArray = getHexColors(length);
      var rgbArray = [];

      $(hexArray).each(function(idx, hex) {
        var rgb = hexToRgb(hex);

        rgbArray.push('rgba('+ rgb.r +','+ rgb.g +','+ rgb.b +','+ opacity +')');
      });

      document.rgbArray = rgbArray;
      window.rgbArray = rgbArray;

      return rgbArray;
    }


    // Convert hex color to RGB
    function hexToRgb(hex) {
      var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

      return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
      } : null;
    }
  });
}( jQuery ));
