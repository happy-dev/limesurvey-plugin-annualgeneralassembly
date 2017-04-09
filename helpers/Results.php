<?php

/**
 * Gathers and compute the appropriate results
 */
class Results {
  protected $surveyId     = 0;
  protected $collegeField = 'attribute_3';
  

  public function __construct($surveyId) {
    $this->surveyId = $surveyId;
  }


  public function getResultsData() {
    //$survey->attributeNames();// List of attributes names, inclusing questions
    //$survey->getAttributes();// Get attributes values
    //$survey->metaData->columns;// Columns
    //$survey->search();// Data provider to Reponses grid widget

    $survey           = SurveyDynamic::model($this->surveyId);
    $questions        = $this->getQuestions(); 
    $choices          = $this->getChoices($question['qid']);
    $answers          = $this->getAnswers();
    $people           = $this->getPeople($answers);
    $answers          = $this->getAnswers();// Required, as rewinding is not an option
    $tokensToColleges = $this->getTokensToColleges($people);
    $resultsByCollege = [];
    $startIndex       = 0;
    $yet              = false;

    Yii::import('AnnualGeneralMeeting.helpers.Utils');

    foreach($answers as $answer) {
      foreach($answer as $k => $v) {

        if (Utils::startsWith($this->surveyId, $k)) {
          $yet = true;

          if (!isset($resultsByCollege[$k])) {
            $resultsByCollege[$k] = [];
          }
          if (!isset($resultsByCollege[$k][$tokensToColleges[$answer['token']]])) {
            $resultsByCollege[$k][ $tokensToColleges[ $answer['token'] ] ] = [];
          }
          if (!isset($resultsByCollege[$k][$tokensToColleges[$answer['token']]][$v])) {
            $resultsByCollege[$k][ $tokensToColleges[ $answer['token'] ] ][$v] = 0;
          }
          $resultsByCollege[$k][ $tokensToColleges[ $answer['token'] ] ][$v]++;
        }
        else if (!$yet) {
          $startIndex++;
        }
      }
    }

    $totalAnswers           = $survey->count();
    $totalCompletedAnswers  = $survey->count('submitdate IS NOT NULL');

    return array(
      'totalAnswers'              => $totalAnswers,
      'totalCompletedAnswers'     => $totalCompletedAnswers,
      'questions'                 => $questions,
      'choices'                   => $choices,
      'resultsByCollege'          => $resultsByCollege,
      'startIndex'                => $startIndex,
    );
  }


  // Returns resolutions only of the given survey
  public function getQuestions() {
    $query      = "SELECT qid, title, question FROM {{questions}} WHERE parent_qid=0 AND sid='{$this->surveyId}' ORDER BY gid, question_order ASC";
    // AND title LIKE 'R%' 

    return Yii::app()->db->createCommand($query)->query();
  }


  // Returns possible choices for a given question (multiple choice question) 
  public function getChoices($qid) {// Question Id
    $query      = "SELECT code, answer FROM {{answers}} WHERE qid='{$qid}' ORDER BY sortorder ASC";

    return  Yii::app()->db->createCommand($query)->query();
  }


  // Returns answers for a given survey
  public function getAnswers() {
    $query      = "SELECT * FROM {{survey_$this->surveyId}}";

    return  Yii::app()->db->createCommand($query)->query();
  }


  // Returns people who responded to the survey
  public function getPeople($answers) {
    $tokens     = [];
    $tokensStr  = '';

    foreach($answers as $answer) {
      $tokens[] = $answer['token'];
    }

    $tokensStr  = "'". implode("','", $tokens) ."'"; 
    $query      = "SELECT token, {$this->collegeField} FROM {{tokens_$this->surveyId}} WHERE token IN ({$tokensStr})";

    return  Yii::app()->db->createCommand($query)->query();
  }


  public function getTokensToColleges($people) {
    $tokensToColleges = [];

    foreach($people as $p) {
      $tokensToColleges[$p['token']] = $p[$this->collegeField];
    }

    return $tokensToColleges;
  }
}
