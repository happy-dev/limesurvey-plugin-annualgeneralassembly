(function ( $ ) {
$(document).ready(function() {

  var FORM            = $("#insert-votes");
  var numberOfVotesEl = FORM.find("#number_of_votes");
  var surveyId        = FORM.find("#survey_id").val();
  var inputs          = FORM.find('input[name^="'+ surveyId +'"], #number_of_votes');
  var sgqas           = JSON.parse(FORM.find('#sgqas').html());
  var formSubmited    = false;
  var formValid       = false;

  var CONSOLE         = FORM.find("#console");


  inputs.on("change", function(e) {
    if (formSubmited) {
      if ($(e.currentTarget).attr("id") != "number_of_votes") {
        computeTotal(e);
      }
      verifyTotals();
    }
  });


  FORM.on("submit", function(e) {
    e.preventDefault();
    formSubmited = true;

    if (!formValid) {

    }
  });


  // Compute local total for a given question
  function computeTotal(e) {
    var input     = $(e.currentTarget);
    var sgqa      = input.data("sgqa");
    var startSgqa = sgqa.split('SQ')[0];
    var total     = 0;
    var totalEl   = FORM.find('input[name="total-'+ sgqa +'"]');

    FORM.find('input[name^="'+ startSgqa +'"]').each(function(idx, el) {
      total += Number(el.value);
    });

    totalEl.val(total);
  }


  // Verify that the amount of choices match the number of total votes
  function verifyTotals() {
    var numberOfVotes   = Number(numberOfVotesEl.val());
    formValid           = true;// We assume everuthing will be valid by default

    // For each question...
    for(var i=0; i<sgqas.length; i++) {

      var totalInput = FORM.find('input[name="total-'+ sgqas[i] +'"]');
      var total      = Number(totalInput.val());
      var formGroup  = totalInput.parents(".form-group");

      if (numberOfVotes != 0 && total != 0 && (numberOfVotes > 1 && total % numberOfVotes == 0 || numberOfVotes == 1 && numberOfVotes == total)) {// Could have more than one answer per question
        formGroup.removeClass('has-error').addClass('has-success');
      }
      else {
        formGroup.addClass('has-error').removeClass('has-success');
        formValid = false;
      }
    }
  }
});
}( jQuery ));
