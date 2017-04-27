<?php
use \ls\menu\MenuItem;

/**
 * Entry point of the plugin
 * Interface with LimeSurvey's API
 */
class AnnualGeneralMeeting extends PluginBase {
  static protected $name        = '';
  static protected $description = '';
  protected $storage            = 'DbStorage';
  protected $defaultSettings    = '{"Consommateurs": 0.2, "Salariés": 0.2, "Producteurs": 0.6}';
  

  public function __construct(PluginManager $manager, $id) {
    parent::__construct($manager, $id);

    self::$name         = $this->getProperty('name');
    self::$description  = $this->getProperty('description');

    $this->subscribe('beforeSurveySettings');
    $this->subscribe('newSurveySettings');
    $this->subscribe('beforeToolsMenuRender');
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
  public function beforeSurveySettings() {
    $event = $this->event;
    $event->set("surveysettings.{$this->id}", array(
      'name' => get_class($this),
      'settings' => array(
        'weights'=>array(
          'type'  =>'json',
          'label' => gT('Pondérations par collège'),
          'editorOptions' =>array('mode'=>'tree'),
          'help'  => gT("Renseignez les pondérations des différents collèges pour le calcul des votes en AG. Exemple : 0.2"),
          'current' => $this->get('weights', 'Survey', $event->get('survey'), $this->defaultSettings),
        ),
        'collegeSGQA' => array(
          'type'  =>'string',
          'label' => gT('SQGA de la question cachée contenant les collèges'),
          'current' => $this->get('collegeSGQA', 'Survey', $event->get('survey'), ""),
        ),
      )
     ));
  }


  public function newSurveySettings() {
    $event = $this->event;
    foreach ($event->get('settings') as $name => $value)
    {
      /* In order use survey setting, if not set, use global, if not set use default */
      $default = $event->get($name, null, null, isset($this->settings[$name]['default']) ? $this->settings[$name]['default'] : null);
      $this->set($name, $value, 'Survey', $event->get('survey'),$default);
    }
  }


  // Adds a menu within the Tools menu if the weightings were set for the survey
  public function beforeToolsMenuRender() {
    $event    = $this->getEvent();
    $menuItem = $event->get('menuItems')[0];

    if (get_class($menuItem) == get_class($this)) {// Preventing double menu on the page
      $weights  = $this->get('weights', 'Survey', $event->get('surveyId'));

      if (isset($weights) && $weights !== $this->defaultSettings) {
        $surveyId = $event->get('surveyId');

        $href = [
          'outputResults' =>  Yii::app()->createUrl(
            'admin/pluginhelper',
            array(
              'sa' => 'sidebody',// sidebody || fullpagewrapper
              'plugin' => 'AnnualGeneralMeeting',
              'method' => 'outputResults',
              'surveyId' => $surveyId
            )
          ),
          'insertVotes' =>  Yii::app()->createUrl(
            'admin/pluginhelper',
            array(
              'sa' => 'sidebody',// sidebody || fullpagewrapper
              'plugin' => 'AnnualGeneralMeeting',
              'method' => 'insertVotes',
              'surveyId' => $surveyId
            )
          ),
          'monitorBatches' =>  Yii::app()->createUrl(
            'admin/pluginhelper',
            array(
              'sa' => 'sidebody',// sidebody || fullpagewrapper
              'plugin' => 'AnnualGeneralMeeting',
              'method' => 'monitorBatches',
              'surveyId' => $surveyId
            )
          ),
        ];

        $event->append('menuItems', [
          new MenuItem(array(
            'label' => gT('Résultats AG'),
            'iconClass' => 'fa fa-magic',
            'href' => $href['outputResults'],
          )),
          new MenuItem(array(
            'label' => gT('Ajouter des votes'),
            'iconClass' => 'fa fa-plus',
            'href' => $href['insertVotes'],
          )),
          new MenuItem(array(
            'label' => gT('Gestion des votes'),
            'iconClass' => 'fa fa-eraser',
            'href' => $href['monitorBatches'],
          )),
        ]);
      }
    }
  }


  // Outputs the HTML of the results page
  public function outputResults($surveyId) {
    Yii::setPathOfAlias('AnnualGeneralMeeting', dirname(__FILE__));
    Yii::import('AnnualGeneralMeeting.helpers.Results');

    $assetsPath = Yii::app()->assetManager->publish(dirname(__FILE__));
    App()->getClientScript()->registerScriptFile($assetsPath . '/node_modules/chart.js/dist/Chart.min.js');
    App()->getClientScript()->registerScriptFile($assetsPath . '/js/result.js');
    App()->getClientScript()->registerCssFile($assetsPath . '/css/results.css');

    $settings = [
      'weights'     => $this->get('weights', 'Survey', $surveyId),
      'collegeSGQA' => $this->get('collegeSGQA', 'Survey', $surveyId),
    ];
    $Results  = new Results($surveyId, $settings);

    return $this->renderPartial('results', $Results->getResultsData(), true);
  }


  // Outputs the HTML of the "Insert Votes" page
  public function insertVotes($surveyId) {
    Yii::setPathOfAlias('AnnualGeneralMeeting', dirname(__FILE__));
    Yii::import('AnnualGeneralMeeting.helpers.InsertVotes');

    $assetsPath = Yii::app()->assetManager->publish(dirname(__FILE__));
    App()->getClientScript()->registerScriptFile($assetsPath . '/js/insertVote.js');
    App()->getClientScript()->registerCssFile($assetsPath . '/css/insertVotes.css');

    $href =  Yii::app()->createUrl(
      'admin/pluginhelper',
      array(
        'sa' => 'sidebody',// sidebody || fullpagewrapper
        'plugin' => 'AnnualGeneralMeeting',
        'method' => 'insertVotes',
        'surveyId' => $surveyId
      )
    );

    $InsertVotes  = new InsertVotes($surveyId, [
      'weights'       =>  $this->get('weights', 'Survey', $surveyId),
      'href'          =>  $href,
      'collegeSGQA'   =>  $this->get('collegeSGQA', 'Survey', $surveyId),
    ]);

    return $this->renderPartial('insertVotes', $InsertVotes->getFormData(), true);
  }


  // Outputs the HTML of the "Monitor Batches" page
  public function monitorBatches($surveyId) {
    Yii::setPathOfAlias('AnnualGeneralMeeting', dirname(__FILE__));
    Yii::import('AnnualGeneralMeeting.helpers.MonitorBatches');

    $assetsPath = Yii::app()->assetManager->publish(dirname(__FILE__));
    App()->getClientScript()->registerScriptFile($assetsPath . '/js/monitorBatches.js');
    App()->getClientScript()->registerCssFile($assetsPath . '/css/monitorBatche.css');
    
    $href =  Yii::app()->createUrl(
      'admin/pluginhelper',
      array(
        'sa' => 'sidebody',// sidebody || fullpagewrapper
        'plugin' => 'AnnualGeneralMeeting',
        'method' => 'monitorBatches',
        'surveyId' => $surveyId
      )
    );

    $MonitorBatches  = new MonitorBatches($surveyId, $href);

    return $this->renderPartial('monitorBatches', $MonitorBatches->getFormData(), true);
  }
}
