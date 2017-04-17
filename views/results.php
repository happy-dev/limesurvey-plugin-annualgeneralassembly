<div class='container-fluid'>
<h3 class='pagetitle'><?php echo gT("Résultats") ?></h3>
  <div class='row'>
<?php
      Yii::import('AnnualGeneralMeeting.helpers.Utils');

      $html = '';

      foreach($questions as $question) {
        $html .=  "<table>";
        $html .=    "<tr>";
        $html .=      "<td colspan=\"8\">{$question['title']}. {$question['question']}</td>";
        $html .=    "</tr>";

        $colleges     = $resultsByCollege[$surveyId .'X'. $question['gid'] .'X'. $question['qid']];
        $emptyCollege = 0;
        $firstCollege = true;

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

            if ($question['type'] == 'M') {// Multiple Choice questions (votes for administrators)
              $html .=    "<tr>";
              foreach($resultsByCollege as $sgqa => $college) {
                foreach($subQuestions as $qid => $subQuestion) {
                  if ($subQuestion['parent_qid'] == $question['qid']) {
                    $html       .=   "<td>{$subQuestion['question']}</td>";
                  }
                }
              }
              $html .=    "</tr>";
            }

            else {// Other questions
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
          }
          else {
            $emptyCollege++;
          }

          next($colleges);
          $firstCollege = false;
        }


        // Last line of the table
        if ($question['type'] == 'M') {// Multiple Choice questions (votes for administrators)
        }
        else {// Other questions
          $html .=    "<tr>";
          $html .=      "<td><strong>". gT("Résultats") ."</strong></td>";

          $total = 0;
          foreach($choices as $code => $answer) {
            if (!Utils::nullOrEmpty($code)) {// We filter out empty votes
              $html .=  "<td>{$resultsByQuestion[$question['qid']][$code]['total']}</td>";
              $html .=  "<td><strong>{$resultsByQuestion[$question['qid']][$code]['result']}%</strong></td>";
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

