(function ( $ ) {
$(document).ready(function() {
  var FORM            = $("#monitor-batches-form");
  var btns            = FORM.find(".delete-btn");
  var confirmModal    = $("#confirm-deletion-modal");
  var iConfirm        = confirmModal.find("#i-confirm");
  var batchName       = '';


  btns.on("click", function(e) {
    batchName = $(e.currentTarget).data("name");
  });


  iConfirm.on("click", function(e) {
    FORM.find("#batch-name").val(batchName);
    FORM.submit();
  });
});
}( jQuery ));
