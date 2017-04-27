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
$html .=    '<div class="form-group">';
$html .=      '<div class="row">';
$html .=        '<label for="batch_name" class="col-xs-2 control-label">';
$html .=          gT('Nom unique');
$html .=        '</label>';
$html .=        '<div class="col-xs-2">';
$html .=          '<input type="text" name="batch_name" class="form-control" />';
$html .=        '</div>';
$html .=      '</div>';
$html .=      '<div class="row">';
$html .=        '<label for="number_of_votes" class="col-xs-2 control-label">';
$html .=          gT('Nombre total de votes');
$html .=        '</label>';
$html .=        '<div class="col-xs-2">';
$html .=          '<input type="number" name="number_of_votes" class="form-control" value="0" id="number_of_votes"/>';
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
      $html .=    '<input type="number" name="'. $question['sgqa'] .'-'. $code .'" data-sgqa="'. $question['sgqa'] .'" class="form-control" value="0" />';
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
        $html .=    '<input type="number" name="'. $subQuestion['sgqa'] .'" data-sgqa="'. $subQuestion['sgqa'] .'" class="form-control" value="0" />';
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
$html .=    '<input type="submit" class="btn btn-primary col-xs-offset-6" value="'. gT('Insérer ces votes') .'"/>';
$html .=  '</div>';

$html .=  '</form>';

echo $html;
?>

</div><!-- #insert-votes-page -->
