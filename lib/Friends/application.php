<?php

/**
 * Folks external application firends implementaton
 *
 * Copyright 2008-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author  Duck <duck@obala.net>
 * @package Folks
 */
class Folks_Friends_application extends Folks_Friends
{
    /**
     * Add user to a friend list
     *
     * @param string $friend   Friend's usersame
     */
    protected function _addFriend($friend)
    {
        if (!$GLOBALS['registry']->hasMethod('addFriend', $this->_params['app'])) {
            return PEAR::raiseError(_("Unsupported"));
        }

        return $GLOBALS['registry']->callByPackage(
            $this->_params['app'],
            'addFriend',
            [$friend]
        );
    }

    /**
     * Remove user from a fiend list
     *
     * @param string $friend   Friend's usersame
     */
    public function removeFriend($friend)
    {
        if (!$GLOBALS['registry']->hasMethod('removeFriend', $this->_params['app'])) {
            return PEAR::raiseError(_("Unsupported"));
        }

        return $GLOBALS['registry']->callByPackage(
            $this->_params['app'],
            'removeFriend',
            [$friend]
        );
    }

    /**
     * Get user friends
     *
     * @return array of users
     */
    public function getFriends()
    {
        if (!$GLOBALS['registry']->hasMethod('getFriends', $this->_params['app'])) {
            return PEAR::raiseError(_("Unsupported"));
        }

        return $GLOBALS['registry']->callByPackage(
            $this->_params['app'],
            'getFriends',
            [$this->_user]
        );
    }

    /**
     * Get user blacklist
     *
     * @return array of users blacklist
     */
    public function getBlacklist()
    {
        if (!$GLOBALS['registry']->hasMethod('getBlacklist', $this->_params['app'])) {
            return PEAR::raiseError(_("Unsupported"));
        }

        return $GLOBALS['registry']->callByPackage(
            $this->_params['app'],
            'getBlacklist',
            [$this->_user]
        );
    }

    /**
     * Add user to a blacklist list
     *
     * @param string $user   Usersame
     */
    protected function _addBlacklisted($user)
    {
        if (!$GLOBALS['registry']->hasMethod('addBlacklisted', $this->_params['app'])) {
            return PEAR::raiseError(_("Unsupported"));
        }

        return $GLOBALS['registry']->callByPackage(
            $this->_params['app'],
            'addBlacklisted',
            [$user]
        );
    }

    /**
     * Add user to a blacklist list
     *
     * @param string $user   Usersame
     */
    public function removeBlacklisted($user)
    {
        if (!$GLOBALS['registry']->hasMethod('removeBlacklisted', $this->_params['app'])) {
            return PEAR::raiseError(_("Unsupported"));
        }

        return $GLOBALS['registry']->callByPackage(
            $this->_params['app'],
            'removeBlacklisted',
            [$user]
        );
    }

    /**
     * Get avaiable groups
     */
    protected function _getGroups()
    {
        if (!$GLOBALS['registry']->hasMethod('getGroups', $this->_params['app'])) {
            return [];
        }

        return $GLOBALS['registry']->callByPackage(
            $this->_params['app'],
            'getGroups'
        );
    }
}
