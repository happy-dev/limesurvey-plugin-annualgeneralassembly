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
    $this->weights        = json_decode($settings['weights'], true);
  }


  public function getResultsData() {
    //$survey->attributeNames();// List of attributes names, inclusing questions
    //$survey->getAttributes();// Get attributes values
    //$survey->metaData->columns;// Columns
    //$survey->search();// Data provider to Reponses grid widget
    //$totalAnswers           = $survey->count();
    //$totalCompletedAnswers  = $survey->count('submitdate IS NOT NULL');

    Yii::import('AnnualGeneralMeeting.helpers.Utils');

    $survey               = SurveyDynamic::model($this->surveyId);
    $questions            = $this->getQuestions(); 
    $subQuestions         = $this->getQuestions(true); 
    $questionsIds         = array_keys($questions);
    $choices              = $this->getMultipleChoices(implode(',', $questionsIds));
    $answers              = $this->getAnswers();
    $resultsByCollege     = $this->getResultsByCollege($answers);
    $resultsByQuestion    = $this->getResultsByQuestion($questions, $choices, $resultsByCollege);
    $resultsBySubQuestion = $this->getResultsBySubQuestion($subQuestions, $resultsByCollege);

    return array(
      'surveyId'                  => $this->surveyId,
      'questions'                 => $questions,
      'subQuestions'              => $subQuestions,
      'choices'                   => $choices,
      'resultsByCollege'          => $resultsByCollege,
      'resultsByQuestion'         => $resultsByQuestion,
      'resultsBySubQuestion'      => $resultsBySubQuestion,
    );
  }


  // Returns resolutions or subquestions of the given survey
  public function getQuestions($sub = false) {
    $s          = $sub ? '!' : '';
    $query      = "SELECT qid, gid, parent_qid, type, title, question FROM {{questions}} WHERE parent_qid{$s}=0 AND sid='{$this->surveyId}' ORDER BY gid, question_order ASC";
    $results    =  Yii::app()->db->createCommand($query)->query();
    $questions  = [];

    foreach($results as $r) {
      $questions[$r['qid']] = $r;
    }

    return $questions;
  }


  // Returns possible choices for a given question (multiple choice question) 
  public function getMultipleChoices($questionsIds) {// Questions Ids
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


  // Computing results by colleges
  public function getResultsByCollege($answers) {
    $sgqaStart        = $this->getSGQAStart();
    $resultsByCollege = [];

    foreach($answers as $answer) {
      foreach($answer as $sgqa => $code) {
        if (!Utils::nullOrEmpty($code)) {
          if (Utils::startsByOneOfThese($sgqa, $sgqaStart)) {
            if (false == strpos($sgqa, 'SQ')) {// Radiobox questions (resolutions)
              if (!isset($resultsByCollege[$sgqa])) {
                $resultsByCollege[$sgqa] = [];
              }
              if (!isset($resultsByCollege[$sgqa][$answer[$this->collegeSGQA]])) {
                $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]]           = [];
                $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]]['total']  = 0;
              }
              if (!isset($resultsByCollege[$sgqa][$answer[$this->collegeSGQA]][$code])) {
                $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]][$code] = 0;
              }
              $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]][$code]++;

              if (!Utils::nullOrEmpty($code)) {// We filter out empty answers
                $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]]['total']++;
              }
            }
            else {// Checkboxes questions (administrators election)
              $array      = explode('SQ', $sgqa);
              $parentSGQA = $array[0];

              if (!isset($resultsByCollege[$parentSGQA])) {
                $resultsByCollege[$parentSGQA] = [];
              }
              if (!isset($resultsByCollege[$parentSGQA][$answer[$this->collegeSGQA]])) {
                $resultsByCollege[$parentSGQA][$answer[$this->collegeSGQA]]           = [];
                $resultsByCollege[$parentSGQA][$answer[$this->collegeSGQA]]['total']  = 0;
              }
              if (!isset($resultsByCollege[$parentSGQA][$answer[$this->collegeSGQA]][$sgqa])) {
                $resultsByCollege[$parentSGQA][$answer[$this->collegeSGQA]][$sgqa] = [];
              }
              if (!isset($resultsByCollege[$parentSGQA][$answer[$this->collegeSGQA]][$sgqa][$code])) {
                $resultsByCollege[$parentSGQA][$answer[$this->collegeSGQA]][$sgqa][$code] = 0;
              }
              $resultsByCollege[$parentSGQA][$answer[$this->collegeSGQA]][$sgqa][$code]++;

              if (!Utils::nullOrEmpty($code)) {// We filter out empty answers
                $resultsByCollege[$parentSGQA][$answer[$this->collegeSGQA]]['total']++;
              }
            }
          }
        }
      }
    }

    return $resultsByCollege;
  }


  // Computing results by questions
  public function getResultsByQuestion($questions, $choices, $resultsByCollege) {
    $resultsByQuestion = [];

    foreach($questions as $question) {
      $resultsByQuestion[ $question['qid'] ]['total'] = 0;
      $colleges = $resultsByCollege[$this->surveyId .'X'. $question['gid'] .'X'. $question['qid']];

      foreach($colleges as $college => $codesToResults) {
        foreach($choices as $code => $answer) {
          if (!Utils::nullOrEmpty($code) && $code != 'total') {
            $percentage = Utils::percentage($codesToResults[$code], $codesToResults['total']);

            if (!isset($resultsByQuestion[ $question['qid'] ][$code])) {
              $resultsByQuestion[ $question['qid'] ][$code] = [
                'total'   => 0,
                'result'  => 0,
              ];
            }
            $resultsByQuestion[ $question['qid'] ][$code]['total'] += $codesToResults[$code];
            $resultsByQuestion[ $question['qid'] ]['total']        += $codesToResults[$code];

            if (isset($this->weights[$college])) {
              $resultsByQuestion[ $question['qid'] ][$code]['result']  += $percentage * $this->weights[$college];
            }
            else {
              die( gT("La pondération pour le collège '{$college}' n'est pas définie.") );
            }
          }
        }
      }
    }

    return $resultsByQuestion;
  }


  // Computing results by subquestions
  public function getResultsBySubQuestion($subQuestions, $resultsByCollege) {
    $resultsBySubQuestion = [];

    foreach($subQuestions as $subQuestion) {
      if (!isset($resultsBySubQuestion[$subQuestion['parent_qid']])) {
        $resultsBySubQuestion[$subQuestion['parent_qid']] = ['total' => 0];
      }
      if (!isset($resultsBySubQuestion[$subQuestion['parent_qid']][$subQuestion['qid']])) {
        $resultsBySubQuestion[$subQuestion['parent_qid']][$subQuestion['qid']] = [
          'total'   => 0,
          'result'  => 0,
        ];
      }

      $parentSGQA = $this->surveyId .'X'. $subQuestion['gid'] .'X'. $subQuestion['parent_qid'];
      $sgqa       = $parentSGQA . $subQuestion['title'];
      $colleges   = $resultsByCollege[$parentSGQA];

      foreach($colleges as $college => $sgqas) {
        $choices = $colleges[$college][$sgqa];

        $result     = isset($choices['Y']) ? $choices['Y'] : 0;
        $percentage = Utils::percentage($result, $sgqas['total']);

        $resultsBySubQuestion[$subQuestion['parent_qid']]['total']        += $result;
        $resultsBySubQuestion[$subQuestion['parent_qid']][$subQuestion['qid']]['total'] += $result;

        if (isset($this->weights[$college])) {
          $resultsBySubQuestion[$subQuestion['parent_qid']][$subQuestion['qid']]['result']  += $percentage * $this->weights[$college];
        }
        else {
          die( gT("La pondération pour le collège '{$college}' n'est pas définie.") );
        }
      }
    }

    return $resultsBySubQuestion;
  }
}
