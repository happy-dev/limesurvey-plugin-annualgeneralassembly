(function ( $ ) {
  $(document).ready(function() {

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

  });
}( jQuery ));
