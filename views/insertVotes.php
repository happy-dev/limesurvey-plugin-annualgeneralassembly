<h3 class='pagetitle'><?php echo gT("Ajouter des votes") ?></h3>

<?php
$html  =  '<form method="post" action="'. $href .'" class="form-horizontal" id="insert-votes">';
$html .=    '<input type="hidden" value="'. Yii::app()->request->csrfToken .'" name="YII_CSRF_TOKEN">';
$html .=  '<div class="form-group">';
$html .=    '<label for="number_of_votes" class="col-xs-2 control-label">';
$html .=      gT('Nombre de votes / pouvoirs');
$html .=    '</label>';
$html .=    '<div class="col-xs-1">';
$html .=      '<input type="number" name="number_of_votes" class="form-control" value="0"/>';
$html .=    '</div>';
$html .=  '</div>';

foreach($questions as $question) {
  $html .=  '<div class="form-group">';
  $html .=    '<div class="question">'. $question['title'] . $question['question'] .'</div>';

  if ($question['type'] != 'M') {// Radiobox questions (Resolutions)
    $answers = $choices[$question['qid']];
    foreach($answers as $code => $answer) {
      $html .=  '<label for="number_of_votes" class="col-xs-2 control-label">'. $answer .'</label>';
      $html .=  '<div class="col-xs-1">';
      $html .=    '<input type="number" name="'. $question['sgqa'] .'-'. $code .'" class="form-control" value="0" />';
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
        $html .=    '<input type="number" name="'. $subQuestion['sgqa'] .'" class="form-control" value="0" />';
        $html .=  '</div>';
        $idx  +=  3;
      }
    }
    $html .= '</div>';
  }

  $html .=  '</div>';// .form-group
}

$html .=  '<div class="form-group">';
$html .=    '<input type="submit" class="btn btn-primary col-xs-offset-6" value="'. gT('Insérer ces votes') .'"/>';
$html .=  '</div>';

$html .=  '</form>';

echo $html;
