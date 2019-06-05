<?php

/**
 * Displays a form and stored new votes in batch
 */
class InsertVotes {
  protected $surveyId       = 0;
  protected $weights        = null;
  protected $collegeSGQA    = '';
  protected $href           = '';
  protected $lastPage       = 123456789;
  protected $questions      = [];
  protected $subQuestions   = [];
  protected $choices        = [];
  protected $sgqas          = [];
  protected $votesInserted  = false;


  public function __construct($surveyId, $settings) {
    Yii::import('AnnualGeneralMeeting.helpers.LSUtils');

    $LSUtils              = new LSUtils($surveyId, $settings['collegeSGQA'], $settings['excludedGroups']);

    $this->surveyId       = $surveyId;
    $this->weights        = json_decode($settings['weights'], true);
    $this->collegeSGQA    = $settings['collegeSGQA'];
    $this->href           = $settings['href'];
    $this->questions      = $LSUtils->getQuestions(); 
    $this->subQuestions   = $LSUtils->getQuestions(true); 
    $questionsIds         = array_keys($this->questions);
    $this->choices        = $LSUtils->getMultipleChoices(implode(',', $questionsIds));
    $this->sgqas          = $LSUtils->getSGQAs($this->questions);

    if('POST' == $_SERVER['REQUEST_METHOD']) {
      if (isset($_POST['ajax'])) {
        $this->checkNameUnicity($_POST['batchName'], true);
      }
      else {
        if (isset($_POST['batch-name']) && $this->checkNameUnicity($_POST['batch-name']) == 0) {
          $this->processFormData();
        }
      }
    }
  }


  // Get the useful information to output the form
  public function getFormData() {
    return array(
      'surveyId'                  => $this->surveyId,
      'weights'                   => $this->weights,
      'href'                      => $this->href,
      'questions'                 => $this->questions,
      'subQuestions'              => $this->subQuestions,
      'choices'                   => $this->choices,
      'sgqas'                     => json_encode($this->sgqas),
      'votesInserted'             => $this->votesInserted,
    );
  }


  // Check batch name unicity
  public function checkNameUnicity($name, $echo = false) {
    //*** Changed by Nathanaël Drouard  : 
    //    Fix mysql_real_escape_string bug : do not work with LS3

    $query  = "SELECT startlanguage FROM {{survey_$this->surveyId}} WHERE startlanguage=:name";
    $result = Yii::app()->db->createCommand($query)->bindParam(":name", $name, PDO::PARAM_INT)->query() or safeDie("Couldn't select name<br />$query<br />");
    //$result =  Yii::app()->db->createCommand($query)->query();

    if ($echo) {
      echo $result->count();
      die();
    }
    else {
      return $result->count();
    }
  }


  // Process the form data
  public function processFormData() {
    $now                  = date("Y-m-d H:i:s");
    $votes                = [];
    $values               = [];

    // Organize results by SGQA => choice's code
    foreach($this->questions as $question) {
      if ($question['type'] != 'M') {// Radiobox type questions (Resolutions)
        $choices = $this->choices[$question['qid']];

        foreach($choices as $code => $answer) {
          $votes[$question['sgqa']][$code] = $_POST[$question['sgqa'] .'-'. $code];
        }
      }

      else {// Multiple choice questions (Administrator election
        foreach($this->subQuestions as $subQuestion) {
          if ($subQuestion['parent_qid'] == $question['qid']) {
            $votes[$subQuestion['sgqa']]['Y'] = $_POST[$subQuestion['sgqa']];
          }
        }
      }
    }


    // Prepare values for the SQL query
    while ($this->_someVotesLeft($votes)) {
      //*** Changed by Nathanaël Drouard  : 
    //    Fix mysql_real_escape_string bug : do not work with LS3
      $name = Yii::app()->db->quoteValue($_POST['batch-name']);
      $college = Yii::app()->db->quoteValue($_POST['college']);

      $filledSGQA = '';
      $buffer     = [];
      $buffer[]   = "'". $now ."'";// submitdate
      $buffer[]   = $this->lastPage;// lastpage
      $buffer[]   = $name;// startlanguage
      $buffer[]   = $college;

      foreach($votes as $sgqa => $codes) {
        $codesLength = count($codes);
        $idx         = 1;
        foreach($codes as $code => $count) {
          if (strpos($sgqa, 'SQ') == false) {// Resolutions, we skip following choices
            if ($sgqa != $filledSGQA) {
              if ($count > 0) {
                $filledSGQA = $sgqa;
                $buffer[]   = "'". $code ."'";

                $votes[$sgqa][$code]--;
              }
              else if ($idx == $codesLength) {
                $buffer[]   = "''";
              }
            }
            $idx++;
          }

          else {
            if ($count > 0) {
              $buffer[]   = "'". $code ."'";

              $votes[$sgqa][$code]--;
            }
            else {
              $buffer[]   = "''";
            }
          }
        }
      }

      $values[] = '('. implode(',', $buffer) .')';
    }

    $query      = "INSERT INTO {{survey_$this->surveyId}} (submitdate, lastpage, startlanguage, {$this->collegeSGQA}, ". implode(', ', array_keys($votes)) .") VALUES ". implode(', ', $values);
    Yii::app()->db->createCommand($query)->query();
    $this->votesInserted = true;
  }


  // Tell us if some votes still need to be dealt with
  private function _someVotesLeft($votes) {
    foreach($votes as $sgqa => $codes) {
      foreach($codes as $code => $count) {
        if ($count > 0) {
          return true;
        }
      }
    }

    return false;
  }
}
