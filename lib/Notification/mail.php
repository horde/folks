<?php
/**
 * Folks Notification Class.
 *
 * $Id: Driver.php 1400 2009-03-09 09:58:40Z duck $
 *
 * Copyright Obala d.o.o. (www.obala.si)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Folks
 */
class Folks_Notification_mail extends Folks_Notification {

    /**
     * Returns method human name
     */
    public function getName()
    {
        return _("E-mail");
    }

    /**
     * Checks if a driver is available for a certain notification type
     *
     * @param string $type Notification type
     *
     * @return boolean
     */
    public function isAvailable($type)
    {
        if ($type == 'friends') {
            return $GLOBALS['registry']->hasMethod('users/getFriends');
        }

        return true;
    }

    /**
     * Notify user
     *
     * @param mixed  $user        User or array of users to send notification to
     * @param string $subject     Subject of message
     * @param string $body        Body of message
     * @param array  $attachments Attached files
     *
     * @return true on succes, PEAR_Error on failure
     */
    public function notify($user, $subject, $body, $attachments = array())
    {
        if (empty($user)) {
            return true;
        }

        list($mail_driver, $mail_params) = Horde::getMailerConfig();
        require_once FOLKS_BASE . '/lib/version.php';

        $mail = new Horde_Mime_Mail($subject, $body, null,
                                    $this->_params['from_addr'],
                                    Horde_Nls::getCharset());

        $mail->addHeader('User-Agent', 'Folks ' . FOLKS_VERSION);
        $mail->addHeader('X-Originating-IP', $_SERVER['REMOTE_ADDR']);
        $mail->addHeader('X-Remote-Browser', $_SERVER['HTTP_USER_AGENT']);

        foreach ($attachments as $file) {
            if (file_exists($file)) {
                $mail->addAttachment($file, null, null, Horde_Nls::getCharset());
            }
        }

        if (is_string($user)) {
            $user = array($user);
        }

        foreach ($user as $recipent) {
            $to = $this->_getUserFromAddr($recipent);
            if (empty($to)) {
                continue;
            }
            $mail->addHeader('To', $to, Horde_Nls::getCharset(), true);
            $mail->send($mail_driver, $mail_params);
        }

        return true;
    }

    /**
     * Notify user's friends
     *
     * @param mixed  $user        User or array of users to send notification to
     * @param string $subject     Subject of message
     * @param string $body        Body of message
     * @param array  $attachments Attached files
     *
     * @return true on succes, PEAR_Error on failure
     */
    public function notifyFriends($user, $subject, $body, $attachments = array())
    {
        $friends = $GLOBALS['registry']->call('users/getFriends');
        if ($friends instanceof PEAR_Error) {
            return $friends;
        }

        return $this->notify($friends, $subject, $body, $attachments);
    }
}
