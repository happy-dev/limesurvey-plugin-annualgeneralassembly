<div class='container-fluid'>
  <h3 class='pagetitle'>A page</h3>
  <div class='row'>
    <div class='col-sm-6'>
      <p>Left column</p>
<?php
      $html = '';
      $idx  = 0;

      foreach($questions as $question) {
        if ($idx >= $startIndex) {
          $html .=  "<table>";
          $html .=    "<tr>";
          $html .=      "<td>{$question['title']}. {$question['question']}</td>";
          $html .=    "</tr>";

          $html .=    "<tr>";
          //foreach($questions as $choices) {
            //foreach($choices as $choice => $result) {
              //$html .=    "<td>{$choice}</td>";
            //}
          //}
          $html .=    "</tr>";

          $html .=    "<tr>";
          //foreach($questions as $choices) {
            //foreach($choices as $choice => $result) {
              //$html .=    "<td>{$result}</td>";
            //}
          //}
          $html .=    "</tr>";
          $html .=  "</table>";
        }

        $idx++;
      }
      echo $html;
?>
      </table>
    </div>
    <div class='col-sm-6'>
      <p>Right column</p>
    </div>
  </div>
</div>

