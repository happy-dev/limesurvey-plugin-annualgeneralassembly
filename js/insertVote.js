(function ( $ ) {
$(document).ready(function() {

  var alertsWrapper   = $("#alerts-wrapper");
  var alertsTemplates = $("#alerts-templates");
  var confirmModal    = $("#confirm-submission-modal");
  var iConfirm        = confirmModal.find("#i-confirm");
  var FORM            = $("#insert-votes");
  var numberOfVotesEl = FORM.find("#number_of_votes");
  var surveyId        = FORM.find("#survey_id").val();
  var inputs          = FORM.find('input[name^="'+ surveyId +'"], #number_of_votes');
  var votesInserted   = FORM.find("#votes-inserted").val();
  var batchNameEl     = FORM.find("#batch-name");
  var sgqas           = JSON.parse(FORM.find('#sgqas').html());
  var formSubmited    = false;
  var formValid       = false;
  var batchNameUnique = false;
  var timer           = null;


  // Displays the selected alert
  function displayAlert(id) {
    alertsTemplates.find(id).clone().appendTo(alertsWrapper);

    clearTimeout(timer);
    timer = setTimeout(function() {
      alertsWrapper.find(".close").click();
    }, 5000);
  }


  if (votesInserted) {// Displays success message if appropriate
    displayAlert("#all-good");
  }


  // Ajax request to ensure name unicity (cheapest way to do it)
  batchNameEl.on("change", function(e) {
    $.post(FORM.attr("action"), {'ajax' : true, 'batchName' : batchNameEl.val()}, function(data) {
      if (Number(data) > 0) {
        batchNameUnique = false;
        displayAlert("#wrong-name");
      }
      else {
        batchNameUnique = true;
      }
    })
  });


  inputs.on("change", function(e) {
    if ($(e.currentTarget).attr("id") != "number_of_votes") {
      computeTotal(e);
    }

    if (formSubmited) {
      verifyTotals();
    }
  });


  iConfirm.on("click", function(e) {
    FORM.submit();
  });

  // Backup
  iConfirm_backup.on("click", function(e) {
    formSubmited = true;
    verifyTotals();

    if (!formValid) {// We display error messages
      confirmModal.modal('hide');

      if (Number(numberOfVotesEl.val()) == 0) {
        displayAlert("#total-equals-zero");
      }
      else if (!batchNameUnique) {
        displayAlert("#wrong-name");
      }
      else {
        displayAlert("#totals-dont-match");
      }
    }

    else {// We submit the form
      FORM.submit();
    }
  });


  // Compute local total for a given question
  function computeTotal(e) {
    var input     = $(e.currentTarget);
    var sgqa      = input.data("sgqa");
    var startSgqa = sgqa.split('SQ')[0];
    var total     = 0;
    var totalEl   = FORM.find('input[name="total-'+ startSgqa +'"]');

    FORM.find('input[name^="'+ startSgqa +'"]').each(function(idx, el) {
      total += Number(el.value);
    });

    totalEl.val(total);
  }


  // Verify that the amount of choices match the number of total votes
  function verifyTotals() {
    var numberOfVotes   = Number(numberOfVotesEl.val());
    formValid           = batchNameUnique;// We assume everything will be valid if name is unique

    // For each question...
    for(var i=0; i<sgqas.length; i++) {

      var totalInput = FORM.find('input[name="total-'+ sgqas[i] +'"]:not(.admin)');

      if (totalInput.length > 0) {
        var total      = Number(totalInput.val());
        var formGroup  = totalInput.parents(".form-group");

        if (numberOfVotes != 0 && total != 0 && total == numberOfVotes) {// Could have more than one answer per question
          formGroup.removeClass('has-error').addClass('has-success');
        }
        else {
          formGroup.addClass('has-error').removeClass('has-success');
          formValid = false;
        }
      }
    }
  }
});
}( jQuery ));
