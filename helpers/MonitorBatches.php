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
  //*** Changed by Nathanaël Drouard ***/
  // add $collegeSGQA to show college column
  public function getFormData($collegeSGQA) {
    $query      = "SELECT startlanguage, $collegeSGQA AS college, COUNT(*) AS count, submitdate FROM {{survey_$this->surveyId}} WHERE startlanguage!='fr' GROUP BY startlanguage";

    return  array(
      'batches' => Yii::app()->db->createCommand($query)->query(),
      'href'    => $this->href,
    );
  }


  // Delete the given batch
  public function deleteBatch() {
    //*** Changed by Nathanaël Drouard  :  
    //    Fix mysql_real_escape_string bug : do not work with LS3

    $name = Yii::app()->db->quoteValue($_POST['batch-name']);
    $query      = "DELETE FROM {{survey_$this->surveyId}} WHERE startlanguage=$name";
    Yii::app()->db->createCommand($query)->query();
  }
}
