<div class='container-fluid'>
<h3 class='pagetitle'><?php echo gT("Résultats") ?></h3>
  <div class='row'>
<?php
      Yii::import('AnnualGeneralMeeting.helpers.Utils');

      $html = '';

      foreach($questions as $question) {
        $html .=  "<table>";
        $html .=    "<tr>";
        $html .=      "<td colspan=\"3\">{$question['title']}. {$question['question']}</td>";
        $html .=    "</tr>";

        $colleges     = current($resultsByCollege);
        $emptyCollege = 0;

        foreach($colleges as $college => $codesToResults) {
          if (!Utils::nullOrEmpty($college)) {
            // Multiple Choice questions
            if ($question['type'] == 'M') {
            }

            // Other questions
            else {
              $html .=    "<tr>";
              $html .=      "<td colspan=\"3\"><strong>{$college}</strong></td>";
              $html .=    "</tr>";

              $html .=    "<tr>";
              $resultsStr = "";
              foreach($choices as $code => $answer) {
                if (!Utils::nullOrEmpty($code)) {// We filter out empty votes
                  $result      = isset($codesToResults[$code]) ? $codesToResults[$code] : 0;
                  $html       .=   "<td>{$answer}</td>";
                  $resultsStr .=   "<td>{$result}</td>";
                }
              }
              $html .=    "</tr>";

              $html .=    "<tr>";
              $html .=      $resultsStr;
              $html .=    "</tr>";

              $html .=    "<tr>";
              $html .=    "<td>&nbsp;</td>";
              $html .=    "</tr>";
            }
          }
          else {
            $emptyCollege++;
          }

          next($colleges);
        }

        $html .=  "</table>";
        $html .=  "<br/><br/><br/><br/>";

        next($resultsByCollege);
      }

      if ($emptyCollege > 0) {
        $html .= "<p>". gT("Attention, {$emptyCollege} réponses ne possèdent aucun collège renseigné.") ."</p>";
      }

      echo $html;
?>
  </div>
</div>

