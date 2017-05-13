<h3 class='pagetitle'><?php echo gT("Résultats") ?></h3>
<?php
Yii::import('AnnualGeneralMeeting.helpers.Utils');

$at    = 'active';// Active Tab
$html  = '';
$html .= '<ul class="nav nav-tabs" role="tablist">';
foreach($questions as $question) {
  $question['sgqa'] = $surveyId .'X'. $question['gid'] .'X'. $question['qid'];

  if ($question['sgqa'] == $collegeSGQA) {// Skipping the college question
    continue;
  }

  $html .=  '<li role="presentation" class="'. $at .'">';
  $html .=    '<a href="#qid-'. $question['qid'] .'" class="'. $at .'" aria-controls="'. $question['title'] .'" role="tab" data-toggle="tab">'. $question['title'] .'</a>';
  $html .=  '</li>';
  $at    =  ''; }
$html .= '</ul>';


$html .= '<div class="tab-content">';
$at    = 'active';// Active Tab
foreach($questions as $question) {
  $question['sgqa'] = $surveyId .'X'. $question['gid'] .'X'. $question['qid'];
  $emptyCollege     = 0;
  $firstCollege     = true;

  if ($question['sgqa'] == $collegeSGQA) {// Skipping the college question
    continue;
  }
  
  $html .= '<div role="tabpanel" class="tab-pane '. $at .'" id="qid-'. $question['qid'] .'">';
  $html .=    '<div class="row">';
  $html .=      "<div class=\"col-sm-6\">{$question['question']}</div>";

  $html .=      '<canvas id="donut-'. $question['sgqa'] .'" data-sgqa="'. $question['sgqa'] .'" class="col-sm-6"></canvas>';// Donut chart
  $html .=    '</div>';// .row

  $html .=  '<div class="table-responsive">';
  $html .=  "<table class=\"table table-bordered table-hover table-condensed\">";

  if ($question['type'] == 'M') {// Multiple Choice questions (votes for administrators)
    // First line
    $html .=    "<tr class=\"active\">";
    $html .=      "<td></td>";
    foreach($subQuestions as $qid => $subQuestion) {
      if ($subQuestion['parent_qid'] == $question['qid']) {
        $html .=   "<td colspan=\"2\"><strong class=\"label-{$question['sgqa']}\">{$subQuestion['question']}</strong></td>";
      }
    }
    $html .=      "<td><strong>". gT("Total") ."</strong></td>";
    $html .=    "</tr>";


    $colleges = $resultsByCollege[$question['sgqa']];
    foreach($colleges as $college => $sgqas) {
      $html .=  "<tr>";
      $html .=    "<td>{$college}</td>";

      $total = 0;
      foreach($sgqas as $sgqa => $chs) {
        if ($sgqa != 'total') {
          $result = isset($chs['Y']) ? $chs['Y'] : 0;
          $html .=  "<td>{$result}</td>";
          $html .=  "<td>". round(Utils::percentage($result, $sgqas['total']), 2) ."%</td>";
        }
        else {
          $total = $chs;
        }
      }

      $html .=    "<td>{$total}</td>";
      $html .=  "</tr>";
    }


    // Last line of the table
    $html .=    "<tr class=\"info\">";
    $html .=      "<td><strong>". gT("Résultats") ."</strong></td>";

    $sqs = $resultsBySubQuestion[$question['qid']];// SubQuestions
    foreach($subQuestions as $qid => $subQuestion) {
      if ($subQuestion['parent_qid'] == $question['qid']) {
        $numbers = $sqs[$qid];

        $html .=  "<td>{$numbers['total']}</td>";
        $html .=  "<td><strong><span class=\"data-{$question['sgqa']}\">". round($numbers['result'], 2) ."</span>%</strong></td>";
      }
    }

    $html .=      "<td colspan=\"2\">{$sqs['total']}</td>";
    $html .=    "</tr>";
  }

  else {// Radiobox questions (votes for resolutions)
    $colleges = $resultsByCollege[$question['sgqa']];

    foreach($colleges as $college => $codesToResults) {

      if (!Utils::nullOrEmpty($college)) {// If college unknown, we can't include your vote...
        if ($firstCollege) {// First line of the table
          $html .=    "<tr class=\"active\">";
          $html .=      "<td></td>";
            foreach($choices[$question['qid']] as $code => $answer) {
              if (!Utils::nullOrEmpty($code)) {// We filter out empty votes
                $html .=   "<td colspan=\"2\"><strong class=\"label-{$question['sgqa']}\">{$answer}</strong></td>";
              }
            }
          $html .=      "<td><strong>". gT("Total") ."</strong></td>";
          $html .=    "</tr>";
        }

        $html .=    "<tr>";
        $html .=      "<td><strong>{$college}</strong></td>";

        foreach($choices[$question['qid']] as $code => $answer) {
          if (!Utils::nullOrEmpty($code)) {// We filter out empty votes
            $result  = isset($codesToResults[$code]) ? $codesToResults[$code] : 0;
            $html   .=   "<td>{$result}</td>";
            $html   .=   "<td>". round(Utils::percentage($result, $codesToResults['total']), 2) ."%</td>";
          }
        }

        $html   .=   "<td colspan=\"2\">{$codesToResults['total']}</td>";
        $html .=    "</tr>";
      }
      else {
        $emptyCollege++;
      }

      next($colleges);
      $firstCollege = false;
    }

    // Last line of the table
    $html .=    "<tr class=\"info\">";
    $html .=      "<td><strong>". gT("Résultats") ."</strong></td>";

    foreach($choices[$question['qid']] as $code => $answer) {
      if (!Utils::nullOrEmpty($code)) {// We filter out empty votes
        $html .=  "<td>{$resultsByQuestion[$question['qid']][$code]['total']}</td>";
        $html .=  "<td><strong><span class=\"data-{$question['sgqa']}\">". round($resultsByQuestion[$question['qid']][$code]['result'], 2) ."</span>%</strong></td>";
      }
    }

    $html .=      "<td colspan=\"2\">{$resultsByQuestion[$question['qid']]['total']}</td>";
    $html .=    "</tr>";
  }

  $html .=  "</table>";
  $html .=  "</div>";// .table-responsive
  $html .=  "</div>";// .tab-pan

  $at    =  '';
}
$html .=  "</div>";// .tab-content

if ($emptyCollege > 0) {
  $html .= '<br/><p class="text-warning">'. gT("Attention, {$emptyCollege} réponses ne possèdent aucun collège renseigné.") ."</p>";
}

echo $html;
?>
