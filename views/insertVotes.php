<div id="insert-votes-page">
  <h3 class='pagetitle'><?php echo gT("Ajouter des votes") ?></h3>

  <div id="alerts-wrapper"></div>

  <div id="alerts-templates" class="hidden">
    <div id="all-good" class="alert alert-success alert-dismissable fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true" class="">&times;</span>
      </button>
      <span>Les votes ont été enregistrés avec succès !</span>
    </div>

    <div id="wrong-name" class="alert alert-danger alert-dismissable fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true" class="">&times;</span>
      </button>
      <span>Le nom renseigné est déjà utilisé. Veuillez essayer autre chose.</span>
    </div>

    <div id="total-equals-zero" class="alert alert-danger alert-dismissable fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true" class="">&times;</span>
      </button>
      <span>Vous devez renseigner un nombre total de votes afin d'évaluer si les nombres renseignés pour chacune des questions sont cohérents</span>
    </div>

    <div id="totals-dont-match" class="alert alert-danger alert-dismissable fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true" class="">&times;</span>
      </button>
      <span>Le nombre de votes que vous souhaitez insérer ne correspond pas au nombre de choix renseignés</span>
    </div>
  </div>

<?php
$html  =  '';
$html .=  '<form method="post" action="'. $href .'" class="form-horizontal" id="insert-votes">';
$html .=  '<div id="console"></div>';
$html .=    '<span class="hidden" id="sgqas">'. $sgqas .'</span>';
$html .=    '<input type="hidden" value="'. Yii::app()->request->csrfToken .'" name="YII_CSRF_TOKEN">';
$html .=    '<input type="hidden" value="'. $surveyId .'" name="survey_id" id="survey_id">';
$html .=    '<input type="hidden" value="'. $votesInserted .'" name="votes-inserted" id="votes-inserted">';
$html .=    '<div class="form-group">';
$html .=      '<div class="row">';
$html .=        '<label for="batch_name" class="col-xs-2 control-label">';
$html .=          gT('Nom unique');
$html .=        '</label>';
$html .=        '<div class="col-xs-3">';
$html .=          '<input type="text" name="batch-name" id="batch-name" class="form-control" maxlength="20" required />';
$html .=        '</div>';
$html .=      '</div>';
$html .=      '<div class="row">';
$html .=        '<label for="college" class="col-xs-2 control-label">';
$html .=          gT('College');
$html .=        '</label>';
$html .=        '<div class="col-xs-3">';
$html .=          '<select name="college" class="form-control" required>';
foreach($weights as $college => $weight) {
  $html .= '<option value="'. $college .'">'. $college .'</option>';
}
$html .=          '</select>';
$html .=        '</div>';
$html .=      '</div>';
$html .=      '<div class="row">';
$html .=        '<label for="number_of_votes" class="col-xs-2 control-label">';
$html .=          gT('Nombre total de votes');
$html .=        '</label>';
$html .=        '<div class="col-xs-3">';
$html .=          '<input type="number" name="number_of_votes" class="form-control" value="0" required id="number_of_votes"/>';
$html .=        '</div>';
$html .=      '</div>';
$html .=    '</div>';// .form-group

foreach($questions as $question) {
  $html .=  '<div class="form-group">';
  $html .=    '<div class="question">'. $question['title'] . $question['question'] .'</div>';

  if ($question['type'] != 'M') {// Radiobox questions (Resolutions)
    $answers = $choices[$question['qid']];
    foreach($answers as $code => $answer) {
      $html .=  '<label for="number_of_votes" class="col-xs-2 control-label">'. $answer .'</label>';
      $html .=  '<div class="col-xs-1">';
      $html .=    '<input type="number" name="'. $question['sgqa'] .'-'. $code .'" data-sgqa="'. $question['sgqa'] .'" class="form-control" value="0" required />';
      $html .=  '</div>';
    }
  }

  else {// Checkbox questions (Administrators vote)
    $html .= '<div class="row">';
    $idx   = 0;
    foreach($subQuestions as $subQuestion) {
      if ($idx > 0 && $idx % 12 == 0) {
        $html .= '</div><div class="row">';
      }

      if ($subQuestion['parent_qid'] == $question['qid']) {
        $html .=  '<label for="number_of_votes" class="col-xs-2 control-label">'; 
        $html .=    $subQuestion['question'];
        $html .=  '</label>';
        $html .=  '<div class="col-xs-1">';
        $html .=    '<input type="number" name="'. $subQuestion['sgqa'] .'" data-sgqa="'. $subQuestion['sgqa'] .'" class="form-control" value="0" required />';
        $html .=  '</div>';
        $idx  +=  3;
      }
    }
    $html .= '</div>';
  }

  $html .= '<div class="row">';
  $html .=  '<label for="total-'. $question['sgqa'] .'" class="col-xs-2 control-label">';
  $html .=    gT('Total');
  $html .=  '</label>';
  $html .=  '<div class="col-xs-1">';
  $html .=    '<input type="number" name="total-'. $question['sgqa'] .'" data-sgqa="'. $question['sgqa'] .'" class="form-control" value="0" readonly/>';
  $html .=  '</div>';
  $html .= '</div>';// .row

  $html .=  '</div>';// .form-group
}

$html .=  '<div class="form-group">';
$html .=    '<button type="button" data-toggle="modal" data-target="#confirm-submission-modal" class="btn btn-lg btn-primary col-xs-offset-6">'. gT('Insérer ces votes') .'</button>';
$html .=  '</div>';

$html .=  '</form>';

echo $html;
?>

  <div id="confirm-submission-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Confirmez-vous la sauvegarde de ces votes ?</h4>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">J'annule</button>
          <button type="button" class="btn btn-primary" id="i-confirm">Je confirme</button>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
</div><!-- #insert-votes-page -->
