<?php

/**
 * Copyright 2008-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author Duck <duck@obala.net>
 * @package Folks
 */
class Folks_Activity_Form extends Horde_Form
{
    /**
     */
    public function __construct($vars, $title, $name)
    {
        parent::__construct($vars, $title, $name);

        if ($name == 'long') {
            $this->addVariable(_("Activity"), 'activity', 'longText', true, false, null, [4]);
        } else {
            $this->addVariable(_("Activity"), 'activity', 'text', true, false, null, ['', 80]);
        }

        $this->setButtons(_("Post"));
    }

    /**
     */
    public function execute()
    {
        $message = trim(strip_tags($this->_vars->get('activity')));

        if (empty($message)) {
            return PEAR::raiseError(_("You cannot post an empty activity message."));
        }

        $filters = ['text2html', 'bbcode', 'highlightquotes', 'emoticons'];
        $filters_params = [['parselevel' => Horde_Text_Filter_Text2html::MICRO],
            [],
            [],
            []];

        if (($hasBBcode = strpos($message, '[')) !== false
                && strpos($message, '[/', $hasBBcode) !== false) {
            $filters_params[0]['parselevel'] = Horde_Text_Filter_Text2html::NOHTML;
        }

        $message = $GLOBALS['injector']->getInstance('Horde_Core_Factory_TextFilter')->filter(trim($message), $filters, $filters_params);

        $result = $GLOBALS['folks_driver']->logActivity($message, 'folks:custom');
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        if ($conf['facebook']['enabled']) {
            $message = trim(strip_tags($this->_vars->get('activity')));
            register_shutdown_function([&$this, '_facebook'], $message);
        }

        return true;
    }

    /**
     */
    public function _facebook($message)
    {
        global $conf, $prefs;

        // Check FB installation
        if (!$conf['facebook']['enabled']) {
            return true;
        }

        // Chacke FB user config
        $fbp = unserialize($prefs->getValue('facebook'));
        if (!$fbp || empty($fbp['uid'])) {
            return true;
        }

        // Load FB
        $context = ['http_client' => new Horde_Http_Client(),
            'http_request' => $GLOBALS['injector']->getInstance('Horde_Controller_Request')];
        $facebook = new Horde_Service_Facebook(
            $conf['facebook']['key'],
            $conf['facebook']['secret'],
            $context
        );

        $facebook->auth->setUser($fbp['uid'], $fbp['sid'], 0);

        try {
            $facebook->users->setStatus($message);
        } catch (Horde_Service_Facebook_Exception $e) {
            // Do noting as we are exiting
        }
    }
}
