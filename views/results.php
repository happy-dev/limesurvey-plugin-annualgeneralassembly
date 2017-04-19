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
              $html .=   "<td><strong>{$subQuestion['question']}</strong></td>";
            }
          }
          $html .=      "<td><strong>". gT("Total") ."</strong></td>";
          $html .=    "</tr>";


          $colleges = $resultsByCollege[$question['sgqa']];
          foreach($colleges as $college => $sgqas) {
            $html .=  "<tr>";
            $html .=    "<td>{$college}</td>";

            $total = 0;
            foreach($sgqas as $sgqa => $choices) {
              if ($sgqa != 'total') {
                $result = isset($choices['Y']) ? $choices['Y'] : 0;
                $html .=  "<td>{$result}</td>";
              }
              else {
                $total = $choices;
              }
            }

            $html .=    "<td>{$total}</td>";
            $html .=  "</tr>";
          }


          // Last line of the table
          $html .=    "<tr>";
          $html .=      "<td><strong>". gT("Résultats") ."</strong></td>";

          $total = 0;
          foreach($choices as $code => $answer) {
            if (!Utils::nullOrEmpty($code)) {// We filter out empty votes
              $html .=  "<td>{$resultsByQuestion[$question['qid']][$code]['total']}</td>";
              $html .=  "<td><strong>". round($resultsByQuestion[$question['qid']][$code]['result'], 2) ."%</strong></td>";
              $total += $resultsByQuestion[$question['qid']][$code]['total'];
            }
          }

          $html .=      "<td colspan=\"2\">{$total}</td>";
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
                      $html .=   "<td colspan=\"2\"><strong>{$answer}</strong></td>";
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

          $total = 0;
          foreach($choices as $code => $answer) {
            if (!Utils::nullOrEmpty($code)) {// We filter out empty votes
              $html .=  "<td>{$resultsByQuestion[$question['qid']][$code]['total']}</td>";
              $html .=  "<td><strong>". round($resultsByQuestion[$question['qid']][$code]['result'], 2) ."%</strong></td>";
              $total += $resultsByQuestion[$question['qid']][$code]['total'];
            }
          }

          $html .=      "<td colspan=\"2\">{$total}</td>";
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

