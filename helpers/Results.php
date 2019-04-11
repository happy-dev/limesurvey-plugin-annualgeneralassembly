<?php

/**
 * Gathers and compute the appropriate results
 */
class Results {
  protected $surveyId     = 0;
  protected $collegeSGQA  = '';
  protected $weights      = null;

  protected $countsByCollege = [];
  

  public function __construct($surveyId, $settings) {
    $this->surveyId       = $surveyId;
    $this->collegeSGQA    = $settings['collegeSGQA'];
    $this->weights        = json_decode($settings['weights'], true);
    $this->excludedGroups = $settings['excludedGroups'];
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

    $LSUtils              = new LSUtils($this->surveyId, $this->collegeSGQA, $this->excludedGroups);
    $survey               = SurveyDynamic::model($this->surveyId);
    $questions            = $LSUtils->getQuestions(); 
    $subQuestions         = $LSUtils->getQuestions(true); 
    $questionsIds         = array_keys($questions);
    $choices              = $LSUtils->getMultipleChoices(implode(',', $questionsIds));
    $answers              = $this->getAnswers();
    $resultsByCollege     = $this->getResultsByCollege($answers);
    $resultsByQuestion    = $this->getResultsByQuestion($questions, $choices, $resultsByCollege);
    //$countsByCollege      = $this->getRespondentsCount($resultsByCollege);
    $resultsBySubQuestion = $this->getResultsBySubQuestion($subQuestions, $resultsByCollege, $this->countsByCollege);

    return array(
      'surveyId'                  => $this->surveyId,
      'collegeSGQA'               => $this->collegeSGQA,
      'questions'                 => $questions,
      'subQuestions'              => $subQuestions,
      'choices'                   => $choices,
      'countsByCollege'           => $this->countsByCollege,
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
    $query      = "SELECT gid FROM {{groups}} WHERE sid='{$this->surveyId}'"; // AND group_name LIKE 'Résolutions%'";
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
      $theCollege = $answer[$this->collegeSGQA];
      $id = $answer['id'];


      if(!isset($this->countsByCollege[$theCollege])) {
        $this->countsByCollege[$theCollege] = 0;
      }
      $hasVoted = false;

      $this->countsByCollege[$theCollege];
      foreach($answer as $sgqa => $code) {
        if (!Utils::nullOrEmpty($code) && !Utils::nullOrEmpty($theCollege)) {
          
          if(false !== strpos($sgqa, 'X')) {
            list($s, $g, $q) = explode('X', $sgqa);

            if(!$hasVoted 
            && false === strpos($this->excludedGroups, $g)
            && $code != $theCollege
            ) {
              $this->countsByCollege[$theCollege]++;
              $hasVoted = true;
            }
          }
          
          
          if (Utils::startsByOneOfThese($sgqa, $sgqaStart)) {
            if (false === strpos($sgqa, 'SQ')
            && false === strpos($sgqa, 'A')) {// Radiobox questions (resolutions)
              if (!isset($resultsByCollege[$sgqa])) {
                $resultsByCollege[$sgqa] = [];
              }
              if (!isset($resultsByCollege[$sgqa][$theCollege])) {
                $resultsByCollege[$sgqa][$theCollege]              = [];
                $resultsByCollege[$sgqa][$theCollege]['total']     = 0;
                $resultsByCollege[$sgqa][$theCollege]['adminVote'] = false;
                $resultsByCollege[$sgqa][$theCollege]['college']   = $theCollege;
              }
              if (!isset($resultsByCollege[$sgqa][$theCollege][$code])) {
                $resultsByCollege[$sgqa][$theCollege][$code] = 0;
              }
              $resultsByCollege[$sgqa][$theCollege][$code]++;

              if (!Utils::nullOrEmpty($code)) {// We filter out empty answers
                $resultsByCollege[$sgqa][$theCollege]['total']++;
              }
            }
            else {// Checkboxes questions (administrators election)
              if (false !== strpos($sgqa, 'SQ'))
                $array      = explode('SQ', $sgqa);
              else
                $array      = explode('A', $sgqa);
              $parentSGQA = $array[0];

              if (!isset($resultsByCollege[$parentSGQA])) {
                $resultsByCollege[$parentSGQA] = [];
              }
              if (!isset($resultsByCollege[$parentSGQA][$theCollege])) {
                $resultsByCollege[$parentSGQA][$theCollege]           = [];
                $resultsByCollege[$parentSGQA][$theCollege]['total']  = 0;
                $resultsByCollege[$parentSGQA][$theCollege]['adminVote']= true;
              }
              if (!isset($resultsByCollege[$parentSGQA][$theCollege][$sgqa])) {
                $resultsByCollege[$parentSGQA][$theCollege][$sgqa] = [];
              }
              if (!isset($resultsByCollege[$parentSGQA][$theCollege][$sgqa][$code])) {
                $resultsByCollege[$parentSGQA][$theCollege][$sgqa][$code] = 0;
              }
              $resultsByCollege[$parentSGQA][$theCollege][$sgqa][$code]++;


              

              if (!Utils::nullOrEmpty($code)) {// We filter out empty answers
                $resultsByCollege[$parentSGQA][$theCollege]['total']++;
              }
            }
            
          }
        }
      }
    }
    //print_r($resultsByCollege);
    return $resultsByCollege;
  }


  // Computing results by questions
  public function getResultsByQuestion($questions, $choices, $resultsByCollege) {
    $resultsByQuestion = [];

    foreach($questions as $question) {
      $qid = $question['qid'];

      $resultsByQuestion[$qid]['total'] = 0;
      $colleges = $resultsByCollege[$this->surveyId .'X'. $question['gid'] .'X'. $question['qid']];

      foreach($colleges as $college => $codesToResults) {
        $codes = isset($choices[$qid]) ? $choices[$qid] : null;

        if (isset($codes)) {
          foreach($codes as $code => $answer) {
            if (isset($codesToResults[$code]) && $code != 'total') {
              $percentage = Utils::percentage($codesToResults[$code], $codesToResults['total']);

              if (!isset($resultsByQuestion[$qid][$code])) {
                $resultsByQuestion[ $qid ][$code] = [
                  'total'   => 0,
                  'result'  => 0,
                ];
              }
              $resultsByQuestion[$qid][$code]['total'] += $codesToResults[$code];
              $resultsByQuestion[$qid]['total']        += $codesToResults[$code];

              if (isset($this->weights[$college])) {
                $resultsByQuestion[ $qid ][$code]['result']  += $percentage * $this->weights[$college];
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
