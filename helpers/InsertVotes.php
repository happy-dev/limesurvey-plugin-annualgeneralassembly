<?php

/**
 * Displays a form and stored new votes in batch
 */
class InsertVotes {
  protected $surveyId     = 0;
  protected $href         = '';


  public function __construct($surveyId, $href) {
    $this->surveyId       = $surveyId;
    $this->href           = $href;

    if('POST' == $_SERVER['REQUEST_METHOD']) {
      $this->processFormData();
    }
  }


  // Get the useful information to output the form
  public function getFormData() {
    Yii::import('AnnualGeneralMeeting.helpers.LSUtils');

    $LSUtils              = new LSUtils($this->surveyId);
    $questions            = $LSUtils->getQuestions(); 
    $subQuestions         = $LSUtils->getQuestions(true); 
    $questionsIds         = array_keys($questions);
    $choices              = $LSUtils->getMultipleChoices(implode(',', $questionsIds));

    return array(
      'surveyId'                  => $this->surveyId,
      'href'                      => $this->href,
      'questions'                 => $questions,
      'subQuestions'              => $subQuestions,
      'choices'                   => $choices,
    );
  }


  // Process the form data
  public function processFormData() {
    echo 'Processing form data...';
  }
}
