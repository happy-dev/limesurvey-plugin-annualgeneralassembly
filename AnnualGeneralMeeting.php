<?php
class AnnualGeneralMeeting extends PluginBase {
  protected $storage            = 'DbStorage';
  static protected $name        = 'Annual General Meeting';
  static protected $description = "Voting at Annual Assembly Meetings made easy";
  

  public function __construct(PluginManager $manager, $id) {
      parent::__construct($manager, $id);
      $this->subscribe('beforeSurveySettings');
      $this->subscribe('newSurveySettings');
  }


  /**
   * This event is fired by the administration panel to gather extra settings
   * available for a survey.
   * The plugin should return setting meta data.
   * @param PluginEvent $event
   */
  public function beforeSurveySettings()
  {
      $event = $this->event;
      $event->set("surveysettings.{$this->id}", array(
          'name' => get_class($this),
          'settings' => array(
              'weights'=>array(
                  'type'=>'json',
                  'label'=>'A json setting',
                  'editorOptions'=>array('mode'=>'tree'),
                  'help'=>'For json settings, here with \'editorOptions\'=>array(\'mode\'=>\'tree\'), . See jsoneditoronline.org',
                  'current' => $this->get('weights', 'Survey', $event->get('survey'), '{"Collège Consommateur": 1, "Collège Salariés": 2, "Collège Producteurs": 3, "Collège Porteurs": 2}'),
              ),
          )
       ));
  }


  public function newSurveySettings()
  {
      $event = $this->event;
      foreach ($event->get('settings') as $name => $value)
      {
          /* In order use survey setting, if not set, use global, if not set use default */
          $default=$event->get($name,null,null,isset($this->settings[$name]['default'])?$this->settings[$name]['default']:NULL);
          $this->set($name, $value, 'Survey', $event->get('survey'),$default);
      }
  }
}
