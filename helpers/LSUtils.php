<?php

// Useful LimeSurvey PHP functions
class LSUtils {
  protected $surveyId     = 0;
  protected $collegeSGQA  = '';


  public function __construct($surveyId, $collegeSGQA, $excludedGroups) {
    $this->surveyId       = $surveyId;
    $this->collegeSGQA    = $collegeSGQA;
    $this->excludedGroups = $excludedGroups;
  }


  // Returns resolutions or subquestions of the given survey
  public function getQuestions($sub = false) {
    $s          = $sub ? '!' : '';
    $tmp        = explode('X', $this->collegeSGQA);
    $qid        = $tmp[count($tmp) - 1];
    $query      = "SELECT qid, gid, parent_qid, type, title, question FROM {{questions}} WHERE parent_qid{$s}=0 AND sid='{$this->surveyId}' AND gid NOT IN ({$this->excludedGroups}) AND qid!='{$qid}' ORDER BY gid, question_order ASC";
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
    if (in_array($question['type'], ['M', 'T']) && $question['parent_qid'] != 0) {
      return $this->surveyId .'X'. $question['gid'] .'X'. $question['parent_qid'] . $question['title'];
    }
    else {
      return $this->surveyId .'X'. $question['gid'] .'X'. $question['qid'];
    }
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
