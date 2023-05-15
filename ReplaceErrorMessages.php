<?php
/**
 * LimeSurvey plugin to replace default LimeSurvey error messages
 * php version 7.4
 *
 * @category Plugin
 * @package  LimeSurvey
 * @author   Adam Zammit <adam.zammit@acspri.org.au>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.github.com/adamzammit/ReplaceErrorMessages
 */

/**
 * LimeSurvey plugin to replace default LimeSurvey error messages
 *
 * @category Plugin
 * @package  LimeSurvey
 * @author   Adam Zammit <adam.zammit@acspri.org.au>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.github.com/adamzammit/ReplaceErrorMessages
 */
class ReplaceErrorMessages extends LimeSurvey\PluginManager\PluginBase
{
    protected $storage = 'LimeSurvey\PluginManager\DbStorage';

    static protected $description = 'Replace the default LimeSurvey error '
        . 'messages that occur due to the survey expired, session expired,'
        . ' already completed, etc';
    static protected $name = 'ReplaceErrorMessages';

    protected $setlist = [
        'noPreviewPermission' => 
            'Message to display if the user does not have permission to preview this deactivated survey',
        'sessionExpired' => 
            'Message to display if the session has expired',
        'surveyNoLongerAvailable' => 
            'Message to display if the survey is expired / no longer available',
        'surveyNotYetStarted' => 
            'Message to display if the survey is active but not available yet',
        'alreadyCompleted' => 
            'Message to display if the survey has already been completed by the respondent',
        'invalidToken' => 
            'Message to display if the token provided is invalid',
    ];

    protected $settings = [
        'ReplaceErrorMessages_surveyDoesNotExist' => [
            'type' => 'string',
            'label' => 'Message to display if survey does not exist (global, leave blank to use default message)',
            'default' => '',
        ],
         'ReplaceErrorMessages_noPreviewPermission' => [
            'type' => 'string',
            'label' => 'Message to display if the user does not have permission to preview a deactivated survey (can be overidden at the survey level, leave blank to use default message)',
            'default' => '',
        ],
         'ReplaceErrorMessages_sessionExpired' => [
            'type' => 'string',
            'label' => 'Message to display if their session has expired (can be overidden at the survey level, leave blank to use default message)',
            'default' => '',
        ],
         'ReplaceErrorMessages_surveyNoLongerAvailable' => [
            'type' => 'string',
            'label' => 'Message to display if the survey is expired / no longer available (can be overidden at the survey level, leave blank to use default message)',
            'default' => '',
        ],
         'ReplaceErrorMessages_surveyNotYetStarted' => [
            'type' => 'string',
            'label' => 'Message to display if the survey is active but not available yet (can be overidden at the survey level, leave blank to use default message)',
            'default' => '',
        ],
         'ReplaceErrorMessages_alreadyCompleted' => [
            'type' => 'string',
            'label' => 'Message to display if the survey has already been completed by the respondent (can be overidden at the survey level, leave blank to use default message)',
            'default' => '',
        ],
         'ReplaceErrorMessages_invalidToken' => [
            'type' => 'string',
            'label' => 'Message to display if the token provided is invalid (can be overidden at the survey level, leave blank to use default message)',
            'default' => '',
        ],
    ];

    /**
     * Set subscribed actions for this plugin
     *
     * @return none
     */
    public function init() 
    {
        $this->subscribe('onSurveyDenied', 'actionReplaceErrorMessage');
        $this->subscribe('newSurveySettings');
        $this->subscribe('beforeSurveySettings');
    }

    /** 
     * Apply global settings as default at survey level
     *
     * @return none
     */
    public function newSurveySettings()
    {
        $event = $this->event;
        foreach ($event->get('settings') as $name => $value) {
            /* In order use survey setting, if not set, use global, if not set use default */
            $default = $event->get($name, null, null, isset($this->settings[$name]['default']) ? $this->settings[$name]['default'] : null);
            $this->set($name, $value, 'Survey', $event->get('survey'), $default);
        }
    }  

    /**
     * This event is fired by the administration panel to gather extra settings
     * available for a survey. These settings override the global settings.
     * The plugin should return setting meta data.
     *
     * @return none
     */
    public function beforeSurveySettings()
    {
        $event = $this->event;

        $settings = [];
        foreach ($this->setlist as $key => $val) {
            $key = 'ReplaceErrorMessages_' . $key;
            $settings[$key] = [
                'type' => 'string',
                'label' => $val,
                'current' => $this->get(
                    $key, 'Survey', $event->get('survey')
                ),
            ];
        }

        $event->set(
            "surveysettings.{$this->id}", [
                'name' => get_class($this),
                'settings' => $settings, 
            ]
        );
    }


    /** 
     * Display the custom error messages instead of the default
     *
     * @return none
     */
    public function actionReplaceErrorMessage() 
    {
        if (!$this->getEvent()) {
            throw new CHttpException(403);
        }

        $reason = $this->getEvent()->get('reason');

        //error with a 404, at a global level
        if ($reason == "surveyDoesNotExist" 
            && $this->get('ReplaceErrorMessages_surveyDoesNotExist') !== ""
        ) {
            throw new CHttpException(
                404, $this->get('ReplaceErrorMessages_surveyDoesNotExist')
            );
        }

        //the rest of the errors are survey specific
        $surveyId = $this->getEvent()->get('surveyId');

        //401 error for no preview permission
        if ($reason == "noPreviewPermission" 
            && $this->_getls('ReplaceErrorMessages_noPreviewPermission', $surveyId) !== ""
        ) {
            throw new CHttpException(
                401, $this->_getls('ReplaceErrorMessages_noPreviewPermission', $surveyId)
            );
        }

        //remainder of errors use a renderer
        if ($this->_getls('ReplaceErrorMessages_' . $reason, $surveyId) !== "") {
            $aErrors  = [gT('Error')];                                     
            $aMessage = [                                                  
                $this->_getls('ReplaceErrorMessages_' . $reason, $surveyId),
            ];                                                                  
            App()->getController()->renderExitMessage(                          
                $surveyId,                                               
                'survey-notstart',                                              
                $aMessage,                                                      
                null,                                                           
                $aErrors                                                        
            );     
        }
    }

    /**
     * Get the local setting if set, if not get the global setting
     *
     * @param $setting  The setting name
     * @param $surveyId The survey id
     *
     * @return string Setting value
     */
    private function _getls($setting, $surveyId) 
    {
        $return =  $this->get($setting, 'survey', $surveyId);
        if ($return === "") {
            $return = $this->get($setting);
        }
        return $return;
    }
}
