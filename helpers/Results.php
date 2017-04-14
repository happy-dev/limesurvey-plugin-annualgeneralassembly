<?php

/**
 * Gathers and compute the appropriate results
 */
class Results {
  protected $surveyId     = 0;
  protected $collegeSGQA   = 0;
  protected $weights      = null;
  

  public function __construct($surveyId, $settings) {
    $this->surveyId       = $surveyId;
    $this->collegeSGQA    = $settings['collegeSGQA'];
    $this->weights        = json_decode($settings['weights']);
  }


  public function getResultsData() {
    //$survey->attributeNames();// List of attributes names, inclusing questions
    //$survey->getAttributes();// Get attributes values
    //$survey->metaData->columns;// Columns
    //$survey->search();// Data provider to Reponses grid widget
    //$totalAnswers           = $survey->count();
    //$totalCompletedAnswers  = $survey->count('submitdate IS NOT NULL');

    $survey           = SurveyDynamic::model($this->surveyId);
    $questions        = $this->getQuestions(); 
    $questionsIds     = array_keys($questions);
    $choices          = $this->getChoices(implode(',', $questionsIds));
    $answers          = $this->getAnswers();
    $sgqaStart        = $this->getSGQAStart();
    $resultsByCollege = [];
    $startIndex       = 0;

    Yii::import('AnnualGeneralMeeting.helpers.Utils');


    // Computing results
    foreach($answers as $answer) {
      foreach($answer as $sgqa => $code) {

        if (Utils::startsByOneOfThese($sgqa, $sgqaStart)) {
          if (!isset($resultsByCollege[$sgqa])) {
            $resultsByCollege[$sgqa] = [];
          }
          if (!isset($resultsByCollege[$sgqa][$answer[$this->collegeSGQA]])) {
            $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]] = [];
          }
          if (!isset($resultsByCollege[$sgqa][$answer[$this->collegeSGQA]][$code])) {
            $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]][$code] = 0;
          }
          $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]][$code]++;
        }
      }
    }


    return array(
      'questions'                 => $questions,
      'choices'                   => $choices,
      'sgqaStart'                 => $sgqaStart,
      'resultsByCollege'          => $resultsByCollege,
    );
  }


  // Returns resolutions only of the given survey
  public function getQuestions() {
    $query      = "SELECT qid, title, question FROM {{questions}} WHERE parent_qid=0 AND sid='{$this->surveyId}' AND title LIKE 'R%' ORDER BY gid, question_order ASC";
    $results    =  Yii::app()->db->createCommand($query)->query();
    $questions  = [];

    foreach($results as $r) {
      $questions[$r['qid']] = [
        'qid'       => $r['qid'],
        'title'     => $r['title'],
        'question'  => $r['question'],
      ];
    }

    return $questions;
  }


  // Returns possible choices for a given question (multiple choice question) 
  public function getChoices($questionsIds) {// Questions Ids
    $query      = "SELECT code, answer FROM {{answers}} WHERE qid IN ({$questionsIds}) ORDER BY code ASC";
    $answers    =  Yii::app()->db->createCommand($query)->query();
    $choices    = [ null => "N'a pas voté"];

    foreach($answers as $answer) {
      $choices[$answer['code']] = $answer['answer'];
    }

    return $choices;
  }


  // Returns answers for a given survey
  public function getAnswers() {
    $query      = "SELECT * FROM {{survey_$this->surveyId}}";

    return  Yii::app()->db->createCommand($query)->query();
  }


  public function getSGQAStart() {
    $SGQAs      = [];
    $query      = "SELECT gid FROM {{groups}} WHERE sid='{$this->surveyId}' AND group_name LIKE 'Résolutions%'";
    $groups     = Yii::app()->db->createCommand($query)->query();

    foreach($groups as $group) {
      $SGQAs[] = $this->surveyId .'X'. $group['gid'];
    }

    return $SGQAs;
  }
}
