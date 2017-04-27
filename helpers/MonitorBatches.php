<?php

/**
 * Displays a form to monitor votes in batch
 */
class MonitorBatches {
  protected $surveyId       = 0; 
  protected $href           = ''; 
  protected $lastPage       = 123456789;


  public function __construct($surveyId, $href) {
    $this->surveyId       = $surveyId;
    $this->href           = $href;

    if('POST' == $_SERVER['REQUEST_METHOD']) {
      $this->deleteBatch();
    }
  }


  // Get the useful information to output the form
  public function getFormData() {
    $query      = "SELECT startlanguage, COUNT(*) AS count, submitdate FROM {{survey_$this->surveyId}} WHERE startlanguage!='fr' GROUP BY startlanguage";

    return  array(
      'batches' => Yii::app()->db->createCommand($query)->query(),
      'href'    => $this->href,
    );
  }


  // Delete the given batch
  public function deleteBatch() {
    $query      = "DELETE FROM {{survey_$this->surveyId}} WHERE startlanguage='{$_POST['batch-name']}'";
    Yii::app()->db->createCommand($query)->query();
  }
}