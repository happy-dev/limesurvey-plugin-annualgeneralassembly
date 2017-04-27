<?php

/**
 * Displays a form and stored new votes in batch
 */
class InsertVotes {
  protected $surveyId       = 0;
  protected $href           = '';
  protected $lastPage       = 123456789;
  protected $questions      = [];
  protected $subQuestions   = [];
  protected $choices        = [];
  protected $sgqas          = [];
  protected $votesInserted  = false;


  public function __construct($surveyId, $href) {
    Yii::import('AnnualGeneralMeeting.helpers.LSUtils');

    $LSUtils              = new LSUtils($surveyId);

    $this->surveyId       = $surveyId;
    $this->href           = $href;
    $this->questions      = $LSUtils->getQuestions(); 
    $this->subQuestions   = $LSUtils->getQuestions(true); 
    $questionsIds         = array_keys($this->questions);
    $this->choices        = $LSUtils->getMultipleChoices(implode(',', $questionsIds));
    $this->sgqas          = $LSUtils->getSGQAs($this->questions);

    if('POST' == $_SERVER['REQUEST_METHOD']) {
      $this->processFormData();
    }
  }


  // Get the useful information to output the form
  public function getFormData() {
    return array(
      'surveyId'                  => $this->surveyId,
      'href'                      => $this->href,
      'questions'                 => $this->questions,
      'subQuestions'              => $this->subQuestions,
      'choices'                   => $this->choices,
      'sgqas'                     => json_encode($this->sgqas),
    );
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
      $filledSGQA = '';
      $buffer     = [];
      $buffer[]   = "'". $now ."'";// submitdate
      $buffer[]   = $this->lastPage;// lastpage
      $buffer[]   = "'". $_POST['batch_name'] ."'";// startlanguage
      $buffer[]   = "'". $now ."'";// startdate
      $buffer[]   = "'". $now ."'";// datestamp

      foreach($votes as $sgqa => $codes) {
        foreach($codes as $code => $count) {
          if (strpos($sgqa, 'SQ') == false) {// Resolutions, we skip following choices
            if ($sgqa != $filledSGQA && $count > 0) {
              $filledSGQA = $sgqa;
              $buffer[]   = $code;

              $votes[$sgqa][$code]--;
            }
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

    $query      = "INSERT INTO {{survey_$this->surveyId}} (submitdate, lastpage, startlanguage, startdate, datestamp, ". implode(', ', array_keys($votes)) .") VALUES ". implode(', ', $values);
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
