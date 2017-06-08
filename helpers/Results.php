<?php

/**
 * Gathers and compute the appropriate results
 */
class Results {
  protected $surveyId     = 0;
  protected $collegeSGQA  = '';
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
    Yii::import('AnnualGeneralMeeting.helpers.LSUtils');

    $LSUtils              = new LSUtils($this->surveyId, $this->collegeSGQA);
    $survey               = SurveyDynamic::model($this->surveyId);
    $questions            = $LSUtils->getQuestions(); 
    $subQuestions         = $LSUtils->getQuestions(true); 
    $questionsIds         = array_keys($questions);
    $choices              = $LSUtils->getMultipleChoices(implode(',', $questionsIds));
    $answers              = $this->getAnswers();
    $resultsByCollege     = $this->getResultsByCollege($answers);
    $resultsByQuestion    = $this->getResultsByQuestion($questions, $choices, $resultsByCollege);
    $countsByCollege      = $this->getRespondentsCount($resultsByCollege);
    $resultsBySubQuestion = $this->getResultsBySubQuestion($subQuestions, $resultsByCollege, $countsByCollege);

    return array(
      'surveyId'                  => $this->surveyId,
      'collegeSGQA'               => $this->collegeSGQA,
      'questions'                 => $questions,
      'subQuestions'              => $subQuestions,
      'choices'                   => $choices,
      'countsByCollege'           => $countsByCollege,
      'resultsByCollege'          => $resultsByCollege,
      'resultsByQuestion'         => $resultsByQuestion,
      'resultsBySubQuestion'      => $resultsBySubQuestion,
    );
  }


  // Returns answers for a given survey
  public function getAnswers() {
    $query      = "SELECT * FROM {{survey_$this->surveyId}} WHERE `submitdate` IS NOT NULL";

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
        if (!Utils::nullOrEmpty($code) && !Utils::nullOrEmpty($answer[$this->collegeSGQA])) {
          if (Utils::startsByOneOfThese($sgqa, $sgqaStart)) {
            if (false == strpos($sgqa, 'SQ')) {// Radiobox questions (resolutions)
              if (!isset($resultsByCollege[$sgqa])) {
                $resultsByCollege[$sgqa] = [];
              }
              if (!isset($resultsByCollege[$sgqa][$answer[$this->collegeSGQA]])) {
                $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]]              = [];
                $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]]['total']     = 0;
                $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]]['adminVote'] = false;
                $resultsByCollege[$sgqa][$answer[$this->collegeSGQA]]['college']   = $answer[$this->collegeSGQA];
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
                $resultsByCollege[$parentSGQA][$answer[$this->collegeSGQA]]['adminVote']= true;
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
        $codes = isset($choices[$question['qid']]) ? $choices[$question['qid']] : null;

        if (isset($codes)) {
          foreach($codes as $code => $answer) {
            if (isset($codesToResults[$code]) && $code != 'total') {
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
    }

    return $resultsByQuestion;
  }


  // Computing results by subquestions
  public function getResultsBySubQuestion($subQuestions, $resultsByCollege, $countsByCollege) {
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
        if (isset($colleges[$college][$sgqa]) && isset($this->weights[$college])) {
          $choices = $colleges[$college][$sgqa];

          $result     = isset($choices['Y']) ? $choices['Y'] : 0;
          $percentage = Utils::percentage($result, $countsByCollege[$college]);

          $resultsBySubQuestion[$subQuestion['parent_qid']]['total']        += $result;
          $resultsBySubQuestion[$subQuestion['parent_qid']][$subQuestion['qid']]['total'] += $result;
          $resultsBySubQuestion[$subQuestion['parent_qid']][$subQuestion['qid']]['result']  += $percentage * $this->weights[$college];
        }
      }
    }

    return $resultsBySubQuestion;
  }


  // Checks which number of respondents seems to be the most frequent comparing 
  // total number of answers, and then returns that number
  public function getRespondentsCount($resultsByCollege) {
    $countsDistribution = [];
    $countsByCollege    = [];

    // Segmenting counts by college
    foreach($resultsByCollege as $parentSGQA => $colleges) {
      foreach($colleges as $key => $college) {
        if ($college['adminVote'] == false) {// Administrator election
          if ($key != 'adminVote') {
            // Counting the different occurences of each count (I know...)
            if (!isset($countsDistribution[$college['college']][$college['total']])) {
              $countsDistribution[$college['college']][$college['total']] = [
                'total' => $college['total'],
                'count' => 0,
              ];
            }

            $countsDistribution[$college['college']][$college['total']]['count']++;
          }
        }
      }
    }

    // Finding the most frequent one
    foreach($countsDistribution as $college => $digests) {
      $mostFrequentCount  = 0;
      $biggest            = 0;

      foreach($digests as $total => $digest) {
        if ($digest['count'] > $biggest) {
          $biggest            = $digest['count'];
          $mostFrequentCount  = $digest['total'];
        }
      }

      $countsByCollege[$college] = $mostFrequentCount;
    }

    return $countsByCollege;
  }
}
