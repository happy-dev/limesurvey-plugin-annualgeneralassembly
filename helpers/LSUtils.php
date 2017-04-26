<?php

// Useful LimeSurvey PHP functions
class LSUtils {
  protected $surveyId     = 0;


  public function __construct($surveyId) {
    $this->surveyId       = $surveyId;
  }


  // Returns resolutions or subquestions of the given survey
  public function getQuestions($sub = false) {
    $s          = $sub ? '!' : '';
    $query      = "SELECT qid, gid, parent_qid, type, title, question FROM {{questions}} WHERE parent_qid{$s}=0 AND sid='{$this->surveyId}' ORDER BY gid, question_order ASC";
    $results    =  Yii::app()->db->createCommand($query)->query();
    $questions  = [];

    foreach($results as $r) {
      $questions[$r['qid']]         = $r;
      $questions[$r['qid']]['sgqa'] = $this->getSGQA($r);
    }

    return $questions;
  }


  // Returns possible choices for a given question (multiple choice question) 
  public function getMultipleChoices($questionsIds) {// Questions Ids
    $query      = "SELECT qid, code, answer FROM {{answers}} WHERE qid IN ({$questionsIds}) ORDER BY code ASC";
    $answers    =  Yii::app()->db->createCommand($query)->query();
    $choices    = [ null => "N'a pas voté"];

    foreach($answers as $answer) {
      $choices[$answer['qid']][$answer['code']] = $answer['answer'];
    }

    return $choices;
  }


  // Get the SGQA for a given (sub)question
  public function getSGQA($question) {
    Yii::import('AnnualGeneralMeeting.helpers.Utils');

    $title = Utils::startsWith('SQ', $question['title']) ? $question['title'] : '';

    return $this->surveyId .'X'. $question['gid'] .'X'. $question['qid'] . $title;
  }


  // Collects questions SGQA codes into an array
  public function getSGQAs($questions) {
    $sgqas = [];

    foreach($questions as $question) {
      $sgqas[] = $this->getSGQA($question);
    }

    return $sgqas;
  }
}
