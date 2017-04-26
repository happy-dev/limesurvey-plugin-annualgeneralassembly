(function ( $ ) {
$(document).ready(function() {

  var FORM            = $("#insert-votes");
  var numberOfVotesEl = FORM.find("#number_of_votes");
  var surveyId        = FORM.find("#survey_id").html();
  var inputs          = FORM.find('input[name^="'+ surveyId +'"]');
  var sgqas           = JSON.parse(FORM.find('#sgqas').html());
  var formSubmited    = false;


  inputs.on("change", function(e) {
    if (formSubmited) {
      verifyTotals();
    }
  });


  FORM.on("submit", function(e) {
    e.preventDefault();
    formSubmited = true;
  });


  // Verify that the amount of choices match the number of total votes
  function verifyTotals() {
    var numberOfVotes   = numberOfVotesEl.val();

    // For each question...
    for(var i=0; i<sgqas.length; i++) {
      var total = 0;

      // For each choice...
      inputs.find('input[name^="'+ sgqas[i] +'"]').each(function(idx, input) {
        total += input.val();
      });
      
      var totalInput = FORM.find('input[name="total-'+ sgqas[i] +'"]');
      if (total == numberOfVotes) {
        totalInput.removeClass('text-danger').addClass('text-success');
      }
      else {
        totalInput.addClass('text-danger').removeClass('text-success');
      }
    }
  }
});
}( jQuery ));
