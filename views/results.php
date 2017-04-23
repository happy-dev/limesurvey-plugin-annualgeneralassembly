<div class='container-fluid'>
<h3 class='pagetitle'><?php echo gT("Résultats") ?></h3>
  <div class='row'>
<?php
      Yii::import('AnnualGeneralMeeting.helpers.Utils');

      $html = '';

      foreach($questions as $question) {
        $question['sgqa'] = $surveyId .'X'. $question['gid'] .'X'. $question['qid'];
        $emptyCollege     = 0;
        $firstCollege     = true;

        $html .= '<canvas id="donut-'. $question['sgqa'] .'" data-sgqa="'. $question['sgqa'] .'" width="400" height="400"></canvas>';// Donut chart

        if ($question['type'] == 'M') {// Multiple Choice questions (votes for administrators)
          $html .=  "<table>";
          $html .=    "<tr>";
          $html .=      "<td colspan=\"8\">{$question['title']}. {$question['question']}</td>";
          $html .=    "</tr>";

          // First line
          $html .=    "<tr>";
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
          $html .=    "<tr>";
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
          $html .=  "<table>";
          $html .=    "<tr>";
          $html .=      "<td colspan=\"8\">{$question['title']}. {$question['question']}</td>";
          $html .=    "</tr>";

          $colleges = $resultsByCollege[$question['sgqa']];
          foreach($colleges as $college => $codesToResults) {

            if (!Utils::nullOrEmpty($college)) {// If college unknown, we can't include your vote...
              if ($firstCollege) {// First line of the table
                $html .=    "<tr>";
                $html .=      "<td></td>";
                  foreach($choices as $code => $answer) {
                    if (!Utils::nullOrEmpty($code)) {// We filter out empty votes
                      $html .=   "<td colspan=\"2\"><strong class=\"label-{$question['sgqa']}\">{$answer}</strong></td>";
                    }
                  }
                $html .=      "<td><strong>". gT("Total") ."</strong></td>";
                $html .=    "</tr>";
              }

              $html .=    "<tr>";
              $html .=      "<td><strong>{$college}</strong></td>";

              foreach($choices as $code => $answer) {
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
          $html .=    "<tr>";
          $html .=      "<td><strong>". gT("Résultats") ."</strong></td>";

          foreach($choices as $code => $answer) {
            if (!Utils::nullOrEmpty($code)) {// We filter out empty votes
              $html .=  "<td>{$resultsByQuestion[$question['qid']][$code]['total']}</td>";
              $html .=  "<td><strong><span class=\"data-{$question['sgqa']}\">". round($resultsByQuestion[$question['qid']][$code]['result'], 2) ."</span>%</strong></td>";
            }
          }

          $html .=      "<td colspan=\"2\">{$resultsByQuestion[$question['qid']]['total']}</td>";
          $html .=    "</tr>";
        }

        $html .=  "</table>";
        $html .=  "<br/><br/><br/><br/>";
      }

      if ($emptyCollege > 0) {
        $html .= "<p>". gT("Attention, {$emptyCollege} réponses ne possèdent aucun collège renseigné.") ."</p>";
      }

      echo $html;
?>
  </div>
</div>

