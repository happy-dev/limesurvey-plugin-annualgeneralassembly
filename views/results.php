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

        $colleges   = current($resultsByCollege);

        foreach($colleges as $college => $codesToResults) {
          if (!Utils::nullOrEmpty($college)) {
            $html .=    "<tr>";
            $html .=      "<td colspan=\"3\"><strong>{$college}</strong></td>";
            $html .=    "</tr>";

            $html .=    "<tr>";
            $resultsStr = "";
            foreach($choices as $code => $answer) {
              $results = current($codesToResults);

              $html    .=   "<td>{$answer}</td>";
              $resultsStr .=   "<td>{$codesToResults[$code]}</td>";

              next($codesToResults);
            }
            $html .=    "</tr>";

            $html .=    "<tr>";
            $html .=      $resultsStr;
            $html .=    "</tr>";

            $html .=    "<tr>";
            $html .=    "<td>&nbsp;</td>";
            $html .=    "</tr>";
          }

          next($colleges);
        }

        $html .=  "</table>";
        $html .=  "<br/><br/><br/><br/>";

        next($resultsByCollege);
      }

      echo $html;
?>
  </div>
</div>

