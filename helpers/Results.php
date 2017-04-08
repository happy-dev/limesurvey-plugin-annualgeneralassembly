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

    $survey     = SurveyDynamic::model($this->surveyId);
    $questions  = $this->getQuestions(); 

    foreach ($questions->readAll() as $question) {
      $choices          = $this->getChoices($question->qid);
      $answers          = $this->getAnswers();
      $people           = $this->getPeople($answers);
      $answers          = $this->getAnswers();// Required, as rewinding is not an option
      $tokensToColleges = $this->getTokensToColleges($people);
      $resultsByCollege = [];

      foreach($answers as $answer) {
        if (!isset($resultsByCollege[ $question->qid ])) {
          $resultsByCollege[ $question->qid ] = [];
        }
        if (!isset($resultsByCollege[ $question->qid ][ $tokensToColleges[ $answer->token ] ])) {
          $resultsByCollege[ $question->qid ][ $tokensToColleges[ $answer->token ] ] = 0;
        }
        $resultsByCollege[ $question->qid ][ $tokensToColleges[ $answer->token ] ]++;
      }
    }

    $totalAnswers           = $survey->count();
    $totalCompletedAnswers  = $survey->count('submitdate IS NOT NULL');

    print_r($resultsByCollege);

    return array(
      'totalAnswers'              => $totalAnswers,
      'totalCompletedAnswers'     => $totalCompletedAnswers,
      'resultsByCollege'          => $resultsByCollege,
    );
  }


  // Returns resolutions only of the given survey
  public function getQuestions() {
    $query      = "SELECT qid, title, question FROM {{questions}} WHERE parent_qid=0 AND sid='{$this->surveyId}' AND title LIKE 'R%' ORDER BY gid, question_order ASC";

    return  Yii::app()->db->createCommand($query)->query();
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
      $tokens[] = $answer->token;
    }

    $tokensStr  = "'". implode("','", $tokens) ."'"; 
    $query      = "SELECT token, {$this->collegeField} FROM {{tokens_$this->surveyId}} WHERE token IN ({$tokensStr})";

    return  Yii::app()->db->createCommand($query)->query();
  }


  public function getTokensToColleges($people) {
    $tokensToColleges = [];

    foreach($people as $p) {
      $tokensToColleges[$p->token] = $p->{$this->collegeField};
    }

    return $tokensToColleges;
  }
}
