<?php
class AnnualGeneralMeeting extends PluginBase {
  protected $storage            = 'DbStorage';
  static protected $name        = '';
  static protected $description = '';
  

  public function __construct(PluginManager $manager, $id) {
    parent::__construct($manager, $id);

    self::$name         = $this->getProperty('name');
    self::$description  = $this->getProperty('description');

    $this->subscribe('beforeSurveySettings');
    $this->subscribe('newSurveySettings');
  }


  public function getProperty($key) {
    switch($key) {
      case 'name':
        return gT("Annual General Meeting");
        break;

      case 'description':
        return gT("Voting at Annual Assembly Meetings made easy");
        break;

      default:
        return $key . ' is not a valid key';
    }
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
                  'current' => $this->get('weights', 'Survey', $event->get('survey'), '{"Consommateurs": 1, "Salariés": 2, "Producteurs": 3, "Porteurs": 4}'),
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
