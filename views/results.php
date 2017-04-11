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

        $colleges = current($resultsByCollege);

        foreach($colleges as $college => $answers) {
          if (!Utils::nullOrEmpty($college)) {
            $html .=    "<tr>";
            $html .=      "<td colspan=\"3\"><strong>{$college}</strong></td>";
            $html .=    "</tr>";

            $html .=    "<tr>";
            $results = "";
            foreach($answers as $code => $result) {
              if (!Utils::nullOrEmpty($choices[$code])) {
                $html    .=   "<td>{$choices[$code]}</td>";
              }
              else {
                $html    .=   "<td>N'a pas voté</td>";
              }
              $results .=   "<td>{$result}</td>";
            }
            $html .=    "</tr>";

            $html .=    "<tr>";
            $html .=      $results;
            $html .=    "</tr>";

            $html .=    "<tr>";
            $html .=    "<td>&nbsp;</td>";
            $html .=    "</tr>";
          }
        }

        $html .=  "</table>";
        $html .=  "<br/><br/><br/><br/>";

        next($resultsByCollege);
      }

      echo $html;
?>
  </div>
</div>

